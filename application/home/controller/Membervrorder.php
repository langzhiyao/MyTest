<?php

namespace app\home\controller;

use think\Lang;

class Membervrorder extends BaseMember
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        Lang::load(APP_PATH . 'home/lang/zh-cn/memberorder.lang.php');
    }

    /**
     * 买家我的订单
     *
     */
    public function index()
    {
        $model_vr_order = Model('vrorder');
        //搜索
        $condition = array();
        $condition['buyer_id'] = session('member_id');
        if (input('param.order_sn') != '') {
            $condition['order_sn'] = input('param.order_sn');
        }
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', input('param.query_start_date'));
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', input('param.query_end_date'));
        $start_unixtime = $if_start_date ? strtotime(input('param.query_start_date')) : null;
        $end_unixtime = $if_end_date ? strtotime(input('param.query_end_date')) : null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('time', array($start_unixtime, $end_unixtime));
        }
        if (input('param.state_type') != '') {
            $condition['order_state'] = str_replace(array(
                                                        'state_new', 'state_pay', 'state_success', 'state_cancel'
                                                    ), array(
                                                        ORDER_STATE_NEW, ORDER_STATE_PAY, ORDER_STATE_SUCCESS,
                                                        ORDER_STATE_CANCEL
                                                    ), input('param.state_type'));
        }

        $order_list = $model_vr_order->getOrderList($condition, 20, '*', 'order_id desc');
        //没有使用的兑换码列表
        $order_list = $model_vr_order->getCodeRefundList($order_list);

        foreach ($order_list as $key => $order) {

            //显示取消订单
            $order_list[$key]['if_cancel'] = $model_vr_order->getOrderOperateState('buyer_cancel', $order);

            //显示支付
            $order_list[$key]['if_pay'] = $model_vr_order->getOrderOperateState('payment', $order);

            //显示删除订单(放入回收站)
            $order_list[$key]['if_delete'] = $model_vr_order->getOrderOperateState('delete', $order);

            //显示永久删除
            $order_list[$key]['if_drop'] = $model_vr_order->getOrderOperateState('drop', $order);

            //显示还原订单
            $order_list[$key]['if_restore'] = $model_vr_order->getOrderOperateState('restore', $order);

            //显示退款
            $order_list[$key]['if_refund'] = $model_vr_order->getOrderOperateState('refund', $order);

            //显示评价
            $order_list[$key]['if_evaluation'] = $model_vr_order->getOrderOperateState('evaluation', $order);

            //显示分享
            $order_list[$key]['if_share'] = $model_vr_order->getOrderOperateState('share', $order);

            //显示商家信息(QQ,WW)
            $order_list[$key]['extend_store'] = Model('store')->getStoreInfoByID($order['store_id']);
        }

        $this->assign('order_list', $order_list);
        $this->assign('show_page', $model_vr_order->page_info->render());
        $this->setMemberCurMenu('member_vr_order');
        $this->setMemberCurItem(input('param.recycle') ? 'member_order_recycle' : 'member_order');
        return $this->fetch($this->template_dir.'index');
    }

    /**
     * 订单详细
     *
     */
    public function show_order()
    {
        $order_id = intval(input('param.order_id'));
        if ($order_id <= 0) {
           $this->error(lang('member_order_none_exist'));
        }
        $model_vr_order = Model('vrorder');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = session('member_id');
        $order_info = $model_vr_order->getOrderInfo($condition);
        if (empty($order_info) || $order_info['delete_state'] == ORDER_DEL_STATE_DROP) {
            $this->error(lang('member_order_none_exist'));
        }
        $order_list = array();
        $order_list[$order_id] = $order_info;
        $order_list = $model_vr_order->getCodeRefundList($order_list);//没有使用的兑换码列表
        $order_info = $order_list[$order_id];

        $store_info = Model('store')->getStoreInfo(array('store_id' => $order_info['store_id']));

        //取兑换码列表
        $vr_code_list = $model_vr_order->getOrderCodeList(array('order_id' => $order_info['order_id']));
        $order_info['extend_vr_order_code'] = $vr_code_list;

        //显示取消订单
        $order_info['if_cancel'] = $model_vr_order->getOrderOperateState('buyer_cancel', $order_info);

        //显示订单进行步骤
        $order_info['step_list'] = $model_vr_order->getOrderStep($order_info);

        //显示退款
        $order_info['if_refund'] = $model_vr_order->getOrderOperateState('refund', $order_info);

        //显示评价
        $order_info['if_evaluation'] = $model_vr_order->getOrderOperateState('evaluation', $order_info);

        //显示分享
        $order_info['if_share'] = $model_vr_order->getOrderOperateState('share', $order_info);

        //显示系统自动取消订单日期
        if ($order_info['order_state'] == ORDER_STATE_NEW) {
            $order_info['order_cancel_day'] = $order_info['add_time'] + ORDER_AUTO_CANCEL_DAY * 24 * 3600;
        }

        $this->assign('order_info', $order_info);
        $this->assign('store_info', $store_info);
        $this->setMemberCurMenu('member_vr_order');
        return $this->fetch($this->template_dir.'show_order');
    }

    /**
     * 买家订单状态操作
     *
     */
    public function change_state()
    {
        $model_vr_order = Model('vrorder');
        $condition = array();
        $condition['order_id'] = intval(input('param.order_id'));
        $condition['buyer_id'] = session('member_id');
        $order_info = $model_vr_order->getOrderInfo($condition);

        if (input('param.state_type') == 'order_cancel') {
            $result = $this->_order_cancel($order_info, $_POST);
        }

        if (!isset($result['code'])) {
            showDialog('没有权限操作', '', 'error');
        }
        else {
            showDialog($result['msg'], 'reload', 'js');
        }
    }

    /**
     * 取消订单
     */
    private function _order_cancel($order_info, $post)
    {
        if (!request()->isPost()) {
            $this->assign('order_info', $order_info);
            echo $this->fetch($this->template_dir.'cancel');
            exit();
        }
        else {
            $model_vr_order = Model('vrorder');
            $logic_vr_order = model('vrorder','logic');
            $if_allow = $model_vr_order->getOrderOperateState('buyer_cancel', $order_info);
            if (!$if_allow) {
                return callback(false, '无权操作');
            }

            $msg = $post['state_info1'] != '' ? $post['state_info1'] : $post['state_info'];
            return $logic_vr_order->changeOrderStateCancel($order_info, 'buyer', $msg);
        }
    }

    /**
     * 发送兑换码到手机
     */
    public function resend()
    {
        if (!request()->isPost()) {
            return $this->fetch($this->template_dir.'resend');
            exit();
        }
        if (!preg_match('/^[\d]{11}$/', $_POST['buyer_phone'])) {
            showDialog('请正确填写手机号');
        }
        $order_id = intval($_POST['order_id']);
        if ($order_id <= 0) {
            showDialog('参数错误');
        }

        $model_vr_order = Model('vrorder');

        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = session('member_id');
        $order_info = $model_vr_order->getOrderInfo($condition);
        if (empty($order_info) && $order_info['order_state'] != ORDER_STATE_PAY) {
            showDialog('订单信息发生错误');
        }
        if ($order_info['vr_send_times'] >= 5) {
            showDialog('您发送的次数过多，无法发送');
        }

        //发送兑换码到手机
        $param = array(
            'order_id' => $order_id, 'buyer_id' => session('member_id'), 'buyer_phone' => $order_info['buyer_phone']
        );
        \mall\queue\QueueClient::push('sendVrCode', $param);

        $model_vr_order->editOrder(array(
                                       'vr_send_times' => array(
                                           'exp', 'vr_send_times+1'
                                       )
                                   ), array('order_id' => $order_id));

        showDialog('发送成功', '', 'succ', "DialogManager.close('vr_code_resend');");
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_type 导航类型
     * @param string $menu_key 当前导航的menu_key
     * @return
     */
    protected function getMemberItemList()
    {
        $menu_array = array(
            array(
                'name' => 'member_order', 'text' => lang('ds_member_path_order_list'),
                'url' => url('membervrorder/index')
            ),
        );
        return $menu_array;
    }
}