<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Order extends AdminControl {

    const EXPORT_SIZE = 1000;

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/order.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    public function index() {
        if(session('admin_is_super') !=1 && !in_array('4',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $model_order = model('order');
        $condition = array();

        $order_sn = input('param.order_sn');
        if ($order_sn) {
            $condition['order_sn'] = $order_sn;
        }
        $store_name = input('param.store_name');
        if ($store_name) {
            $condition['store_name'] = $store_name;
        }
        $order_state = input('param.order_state');
        if (in_array($order_state, array('0', '10', '20', '30', '40'))) {
            $condition['order_state'] = $order_state;
        }
        $payment_code = input('param.payment_code');
        if ($payment_code) {
            $condition['payment_code'] = $payment_code;
        }
        $buyer_name = input('param.buyer_name');
        if ($buyer_name) {
            $condition['buyer_name'] = $buyer_name;
        }
        $query_start_time = input('param.query_start_time');
        $query_end_time = input('param.query_end_time');
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_start_time);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_end_time);
        $start_unixtime = $if_start_time ? strtotime($query_start_time) : null;
        $end_unixtime = $if_end_time ? strtotime($query_end_time) : null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('between', array($start_unixtime, $end_unixtime));
        }
        $order_list = $model_order->getOrderList($condition, 10);
        $this->assign('show_page', $model_order->page_info->render());
        
        foreach ($order_list as $order_id => $order_info) {
            //显示取消订单
            $order_list[$order_id]['if_cancel'] = $model_order->getOrderOperateState('system_cancel', $order_info);
            //显示收到货款
            $order_list[$order_id]['if_system_receive_pay'] = $model_order->getOrderOperateState('system_receive_pay', $order_info);
        }
        //显示支付接口列表(搜索)
        $payment_list = Model('payment')->getPaymentOpenList();
        $this->assign('payment_list', $payment_list);
        $this->assign('order_list', $order_list);
        $this->setAdminCurItem('add');
        return $this->fetch('index');
    }

    /**
     * 平台订单状态操作
     *
     */
    public function change_state() {
        $order_id = intval(input('param.order_id'));
        if ($order_id <= 0) {
            $this->error(lang('miss_order_number'));
        }
        $model_order = Model('order');

        //获取订单详细
        $condition = array();
        $condition['order_id'] = $order_id;
        $order_info = $model_order->getOrderInfo($condition);

        $state_type = input('param.state_type');
        if ($state_type == 'cancel') {
            $result = $this->_order_cancel($order_info);
        } elseif ($state_type == 'receive_pay') {
            $result = $this->_order_receive_pay($order_info, $_POST);
        }
        if (!$result['code']) {
            $this->error($result['msg']);
        } else {
            $this->success($result['msg'],'Order/index');
        }
    }

    /**
     * 系统取消订单
     */
    private function _order_cancel($order_info) {
        $order_id = $order_info['order_id'];
        $model_order = Model('order');
        $logic_order = model('order','logic');
        $if_allow = $model_order->getOrderOperateState('system_cancel', $order_info);
        if (!$if_allow) {
            return ds_callback(false, '无权操作');
        }
        $result = $logic_order->changeOrderStateCancel($order_info, 'system', $this->admin_info['admin_name']);
        if ($result['code']) {
            $this->log(lang('order_log_cancel') . ',' . lang('order_number') . ':' . $order_info['order_sn'], 1);
        }
        return $result;
    }

    /**
     * 系统收到货款
     * @throws Exception
     */
    private function _order_receive_pay($order_info, $post) {
        $order_id = $order_info['order_id'];
        $model_order = Model('order');
        $logic_order = model('order','logic');
        $if_allow = $model_order->getOrderOperateState('system_receive_pay', $order_info);
        if (!$if_allow) {
            return ds_callback(false, '无权操作');
        }

        if (!request()->isPost()) {
            $this->assign('order_info', $order_info);
            //显示支付接口列表
            $payment_list = Model('payment')->getPaymentOpenList();
            //去掉预存款和货到付款
            foreach ($payment_list as $key => $value) {
                if ($value['payment_code'] == 'predeposit' || $value['payment_code'] == 'offline') {
                    unset($payment_list[$key]);
                }
            }
            $this->assign('payment_list', $payment_list);
            $this->setAdminCurItem('receive_pay');
            echo $this->fetch('receive_pay');
            exit();
        }

        $order_list = $model_order->getOrderList(array('pay_sn' => $order_info['pay_sn'], 'order_state' => ORDER_STATE_NEW));
        $result = $logic_order->changeOrderReceivePay($order_list, 'system', $this->admin_info['admin_name'], $post);
        if ($result['code']) {
            $this->log('将订单改为已收款状态,' . lang('order_number') . ':' . $order_info['order_sn'], 1);
        }
        return $result;
    }

    /**
     * 查看订单
     *
     */
    public function show_order() {
        $order_id = intval(input('param.order_id'));
        if ($order_id <= 0) {
            $this->error(lang('miss_order_number'));
        }
        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo(array('order_id' => $order_id), array('order_goods', 'order_common', 'store'));

        //订单变更日志
        $log_list = $model_order->getOrderLogList(array('order_id' => $order_info['order_id']));
        $this->assign('order_log', $log_list);

        //退款退货信息
        $model_refund = Model('refundreturn');
        $condition = array();
        $condition['order_id'] = $order_info['order_id'];
        $condition['seller_state'] = 2;
        $condition['admin_time'] = array('gt', 0);
        $return_list = $model_refund->getReturnList($condition);
        $this->assign('return_list', $return_list);

        //退款信息
        $refund_list = $model_refund->getRefundList($condition);
        $this->assign('refund_list', $refund_list);

        //卖家发货信息
        if (!empty($order_info['extend_order_common']['daddress_id'])) {
            $daddress_info = Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_common']['daddress_id']));
            $this->assign('daddress_info', $daddress_info);
        }
        $this->assign('order_info', $order_info);
        $this->setAdminCurItem('add');
        return $this->fetch('show_order');
    }

    /**
     * 导出
     *
     */
    public function export_step1() {

        $model_order = Model('order');
        $condition = array();
        $order_sn = input('param.order_sn');
        if ($order_sn) {
            $condition['order_sn'] = $order_sn;
        }
        $store_name = input('param.store_name');
        if ($store_name) {
            $condition['store_name'] = $store_name;
        }
        $order_state = input('param.order_state');
        if (in_array($order_state, array('0', '10', '20', '30', '40'))) {
            $condition['order_state'] = $order_state;
        }
        $payment_code = input('param.payment_code');
        if ($payment_code) {
            $condition['payment_code'] = $payment_code;
        }
        $buyer_name = input('param.buyer_name');
        if ($buyer_name) {
            $condition['buyer_name'] = $buyer_name;
        }
        $query_start_time = input('param.query_start_time');
        $query_end_time = input('param.query_end_time');
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_start_time);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_end_time);
        $start_unixtime = $if_start_time ? strtotime($query_start_time) : null;
        $end_unixtime = $if_end_time ? strtotime($query_end_time) : null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('between', array($start_unixtime, $end_unixtime));
        }

        if (!is_numeric($_GET['curpage'])) {
            $count = $model_order->getOrderCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE) { //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                $this->assign('list', $array);
                $this->assign('murl', url('order/index'));
                return $this->fetch('export.excel');
            } else { //如果数量小，直接下载
                $data = $model_order->getOrderList($condition, '', '*', 'order_id desc', self::EXPORT_SIZE);
                $this->createExcel($data);
            }
        } else { //下载
            $limit1 = ($_GET['curpage'] - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_order->getOrderList($condition, '', '*', 'order_id desc', "{$limit1},{$limit2}");
            $this->createExcel($data);
        }
    }

    /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel($data = array()) {
        $excel_obj = new \excel\Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id' => 's_title', 'Font' => array('FontName' => '宋体', 'Size' => '12', 'Bold' => '1')));
        //header
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_no'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_store'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_buyer'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_xtimd'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_count'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_yfei'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_paytype'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_state'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_storeid'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_buyerid'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_bemail'));
        //data
        foreach ((array) $data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => 'DS' . $v['order_sn']);
            $tmp[] = array('data' => $v['store_name']);
            $tmp[] = array('data' => $v['buyer_name']);
            $tmp[] = array('data' => date('Y-m-d H:i:s', $v['add_time']));
            $tmp[] = array('format' => 'Number', 'data' => dsPriceFormat($v['order_amount']));
            $tmp[] = array('format' => 'Number', 'data' => dsPriceFormat($v['shipping_fee']));
            $tmp[] = array('data' => orderPaymentName($v['payment_code']));
            $tmp[] = array('data' => orderState($v));
            $tmp[] = array('data' => $v['store_id']);
            $tmp[] = array('data' => $v['buyer_id']);
            $tmp[] = array('data' => $v['buyer_email']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset(lang('exp_od_order'), CHARSET));
        $excel_obj->generateXML($excel_obj->charset(lang('exp_od_order'), CHARSET) . $_GET['curpage'] . '-' . date('Y-m-d-H', time()));
    }

}
