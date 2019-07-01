<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Vrbill extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/vrbill.lang.php');
    }

    /**
     * 所有月份销量账单
     *
     */
    public function index() {

        $condition = array();
        $query_year = input('get.query_year');
        if (preg_match('/^\d{4}$/', $query_year, $match)) {
            $condition['os_year'] = $query_year;
        }
        $model_bill = Model('vrbill');
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
        $model_bill = Model('vr_bill');
        $condtion = array();
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
        $model_bill = Model('vrbill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_no' => $ob_no));
        if (!$bill_info) {
            $this->error(lang('param_error'));
        }
        $model_order = Model('vr_order');
        $condition = array();
        $condition['store_id'] = $bill_info['ob_store_id'];
        $query_type = input('param.query_type');
        if ($query_type == 'timeout') {
            //计算未使用已过期不可退兑换码列表
            $condition['vr_state'] = 0;
            $condition['vr_invalid_refund'] = 0;
            $condition['vr_indate'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        } else {
            //计算已使用兑换码列表
            $condition['vr_state'] = 1;
            $condition['vr_usetime'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }
        $code_list = $model_order->getCodeList($condition, '*', 20, 'rec_id desc');

        //然后取订单编号
        $order_id_array = array();
        if (is_array($code_list)) {
            foreach ($code_list as $code_info) {
                $order_id_array[] = $code_info['order_id'];
            }
        }
        $condition = array();
        $condition['order_id'] = array('in', $order_id_array);
        $order_list = $model_order->getOrderList($condition);
        $order_new_list = array();
        if (!empty($order_list)) {
            foreach ($order_list as $v) {
                $order_new_list[$v['order_id']]['order_sn'] = $v['order_sn'];
                $order_new_list[$v['order_id']]['buyer_name'] = $v['buyer_name'];
                $order_new_list[$v['order_id']]['store_name'] = $v['store_name'];
                $order_new_list[$v['order_id']]['store_id'] = $v['store_id'];
            }
        }
        $this->assign('order_list', $order_new_list);
        $this->assign('code_list', $code_list);
        $this->assign('show_page', $model_order->page_info->render());

        $this->assign('bill_info', $bill_info);
        return $this->fetch('show_bill');
    }

    public function bill_check() {
        $ob_no = input('param.ob_no');
        if (!preg_match('/^20\d{5,12}$/', $ob_no)) {
            $this->error(lang('param_error'));
        }
        $model_bill = Model('vr_bill');
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
        $model_bill = Model('vr_bill');
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
                // 发送店铺消息
                $param = array();
                $param['code'] = 'store_bill_gathering';
                $param['store_id'] = $bill_info['ob_store_id'];
                $param['param'] = array(
                    'bill_no' => $bill_info['ob_no']
                );
                \mall\queue\QueueClient::push('sendStoreMsg', $param);

                $this->log('账单付款,账单号：' . $ob_no, 1);
                $this->success('保存成功', 'vrbill/show_statis?os_month=' . $bill_info['os_month']);
            } else {
                $this->log('账单付款,账单号：' . $ob_no, 1);
                $this->error('保存失败');
            }
        } else {
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
        $model_bill = Model('vr_bill');
        $condition = array();
        $condition['ob_no'] = $ob_no;
        $condition['ob_state'] = BILL_STATE_SUCCESS;
        $bill_info = $model_bill->getOrderBillInfo($condition);
        if (!$bill_info) {
            $this->error(lang('param_error'));
        }

        $this->assign('bill_info', $bill_info);

        return $this->fetch('bill_print');
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
        $model_bill = Model('vr_bill');
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
                $this->assign('murl', url('vrbill/index'));
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
        $export_data[0] = array('账单编号', '开始日期', '结束日期', '消费金额', '佣金金额', '本期应结', '出账日期', '账单状态', '店铺', '店铺ID');
        $ob_order_totals = 0;
        $ob_commis_totals = 0;
        $ob_result_totals = 0;
        foreach ($data as $k => $v) {
            $export_data[$k + 1][] = $v['ob_no'];
            $export_data[$k + 1][] = date('Y-m-d', $v['ob_start_date']);
            $export_data[$k + 1][] = date('Y-m-d', $v['ob_end_date']);
            $ob_order_totals += $export_data[$k + 1][] = $v['ob_order_totals'];
            $ob_commis_totals += $export_data[$k + 1][] = $v['ob_commis_totals'];
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
        $export_data[$count][] = $ob_commis_totals;
        $export_data[$count][] = $ob_result_totals;
        $csv = new Csv();
        $export_data = $csv->charset($export_data, CHARSET, 'gbk');
        $csv->filename = $csv->charset('账单-', CHARSET) . $os_month;
        $csv->export($export_data);
    }

    /**
     * 导出兑换码明细CSV
     *
     */
    public function export_order() {
        $ob_no = input('param.ob_no');
        if (!preg_match('/^20\d{5,12}$/', $ob_no, $match)) {
            $this->error(lang('param_error'));
        }
        $model_bill = Model('vr_bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_no' => $ob_no));
        if (!$bill_info) {
            $this->error(lang('param_error'));
        }
        $model_order = Model('vr_order');
        $condition = array();
        $condition['store_id'] = $bill_info['ob_store_id'];
        $query_type = input('param.query_type');
        if ($query_type == 'timeout') {
            //计算未使用已过期不可退兑换码列表
            $condition['vr_state'] = 0;
            $condition['vr_invalid_refund'] = 0;
            $condition['vr_indate'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        } else {
            //计算已使用兑换码列表
            $condition['vr_state'] = 1;
            $condition['vr_usetime'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }

        if (!is_numeric($_GET['curpage'])) {
            $count = $model_order->getOrderCodeCount($condition);
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
                $this->assign('murl', url('vrbill/show_bill',['ob_no'=>$ob_no]));
                return $this->fetch('excel');
                exit();
            } else {
                //如果数量小，直接下载
                $data = $model_order->getCodeList($condition, '*', '', 'rec_id desc', self::EXPORT_SIZE);
            }
        } else {
            //下载
            $limit1 = ($_GET['curpage'] - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_order->getCodeList($condition, '*', '', 'rec_id desc', "{$limit1},{$limit2}");
        }

        //然后取订单编号
        $order_id_array = array();
        if (is_array($data)) {
            foreach ($data as $code_info) {
                $order_id_array[] = $code_info['order_id'];
            }
        }
        $condition = array();
        $condition['order_id'] = array('in', $order_id_array);
        $order_list = $model_order->getOrderList($condition);
        $order_new_list = array();
        if (!empty($order_list)) {
            foreach ($order_list as $v) {
                $order_new_list[$v['order_id']]['order_sn'] = $v['order_sn'];
                $order_new_list[$v['order_id']]['buyer_name'] = $v['buyer_name'];
                $order_new_list[$v['order_id']]['buyer_id'] = $v['buyer_id'];
                $order_new_list[$v['order_id']]['store_name'] = $v['store_name'];
                $order_new_list[$v['order_id']]['store_id'] = $v['store_id'];
                $order_new_list[$v['order_id']]['goods_name'] = $v['goods_name'];
            }
        }

        $export_data = array();
        $export_data[0] = array('兑换码', '消费时间', '订单号', '消费金额', '佣金金额', '商家', '商家编号', '买家', '买家编号', '商品');
        $query_type = input('param.query_type');
        if ($query_type == 'timeout') {
            $export_data[0][1] = '过期时间';
        }

        $pay_totals = 0;
        $commis_totals = 0;
        $k = 0;
        foreach ($data as $v) {
            //该订单算佣金
            $export_data[$k + 1][] = $v['vr_code'];
            if ($query_type == 'timeout') {
                $export_data[$k + 1][] = date('Y-m-d H:i:s', $v['vr_indate']);
            } else {
                $export_data[$k + 1][] = date('Y-m-d H:i:s', $v['vr_usetime']);
            }
            $export_data[$k + 1][] = 'DS' . $order_new_list[$v['order_id']]['order_sn'];
            $pay_totals += $export_data[$k + 1][] = floatval($v['pay_price']);
            $commis_totals += $export_data[$k + 1][] = floatval($v['pay_price'] * $v['commis_rate'] / 100);
            $export_data[$k + 1][] = $order_new_list[$v['order_id']]['store_name'];
            $export_data[$k + 1][] = $order_new_list[$v['order_id']]['store_id'];
            $export_data[$k + 1][] = $order_new_list[$v['order_id']]['buyer_name'];
            $export_data[$k + 1][] = $order_new_list[$v['order_id']]['buyer_id'];
            $export_data[$k + 1][] = $order_new_list[$v['order_id']]['goods_name'];
            $k++;
        }
        $count = count($export_data);
        $export_data[$count][] = '合计';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = $pay_totals;
        $export_data[$count][] = $commis_totals;
        $csv = new Csv();
        $export_data = $csv->charset($export_data, CHARSET, 'gbk');
        $file_name = $query_type == 'timeout' ? '过期兑换码列表' : '已消费兑换码列表';
        $csv->filename = $csv->charset($file_name . '-', CHARSET) . $ob_no;
        $csv->export($export_data);
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '管理',
                'url' => url('Admin/Vrbill/index')
            ),
        );
        return $menu_array;
    }

}

?>
