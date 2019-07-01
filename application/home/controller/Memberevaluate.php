<?php

namespace app\home\controller;
use think\Lang;
use think\Model;

class Memberevaluate extends BaseMember {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/memberevaluate.lang.php');
    }

    /**
     * 订单添加评价
     */
    public function add(){
        $order_id = intval(input('order_id'));
        if (!$order_id){
            $this->error(lang('wrong_argument'),'member_order/index');
        }

        $model_order = Model('order');
        $model_store = Model('store');
        $model_evaluate_goods = Model('evaluategoods');
        $model_evaluate_store = Model('evaluatestore');

        //获取订单信息
        $order_info = $model_order->getOrderInfo(array('order_id' => $order_id));
        //判断订单身份
        if($order_info['buyer_id'] != session('member_id')) {
            $this->error(lang('wrong_argument'),'member_order/index');
        }
        //订单为'已收货'状态，并且未评论
        $order_info['evaluate_able'] = $model_order->getOrderOperateState('evaluation',$order_info);
        if (empty($order_info) || !$order_info['evaluate_able']){
            $this->error(lang('member_evaluation_order_notexists'),'member_order/index');
        }

        //查询店铺信息
        $store_info = $model_store->getStoreInfoByID($order_info['store_id']);
        if(empty($store_info)){
            $this->error(lang('member_evaluation_store_notexists'),'member_order/index');
        }

        //获取订单商品
        $order_goods = $model_order->getOrderGoodsList(array('order_id'=>$order_id));
        if(empty($order_goods)){
            $this->error(lang('member_evaluation_order_notexists'),'member_order/index');
        }

        //判断是否为页面
        if (!request()->isPost()){
           /* for ($i = 0, $j = count($order_goods); $i < $j; $i++) {
                $order_goods[$i]['goods_image_url'] = cthumb($order_goods[$i]['goods_image'], 60, $store_info['store_id']);
            }*/

            //处理积分、经验值计算说明文字
            $model_config=Model('config');
            $expset=$model_config->getListConfig();
            $ruleexplain = '';
            $exppoints_rule = $expset['expset']?unserialize($expset['expset']):array();
            $exppoints_rule['exp_comments'] = intval($exppoints_rule['comment_exp']);

            if ($exppoints_rule['exp_comments'] > 0 ){
                $ruleexplain .= '评价完成将获得';
                if ($exppoints_rule['exp_comments'] > 0){
                    $ruleexplain .= (' “'.$exppoints_rule['exp_comments'].'经验值。”');
                }
            }
            $this->assign('ruleexplain', $ruleexplain);

            //不显示左菜单
            $this->assign('left_show','order_view');
            $this->assign('order_info',$order_info);
            $this->assign('order_goods',$order_goods);
            $this->assign('store_info',$store_info);
            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('member_evaluate');
            return $this->fetch($this->template_dir.'evaluation_add');
        }else {
            $evaluate_goods_array = array();
            $goodsid_array = array();

            foreach ($order_goods as $value){
                //如果未评分，默认为5分
                $evaluate_score = intval($_POST['goods'][$value['goods_id']]['score']);
                if($evaluate_score <= 0 || $evaluate_score > 5) {
                    $evaluate_score = 5;
                }
                //默认评语
                $evaluate_comment = $_POST['goods'][$value['goods_id']]['comment'];
                if(empty($evaluate_comment)) {
                    $evaluate_comment = '不错哦';
                }

                $evaluate_goods_info = array();
                $evaluate_goods_info['geval_orderid'] = $order_id;
                $evaluate_goods_info['geval_orderno'] = $order_info['order_sn'];
                $evaluate_goods_info['geval_ordergoodsid'] = $value['rec_id'];
                $evaluate_goods_info['geval_goodsid'] = $value['goods_id'];
                $evaluate_goods_info['geval_goodsname'] = $value['goods_name'];
                $evaluate_goods_info['geval_goodsprice'] = $value['goods_price'];
                $evaluate_goods_info['geval_goodsimage'] = $value['goods_image'];
                $evaluate_goods_info['geval_scores'] = $evaluate_score;
                $evaluate_goods_info['geval_content'] = $evaluate_comment;
                $evaluate_goods_info['geval_isanonymous'] = isset($_POST['anony'])?1:0;
                $evaluate_goods_info['geval_addtime'] = TIMESTAMP;
                $evaluate_goods_info['geval_storeid'] = $store_info['store_id'];
                $evaluate_goods_info['geval_storename'] = $store_info['store_name'];
                $evaluate_goods_info['geval_frommemberid'] = session('member_id');
                $evaluate_goods_info['geval_frommembername'] = session('member_name');

                $evaluate_goods_array[] = $evaluate_goods_info;

                $goodsid_array[] = $value['goods_id'];
            }

            $model_evaluate_goods->addEvaluateGoodsArray($evaluate_goods_array, $goodsid_array);

            //             //添加店铺评价
            if (!$store_info['is_own_shop']) {

                $store_desccredit = intval($_POST['store_desccredit']);
                if($store_desccredit <= 0 || $store_desccredit > 5) {
                    $store_desccredit= 5;
                }
                $store_servicecredit = intval($_POST['store_servicecredit']);
                if($store_servicecredit <= 0 || $store_servicecredit > 5) {
                    $store_servicecredit = 5;
                }
                $store_deliverycredit = intval($_POST['store_deliverycredit']);
                if($store_deliverycredit <= 0 || $store_deliverycredit > 5) {
                    $store_deliverycredit = 5;
                }

                $evaluate_store_info = array();
                $evaluate_store_info['seval_orderid'] = $order_id;
                $evaluate_store_info['seval_orderno'] = $order_info['order_sn'];
                $evaluate_store_info['seval_addtime'] = time();
                $evaluate_store_info['seval_storeid'] = $store_info['store_id'];
                $evaluate_store_info['seval_storename'] = $store_info['store_name'];
                $evaluate_store_info['seval_memberid'] = session('member_id');
                $evaluate_store_info['seval_membername'] = session('member_name');
                $evaluate_store_info['seval_desccredit'] = $store_desccredit;
                $evaluate_store_info['seval_servicecredit'] = $store_servicecredit;
                $evaluate_store_info['seval_deliverycredit'] = $store_deliverycredit;
                $model_evaluate_store->addEvaluateStore($evaluate_store_info);
            }

            //更新订单信息并记录订单日志
            $state = $model_order->editOrder(array('evaluation_state'=>1), array('order_id' => $order_id));
            $model_order->editOrderCommon(array('evaluation_time'=>TIMESTAMP), array('order_id' => $order_id));
            if ($state){
                $data = array();
                $data['order_id'] = $order_id;
                $data['log_role'] = 'buyer';
                $data['log_msg'] = lang('order_log_eval');
                $model_order->addOrderLog($data);
            }

            //添加会员积分
            if (config('points_isuse') == 1){
                $points_model = Model('points');
                $points_model->savePointsLog('comments',array('pl_memberid'=>session('member_id'),'pl_membername'=>session('member_name')));
            }
            //添加会员经验值
            Model('exppoints')->saveExppointsLog('comments',array('exp_memberid'=>session('member_id'),'exp_membername'=>session('member_name')));;

            showDialog(lang('member_evaluation_evaluat_success'),url('memberorder/index'), 'succ');
        }
    }

    /**
     * 虚拟商品评价
     */
    public function add_vr(){
        $order_id = intval(input('param.order_id'));
        if (!$order_id){
            $this->error(lang('wrong_argument'),'member_vr_order/index');
        }

        $model_order = Model('vrorder');
        $model_store = Model('store');
        $model_evaluate_goods = Model('evaluategoods');
        $model_evaluate_store = Model('evaluatestore');

        //获取订单信息
        $order_info = $model_order->getOrderInfo(array('order_id' => $order_id));
        //判断订单身份
        if($order_info['buyer_id'] !=session('member_id')) {
            $this->error(lang('wrong_argument'),'member_vr_order/index');
        }
        //订单为'已收货'状态，并且未评论
        $order_info['evaluate_able'] = $model_order->getOrderOperateState('evaluation',$order_info);
        if (!$order_info['evaluate_able']){
            $this->error(lang('member_evaluation_order_notexists'),'member_vr_order/index');
        }

        //查询店铺信息
        $store_info = $model_store->getStoreInfoByID($order_info['store_id']);
        if(empty($store_info)){
            $this->error(lang('member_evaluation_store_notexists'),'member_vr_order/index');
        }
        $order_goods = array($order_info);

        //判断是否为页面
        if (!$_POST){
            $order_goods[0]['goods_image_url'] = cthumb($order_info['goods_image'], 60, $order_info['store_id']);

            //处理积分、经验值计算说明文字
            $ruleexplain = '';
            $exppoints_rule = config("exppoints_rule")?unserialize(config("exppoints_rule")):array();
            $exppoints_rule['exp_comments'] = intval($exppoints_rule['exp_comments']);
            $points_comments = intval(config('points_comments'));
            if ($exppoints_rule['exp_comments'] > 0 || $points_comments > 0){
                $ruleexplain .= '评价完成将获得';
                if ($exppoints_rule['exp_comments'] > 0){
                    $ruleexplain .= (' “'.$exppoints_rule['exp_comments'].'经验值”');
                }
                if ($points_comments > 0){
                    $ruleexplain .= (' “'.$points_comments.'积分”');
                }
                $ruleexplain .= '。';
            }
            $this->assign('ruleexplain', $ruleexplain);

            //不显示左菜单
            $this->assign('left_show','order_view');
            $this->assign('order_info',$order_info);
            $this->assign('order_goods',$order_goods);
            $this->assign('store_info',$store_info);
            return $this->fetch($this->template_dir.'evaluation.add');
        }else {
            $evaluate_goods_array = array();
            $goodsid_array = array();
            foreach ($order_goods as $value){
                //如果未评分，默认为5分
                $evaluate_score = intval($_POST['goods'][$value['goods_id']]['score']);
                if($evaluate_score <= 0 || $evaluate_score > 5) {
                    $evaluate_score = 5;
                }
                //默认评语
                $evaluate_comment = $_POST['goods'][$value['goods_id']]['comment'];
                if(empty($evaluate_comment)) {
                    $evaluate_comment = '不错哦';
                }

                $evaluate_goods_info = array();
                $evaluate_goods_info['geval_orderid'] = $order_id;
                $evaluate_goods_info['geval_orderno'] = $order_info['order_sn'];
                $evaluate_goods_info['geval_ordergoodsid'] = $order_id;
                $evaluate_goods_info['geval_goodsid'] = $value['goods_id'];
                $evaluate_goods_info['geval_goodsname'] = $value['goods_name'];
                $evaluate_goods_info['geval_goodsprice'] = $value['goods_price'];
                $evaluate_goods_info['geval_goodsimage'] = $value['goods_image'];
                $evaluate_goods_info['geval_scores'] = $evaluate_score;
                $evaluate_goods_info['geval_content'] = $evaluate_comment;
                $evaluate_goods_info['geval_isanonymous'] = $_POST['anony']?1:0;
                $evaluate_goods_info['geval_addtime'] = TIMESTAMP;
                $evaluate_goods_info['geval_storeid'] = $store_info['store_id'];
                $evaluate_goods_info['geval_storename'] = $store_info['store_name'];
                $evaluate_goods_info['geval_frommemberid'] = session('member_id');
                $evaluate_goods_info['geval_frommembername'] = session('member_name');

                $evaluate_goods_array[] = $evaluate_goods_info;

                $goodsid_array[] = $value['goods_id'];
            }
            $model_evaluate_goods->addEvaluateGoodsArray($evaluate_goods_array, $goodsid_array);

            //             $store_desccredit = intval($_POST['store_desccredit']);
            //             if($store_desccredit <= 0 || $store_desccredit > 5) {
            //                 $store_desccredit= 5;
            //             }
            //             $store_servicecredit = intval($_POST['store_servicecredit']);
            //             if($store_servicecredit <= 0 || $store_servicecredit > 5) {
            //                 $store_servicecredit = 5;
            //             }
            //             $store_deliverycredit = intval($_POST['store_deliverycredit']);
            //             if($store_deliverycredit <= 0 || $store_deliverycredit > 5) {
            //                 $store_deliverycredit = 5;
            //             }
            //          //添加店铺评价
            //             if (!$store_info['is_own_shop']) {
            //                 $evaluate_store_info = array();
            //                 $evaluate_store_info['seval_orderid'] = $order_id;
            //                 $evaluate_store_info['seval_orderno'] = $order_info['order_sn'];
            //                 $evaluate_store_info['seval_addtime'] = time();
            //                 $evaluate_store_info['seval_storeid'] = $store_info['store_id'];
            //                 $evaluate_store_info['seval_storename'] = $store_info['store_name'];
            //                 $evaluate_store_info['seval_memberid'] = session('member_id');
            //                 $evaluate_store_info['seval_membername'] = session('member_name');
            //                 $evaluate_store_info['seval_desccredit'] = $store_desccredit;
            //                 $evaluate_store_info['seval_servicecredit'] = $store_servicecredit;
            //                 $evaluate_store_info['seval_deliverycredit'] = $store_deliverycredit;
            //                 $model_evaluate_store->addEvaluateStore($evaluate_store_info);
            //             }

            //更新订单信息并记录订单日志
            $state = $model_order->editOrder(array('evaluation_state'=>1,'evaluation_time'=>TIMESTAMP), array('order_id' => $order_id));

            //添加会员积分
            if (config('points_isuse') == 1){
                $points_model = Model('points');
                $points_model->savePointsLog('comments',array('pl_memberid'=>session('member_id'),'pl_membername'=>session('member_name')));
            }
            //添加会员经验值
            Model('exppoints')->saveExppointsLog('comments',array('exp_memberid'=>session('member_id'),'exp_membername'=>session('member_name')));;

            $this->success(lang('member_evaluation_evaluat_success'),'member_vr_order/index');
        }
    }

    /**
     * 评价列表
     */
    public function index(){
        $model_evaluate_goods = Model('evaluategoods');

        $condition = array();
        $condition['geval_frommemberid'] = session('member_id');
        $goodsevallist = $model_evaluate_goods->getEvaluateGoodsList($condition, 1,'geval_id desc');
        $this->assign('goodsevallist',$goodsevallist);
        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('member_evaluate');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('evaluate');
        $this->assign('show_page',$model_evaluate_goods->page_info->render());

        return $this->fetch($this->template_dir.'index');
    }

    public function add_image() {
        $geval_id = intval(input('geval_id'));

        $model_evaluate_goods = Model('evaluategoods');
        $model_store = Model('store');
        $model_sns_alumb = Model('snsalbum');

        $geval_info = $model_evaluate_goods->getEvaluateGoodsInfoByID($geval_id);

        if(!empty($geval_info['geval_image'])) {
            $this->error('该商品已经发表过晒单');
        }

        if($geval_info['geval_frommemberid'] != session('member_id')) {
            $this->error(lang('param_error'));
        }
        $this->assign('geval_info', $geval_info);

        $store_info = $model_store->getStoreInfoByID($geval_info['geval_storeid']);
        $this->assign('store_info', $store_info);

        $ac_id = $model_sns_alumb->getSnsAlbumClassDefault(session('member_id'));

        $this->assign('ac_id', $ac_id);
        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('member_evaluate');
        //不显示左菜单
        $this->assign('left_show','order_view');
        return $this->fetch($this->template_dir.'add_image');
    }

    public function add_image_save() {
        $geval_id = intval(input('param.geval_id'));
        $geval_image = '';
        foreach ($_POST['evaluate_image'] as $value) {
            if(!empty($value)) {
                $geval_image .= $value . ',';
            }
        }
        $geval_image = rtrim($geval_image, ',');

        $model_evaluate_goods = Model('evaluategoods');

        $geval_info = $model_evaluate_goods->getEvaluateGoodsInfoByID($geval_id);
        if(empty($geval_info)) {
            showDialog(lang('param_error'));
        }
        if($geval_info['geval_frommemberid'] != session('member_id')) {
            showDialog(lang('param_error'));
        }

        $update = array();
        $update['geval_image'] = $geval_image;
        $condition = array();
        $condition['geval_id'] = $geval_id;
        $result = $model_evaluate_goods->editEvaluateGoods($update, $condition);

        list($sns_image) = explode(',', $geval_image);
        $goods_url = url('Goods/index', array('goods_id' => $geval_info['geval_goodsid']));
        //同步到sns
        $content = "
            <div class='fd-media'>
            <div class='goodsimg'><a target=\"_blank\" href=\"{$goods_url}\"><img src=\"".snsThumb($sns_image, 240)."\" title=\"{$geval_info['geval_goodsname']}\" alt=\"{$geval_info['geval_goodsname']}\"></a></div>
            <div class='goodsinfo'>
            <dl>
            <dt><a target=\"_blank\" href=\"{$goods_url}\">{$geval_info['geval_goodsname']}</a></dt>
            <dd>价格".lang('ds_colon').lang('currency').$geval_info['geval_goodsprice']."</dd>
            <dd><a target=\"_blank\" href=\"{$goods_url}\">去看看</a></dd>
            </dl>
            </div>
            </div>
            ";

        $tracelog_model = Model('snstracelog');
        $insert_arr = array();
        $insert_arr['trace_originalid'] = '0';
        $insert_arr['trace_originalmemberid'] = '0';
        $insert_arr['trace_memberid'] = session('member_id');
        $insert_arr['trace_membername'] = session('member_name');
        $insert_arr['trace_memberavatar'] = session('member_avatar');
        $insert_arr['trace_title'] = '发表了商品晒单';
        $insert_arr['trace_content'] = $content;
        $insert_arr['trace_addtime'] = TIMESTAMP;
        $insert_arr['trace_state'] = '0';
        $insert_arr['trace_privacy'] = 0;
        $insert_arr['trace_commentcount'] = 0;
        $insert_arr['trace_copycount'] = 0;
        $insert_arr['trace_from'] = '1';
        $result = $tracelog_model->tracelogAdd($insert_arr);

        if($result) {
            showDialog(lang('ds_common_save_succ'), url('memberevaluate/index'), 'succ');
        } else {
            showDialog(lang('ds_common_save_succ'), url('memberevaluate/index'), 'list');
        }
    }
    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_type 导航类型
     * @param string $menu_key 当前导航的menu_key
     * @return
     */
    public function getMemberItemList()
    {
        $menu_array = array(
            array(
                'name' => 'evaluate',
                'text' => '交易评价/晒单',
                'url' => url('memberevaluate/index')
            ),
        );
        return $menu_array;
    }

}
