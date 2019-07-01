<?php

namespace app\home\controller;

use think\Lang;
use think\Validate;

class Goods extends BaseGoods {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/goods.lang.php');
    }

    /**
     * 单个商品信息页
     */
    public function index() {
        $goods_id = intval(input('param.goods_id'));

        // 商品详细信息
        $model_goods = model('goods');
        $goods_detail = $model_goods->getGoodsDetail($goods_id);
        $goods_info = $goods_detail['goods_info'];

        if (empty($goods_info)) {
            $this->success(lang('goods_index_no_goods'));
        }
        // 获取销量 BEGIN
        $rs = $model_goods->getGoodsList(array('goods_commonid' => $goods_info['goods_commonid']));
        $count = 0;
        foreach ($rs as $v) {
            $count += $v['goods_salenum'];
        }
        $goods_info['goods_salenum'] = $count;
        // 获取销量 END
        $this->getStoreInfo($goods_info['store_id']);

        // 看了又看（同分类本店随机商品）
        $goods_rand_list = model('goods')->getGoodsGcStoreRandList($goods_info['gc_id_1'], $goods_info['store_id'], $goods_info['goods_id'], 2);

        $this->assign('goods_rand_list', $goods_rand_list);

        $this->assign('spec_list', $goods_detail['spec_list']);
        $this->assign('spec_image', $goods_detail['spec_image']);
        $this->assign('goods_image', $goods_detail['goods_image']);
        $this->assign('mansong_info', $goods_detail['mansong_info']);
        $this->assign('gift_array', $goods_detail['gift_array']);

        // 生成缓存的键值
        $hash_key = $goods_info['goods_id'];
        $_cache = rcache($hash_key, 'product');
        if (empty($_cache)) {
            // 查询SNS中该商品的信息
            $snsgoodsinfo = Model('snsgoods')->getSNSGoodsInfo(array('snsgoods_goodsid' => $goods_info['goods_id']), 'snsgoods_likenum,snsgoods_sharenum');
            $data = array();
            $data['likenum'] = $snsgoodsinfo['snsgoods_likenum'];
            $data['sharenum'] = $snsgoodsinfo['snsgoods_sharenum'];
            // 缓存商品信息
            wcache($hash_key, $data, 'product');
        }
        $goods_info = array_merge($goods_info, $_cache);

        $inform_switch = true;
        // 检测商品是否下架,检查是否为店主本人
        if ($goods_info['goods_state'] != 1 || $goods_info['goods_verify'] != 1 || $goods_info['store_id'] == session('store_id')) {
            $inform_switch = false;
        }
        $this->assign('inform_switch', $inform_switch);

        // 如果使用售卖区域
        if ($goods_info['transport_id'] > 0) {
            // 取得三种运送方式默认运费
            $model_transport = Model('transport');
            $transport = $model_transport->getExtendList(array('transport_id' => $goods_info['transport_id'],'is_default'=>1));
            if (!empty($transport) && is_array($transport)) {
                foreach ($transport as $v) {
                    $goods_info["transport_price"] = $v['sprice'];
                }
            }
        }
       // halt($goods_info);
        $this->assign('goods', $goods_info);


        //抢购商品是否开始
        $IsHaveBuy = 0;
        if (session('member_id')) {
            $buyer_id = session('member_id');
            $promotion_type = isset($goods_info["promotion_type"]) ? $goods_info["promotion_type"] : '';
            if ($promotion_type == 'groupbuy') {
                //检测是否限购数量
                $upper_limit = $goods_info["upper_limit"];
                if ($upper_limit > 0) {
                    //查询些会员的订单中，是否已买过了
                    $model_order = Model('order');
                    //取商品列表
                    $order_goods_list = $model_order->getOrderGoodsList(array('goods_id' => $goods_id, 'buyer_id' => $buyer_id, 'goods_type' => 2));
                    if ($order_goods_list) {
                        //取得上次购买的活动编号(防一个商品参加多次团购活动的问题)
                        $promotions_id = $order_goods_list[0]["promotions_id"];
                        //用此编号取数据，检测是否这次活动的订单商品。
                        $model_groupbuy = Model('groupbuy');
                        $groupbuy_info = $model_groupbuy->getGroupbuyInfo(array('groupbuy_id' => $promotions_id));
                        if ($groupbuy_info) {
                            $IsHaveBuy = 1;
                        } else {
                            $IsHaveBuy = 0;
                        }
                    }
                }
            }
        }
        $this->assign('IsHaveBuy', $IsHaveBuy);
        //end

        $model_plate = Model('storeplate');
        // 顶部关联版式
        if ($goods_info['plateid_top'] > 0) {
            $plate_top = $model_plate->getStorePlateInfoByID($goods_info['plateid_top']);
            $this->assign('plate_top', $plate_top);
        }
        // 底部关联版式
        if ($goods_info['plateid_bottom'] > 0) {
            $plate_bottom = $model_plate->getStorePlateInfoByID($goods_info['plateid_bottom']);
            $this->assign('plate_bottom', $plate_bottom);
        }
        $this->assign('store_id', $goods_info['store_id']);

        //推荐商品 v3-b12
        $goods_commend_list = $model_goods->getGoodsOnlineList(array('store_id' => $goods_info['store_id'], 'goods_commend' => 1), 'goods_id,goods_name,goods_jingle,goods_image,store_id,goods_price', 0, 'rand()', 5, 'goods_commonid');
        $this->assign('goods_commend', $goods_commend_list);


        // 当前位置导航
        $nav_link_list = Model('goodsclass')->getGoodsClassNav($goods_info['gc_id'], 0);
        $nav_link_list[] = array('title' => $goods_info['goods_name']);
        $this->assign('nav_link_list', $nav_link_list);

        //评价信息
        $goods_evaluate_info = Model('evaluategoods')->getEvaluateGoodsInfoByGoodsID($goods_id);
        $this->assign('goods_evaluate_info', $goods_evaluate_info);

        //SEO 设置
        $seo_param = array();
        $seo_param['name'] = $goods_info['goods_name'];
        $seo_param['key'] = isset($goods_info['goods_keywords'])?$goods_info['goods_keywords']:'';
        $seo_param['description'] = isset($goods_info['goods_description'])?$goods_info['goods_description']:'';
        $this->_assign_seo(Model('seo')->type('product')->param($seo_param)->show());

        return $this->fetch($this->template_dir . 'goods');
    }

    /**
     * 记录浏览历史
     */
    public function addbrowse() {
        $goods_id = intval(input('param.gid'));
        Model('goodsbrowse')->addViewedGoods($goods_id, session('member_id'), session('store_id'));
        exit();
    }

    /**
     * 商品评论
     */
    public function comments() {
        $goods_id = intval(input('param.goods_id'));
        $type = input('param.type');
        $this->_get_comments($goods_id, $type, 1);
        echo $this->fetch($this->template_dir . 'goods_comments');
    }

    /**
     * 商品评价详细页
     */
    public function comments_list() {
        $goods_id = intval(input('param.goods_id'));

        // 商品详细信息
        $model_goods = Model('goods');
        $goods_info = $model_goods->getGoodsInfoByID($goods_id, '*');
        // 验证商品是否存在
        if (empty($goods_info)) {
            $this->error(lang('goods_index_no_goods'), '', 'html', 'error');
        }
        $this->assign('goods', $goods_info);

        $this->getStoreInfo($goods_info['store_id']);

        // 当前位置导航
        $nav_link_list = Model('goodsclass')->getGoodsClassNav($goods_info['gc_id'], 0);
        $nav_link_list[] = array('title' => $goods_info['goods_name'], 'link' => url('/Home/goods/index', ['goods_id' => $goods_id]));
        $nav_link_list[] = array('title' => '商品评价');
        $this->assign('nav_link_list', $nav_link_list);

        //评价信息
        $goods_evaluate_info = Model('evaluategoods')->getEvaluateGoodsInfoByGoodsID($goods_id);
        $this->assign('goods_evaluate_info', $goods_evaluate_info);

        //SEO 设置
        $seo_param = array();
        $seo_param['name'] = $goods_info['goods_name'];
        $seo_param['key'] = isset($goods_info['goods_keywords'])?$goods_info['goods_keywords']:'';
        $seo_param['description'] = isset($goods_info['goods_description'])?$goods_info['goods_description']:'';
        $this->_assign_seo(Model('seo')->type('product')->param($seo_param)->show());

        $this->_get_comments($goods_id, input('param.type'), 20);

        return $this->fetch($this->template_dir . 'comments_list');
    }

    private function _get_comments($goods_id, $type, $page) {
        $condition = array();
        $condition['geval_goodsid'] = $goods_id;
        switch ($type) {
            case '1':
                $condition['geval_scores'] = array('in', '5,4');
                $this->assign('type', '1');
                break;
            case '2':
                $condition['geval_scores'] = array('in', '3,2');
                $this->assign('type', '2');
                break;
            case '3':
                $condition['geval_scores'] = array('in', '1');
                $this->assign('type', '3');
                break;
        }

        //查询商品评分信息
        $model_evaluate_goods = Model("evaluategoods");
        $goodsevallist = $model_evaluate_goods->getEvaluateGoodsList($condition, $page);
        $this->assign('goodsevallist', $goodsevallist);
        $this->assign('show_page', $model_evaluate_goods->page_info->render());
    }

    /**
     * 销售记录
     */
    public function salelog() {
        $goods_id = intval(input('param.goods_id'));
        $vr = intval('param.vr');
        if ($vr) {
            $model_order = Model('vrorder');
            $sales = $model_order->getOrderAndOrderGoodsSalesRecordList(array('goods_id' => $goods_id), '*', 10);
        } else {
            $model_order = Model('order');
            $sales = $model_order->getOrderAndOrderGoodsSalesRecordList(array('order_goods.goods_id' => $goods_id), 'order_goods.*, order.buyer_name, order.add_time', 10);
        }
        $this->assign('show_page', $model_order->page_info->render());
        $this->assign('sales', $sales);

        $this->assign('order_type', array(2 => '抢', 3 => '折', '4' => '套装'));
        echo $this->fetch($this->template_dir . 'goods_salelog');
    }

    /**
     * 产品咨询
     */
    public function consulting() {
        $goods_id = intval(input('param.goods_id'));
        if ($goods_id <= 0) {
            $this->error(lang('wrong_argument'), '', 'html', 'error');
        }

        //得到商品咨询信息
        $model_consult = Model('consult');
        $where = array();
        $where['goods_id'] = $goods_id;

        $ctid = intval(input('param.ctid'));
        if ($ctid > 0) {
            $where['ct_id'] = $ctid;
        }
        $consult_list = $model_consult->getConsultList($where, '*', '10');
        $this->assign('consult_list', $consult_list);

        // 咨询类型
        $consult_type = rkcache('consult_type', true);
        $this->assign('consult_type', $consult_type);

        $this->assign('consult_able', $this->checkConsultAble());
        echo $this->fetch($this->template_dir . 'goods_consulting');
    }

    /**
     * 产品咨询
     */
    public function consulting_list() {

        $this->assign('hidden_nctoolbar', 1);
        $goods_id = intval(input('param.goods_id'));
        if ($goods_id <= 0) {
            $this->error(lang('wrong_argument'));
        }

        // 商品详细信息
        $model_goods = Model('goods');
        $goods_info = $model_goods->getGoodsInfoByID($goods_id, '*');
        // 验证商品是否存在
        if (empty($goods_info)) {
            $this->error(lang('goods_index_no_goods'));
        }
        $this->assign('goods', $goods_info);

        $this->getStoreInfo($goods_info['store_id']);

        // 当前位置导航
        $nav_link_list = Model('goodsclass')->getGoodsClassNav($goods_info['gc_id'], 0);
        $nav_link_list[] = array('title' => $goods_info['goods_name'], 'link' => url('/Home/goods/index', ['goods_id' => $goods_id]));
        $nav_link_list[] = array('title' => '商品咨询');
        $this->assign('nav_link_list', $nav_link_list);

        //得到商品咨询信息
        $model_consult = Model('consult');
        $where = array();
        $where['goods_id'] = $goods_id;
        if (intval(input('param.ctid')) > 0) {
            $where['ct_id'] = intval(input('param.ctid'));
        }
        $consult_list = $model_consult->getConsultList($where, '*');
        $this->assign('consult_list', $consult_list);
        $this->assign('show_page', $model_consult->page_info->render());

        // 咨询类型
        $consult_type = rkcache('consult_type', true);
        $this->assign('consult_type', $consult_type);

        //SEO 设置
        $seo_param = array ();
        $seo_param['name'] = $goods_info['goods_name'];
        $seo_param['key'] = isset($goods_info['goods_keywords'])?$goods_info['goods_keywords']:'';
        $seo_param['description'] = isset($goods_info['goods_description'])?$goods_info['goods_description']:'';
        $this->_assign_seo(Model('seo')->type('product')->param($seo_param)->show());

        $this->assign('consult_able', $this->checkConsultAble($goods_info['store_id']));
        return $this->fetch($this->template_dir . 'consulting_list');
    }

    private function checkConsultAble($store_id = 0) {
        //检查是否为店主本身
        $store_self = false;
        if (session('store_id')) {
            if (($store_id == 0 && intval(input('param.store_id')) == session('store_id')) || ($store_id != 0 && $store_id == session('store_id'))) {
                $store_self = true;
            }
        }
        //查询会员信息
        $member_info = array();
        $member_model = Model('member');
        if (session('member_id'))
            $member_info = $member_model->getMemberInfoByID(session('member_id'), 'is_allowtalk');
        //检查是否可以评论
        $consult_able = true;
        if ((!config('guest_comment') && !session('member_id') ) || $store_self == true || (session('member_id') > 0 && $member_info['is_allowtalk'] == 0)) {
            $consult_able = false;
        }
        return $consult_able;
    }

    /**
     * 商品咨询添加
     */
    public function save_consult() {
        //检查是否可以评论
        if (!config('guest_comment') && !session('member_id')) {
            showDialog(lang('goods_index_goods_noallow'));
        }
        $goods_id = intval($_POST['goods_id']);
        if ($goods_id <= 0) {
            showDialog(lang('wrong_argument'));
        }
        //咨询内容的非空验证
        if (trim($_POST['goods_content']) == "") {
            showDialog(lang('goods_index_input_consult'));
        }
        //表单验证
        $ob_validate = new Validate();
        $data = [
            'goods_content' => $_POST['goods_content']
        ];
        $rule = [
            ['goods_content', 'require', '咨询内容不能为空'],
        ];
        $res = $ob_validate->check($data, $rule);
        if (!$res) {
            showDialog($ob_validate->getError());
        }

        if (session('member_id')) {
            //查询会员信息
            $member_model = Model('member');
            $member_info = $member_model->getMemberInfo(array('member_id' => session('member_id')));
            if (empty($member_info) || $member_info['is_allowtalk'] == 0) {
                showDialog(lang('goods_index_goods_noallow'));
            }
        }
        //判断商品编号的存在性和合法性
        $goods = Model('goods');
        $goods_info = $goods->getGoodsInfoByID($goods_id, 'goods_name,store_id');
        if (empty($goods_info)) {
            showDialog(lang('goods_index_goods_not_exists'));
        }
        //判断是否是店主本人
        if (session('store_id') && $goods_info['store_id'] == session('store_id')) {
            showDialog(lang('goods_index_consult_store_error'));
        }
        //检查店铺状态
        $store_model = Model('store');
        $store_info = $store_model->getStoreInfoByID($goods_info['store_id']);
        if ($store_info['store_state'] == '0' || intval($store_info['store_state']) == '2' || (intval($store_info['store_end_time']) != 0 && $store_info['store_end_time'] <= time())) {
            showDialog(lang('goods_index_goods_store_closed'));
        }
        //接收数据并保存
        $input = array();
        $input['goods_id'] = $goods_id;
        $input['goods_name'] = $goods_info['goods_name'];
        $input['member_id'] = intval(session('member_id')) > 0 ? session('member_id') : 0;
        $input['member_name'] = session('member_name') ? session('member_name') : '';
        $input['store_id'] = $store_info['store_id'];
        $input['store_name'] = $store_info['store_name'];
        $input['ct_id'] = isset($_POST['consult_type_id'])?intval($_POST['consult_type_id']):'1';
        $input['consult_addtime'] = TIMESTAMP;
        $input['consult_content'] = $_POST['goods_content'];
        $input['isanonymous'] = isset($_POST['hide_name']) ? 1 : 0;
        $consult_model = Model('consult');
        if ($consult_model->addConsult($input)) {
            showDialog(lang('goods_index_consult_success'), 'reload', 'succ');
        } else {
            showDialog(lang('goods_index_consult_fail'));
        }
    }

    /**
     * 异步显示优惠套装/推荐组合
     */
    public function get_bundling() {
        $goods_id = intval(input('param.goods_id'));
        if ($goods_id <= 0) {
            exit();
        }
        $model_goods = Model('goods');
        $goods_info = $model_goods->getGoodsOnlineInfoByID($goods_id);
        if (empty($goods_info)) {
            exit();
        }

        // 优惠套装
        $array = Model('pbundling')->getBundlingCacheByGoodsId($goods_id);
        if (!empty($array)) {
            $this->assign('bundling_array', unserialize($array['bundling_array']));
            $this->assign('b_goods_array', unserialize($array['b_goods_array']));
        }

        // 推荐组合
        if (!empty($goods_info) && $model_goods->checkIsGeneral($goods_info)) {
            $array = Model('goodscombo')->getGoodsComboCacheByGoodsId($goods_id);
            $this->assign('goods_info', $goods_info);
            $this->assign('gcombo_list', unserialize($array['gcombo_list']));
        }

        echo $this->fetch($this->template_dir . 'goods_bundling');
    }

    /**
     * 商品详细页运费显示
     *
     * @return unknown
     */
    public function calc() {
        if (!is_numeric(input('param.area_id')) || !is_numeric(input('param.tid')))
            return false;
        $freight_total = Model('transport')->calc_transport(intval(input('param.tid')), intval(input('param.area_id')));
        if ($freight_total > 0) {
            if (input('param.myf') > 0) {
                if ($freight_total >= input('param.myf')) {
                    $freight_total = '免运费';
                } else {
                    $freight_total = '运费：' . $freight_total . ' 元，店铺满 ' . input('param.myf') . ' 元 免运费';
                }
            } else {
                $freight_total = '运费：' . $freight_total . ' 元';
            }
        } else {
            if ($freight_total !== false) {
                $freight_total = '免运费';
            }
        }
        echo input('param.callback') . '(' . json_encode(array('total' => $freight_total)) . ')';
    }

    /**
     * 到货通知
     */
    public function arrival_notice() {
        if (!session('is_login')) {
            $this->error(lang('wrong_argument'));
        }
        $member_info = Model('member')->getMemberInfoByID(session('member_id'), 'member_email,member_mobile');
        $this->assign('member_info', $member_info);

        return $this->fetch($this->template_dir . 'arrival_notice.submit');
    }

    /**
     * 到货通知表单
     */
    public function arrival_notice_submit() {
        $type = intval($_POST['type']) == 2 ? 2 : 1;
        $goods_id = intval($_POST['goods_id']);
        if ($goods_id <= 0) {
            showDialog(lang('wrong_argument'), 'reload');
        }
        // 验证商品数是否充足
        $goods_info = Model('goods')->getGoodsInfoByID($goods_id, 'goods_id,goods_name,goods_storage,goods_state,store_id');
        if (empty($goods_info) || ($goods_info['goods_storage'] > 0 && $goods_info['goods_state'] == 1)) {
            showDialog(lang('wrong_argument'), 'reload');
        }

        $model_arrivalnotice = Model('arrivalnotice');
        // 验证会员是否已经添加到货通知
        $where = array();
        $where['goods_id'] = $goods_info['goods_id'];
        $where['member_id'] = session('member_id');
        $where['an_type'] = $type;
        $notice_info = $model_arrivalnotice->getArrivalNoticeInfo($where);
        if (!empty($notice_info)) {
            if ($type == 1) {
                showDialog('您已经添加过通知提醒，请不要重复添加', 'reload');
            } else {
                showDialog('您已经预约过了，请不要重复预约', 'reload');
            }
        }

        $insert = array();
        $insert['goods_id'] = $goods_info['goods_id'];
        $insert['goods_name'] = $goods_info['goods_name'];
        $insert['member_id'] = session('member_id');
        $insert['store_id'] = $goods_info['store_id'];
        $insert['an_mobile'] = $_POST['mobile'];
        $insert['an_email'] = $_POST['email'];
        $insert['an_type'] = $type;
        $model_arrivalnotice->addArrivalNotice($insert);

        $title = $type == 1 ? '到货通知' : '立即预约';
        $js = "ajax_form('arrival_notice', '" . $title . "', '" . url('Home/Goods/arrival_notice_succ', ['type' => $type]) . "', 480);";
        showDialog('', '', 'js', $js);
    }

    /**
     * 到货通知添加成功
     */
    public function arrival_notice_succ() {
        // 可能喜欢的商品
        $goods_list = Model('goodsbrowse')->getGuessLikeGoods(session('member_id'), 4);
        $this->assign('goods_list', $goods_list);
        return $this->fetch($this->template_dir . 'arrival_notice.message');
    }

    public function index_bak() {
        $goods_id = input('param.goods_id');
        $goods_detail = $this->getGoodsDetail($goods_id);
        $goods_info = $goods_detail['goods_info'];
        if (empty($goods_info)) {
            $this->error(lang('goods_index_no_goods'), '', 'html', 'error');
        }
        $this->assign('goods', $goods_info);
        $this->getStoreInfo($goods_info['store_id']);
        $this->assign('spec_list', $goods_detail['spec_list']);
        $this->assign('spec_image', $goods_detail['spec_image']);
        $this->assign('goods_image', $goods_detail['goods_image']);
        $this->assign('mansong_info', $goods_detail['mansong_info']);
        $this->assign('gift_array', $goods_detail['gift_array']);

        return $this->fetch($this->template_dir . 'index');
    }

    function getGoodsDetail_bak($goods_id) {
        if ($goods_id <= 0) {
            $this->error('商品不存在');
        }
        //获取商品goods信息
        $goods_info1 = db('goods')->where('goods_id', $goods_id)->find();
        //获取公共goodscommon信息
        $goods_info2 = db('goodscommon')->where('goods_commonid', $goods_info1['goods_commonid'])->find();

        $goods_info = array_merge($goods_info2, $goods_info1);
        $goods_info['spec_value'] = unserialize($goods_info['spec_value']);
        $goods_info['spec_name'] = unserialize($goods_info['spec_name']);
        $goods_info['goods_spec'] = unserialize($goods_info['goods_spec']);
        $goods_info['goods_attr'] = unserialize($goods_info['goods_attr']);

        // 查询所有规格商品
        $spec_array = db('goods')->where('goods_commonid', $goods_info1['goods_commonid'])->field('goods_spec,goods_id,store_id,goods_image,color_id')->select();
        $spec_list = array();       // 各规格商品地址，js使用
        $spec_list_mobile = array();       // 各规格商品地址，js使用
        $spec_image = array();      // 各规格商品主图，规格颜色图片使用
        foreach ($spec_array as $key => $value) {
            $s_array = unserialize($value['goods_spec']);
            $tmp_array = array();
            if (!empty($s_array) && is_array($s_array)) {
                foreach ($s_array as $k => $v) {
                    $tmp_array[] = $k;
                }
            }
            sort($tmp_array);
            $spec_sign = implode('|', $tmp_array);
            $tpl_spec = array();
            $tpl_spec['sign'] = $spec_sign;
            $tpl_spec['url'] = url('home/goods/index', ['goods_id' => $value['goods_id']]);
            $spec_list[] = $tpl_spec;
            $spec_list_mobile[$spec_sign] = $value['goods_id'];
            $spec_image[$value['color_id']] = thumb($value, 60);
        }
        $spec_list = json_encode($spec_list);
        // 商品多图



        $result = array();
        $result['goods_info'] = $goods_info;
        $result['spec_list'] = $spec_list;
        $result['spec_list_mobile'] = $spec_list_mobile;
        $result['spec_image'] = $spec_image;
        $result['goods_image'] = $goods_image;
        $result['goods_image_mobile'] = $goods_image_mobile;
        $result['mansong_info'] = $mansong_info;
        $result['gift_array'] = $gift_array;
        return $result;
    }

    public function json_area() {
        echo input('param.callback') . '(' . json_encode(Model('area')->getAreaArrayForJson()) . ')';
    }

}

?>
