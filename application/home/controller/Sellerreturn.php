<?php

namespace app\home\controller;

use think\Lang;

class Sellerreturn extends BaseSeller {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/sellerreturn.lang.php');
        //向模板页面输出退款退货状态
        $this->getRefundStateArray();
    }

    /**
     * 退货记录列表页
     *
     */
    public function index() {
        $model_refund = Model('refundreturn');
        $condition = array();
        $condition['store_id'] = session('store_id');
        $condition['refund_type'] = '2'; //类型:1为退款,2为退货
        $keyword_type = array('order_sn', 'refund_sn', 'buyer_name');

        $key = input('get.key');
        $type = input('get.type');
        if (trim($key) != '' && in_array($type, $keyword_type)) {
            $condition[$type] = array('like', '%' . $key . '%');
        }
        $add_time_from = input('get.add_time_from');
        $add_time_to = input('get.add_time_to');
        if (trim($add_time_from) != '' || trim($add_time_to) != '') {
            $add_time_from = strtotime(trim($add_time_from));
            $add_time_to = strtotime(trim($add_time_to));
            if ($add_time_from !== false || $add_time_to !== false) {
                $condition['add_time'] = array('between', array($add_time_from, $add_time_to));
            }
        }
        $seller_state = intval(input('get.state'));
        if ($seller_state > 0) {
            $condition['seller_state'] = $seller_state;
        }
        $order_lock = intval(input('param.lock'));
        if ($order_lock != 1) {
            $order_lock = 2;
        }
        $condition['order_lock'] = $order_lock;
        $return_list = $model_refund->getReturnList($condition, 10);

        $this->assign('return_list', $return_list);
        //分页
        $page = $model_refund->page_info->render();
        $this->assign('page', $page);

        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('seller_return');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem($order_lock);

        return $this->fetch($this->template_dir.'index');
    }

    /**
     * 退货审核页
     *
     */
    public function edit() {
        $model_refund = Model('refundreturn');
        $condition = array();
        $condition['store_id'] = session('store_id');
        $condition['refund_id'] = intval(input('param.return_id'));
        $return_list = $model_refund->getReturnList($condition);
        $return = $return_list[0];



        if (!request()->isPost()) {

            $this->assign('return', $return);
            $info['buyer'] = array();
            if (!empty($return['pic_info'])) {
                $info = unserialize($return['pic_info']);
            }
//            $this->assign('pic_list', $info['buyer']);
            $this->assign('pic_list', '');
            $model_member = Model('member');
            $member = $model_member->getMemberInfoByID($return['buyer_id']);
            $this->assign('member', $member);
            $condition = array();
            $condition['order_id'] = $return['order_id'];
            $order = $model_refund->getRightOrderList($condition, $return['order_goods_id']);
            $this->assign('order', $order);
            $this->assign('store', $order['extend_store']);
            $this->assign('order_common', $order['extend_order_common']);
            $this->assign('goods_list', $order['goods_list']);

            /* 设置卖家当前菜单 */
            $this->setSellerCurMenu('seller_return');
            /* 设置卖家当前栏目 */
            $this->setSellerCurItem();
            return $this->fetch($this->template_dir.'edit');
        } else {


            $reload = url('sellerreturn/index',['lock'=>'1']);
            if ($return['order_lock'] == '2') {
                $reload = url('Sellerreturn/index',['lock'=>2]);
            }
            if ($return['seller_state'] != '1') {//检查状态,防止页面刷新不及时造成数据错误
                showDialog(lang('wrong_argument'), $reload, 'error');
            }
            $order_id = $return['order_id'];
            $refund_array = array();
            $refund_array['seller_time'] = time();
            $refund_array['seller_state'] = $_POST['seller_state']; //卖家处理状态:1为待审核,2为同意,3为不同意
            $refund_array['seller_message'] = $_POST['seller_message'];

            if ($refund_array['seller_state'] == '2' && empty($_POST['return_type'])) {
                $refund_array['return_type'] = '2'; //退货类型:1为不用退货,2为需要退货
            } elseif ($refund_array['seller_state'] == '3') {
                $refund_array['refund_state'] = '3'; //状态:1为处理中,2为待管理员处理,3为已完成
            } else {
                $refund_array['seller_state'] = '2';
                $refund_array['refund_state'] = '2';
                $refund_array['return_type'] = '1'; //选择弃货
            }
            $state = $model_refund->editRefundReturn($condition, $refund_array);
            if ($state) {
                if ($refund_array['seller_state'] == '3' && $return['order_lock'] == '2') {
                    $model_refund->editOrderUnlock($order_id); //订单解锁
                }
                $this->recordSellerLog('退货处理，退货编号：' . $return['refund_sn']);

                // 发送买家消息
                $param = array();
                $param['code'] = 'refund_return_notice';
                $param['member_id'] = $return['buyer_id'];
                $param['param'] = array(
                    'refund_url' => url('Home/Memberreturn/view', ['return_id' => $return['refund_id']]),
                    'refund_sn' => $return['refund_sn']
                );
                \mall\queue\QueueClient::push('sendMemberMsg', sendMemberMsg);

                showDialog(lang('ds_common_save_succ'), $reload, 'succ');
            } else {
                showDialog(lang('ds_common_save_fail'), $reload, 'error');
            }
        }
    }

    /**
     * 收货
     *
     */
    public function receive() {
        $model_refund = Model('refundreturn');
        $model_trade = Model('trade');
        $condition = array();
        $condition['store_id'] = session('store_id');
        $condition['refund_id'] = intval(input('param.return_id'));
        $return_list = $model_refund->getReturnList($condition);
        $return = $return_list[0];
        $this->assign('return', $return);
        $return_delay = $model_trade->getMaxDay('return_delay'); //发货默认5天后才能选择没收到
        $delay_time = time() - $return['delay_time'] - 60 * 60 * 24 * $return_delay;
        $this->assign('return_delay', $return_delay);
        $this->assign('return_confirm', $model_trade->getMaxDay('return_confirm')); //卖家不处理收货时按同意并弃货处理
        $this->assign('delay_time', $delay_time);
        if (!request()->isPost()) {
            $express_list = rkcache('express', true);
            if ($return['express_id'] > 0 && !empty($return['invoice_no'])) {
                $this->assign('e_name', $express_list[$return['express_id']]['e_name']);
                $this->assign('e_code', $express_list[$return['express_id']]['e_code']);
            }
            return $this->fetch($this->template_dir.'receive');
        } else {

            if ($return['seller_state'] != '2' || $return['goods_state'] != '2') {//检查状态,防止页面刷新不及时造成数据错误
                showDialog(lang('wrong_argument'), 'reload', 'error', 'CUR_DIALOG.close();');
            }
            $refund_array = array();
            if ($_POST['return_type'] == '3' && $delay_time > 0) {
                $refund_array['goods_state'] = '3';
            } else {
                $refund_array['receive_time'] = time();
                $refund_array['receive_message'] = '确认收货完成';
                $refund_array['refund_state'] = '2'; //状态:1为处理中,2为待管理员处理,3为已完成
                $refund_array['goods_state'] = '4';
            }
            $state = $model_refund->editRefundReturn($condition, $refund_array);
            if ($state) {
                $this->recordSellerLog('退货确认收货，退货编号：' . $return['refund_sn']);
                
                // 发送买家消息
                $param = array();
                $param['code'] = 'refund_return_notice';
                $param['member_id'] = $return['buyer_id'];
                $param['param'] = array(
                    'refund_url' => url('Home/memberreturn/view',['return_id'=>$return['refund_id']]),
                    'refund_sn' => $return['refund_sn']
                );
                \mall\queue\QueueClient::push('sendMemberMsg', sendMemberMsg);
                
                showDialog(lang('ds_common_save_succ'), 'reload', 'succ', 'CUR_DIALOG.close();');
            } else {
                showDialog(lang('ds_common_save_fail'), 'reload', 'error', 'CUR_DIALOG.close();');
            }
        }
    }

    /**
     * 退货记录查看页
     *
     */
    public function view() {
        $model_refund = Model('refundreturn');
        $condition = array();
        $condition['store_id'] = session('store_id');
        $condition['refund_id'] = intval(input('param.return_id'));
        $return_list = $model_refund->getReturnList($condition);
        $return = $return_list[0];
        $this->assign('return', $return);
        $express_list = rkcache('express', true);
        if ($return['express_id'] > 0 && !empty($return['invoice_no'])) {
            $this->assign('e_name', $express_list[$return['express_id']]['e_name']);
            $this->assign('e_code', $express_list[$return['express_id']]['e_code']);
        }
        $info['buyer'] = array();
        if (!empty($return['pic_info'])) {
            $info = unserialize($return['pic_info']);
        }
//        $this->assign('pic_list', $info['buyer']);
        $this->assign('pic_list', '');
        $model_member = Model('member');
        $member = $model_member->getMemberInfoByID($return['buyer_id']);
        $this->assign('member', $member);
        $condition = array();
        $condition['order_id'] = $return['order_id'];
        $order = $model_refund->getRightOrderList($condition, $return['order_goods_id']);
        $this->assign('order', $order);
        $this->assign('store', $order['extend_store']);
        $this->assign('order_common', $order['extend_order_common']);
        $this->assign('goods_list', $order['goods_list']);
        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('seller_return');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem();
        return $this->fetch($this->template_dir.'view');
    }

    function getRefundStateArray($type = 'all') {
        $state_array = array(
            '1' => lang('refund_state_confirm'),
            '2' => lang('refund_state_yes'),
            '3' => lang('refund_state_no')
        ); //卖家处理状态:1为待审核,2为同意,3为不同意
        $this->assign('state_array', $state_array);

        $admin_array = array(
            '1' => '处理中',
            '2' => '待处理',
            '3' => '已完成'
        ); //确认状态:1为买家或卖家处理中,2为待平台管理员处理,3为退款退货已完成
        $this->assign('admin_array', $admin_array);

        $state_data = array(
            'seller' => $state_array,
            'admin' => $admin_array
        );
        if ($type == 'all') {
            return $state_data; //返回所有
        }
        return $state_data[$type];
    }
    
    
    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_type 导航类型
     * @param string $menu_key 当前导航的menu_key
     * @return
     */
    function getSellerItemList() {
        $menu_array = array(
            array(
                'name' => '2',
                'text' => '售前退货',
                'url' => url('home/sellerreturn/index',['lock'=>2])
            ),
            array(
                'name' => '1',
                'text' => '售后退货',
                'url' => url('home/sellerreturn/index',['lock'=>1])
            ),
        );
        return $menu_array;
    }


}
