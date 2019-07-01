<?php

/*
 * 卖家相关控制中心
 */

namespace app\home\controller;
use think\Lang;

class BaseSeller extends BaseMall {

    //店铺信息
    protected $store_info = array();

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/basemember.lang.php');
        Lang::load(APP_PATH . 'home/lang/zh-cn/baseseller.lang.php');
        //卖家中心模板路径
        $this->template_dir = 'default/seller/' . strtolower(request()->controller()) . '/';
        if (request()->controller() != 'Sellerlogin') {
            if (!session('member_id')) {
                $this->redirect('Home/Sellerlogin/login');
            }
            if (!session('seller_id')) {
                $this->redirect('Home/Sellerlogin/login');
            }

            // 验证店铺是否存在
            $model_store = Model('store');
            $this->store_info = $model_store->getStoreInfoByID(session('store_id'));
            if (empty($this->store_info)) {
                $this->redirect('Home/Sellerlogin/login');
            }

            // 店铺关闭标志
            if (intval($this->store_info['store_state']) === 0) {
                $this->assign('store_closed', true);
                $this->assign('store_close_info', $this->store_info['store_close_info']);
            }

            // 店铺等级
            if (session('is_own_shop')) {
                $this->store_grade = array(
                    'sg_id' => '0',
                    'sg_name' => '自营店铺专属等级',
                    'sg_goods_limit' => '0',
                    'sg_album_limit' => '0',
                    'sg_space_limit' => '999999999',
                    'sg_template_number' => '6',
                    // see also store_settingControl.themeOp()
                    // 'sg_template' => 'default|style1|style2|style3|style4|style5',
                    'sg_price' => '0.00',
                    'sg_description' => '',
                    'sg_function' => 'editor_multimedia',
                    'sg_sort' => '0',
                );
            } else {
                $store_grade = rkcache('store_grade', true);
                $this->store_grade = @$store_grade[$this->store_info['grade_id']];
            }
            if (session('seller_is_admin') !== 1 && request()->controller() !== 'Seller' && request()->controller() !== 'Sellerlogin') {
                if (!in_array(request()->controller(), session('seller_limits'))) {
                    $this->error('没有权限', 'Home/seller/index');
                }
            }
        }
    }

    /**
     * 记录卖家日志
     *
     * @param $content 日志内容
     * @param $state 1成功 0失败
     */
    protected function recordSellerLog($content = '', $state = 1) {
        $seller_info = array();
        $seller_info['log_content'] = $content;
        $seller_info['log_time'] = TIMESTAMP;
        $seller_info['log_seller_id'] = session('seller_id');
        $seller_info['log_seller_name'] = session('seller_name');
        $seller_info['log_store_id'] = session('store_id');
        $seller_info['log_seller_ip'] = request()->ip();
        $seller_info['log_url'] = request()->module() . '/' . request()->controller() . '/' . request()->action();
        $seller_info['log_state'] = $state;
        $model_seller_log = Model('sellerlog');
        $model_seller_log->addSellerLog($seller_info);
    }

    /**
     * 记录店铺费用
     *
     * @param $cost_price 费用金额
     * @param $cost_remark 费用备注
     */
    protected function recordStoreCost($cost_price, $cost_remark) {
        // 平台店铺不记录店铺费用
        if (checkPlatformStore()) {
            return false;
        }
        $model_store_cost = Model('storecost');
        $param = array();
        $param['cost_store_id'] = session('store_id');
        $param['cost_seller_id'] = session('seller_id');
        $param['cost_price'] = $cost_price;
        $param['cost_remark'] = $cost_remark;
        $param['cost_state'] = 0;
        $param['cost_time'] = TIMESTAMP;
        $model_store_cost->addStoreCost($param);

        // 发送店铺消息
        $param = array();
        $param['code'] = 'store_cost';
        $param['store_id'] = session('store_id');
        $param['param'] = array(
            'price' => $cost_price,
            'seller_name' => session('seller_name'),
            'remark' => $cost_remark
        );

        \mall\queue\QueueClient::push('sendStoreMsg', $param);
    }

    /**
     * 添加到任务队列
     *
     * @param array $goods_array
     * @param boolean $ifdel 是否删除以原记录
     */
    protected function addcron($data = array(), $ifdel = false) {
        $model_cron = Model('cron');
        if (isset($data[0])) { // 批量插入
            $where = array();
            foreach ($data as $k => $v) {
                if (isset($v['content'])) {
                    $data[$k]['content'] = serialize($v['content']);
                }
                // 删除原纪录条件
                if ($ifdel) {
                    $where[] = '(type = ' . $data['type'] . ' and exeid = ' . $data['exeid'] . ')';
                }
            }
            // 删除原纪录
            if ($ifdel) {
                $model_cron->delCron(implode(',', $where));
            }
            $model_cron->addCronAll($data);
        } else { // 单条插入
            if (isset($data['content'])) {
                $data['content'] = serialize($data['content']);
            }
            // 删除原纪录
            if ($ifdel) {
                $model_cron->delCron(array('type' => $data['type'], 'exeid' => $data['exeid']));
            }
            $model_cron->addCron($data);
        }
    }

    /**
     *    当前选中的栏目
     */
    protected function setSellerCurItem($curitem = '') {
        $this->assign('seller_item', $this->getSellerItemList());
        $this->assign('curitem', $curitem);
    }

    /**
     *    当前选中的子菜单
     */
    protected function setSellerCurMenu($cursubmenu = '') {
        $seller_menu = $this->getSellerMenuList();
        $this->assign('seller_menu', $seller_menu);
        $curmenu = '';
        foreach ($seller_menu as $key => $menu) {
            foreach ($menu['submenu'] as $subkey => $submenu) {
                if ($submenu['name'] == $cursubmenu) {
                    $curmenu = $menu['name'];
                }
            }
        }
        //当前一级菜单
        $this->assign('curmenu', $curmenu);
        //当前二级菜单
        $this->assign('cursubmenu', $cursubmenu);
    }

    /*
     * 获取卖家栏目列表,针对控制器下的栏目
     */

    protected function getSellerItemList() {
        return array();
    }

    /*
     * 获取卖家菜单列表
     */

    private function getSellerMenuList() {
        //controller  注意第一个字母要大写
        $menu_list = array(
            'sellergoods' =>
            array(
                'name' => 'sellergoods',
                'text' => '商品',
                'url' => url('Home/Sellergoodsonline/index'),
                'submenu' => array(
                    array('name' => 'sellergoodsadd', 'text' => '商品发布', 'controller' => 'Sellergoodsadd', 'url' => url('Home/Sellergoodsadd/index'),),
                    array('name' => 'sellergoodsonline', 'text' => '出售中的商品', 'controller' => 'Sellergoodsonline', 'url' => url('Home/Sellergoodsonline/index'),),
                    array('name' => 'sellergoodsoffline', 'text' => '仓库中的商品', 'controller' => 'Sellergoodsoffline', 'url' => url('Home/Sellergoodsoffline/index'),),
                    array('name' => 'sellerplate', 'text' => '关联版式', 'controller' => 'Sellerplate', 'url' => url('Home/Sellerplate/index'),),
                    array('name' => 'sellerspec', 'text' => '商品规格', 'controller' => 'Sellerspec', 'url' => url('Home/sellerspec/index'),),
                    array('name' => 'selleralbum', 'text' => '图片空间', 'controller' => 'Selleralbum', 'url' => url('Home/selleralbum/index'),),
                )
            ),
            'sellerorder' =>
            array(
                'name' => 'sellerorder',
                'text' => '订单',
                'url' => url('Home/sellerorder/index'),
                'submenu' => array(
                    array('name' => 'sellerorder', 'text' => '实物交易订单', 'controller' => 'Sellerorder', 'url' => url('Home/sellerorder/index'),),
                    array('name' => 'sellervrorder', 'text' => '虚拟兑码订单', 'controller' => 'Sellervrorder', 'url' => url('Home/Sellervrorder/index'),),
                    array('name' => 'sellerdeliver', 'text' => '发货管理', 'controller' => 'Sellerdeliver', 'url' => url('Home/Sellerdeliver/index'),),
                    array('name' => 'sellerdeliverset', 'text' => '发货设置', 'controller' => 'Sellerdeliverset', 'url' => url('Home/Sellerdeliverset/index'),),
                    array('name' => 'sellerwaybill', 'text' => '运单模板', 'controller' => 'Sellerwaybill', 'url' => url('Home/Sellerwaybill/index')),
                    array('name' => 'sellerevaluate', 'text' => '评价管理', 'controller' => 'Sellerevaluate', 'url' => url('Home/Sellerevaluate/index'),),
                    array('name' => 'sellertransport', 'text' => '售卖区域', 'controller' => 'Sellertransport', 'url' => url('Home/Sellertransport/index'),),
                )
            ),
            'sellergroupbuy' =>
            array(
                'name' => '促销',
                'text' => '促销',
                'url' => url('Home/Sellergroupbuy/index'),
                'submenu' => array(
                    array('name' => 'Sellergroupbuy', 'text' => '团购管理', 'controller' => 'Sellergroupbuy', 'url' => url('Home/Sellergroupbuy/index'),),
                    array('name' => 'Sellerpromotionxianshi', 'text' => '限时抢购', 'controller' => 'Sellerpromotionxianshi', 'url' => url('Home/Sellerpromotionxianshi/index'),),
                    array('name' => 'Sellerpromotionmansong', 'text' => '满即送 ', 'controller' => 'Sellerpromotionmansong', 'url' => url('Home/Sellerpromotionmansong/index'),),
                    array('name' => 'Sellerpromotionbundling', 'text' => '优惠套装 ', 'controller' => 'Sellerpromotionbundling', 'url' => url('Home/Sellerpromotionbundling/index'),),
                    array('name' => 'Sellerpromotionbooth', 'text' => '推荐展位 ', 'controller' => 'Sellerpromotionbooth', 'url' => url('Home/Sellerpromotionbooth/index'),),
                    array('name' => 'Sellervoucher', 'text' => '代金券管理 ', 'controller' => 'Sellervoucher', 'url' => url('Home/Sellervoucher/templatelist'),),
                    array('name' => 'Selleractivity', 'text' => '活动管理 ', 'controller' => 'Selleractivity', 'url' => url('Home/Selleractivity/index'),),
                )
            ),
            'seller' =>
            array(
                'name' => 'seller',
                'text' => '店铺',
                'url' => url('Home/seller/index'),
                'submenu' => array(
                    array('name' => 'seller_index', 'text' => '店铺概览', 'controller' => 'Seller', 'url' => url('Home/Seller/index'),),
                    array('name' => 'seller_setting', 'text' => '店铺设置', 'controller' => 'Sellersetting', 'url' => url('Home/Sellersetting/setting'),),
                    /*array('name' => 'seller_decoration', 'text' => '店铺装修', 'controller' => 'Sellerdecoration', 'url' => url('Home/sellerdecoration/index'),),*/
                    array('name' => 'seller_navigation', 'text' => '店铺导航', 'controller' => 'Sellernavigation', 'url' => url('Home/sellernavigation/index'),),
                    array('name' => 'sellersns', 'text' => '店铺动态', 'controller' => 'Sellersns', 'url' => url('Home/sellersns/index'),),
                    array('name' => 'sellerinfo', 'text' => '店铺信息', 'controller' => 'Sellerinfo', 'url' => url('Home/sellerinfo/bind_class'),),
                    array('name' => 'sellergoodsclass', 'text' => '店铺分类', 'controller' => 'Sellergoodsclass', 'url' => url('Home/sellergoodsclass/index'),),
                    array('name' => 'sellerlive', 'text' => '线下商铺', 'controller' => 'Sellerlive', 'url' => url('Home/sellerlive/index'),),
                    array('name' => 'seller_brand', 'text' => '品牌申请', 'controller' => 'Sellerbrand', 'url' => url('Home/sellerbrand/index'),),
                )
            ),
            'sellerconsult' =>
            array(
                'name' => '售后服务',
                'text' => '售后服务',
                'url' => url('Home/sellerconsult/index'),
                'submenu' => array(
                    array('name' => 'seller_consult', 'text' => '咨询管理', 'controller' => 'Sellerconsult', 'url' => url('Home/sellerconsult/index'),),
                    array('name' => 'seller_complain', 'text' => '投诉记录', 'controller' => 'Sellercomplain', 'url' => url('Home/sellercomplain/index'),),
                    array('name' => 'seller_refund', 'text' => '退款记录', 'controller' => 'Sellerrefund', 'url' => url('Home/sellerrefund/index'),),
                    array('name' => 'seller_return', 'text' => '退货记录', 'controller' => 'Sellerreturn', 'url' => url('Home/sellerreturn/index'),),
                )
            ),
            'sellerstatistics' =>
            array(
                'name' => '统计',
                'text' => '统计',
                'url' => url('Home/Statisticsgeneral/index'),
                'submenu' => array(
                    array('name' => 'Statisticsgeneral', 'text' => '店铺概况', 'controller' => 'Statisticsgeneral', 'url' => url('Home/statisticsgeneral/index'),),
                    array('name' => 'Statisticsgoods', 'text' => '商品分析', 'controller' => 'Statisticsgoods', 'url' => url('Home/statisticsgoods/index'),),
                    array('name' => 'Statisticssale', 'text' => '运营报告', 'controller' => 'Statisticssale', 'url' => url('Home/statisticssale/index'),),
                    array('name' => 'Statisticsindustry', 'text' => '行业分析', 'controller' => 'Statisticsindustry', 'url' => url('Home/statisticsindustry/index'),),
                    array('name' => 'Statisticsflow', 'text' => '流量统计', 'controller' => 'Statisticsflow', 'url' => url('Home/statisticsflow/index'),),
                    array('name' => 'Sellerbill', 'text' => '实物结算', 'controller' => 'Sellerbill', 'url' => url('Home/Sellerbill/index'),),
                    array('name' => 'Sellervrbill', 'text' => '虚拟结算', 'controller' => 'Sellervrbill', 'url' => url('Home/Sellervrbill/index'),),
                )
            ),
            'sellercallcenter' =>
            array(
                'name' => '客服消息',
                'text' => '客服消息',
                'url' => url('Home/Sellercallcenter/index'),
                'submenu' => array(
                    array('name' => 'Sellercallcenter', 'text' => '客服设置', 'controller' => 'Sellercallcenter', 'url' => url('Home/Sellercallcenter/index'),),
                    array('name' => 'Sellermsg', 'text' => '系统消息', 'controller' => 'Sellermsg', 'url' => url('Home/Sellermsg/index'),),
                    array('name' => 'Sellerim', 'text' => '聊天记录查询', 'controller' => 'Sellerim', 'url' => url('Home/Sellerim/index'),),
                )
            ),
            'selleraccount' =>
            array(
                'name' => '账号',
                'text' => '账号',
                'url' => url('Home/selleraccount/account_list'),
                'submenu' => array(
                    array('name' => 'selleraccount', 'text' => '账号列表', 'controller' => 'Selleraccount', 'url' => url('Home/selleraccount/account_list'),),
                    array('name' => 'selleraccountgroup', 'text' => '账号组', 'controller' => 'Selleraccountgroup', 'url' => url('Home/selleraccountgroup/group_list'),),
                    array('name' => 'sellerlog', 'text' => '账号日志', 'controller' => 'Sellerlog', 'url' => url('Home/sellerlog/log_list'),),
                    array('name' => 'sellercost', 'text' => '店铺消费', 'controller' => 'Sellercost', 'url' => url('Home/sellercost/cost_list'),),
                )
            ),
        );
        return $menu_list;
    }

    /**
     * 自动发布店铺动态
     *
     * @param array $data 相关数据
     * @param string $type 类型 'new','coupon','xianshi','mansong','bundling','groupbuy'
     *            所需字段
     *            new       goods表'             goods_id,store_id,goods_name,goods_image,goods_price,goods_transfee_charge,goods_freight
     *            xianshi   p_xianshi_goods表'   goods_id,store_id,goods_name,goods_image,goods_price,goods_freight,xianshi_price
     *            mansong   p_mansong表'         mansong_name,start_time,end_time,store_id
     *            bundling  p_bundling表'        bl_id,bl_name,bl_img,bl_discount_price,bl_freight_choose,bl_freight,store_id
     *            groupbuy  goods_group表'       group_id,group_name,goods_id,goods_price,groupbuy_price,group_pic,rebate,start_time,end_time
     *            coupon在后台发布
     */
    public function storeAutoShare($data, $type) {
        $param = array(
            3 => 'new',
            4 => 'coupon',
            5 => 'xianshi',
            6 => 'mansong',
            7 => 'bundling',
            8 => 'groupbuy'
        );
        $param_flip = array_flip($param);
        if (!in_array($type, $param) || empty($data)) {
            return false;
        }

        $auto_setting = Model('storesnssetting')->getStoreSnsSettingInfo(array('sauto_storeid' => session('store_id')));
        $auto_sign = false; // 自动发布开启标志

        if ($auto_setting['sauto_' . $type] == 1) {
            $auto_sign = true;

            $goodsdata = addslashes(json_encode($data));
            if ($auto_setting['sauto_' . $type . 'title'] != '') {
                $title = $auto_setting['sauto_' . $type . 'title'];
            } else {
                $auto_title = 'ds_store_auto_share_' . $type . rand(1, 5);
                $title = lang($auto_title);
            }
        }
        if ($auto_sign) {
            // 插入数据
            $stracelog_array = array();
            $stracelog_array['strace_storeid'] = $this->store_info['store_id'];
            $stracelog_array['strace_storename'] = $this->store_info['store_name'];
            $stracelog_array['strace_storelogo'] = empty($this->store_info['store_avatar']) ? '' : $this->store_info['store_avatar'];
            $stracelog_array['strace_title'] = $title;
            $stracelog_array['strace_content'] = '';
            $stracelog_array['strace_time'] = TIMESTAMP;
            $stracelog_array['strace_type'] = $param_flip[$type];
            $stracelog_array['strace_goodsdata'] = $goodsdata;
            Model('storesnstracelog')->saveStoreSnsTracelog($stracelog_array);
            return true;
        } else {
            return false;
        }
    }
}

?>
