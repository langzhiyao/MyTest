<?php

namespace app\home\controller;

use think\Lang;

class Member extends BaseMember {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/member.lang.php');
    }

    public function index() {
        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('selleralbum');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('my_album');
        return $this->fetch($this->template_dir . 'index');
    }

    public function ajax_load_member_info() {
        $member_info = $this->member_info;
        $member_info['security_level'] = Model('member')->getMemberSecurityLevel($member_info);

        //代金券数量
        $member_info['voucher_count'] = Model('voucher')->getCurrentAvailableVoucherCount(session('member_id'));
        $this->assign('home_member_info', $member_info);

        return $this->fetch($this->template_dir . 'member_info');
    }

    public function ajax_load_order_info() {
        $model_order = Model('order');

        //交易提醒 - 显示数量
        $member_info['order_nopay_count'] = $model_order->getOrderCountByID('buyer', session('member_id'), 'NewCount');
        $member_info['order_noreceipt_count'] = $model_order->getOrderCountByID('buyer', session('member_id'), 'SendCount');
        $member_info['order_noeval_count'] = $model_order->getOrderCountByID('buyer', session('member_id'), 'EvalCount');
        $this->assign('home_member_info', $member_info);

        //交易提醒 - 显示订单列表
        $condition = array();
        $condition['buyer_id'] = session('member_id');
        $condition['order_state'] = array('in', array(ORDER_STATE_NEW, ORDER_STATE_PAY, ORDER_STATE_SEND, ORDER_STATE_SUCCESS));
        $order_list = $model_order->getNormalOrderList($condition, '', '*', 'order_id desc', 3, array('order_goods'));

        foreach ($order_list as $order_id => $order) {
            //显示物流跟踪
            $order_list[$order_id]['if_deliver'] = $model_order->getOrderOperateState('deliver', $order);
            //显示评价
            $order_list[$order_id]['if_evaluation'] = $model_order->getOrderOperateState('evaluation', $order);
            //显示支付
            $order_list[$order_id]['if_payment'] = $model_order->getOrderOperateState('payment', $order);
            //显示收货
            $order_list[$order_id]['if_receive'] = $model_order->getOrderOperateState('receive', $order);
        }

        $this->assign('order_list', $order_list);

        //取出购物车信息
        $model_cart = Model('cart');
        $cart_list = $model_cart->listCart('db', array('buyer_id' => session('member_id')), 3);
        $this->assign('cart_list', $cart_list);
        return $this->fetch($this->template_dir . 'order_info');
    }

    public function ajax_load_goods_info() {
        //商品收藏
        $favorites_model = Model('favorites');
        $favorites_list = $favorites_model->getGoodsFavoritesList(array('member_id' => session('member_id')), '*', 7);
        if (!empty($favorites_list) && is_array($favorites_list)) {
            $favorites_id = array(); //收藏的商品编号
            foreach ($favorites_list as $key => $favorites) {
                $fav_id = $favorites['fav_id'];
                $favorites_id[] = $favorites['fav_id'];
                $favorites_key[$fav_id] = $key;
            }
            $goods_model = Model('goods');
            $field = 'goods.goods_id,goods.goods_name,goods.store_id,goods.goods_image,goods.goods_price,goods.evaluation_count,goods.goods_salenum,goods.goods_collect,store.store_name,store.member_id,store.member_name,store.store_qq,store.store_ww,store.store_domain';
            $goods_list = $goods_model->getGoodsStoreList(array('goods_id' => array('in', $favorites_id)), $field);
            $store_array = array(); //店铺编号
            if (!empty($goods_list) && is_array($goods_list)) {
                $store_goods_list = array(); //店铺为分组的商品
                foreach ($goods_list as $key => $fav) {
                    $fav_id = $fav['goods_id'];
                    $fav['goods_member_id'] = $fav['member_id'];
                    $key = $favorites_key[$fav_id];
                    $favorites_list[$key]['goods'] = $fav;
                }
            }
        }
        $this->assign('favorites_list', $favorites_list);

        //店铺收藏
        $favorites_list = $favorites_model->getStoreFavoritesList(array('member_id' => session('member_id')), '*', 6);
        if (!empty($favorites_list) && is_array($favorites_list)) {
            $favorites_id = array(); //收藏的店铺编号
            foreach ($favorites_list as $key => $favorites) {
                $fav_id = $favorites['fav_id'];
                $favorites_id[] = $favorites['fav_id'];
                $favorites_key[$fav_id] = $key;
            }
            $store_model = Model('store');
            $store_list = $store_model->getStoreList(array('store_id' => array('in', $favorites_id)));
            if (!empty($store_list) && is_array($store_list)) {
                foreach ($store_list as $key => $fav) {
                    $fav_id = $fav['store_id'];
                    $key = $favorites_key[$fav_id];
                    $favorites_list[$key]['store'] = $fav;
                }
            }
        }
        $this->assign('favorites_store_list', $favorites_list);
        $goods_count_new = array();
        if (!empty($favorites_id)) {
            foreach ($favorites_id as $v) {
                $count = Model('goods')->getGoodsCommonOnlineCount(array('store_id' => $v));
                $goods_count_new[$v] = $count;
            }
        }
        $this->assign('goods_count', $goods_count_new);
        return $this->fetch($this->template_dir . 'goods_info');
    }

    public function ajax_load_sns_info() {
        //我的足迹
        $goods_list = Model('goodsbrowse')->getViewedGoodsList(session('member_id'), 20);
        $viewed_goods = array();
        if (is_array($goods_list) && !empty($goods_list)) {
            foreach ($goods_list as $key => $val) {
                $goods_id = $val['goods_id'];
                $val['url'] = url('Home/Goods/index',['goods_id'=>$goods_id]);
                $val['goods_image'] = thumb($val, 240);
                $viewed_goods[$goods_id] = $val;
            }
        }
        $this->assign('viewed_goods', $viewed_goods);

        //我的圈子
        /*德尚网络待完善 BEGIN*/
        /*
        $circlemember_array = db('circlemember')->where(array('member_id' => session('member_id')))->select();
        if (!empty($circlemember_array)) {
            $circlemember_array = array_under_reset($circlemember_array, 'circle_id');
            $circleid_array = array_keys($circlemember_array);
            $circle_list = db('circle')->where(array('circle_id' => array('in', $circleid_array)))->limit(6)->select();
            $this->assign('circle_list', $circle_list);
        }
         */
        /*德尚网络待完善 END*/

        //好友动态
        $model_fd = Model('snsfriend');
        $fields = 'member.member_id,member.member_name,member.member_avatar';
        $follow_list = $model_fd->listFriend(array('friend_frommid' => "{session('member_id')}"), $fields, 15, 'detail');
        $member_ids = array();
        $follow_list_new = array();
        if (is_array($follow_list)) {
            foreach ($follow_list as $v) {
                $follow_list_new[$v['member_id']] = $v;
                $member_ids[] = $v['member_id'];
            }
        }
        $tracelog_model = Model('snstracelog');
        //条件
        $condition = array();
        $condition['trace_memberid'] = array('in', $member_ids);
        $condition['trace_privacy'] = 0;
        $condition['trace_state'] = 0;
        $tracelist = db('snstracelog')->where($condition)->field('count(*) as _count,trace_memberid')->group('trace_memberid')->limit(5)->select();
        $tracelist_new = array();
        $follow_list = array();
        if (!empty($tracelist)) {
            foreach ($tracelist as $k => $v) {
                $tracelist_new[$v['trace_memberid']] = $v['_count'];
                $follow_list[] = $follow_list_new[$v['trace_memberid']];
            }
        }
        $this->assign('tracelist', $tracelist_new);
        $this->assign('follow_list', $follow_list);
        return $this->fetch($this->template_dir . 'sns_info');
    }

    /**
     * 设置常用菜单
     */
    public function common_operations() {
        $type = $_GET['type'];
        $value = $_GET['value'];
        if (!in_array($type, array('add', 'del')) || empty($value)) {
            echo false;
            exit;
        }
        $quicklink = $this->quicklink;
        if ($type == 'add') {
            if (!empty($quicklink)) {
                array_push($quicklink, $value);
            } else {
                $quicklink[] = $value;
            }
        } else {
            $quicklink = array_diff($quicklink, array($value));
        }
        $quicklink = array_unique($quicklink);
        $quicklink = implode(',', $quicklink);
        $result = Model('member')->editMember(array('member_id' => session('member_id')), array('member_quicklink' => $quicklink));
        if ($result) {
            echo true;
            exit;
        } else {
            echo false;
            exit;
        }
    }

}

?>
