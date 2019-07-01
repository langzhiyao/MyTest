<?php

namespace app\home\controller;

use think\Lang;

class Sellerbill extends BaseSeller {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/sellerbill.lang.php');
    }

    /**
     * 结算列表
     *
     */
    public function index() {
        $model_bill = Model('bill');
        $condition = array();
        $condition['ob_store_id'] = session('store_id');

        $ob_no = input('param.ob_no');
        if (preg_match('/^20\d{5,12}$/', $ob_no)) {
            $condition['ob_no'] = $ob_no;
        }
        $bill_state = intval(input('bill_state'));
        if ($bill_state) {
            $condition['ob_state'] = $bill_state;
        }

//        $bill_list = $model_bill->getOrderBillList($condition, '*', 12, 'ob_state asc,ob_no asc');
        $page = input('param.page') ? input('param.page') : 0;
        $bill_list = db('orderbill')->where($condition)->order('ob_state asc,ob_no asc')->page($page)->limit(12)->select();
        $this->assign('bill_list', $bill_list);
        $bill_list_page = db('orderbill')->where($condition)->order('ob_state asc,ob_no asc')->paginate(12,false,['query' => request()->param()]);
        $this->assign('page', $bill_list_page->render());



//        $this->profile_menu('list', 'list');
        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('Sellerbill');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem('seller_slide');
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
        $model_bill = Model('bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_no' => $ob_no));
        if (!$bill_info) {
            $this->error('参数错误');
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
        if ($if_start_date || $if_end_date) {
            $order_condition['finnshed_time'] = array('between', array($start_unixtime, $end_unixtime));
        } else {
            $order_condition['finnshed_time'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }
        $query_order_no = input('get.query_order_no');
        $type = input('get.type');
        if ($type == 'refund') {
            if (preg_match('/^\d{8,20}$/', $query_order_no)) {
                $order_condition['refund_sn'] = $query_order_no;
            }
            //退款订单列表
            $model_refund = Model('refundreturn');
            $refund_condition = array();
            $refund_condition['seller_state'] = 2;
            $refund_condition['store_id'] = $bill_info['ob_store_id'];
            $refund_condition['goods_id'] = array('gt', 0);
            $refund_condition['admin_time'] = $order_condition['finnshed_time'];
            if (preg_match('/^\d{8,20}$/', $query_order_no)) {
                $refund_condition['refund_sn'] = $query_order_no;
            }

//            $refund_list = $model_refund->getRefundReturnList($refund_condition, 20, '*,ROUND(refund_amount*commis_rate/100,2) as commis_amount');
            $refund_list = db('refundreturn')->field('*,ROUND(refund_amount*commis_rate/100,2) as commis_amount')->where($refund_condition)->page($page)->limit(20)->select();
            if (is_array($refund_list) && count($refund_list) == 1 && $refund_list[0]['refund_id'] == '') {
                $refund_list = array();
            }
            //取返还佣金
            $this->assign('refund_list', $refund_list);

            //页面
            $refund_list_page = db('refundreturn')->field('*,ROUND(refund_amount*commis_rate/100,2) as commis_amount')->where($refund_condition)->paginate(20,false,['query' => request()->param()]);
            $this->assign('page', $refund_list_page->render());

            $sub_tpl_name = 'show_refund_list';
//            $this->profile_menu('show', 'refund_list');
            /* 设置卖家当前菜单 */
            $this->setSellerCurMenu('Sellerbill');
            /* 设置卖家当前栏目 */
            $this->setSellerCurItem('seller_slide');
        } elseif ($type == 'cost') {
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

            $store_cost_list_page = db('storecost')->where($cost_condition)->paginate(20,false,['query' => request()->param()]);
            $this->assign('page', $store_cost_list_page->render());

            $sub_tpl_name = 'show_cost_list';
//            $this->profile_menu('show', 'cost_list');
            /* 设置卖家当前菜单 */
            $this->setSellerCurMenu('Sellerbill');
            /* 设置卖家当前栏目 */
            $this->setSellerCurItem('seller_slide');
        } else {

            if (preg_match('/^\d{8,20}$/', $query_order_no)) {
                $order_condition['order_sn'] = $query_order_no;
            }
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

            $order_list_page = db('order')->where($order_condition)->paginate(20,false,['query' => request()->param()]);
            $this->assign('page', $order_list_page->render());
            $sub_tpl_name = 'show_order_list';
//            $this->profile_menu('show', 'order_list');
            /* 设置卖家当前菜单 */
            $this->setSellerCurMenu('Sellerbill');
            /* 设置卖家当前栏目 */
            $this->setSellerCurItem('seller_slide');
        }
        $this->assign('bill_info', $bill_info);

        return $this->fetch($this->template_dir.$sub_tpl_name);
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
        $model_bill = Model('bill');
        $condition = array();
        $condition['ob_no'] = $ob_no;
        $condition['ob_state'] = BILL_STATE_SUCCESS;
        $bill_info = $model_bill->getOrderBillInfo($condition);
        if (!$bill_info) {
            $this->error('参数错误');
        }

        $this->assign('bill_info', $bill_info);
        return $this->fetch($this->template_dir.'store_bill.print');
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
        $model_bill = Model('bill');
        $condition = array();
        $condition['ob_no'] = $ob_no;
        $condition['ob_store_id'] = session('store_id');
        $condition['ob_state'] = BILL_STATE_CREATE;
        $update = $model_bill->editOrderBill(array('ob_state' => BILL_STATE_STORE_COFIRM), $condition);
        if ($update) {
            showDialog('确认成功', 'reload', 'succ');
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

        $model_bill = Model('bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_no' => $ob_no));
        if (!$bill_info) {
            $this->error('参数错误');
        }

        $model_order = Model('order');
        $condition = array();
        $condition['order_state'] = ORDER_STATE_SUCCESS;
        $condition['store_id'] = session('store_id');
        $query_start_date = input('get.query_start_date');
        $query_end_date = input('get.query_end_date');
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_start_date);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_end_date);
        $start_unixtime = $if_start_date ? strtotime($query_start_date) : null;
        $end_unixtime = $if_end_date ? strtotime($query_end_date) : null;
        if ($if_start_date || $if_end_date) {
            $condition['finnshed_time'] = array('between', array($start_unixtime, $end_unixtime));
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
                $this->assign('murl', url('sellerbill/show_bill',['ob_no'=>$ob_no]));
                return $this->fetch($this->template_dir.'export_excel');
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
        $export_data[0] = array('订单编号', '订单金额', '运费', '佣金', '下单日期', '成交日期', '买家', '买家编号', '商品');
        $order_totals = 0;
        $shipping_fee_totals = 0;
        $commis_totals = 0;
        $k = 0;
        foreach ($data as $v) {
            //该订单算佣金
            $field = 'SUM(ROUND(goods_pay_price*commis_rate/100,2)) as commis_amount,order_id';
            $commis_list = $model_order->getOrderGoodsList($order_goods_condition, $field, null, null, '', 'order_id', 'order_id');
            $export_data[$k + 1][] = 'NC' . $v['order_sn'];
            $order_totals += $export_data[$k + 1][] = $v['order_amount'];
            $shipping_fee_totals += $export_data[$k + 1][] = $v['shipping_fee'];
            $commis_totals += $export_data[$k + 1][] = $commis_list[$v['order_id']]['commis_amount'];
            $export_data[$k + 1][] = date('Y-m-d', $v['add_time']);
            $export_data[$k + 1][] = date('Y-m-d', $v['finnshed_time']);
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
        $export_data[$count][] = $shipping_fee_totals;
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
            $this->error('参数错误');
        }
        if (substr($ob_no, 6) != session('store_id')) {
            $this->error('参数错误');
        }
        $model_bill = Model('bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_no' => $ob_no));
        if (!$bill_info) {
            $this->error('参数错误');
        }

        $model_refund = Model('refundreturn');
        $condition = array();
        $condition['seller_state'] = 2;
        $condition['store_id'] = session('store_id');
        $condition['goods_id'] = array('gt', 0);

        $query_start_date = input('get.query_start_date');
        $query_end_date = input('get.query_end_date');
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_start_date);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_end_date);
        $start_unixtime = $if_start_date ? strtotime($query_start_date) : null;
        $end_unixtime = $if_end_date ? strtotime($query_end_date) : null;
        if ($if_start_date || $if_end_date) {
            $condition['admin_time'] = array('between', array($start_unixtime, $end_unixtime));
        } else {
            $condition['admin_time'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
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
                $this->assign('murl', url('sellerbill/index',['query_type'=>'refund','ob_no'=>$ob_no]));
                return $this->fetch($this->template_dir.'export_excel');
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
        $export_data[0] = array('退单编号', '订单编号', '退单金额', '退单佣金', '类型', '退款日期', '买家', '买家编号');
        $refund_amount = 0;
        $commis_totals = 0;
        $k = 0;
        foreach ($data as $v) {
            $export_data[$k + 1][] = 'NC' . $v['refund_sn'];
            $export_data[$k + 1][] = 'NC' . $v['order_sn'];
            $refund_amount += $export_data[$k + 1][] = $v['refund_amount'];
            $commis_totals += $export_data[$k + 1][] = dsPriceFormat($v['commis_amount']);
            $export_data[$k + 1][] = str_replace(array(1, 2), array('退款', '退货'), $v['refund_type']);
            $export_data[$k + 1][] = date('Y-m-d', $v['admin_time']);
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
     * 用户中心右边，小导航
     *
     * @param string $menu_type 导航类型
     * @param string $menu_key 当前导航的menu_key
     * @return
     */
    function getSellerItemList() {
        $ob_no = input('param.ob_no');
        if (request()->action()=='index') {
            $menu_array = array(
                array(
                    'name' => 'list',
                    'text' => '实物订单结算',
                    'url' => url('home/sellerbill/index')
                ),
            );
        }else if(request()->action()=='show_bill'){
            $menu_array = array(
                array(
                    'name' => 'order_list',
                    'text' => '订单列表',
                    'url' => url('home/sellerbill/show_bill', ['ob_no' => $ob_no])
                ),
                array(
                    'name' => 'refund_list',
                    'text' => '退款订单',
                    'url' => url('home/sellerbill/show_bill', ['ob_no' => $ob_no])
                ),
                array(
                    'name' => 'cost_list',
                    'text' => '促销费用',
                    'url' => url('home/sellerbill/show_bill', ['ob_no' => $ob_no])
                ),
                
            );
        }
        return $menu_array;
    }

}

?>
