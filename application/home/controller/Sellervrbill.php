<?php

namespace app\home\controller;

use think\Lang;

class Sellervrbill extends BaseSeller {

    /**
     * 每次导出多少条记录
     * @var unknown
     */
    const EXPORT_SIZE = 1000;

    /**
     * 结算列表
     *
     */
    public function index() {
        $model_bill = Model('vrbill');
        $condition = array();
        $condition['ob_store_id'] = session('store_id');
        $ob_no = input('param.ob_no');
        if (preg_match('/^20\d{5,12}$/', $ob_no)) {
            $condition['ob_no'] = $ob_no;
        }
        $bill_state = input('param.bill_state');
        if (is_numeric($bill_state)) {
            $condition['ob_state'] = intval($bill_state);
        }
        $bill_list = $model_bill->getOrderBillList($condition, '*', 12, 'ob_state asc,ob_no asc');
        $this->assign('bill_list', $bill_list);
        
        $this->assign('show_page', $model_bill->page_info->render());
        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('Sellervrbill');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem('index');
        return $this->fetch($this->template_dir.'index');
    }

    /**
     * 查看结算单详细
     *
     */
    public function show_bill() {
        $ob_no = input('param.ob_no');
        if (!preg_match('/^20\d{5,12}$/', $ob_no)) {
            $this->error('参数错误');
        }
        if (substr($ob_no, 6) != session('store_id')) {
            $this->error('参数错误');
        }
        $model_bill = Model('vrbill');
        $model_order = Model('vrorder');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_no' => $ob_no));
        if (!$bill_info) {
            $this->error('参数错误');
        }

        $condition = array();
        $condition['store_id'] = $bill_info['ob_store_id'];
        $query_order_no = input('param.query_order_no');
        if (preg_match('/^\d{8,20}$/', $query_order_no)) {
            //取order_id
            $order_info = $model_order->getOrderInfo(array('order_sn' => $query_order_no), 'order_id');
            $condition['order_id'] = $order_info['order_id'];
        }
        $query_start_date = input('param.query_start_date');
        $query_end_date = input('param.query_end_date');
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_start_date);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_end_date);
        $start_unixtime = $if_start_date ? strtotime($query_start_date) : null;
        $end_unixtime = $if_end_date ? strtotime($query_end_date) : null;
        if ($if_start_date || $if_end_date) {
            $condition_time = array('between', array($start_unixtime, $end_unixtime));
        } else {
            $condition_time = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }
        $type = input('param.type');
        if ($type == 'timeout') {
            //计算未使用已过期不可退兑换码列表
            $condition['vr_state'] = 0;
            $condition['vr_invalid_refund'] = 0;
            $condition['vr_indate'] = $condition_time;
        } else {
            //计算已使用兑换码列表
            $condition['vr_state'] = 1;
            $condition['vr_usetime'] = $condition_time;
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
            }
        }
        $this->assign('order_list', $order_new_list);
        $this->assign('code_list', $code_list);
        $this->assign('show_page', $model_order->page_info->render());
        
        $this->assign('bill_info', $bill_info);
        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('Sellervrbill');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem('show_bill');
        return $this->fetch($this->template_dir.'show_bill');
    }

    /**
     * 打印结算单
     *
     */
    public function bill_print() {
        $ob_no = input('param.ob_no');
        if (!preg_match('/^20\d{5,12}$/', $ob_no)) {
            $this->error('参数错误');
        }
        if (substr($ob_no, 6) != session('store_id')) {
            $this->error('参数错误');
        }
        $model_bill = Model('vrbill');
        $condition = array();
        $condition['ob_no'] = $ob_no;
        $condition['ob_state'] = BILL_STATE_SUCCESS;
        $bill_info = $model_bill->getOrderBillInfo($condition);
        if (!$bill_info) {
            $this->error('参数错误');
        }

        $this->assign('bill_info', $bill_info);
        return $this->fetch($this->template_dir.'bill_print');
    }

    /**
     * 店铺确认出账单
     *
     */
    public function confirm_bill() {
        $ob_no = input('param.ob_no');
        if (!preg_match('/^20\d{5,12}$/', $ob_no)) {
            showDialog('参数错误', '', 'error');
        }
        $model_bill = Model('vrbill');
        $condition = array();
        $condition['ob_no'] = $ob_no;
        $condition['ob_store_id'] = session('store_id');
        $condition['ob_state'] = BILL_STATE_CREATE;
        $update = $model_bill->editOrderBill(array('ob_state' => BILL_STATE_STORE_COFIRM), $condition);
        if ($update) {
            showDialog('确认成功，请等待系统审核', '', 'succ');
        } else {
            showDialog(lang('ds_common_op_fail'), 'reload', 'error');
        }
    }

    /**
     * 导出结算订单明细CSV
     *
     */
    public function export_order() {
        $ob_no = input('param.ob_no');
        if (!preg_match('/^20\d{5,12}$/', $ob_no)) {
            $this->error('参数错误');
        }
        if (substr($ob_no, 6) != session('store_id')) {
            $this->error('参数错误');
        }

        $model_bill = Model('vrbill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_no' => $ob_no));
        if (!$bill_info) {
            $this->error('参数错误');
        }

        $model_order = Model('vr_order');
        $condition = array();
        $condition['store_id'] = session('store_id');
        $query_order_no = input('param.query_order_no');
        if (preg_match('/^\d{8,20}$/', $query_order_no)) {
            //取order_id
            $order_info = $model_order->getOrderInfo(array('order_sn' => $query_order_no), 'order_id');
            $condition['order_id'] = $order_info['order_id'];
        }
        $query_start_date = input('param.query_start_date');
        $query_end_date = input('param.query_end_date');
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_start_date);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_end_date);
        $start_unixtime = $if_start_date ? strtotime($query_start_date) : null;
        $end_unixtime = $if_end_date ? strtotime($query_end_date) : null;
        if ($if_start_date || $if_end_date) {
            $condition_time = array('between', array($start_unixtime, $end_unixtime));
        } else {
            $condition_time = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }
        $type = input('param.type');
        if ($type == 'timeout') {
            //计算未使用已过期不可退兑换码列表
            $condition['vr_state'] = 0;
            $condition['vr_invalid_refund'] = 0;
            $condition['vr_indate'] = $condition_time;
        } else {
            //计算已使用兑换码列表
            $condition['vr_state'] = 1;
            $condition['vr_usetime'] = $condition_time;
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
                $this->assign('murl', url('sellervrbill/show_bill',['ob_no'=>$ob_no]));
                return $this->fetch($this->template_dir.'export_order');
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
        $export_data[0] = array('兑换码', '消费时间', '订单号', '消费金额', '佣金金额', '买家', '买家编号', '商品');
        if ($type == 'timeout') {
            $export_data[0][1] = '过期时间';
        }
        $pay_totals = 0;
        $commis_totals = 0;
        $k = 0;
        foreach ($data as $v) {
            //该订单算佣金
            $export_data[$k + 1][] = $v['vr_code'];
            if ($type == 'timeout') {
                $export_data[$k + 1][] = date('Y-m-d H:i:s', $v['vr_indate']);
            } else {
                $export_data[$k + 1][] = date('Y-m-d H:i:s', $v['vr_usetime']);
            }
            $export_data[$k + 1][] = 'NC' . $order_new_list[$v['order_id']]['order_sn'];
            $pay_totals += $export_data[$k + 1][] = floatval($v['pay_price']);
            $commis_totals += $export_data[$k + 1][] = floatval($v['pay_price'] * $v['commis_rate'] / 100);
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
        $file_name = $type == 'timeout' ? '过期兑换码列表' : '已消费兑换码列表';
        $csv->filename = $csv->charset($file_name . '-', CHARSET) . $ob_no;
        $csv->export($export_data);
    }

    /**
     *    栏目菜单
     */
    function getSellerItemList() {
        $menu_array[] = array(
            'name' => 'account_list',
            'text' => '账号列表',
            'url' => url('Home/selleraccount/account_list'),
        );

        if (request()->action() === 'account_add') {
            $menu_array[] = array(
                'name' => 'account_add',
                'text' => '添加账号',
                'url' => url('Home/selleraccount/account_add'),
            );
        }
        if (request()->action() === 'group_edit') {
            $menu_array[] = array(
                'name' => 'account_edit',
                'text' => '编辑账号',
                'url' => url('Home/selleraccount/account_edit'),
            );
        }

        return $menu_array;
    }

}
