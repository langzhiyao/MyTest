<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Bill extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/bill.lang.php');
    }

    /**
     * 所有月份销量账单
     *
     */
    public function index() {
        //检查是否需要生成上月及更早结算单的程序不再执行，执行量较大，放到任务计划中触发
        $condition = array();
        $query_year = input('get.query_year');
        if (preg_match('/^\d{4}$/', $query_year, $match)) {
            $condition['os_year'] = $query_year;
        }
        $model_bill = Model('bill');
        $bill_list = $model_bill->getOrderStatisList($condition, '*', 12, 'os_month desc');
        $this->assign('bill_list', $bill_list);
        $this->assign('show_page', $model_bill->page_info->render());

        $this->setAdminCurItem('index');
        return $this->fetch('index');
    }

    /**
     * 某月所有店铺销量账单
     *
     */
    public function show_statis() {
        $os_month = input('param.os_month');
        if (!empty($os_month) && !preg_match('/^20\d{4}$/', $os_month, $match)) {
            $this->error(lang('param_error'));
        }
        $model_bill = Model('bill');
        $condition = array();
        if (!empty($os_month)) {
            $condition['os_month'] = intval($os_month);
        }
        $bill_state = input('get.bill_state');
        if (is_numeric($bill_state)) {
            $condition['ob_state'] = intval($bill_state);
        }
        $query_store = input('get.query_store');
        if (preg_match('/^\d{1,8}$/', $query_store)) {
            $condition['ob_store_id'] = $query_store;
        } elseif ($query_store != '') {
            $condition['ob_store_name'] = $query_store;
        }
        $bill_list = $model_bill->getOrderBillList($condition, '*', 30, 'ob_no desc');
        $this->assign('bill_list', $bill_list);
        $this->assign('show_page', $model_bill->page_info->render());

        $this->setAdminCurItem('show_statis');
        return $this->fetch('show_statis');
    }

    /**
     * 某店铺某月订单列表
     *
     */
    public function show_bill() {
        $ob_no = input('param.ob_no');
        if (!preg_match('/^20\d{5,12}$/', $ob_no, $match)) {
            $this->error(lang('param_error'));
        }
        $model_bill = Model('bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_no' => $ob_no));
        if (!$bill_info) {
            $this->error(lang('param_error'));
        }

        $order_condition = array();
        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
        $order_condition['store_id'] = $bill_info['ob_store_id'];
        
        $query_start_date = input('get.query_start_date');
        $query_end_date = input('get.query_end_date');
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_start_date);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_end_date);
        $start_unixtime = $if_start_date ? strtotime($query_start_date) : null;
        $end_unixtime = $if_end_date ? strtotime($query_end_date) : null;
        
        $end_unixtime = $if_end_date ? $end_unixtime + 86400 - 1 : null;
        if ($if_start_date || $if_end_date) {
            $order_condition['finnshed_time'] = array('between', "{$start_unixtime},{$end_unixtime}");
        } else {
            $order_condition['finnshed_time'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }
        
        $query_type = input('param.query_type');
        if ($query_type == 'refund') {
            //退款订单列表
            $model_refund = Model('refundreturn');
            $refund_condition = array();
            $refund_condition['seller_state'] = 2;
            $refund_condition['store_id'] = $bill_info['ob_store_id'];
            $refund_condition['goods_id'] = array('gt', 0);
            $refund_condition['admin_time'] = $order_condition['finnshed_time'];
            $refund_list = $model_refund->getRefundReturnList($refund_condition, 20, '*,ROUND(refund_amount*commis_rate/100,2) as commis_amount');
            if (is_array($refund_list) && count($refund_list) == 1 && $refund_list[0]['refund_id'] == '') {
                $refund_list = array();
            }
            //取返还佣金
            $this->assign('refund_list', $refund_list);
            $this->assign('show_page', $model_refund->page_info->render());
            $sub_tpl_name = 'show_refund_list';
        } elseif ($query_type == 'cost') {

            //店铺费用
            $model_store_cost = Model('storecost');
            $cost_condition = array();
            $cost_condition['cost_store_id'] = $bill_info['ob_store_id'];
            $cost_condition['cost_time'] = $order_condition['finnshed_time'];
            $store_cost_list = $model_store_cost->getStoreCostList($cost_condition, 20);
            //取得店铺名字
            $store_info = Model('store')->getStoreInfoByID($bill_info['ob_store_id']);
            $this->assign('cost_list', $store_cost_list);
            $this->assign('store_info', $store_info);
            $this->assign('show_page', $model_store_cost->page_info->render());
            $sub_tpl_name = 'show_cost_list';
        } else {

            //订单列表
            $model_order = Model('order');
            $order_list = $model_order->getOrderList($order_condition, 20);

            //然后取订单商品佣金
            $order_id_array = array();
            if (is_array($order_list)) {
                foreach ($order_list as $order_info) {
                    $order_id_array[] = $order_info['order_id'];
                }
            }
            $order_goods_condition = array();
            $order_goods_condition['order_id'] = array('in', $order_id_array);
            $field = 'SUM(ROUND(goods_pay_price*commis_rate/100,2)) as commis_amount,order_id';
            $commis_list = $model_order->getOrderGoodsList($order_goods_condition, $field, null, null, '', 'order_id', 'order_id');
            $this->assign('commis_list', $commis_list);
            $this->assign('order_list', $order_list);
            $this->assign('show_page', $model_order->page_info->render());
            $sub_tpl_name = 'show_order_list';
        }

        $this->assign('bill_info', $bill_info);
        $this->setAdminCurItem('show_bill');
        return $this->fetch($sub_tpl_name);
    }

    public function bill_check() {
        $ob_no = input('param.ob_no');
        if (!preg_match('/^20\d{5,12}$/', $ob_no)) {
            $this->error(lang('param_error'));
        }
        $model_bill = Model('bill');
        $condition = array();
        $condition['ob_no'] = $ob_no;
        $condition['ob_state'] = BILL_STATE_STORE_COFIRM;
        $update = $model_bill->editOrderBill(array('ob_state' => BILL_STATE_SYSTEM_CHECK), $condition);
        if ($update) {
            $this->log('审核账单,账单号：' . $ob_no, 1);
            $this->success('审核成功，账单进入付款环节');
        } else {
            $this->log('审核账单，账单号：' . $ob_no, 0);
            $this->error('审核失败');
        }
    }

    /**
     * 账单付款
     *
     */
    public function bill_pay() {
        $ob_no = input('param.ob_no');
        if (!preg_match('/^20\d{5,12}$/', $ob_no)) {
            $this->error(lang('param_error'));
        }
        $model_bill = Model('bill');
        $condition = array();
        $condition['ob_no'] = $ob_no;
        $condition['ob_state'] = BILL_STATE_SYSTEM_CHECK;
        $bill_info = $model_bill->getOrderBillInfo($condition);
        if (!$bill_info) {
            $this->error(lang('param_error'));
        }
        if (chksubmit()) {
            if (!preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_POST['pay_date'])) {
                $this->error(lang('param_error'));
            }
            $input = array();
            $input['ob_pay_content'] = $_POST['pay_content'];
            $input['ob_pay_date'] = strtotime($_POST['pay_date']);
            $input['ob_state'] = BILL_STATE_SUCCESS;
            $update = $model_bill->editOrderBill($input, $condition);
            if ($update) {
                $model_store_cost = Model('storecost');
                $cost_condition = array();
                $cost_condition['cost_store_id'] = $bill_info['ob_store_id'];
                $cost_condition['cost_state'] = 0;
                $cost_condition['cost_time'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
                $model_store_cost->editStoreCost(array('cost_state' => 1), $cost_condition);

                // 发送店铺消息
                $param = array();
                $param['code'] = 'store_bill_gathering';
                $param['store_id'] = $bill_info['ob_store_id'];
                $param['param'] = array(
                    'bill_no' => $bill_info['ob_no']
                );
                \mall\queue\QueueClient::push('sendStoreMsg', $param);

                $this->log('账单付款,账单号：' . $ob_no, 1);
                $this->success('保存成功', 'bill/show_statis?os_month=' . $bill_info['os_month']);
            } else {
                $this->log('账单付款,账单号：' . $ob_no, 1);
                $this->error('保存失败');
            }
        } else {
            $this->setAdminCurItem('bill_pay');
            return $this->fetch('bill_pay');
        }
    }

    /**
     * 打印结算单
     *
     */
    public function bill_print() {
        $ob_no = input('param.ob_no');
        if (!preg_match('/^20\d{5,12}$/', $ob_no)) {
            $this->error(lang('param_error'));
        }
        $model_bill = Model('bill');
        $condition = array();
        $condition['ob_no'] = $ob_no;
        $condition['ob_state'] = BILL_STATE_SUCCESS;
        $bill_info = $model_bill->getOrderBillInfo($condition);
        if (!$bill_info) {
            $this->error(lang('param_error'));
        }

        $this->assign('bill_info', $bill_info);

        return $this->fetch('bill_print', 'null_layout');
    }

    /**
     * 导出平台月出账单表
     *
     */
    public function export_bill() {
        $os_month = input('param.os_month');
        if (!empty($os_month) && !preg_match('/^20\d{4}$/', $os_month, $match)) {
            $this->error(lang('param_error'));
        }
        $model_bill = Model('bill');
        $condition = array();
        if (!empty($os_month)) {
            $condition['os_month'] = intval($os_month);
        }
        $bill_state = input('get.bill_state');
        if (is_numeric($bill_state)) {
            $condition['ob_state'] = intval($bill_state);
        }
        $query_store = input('get.query_store');
        if (preg_match('/^\d{1,8}$/', $query_store)) {
            $condition['ob_store_id'] = $query_store;
        } elseif ($query_store != '') {
            $condition['ob_store_name'] = $query_store;
        }
        if (!is_numeric($_GET['curpage'])) {
            $count = $model_bill->getOrderBillCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE) {
                //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                $this->assign('list', $array);
                $this->assign('murl', url('bill/index'));
                return $this->fetch('excel');
                exit();
            } else {
                //如果数量小，直接下载
                $data = $model_bill->getOrderBillList($condition, '*', '', 'ob_no desc', self::EXPORT_SIZE);
            }
        } else {
            //下载
            $limit1 = ($_GET['curpage'] - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_bill->getOrderBillList($condition, '*', '', 'ob_no desc', "{$limit1},{$limit2}");
        }

        $export_data = array();
        $export_data[0] = array('账单编号', '开始日期', '结束日期', '订单金额', '运费', '佣金金额', '退款金额', '退还佣金', '店铺费用', '本期应结', '出账日期', '账单状态', '店铺', '店铺ID');
        $ob_order_totals = 0;
        $ob_shipping_totals = 0;
        $ob_commis_totals = 0;
        $ob_order_return_totals = 0;
        $ob_commis_return_totals = 0;
        $ob_store_cost_totals = 0;
        $ob_result_totals = 0;
        foreach ($data as $k => $v) {
            $export_data[$k + 1][] = $v['ob_no'];
            $export_data[$k + 1][] = date('Y-m-d', $v['ob_start_date']);
            $export_data[$k + 1][] = date('Y-m-d', $v['ob_end_date']);
            $ob_order_totals += $export_data[$k + 1][] = $v['ob_order_totals'];
            $ob_shipping_totals += $export_data[$k + 1][] = $v['ob_shipping_totals'];
            $ob_commis_totals += $export_data[$k + 1][] = $v['ob_commis_totals'];
            $ob_order_return_totals += $export_data[$k + 1][] = $v['ob_order_return_totals'];
            $ob_commis_return_totals += $export_data[$k + 1][] = $v['ob_commis_return_totals'];
            $ob_store_cost_totals += $export_data[$k + 1][] = $v['ob_store_cost_totals'];
            $ob_result_totals += $export_data[$k + 1][] = $v['ob_result_totals'];
            $export_data[$k + 1][] = date('Y-m-d', $v['ob_create_date']);
            $export_data[$k + 1][] = billState($v['ob_state']);
            $export_data[$k + 1][] = $v['ob_store_name'];
            $export_data[$k + 1][] = $v['ob_store_id'];
        }
        $count = count($export_data);
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '合计';
        $export_data[$count][] = $ob_order_totals;
        $export_data[$count][] = $ob_shipping_totals;
        $export_data[$count][] = $ob_commis_totals;
        $export_data[$count][] = $ob_order_return_totals;
        $export_data[$count][] = $ob_commis_return_totals;
        $export_data[$count][] = $ob_store_cost_totals;
        $export_data[$count][] = $ob_result_totals;
        $csv = new Csv();
        $export_data = $csv->charset($export_data, CHARSET, 'gbk');
        $csv->filename = $csv->charset('账单-', CHARSET) . $os_month;
        $csv->export($export_data);
    }

    /**
     * 导出结算订单明细CSV
     *
     */
    public function export_order() {
        $ob_no = input('param.ob_no');
        if (!preg_match('/^20\d{5,12}$/', $ob_no)) {
            $this->error(lang('param_error'));
        }
        $model_bill = Model('bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_no' => $ob_no));
        if (!$bill_info) {
            $this->error(lang('param_error'));
        }

        $model_order = Model('order');
        $condition = array();
        $condition['order_state'] = ORDER_STATE_SUCCESS;
        $condition['store_id'] = $bill_info['ob_store_id'];
        $query_start_date = input('get.query_start_date');
        $query_end_date = input('get.query_end_date');
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_start_date);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_end_date);
        $start_unixtime = $if_start_date ? strtotime($query_start_date) : null;
        $end_unixtime = $if_end_date ? strtotime($query_end_date) : null;
        $end_unixtime = $if_end_date ? $end_unixtime + 86400 - 1 : null;
        if ($if_start_date || $if_end_date) {
            $condition['finnshed_time'] = array('between', "{$start_unixtime},{$end_unixtime}");
        } else {
            $condition['finnshed_time'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }
        if (!is_numeric($_GET['curpage'])) {
            $count = $model_order->getOrderCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE) {
                //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                $this->assign('list', $array);
                $this->assign('murl', url('bill/show_bill',['ob_no'=>$ob_no]));
                return $this->fetch('excel');
                exit();
            } else {
                //如果数量小，直接下载
                $data = $model_order->getOrderList($condition, '', '*', 'order_id desc', self::EXPORT_SIZE, array('order_goods'));
            }
        } else {
            //下载
            $limit1 = ($_GET['curpage'] - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_order->getOrderList($condition, '', '*', 'order_id desc', "{$limit1},{$limit2}", array('order_goods'));
        }

        //订单商品表查询条件
        $order_id_array = array();
        if (is_array($data)) {
            foreach ($data as $order_info) {
                $order_id_array[] = $order_info['order_id'];
            }
        }
        $order_goods_condition = array();
        $order_goods_condition['order_id'] = array('in', $order_id_array);

        $export_data = array();
        $export_data[0] = array('订单编号', '订单金额', '运费', '佣金', '下单日期', '成交日期', '商家', '商家编号', '买家', '买家编号', '商品');
        $order_totals = 0;
        $shipping_totals = 0;
        $commis_totals = 0;
        $k = 0;
        foreach ($data as $v) {
            //该订单算佣金
            $field = 'SUM(ROUND(goods_pay_price*commis_rate/100,2)) as commis_amount,order_id';
            $commis_list = $model_order->getOrderGoodsList($order_goods_condition, $field, null, null, '', 'order_id', 'order_id');
            $export_data[$k + 1][] = 'DS' . $v['order_sn'];
            $order_totals += $export_data[$k + 1][] = $v['order_amount'];
            $shipping_totals += $export_data[$k + 1][] = $v['shipping_fee'];
            $commis_totals += $export_data[$k + 1][] = floatval($commis_list[$v['order_id']]['commis_amount']);
            $export_data[$k + 1][] = date('Y-m-d', $v['add_time']);
            $export_data[$k + 1][] = date('Y-m-d', $v['finnshed_time']);
            $export_data[$k + 1][] = $v['store_name'];
            $export_data[$k + 1][] = $v['store_id'];
            $export_data[$k + 1][] = $v['buyer_name'];
            $export_data[$k + 1][] = $v['buyer_id'];
            $goods_string = '';
            if (is_array($v['extend_order_goods'])) {
                foreach ($v['extend_order_goods'] as $v) {
                    $goods_string .= $v['goods_name'] . '|单价:' . $v['goods_price'] . '|数量:' . $v['goods_num'] . '|实际支付:' . $v['goods_pay_price'] . '|佣金比例:' . $v['commis_rate'] . '%';
                }
            }
            $export_data[$k + 1][] = $goods_string;
            $k++;
        }
        $count = count($export_data);
        $export_data[$count][] = '合计';
        $export_data[$count][] = $order_totals;
        $export_data[$count][] = $shipping_totals;
        $export_data[$count][] = $commis_totals;
        $csv = new Csv();
        $export_data = $csv->charset($export_data, CHARSET, 'gbk');
        $csv->filename = $csv->charset('订单明细-', CHARSET) . $ob_no;
        $csv->export($export_data);
    }

    /**
     * 导出结算退单明细CSV
     *
     */
    public function export_refund_order() {
        $ob_no = input('param.ob_no');
        if (!preg_match('/^20\d{5,12}$/', $ob_no)) {
            $this->error(lang('param_error'));
        }
        $model_bill = Model('bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_no' => $ob_no));
        if (!$bill_info) {
            $this->error(lang('param_error'));
        }

        $model_refund = Model('refundreturn');
        $condition = array();
        $condition['seller_state'] = 2;
        $condition['store_id'] = $bill_info['ob_store_id'];
        $condition['goods_id'] = array('gt', 0);
        $query_start_date = input('get.query_start_date');
        $query_end_date = input('get.query_end_date');
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_start_date);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_end_date);
        $start_unixtime = $if_start_date ? strtotime($query_start_date) : null;
        $end_unixtime = $if_end_date ? strtotime($query_end_date) : null;
        $end_unixtime = $if_end_date ? $end_unixtime + 86400 - 1 : null;
        if ($if_start_date || $if_end_date) {
            $condition['finnshed_time'] = array('between', "{$start_unixtime},{$end_unixtime}");
        } else {
            $condition['finnshed_time'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }
        if (!is_numeric($_GET['curpage'])) {
            $count = $model_refund->getRefundReturn($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE) { //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                $this->assign('list', $array);
                $this->assign('murl', url('bill/show_bill',['query_type'=>'refund','ob_no'=>$ob_no]));
                return $this->fetch('excel');
                exit();
            } else {
                //如果数量小，直接下载
                $data = $model_refund->getRefundReturnList($condition, '', '*,ROUND(refund_amount*commis_rate/100,2) as commis_amount', self::EXPORT_SIZE);
            }
        } else {
            //下载
            $limit1 = ($_GET['curpage'] - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_refund->getRefundReturnList(condition, '', '*,ROUND(refund_amount*commis_rate/100,2) as commis_amount', "{$limit1},{$limit2}");
        }
        if (is_array($data) && count($data) == 1 && $data[0]['refund_id'] == '') {
            $refund_list = array();
        }
        $export_data = array();
        $export_data[0] = array('退单编号', '订单编号', '退单金额', '退单佣金', '类型', '退款日期', '商家', '商家编号', '买家', '买家编号');
        $refund_amount = 0;
        $commis_totals = 0;
        $k = 0;
        foreach ($data as $v) {
            $export_data[$k + 1][] = 'DS' . $v['refund_sn'];
            $export_data[$k + 1][] = 'DS' . $v['order_sn'];
            $refund_amount += $export_data[$k + 1][] = $v['refund_amount'];
            $commis_totals += $export_data[$k + 1][] = dsPriceFormat($v['commis_amount']);
            $export_data[$k + 1][] = str_replace(array(1, 2), array('退款', '退货'), $v['refund_type']);
            $export_data[$k + 1][] = date('Y-m-d', $v['admin_time']);
            $export_data[$k + 1][] = $v['store_name'];
            $export_data[$k + 1][] = $v['store_id'];
            $export_data[$k + 1][] = $v['buyer_name'];
            $export_data[$k + 1][] = $v['buyer_id'];
            $k++;
        }
        $count = count($export_data);
        $export_data[$count][] = '';
        $export_data[$count][] = '合计';
        $export_data[$count][] = $refund_amount;
        $export_data[$count][] = $commis_totals;
        $csv = new Csv();
        $export_data = $csv->charset($export_data, CHARSET, 'gbk');
        $csv->filename = $csv->charset('退单明细-', CHARSET) . $ob_no;
        $csv->export($export_data);
    }
    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '结算管理',
                'url' => url('Admin/Bill/index')
            ),
        );
        if (request()->action() == 'show_statis') {
            $title = !empty(input('param.os_month'))?input('param.os_month').'期':'';
            $menu_array[] = array(
                'name' => 'show_statis',
                'text' => $title.'商家账单列表',
                'url' => !empty($title) ? url('Admin/Bill/show_statis',['os_month'=>input('param.os_month')]) : url('Admin/Bill/show_statis'),
            );
        }
        if (request()->action() == 'show_bill') {
            $menu_array[] = array(
                'name' => 'show_statis',
                'text' => '账单明细',
                'url' => 'javascript:void(0)',
            );
        }
        return $menu_array;
    }
}

?>
