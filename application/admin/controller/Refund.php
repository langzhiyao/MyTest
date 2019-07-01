<?php

namespace app\admin\controller;

use think\Lang;

class Refund extends AdminControl
{

    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/refund.lang.php');
        //向模板页面输出退款退货状态
        $this->getRefundStateArray();
    }

    function getRefundStateArray($type = 'all')
    {
        $state_array = array(
            '1' => lang('refund_state_confirm'), '2' => lang('refund_state_yes'), '3' => lang('refund_state_no')
        ); //卖家处理状态:1为待审核,2为同意,3为不同意
        $this->assign('state_array', $state_array);

        $admin_array = array(
            '1' => '处理中', '2' => '待处理', '3' => '已完成'
        ); //确认状态:1为买家或卖家处理中,2为待平台管理员处理,3为退款退货已完成
        $this->assign('admin_array', $admin_array);

        $state_data = array(
            'seller' => $state_array, 'admin' => $admin_array
        );
        if ($type == 'all') {
            return $state_data; //返回所有
        }
        return $state_data[$type];
    }

    /**
     * 待处理列表
     */
    public function refund_manage()
    {
        $model_refund = Model('refundreturn');
        $condition = array();
        $condition['refund_state'] = '2'; //状态:1为处理中,2为待管理员处理,3为已完成

        $keyword_type = array('order_sn', 'refund_sn', 'store_name', 'buyer_name', 'goods_name');
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
        $refund_list = $model_refund->getRefundList($condition, 10);
        $this->assign('show_page', $model_refund->page_info->render());
        $this->assign('refund_list', $refund_list);
        $this->setAdminCurItem('refund_manage');
        return $this->fetch('refund_manage');
    }

    /**
     * 所有记录
     */
    public function refund_all()
    {
        $model_refund = Model('refundreturn');
        $condition = array();

        $keyword_type = array('order_sn', 'refund_sn', 'store_name', 'buyer_name', 'goods_name');
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
        $refund_list = $model_refund->getRefundList($condition, 10);
        $this->assign('show_page', $model_refund->page_info->render());
        $this->assign('refund_list', $refund_list);
        $this->setAdminCurItem('refund_all');
        return $this->fetch('refund_all');
    }

    /**
     * 退款处理页
     *
     */
    public function edit()
    {
        $model_refund = Model('refundreturn');
        $condition = array();
        $condition['refund_id'] = intval(input('param.refund_id'));
        $refund_list = $model_refund->getRefundList($condition);
        $refund = $refund_list[0];
        if (request()->isPost()) {
            if ($refund['refund_state'] != '2') {//检查状态,防止页面刷新不及时造成数据错误
                $this->error(lang('ds_common_save_fail'));
            }
            $order_id = $refund['order_id'];
            $refund_array = array();
            $refund_array['admin_time'] = time();
            $refund_array['refund_state'] = '3'; //状态:1为处理中,2为待管理员处理,3为已完成
            $refund_array['admin_message'] = $_POST['admin_message'];
            $state = $model_refund->editOrderRefund($refund);
            if ($state) {
                $model_refund->editRefundReturn($condition, $refund_array);

                // 发送买家消息
                $param = array();
                $param['code'] = 'refund_return_notice';
                $param['member_id'] = $refund['buyer_id'];
                $param['param'] = array(
                    'refund_url' => url('Home/memberrefund/view', array('refund_id' => $refund['refund_id'])),
                    'refund_sn' => $refund['refund_sn']
                );
                \mall\queue\QueueClient::push('sendMemberMsg', $param);
                $this->log('退款确认，退款编号' . $refund['refund_sn']);
                $this->success(lang('ds_common_save_succ'), 'refund/refund_manage');
            }
            else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
        $this->assign('refund', $refund);
        $info['buyer'] = array();
        if (!empty($refund['pic_info'])) {
            $info = unserialize($refund['pic_info']);
        }
        $this->assign('pic_list', $info['buyer']);
        $this->setAdminCurItem('edit');
        return $this->fetch('edit');
    }

    /**
     * 退款记录查看页
     *
     */
    public function view()
    {
        $model_refund = Model('refundreturn');
        $condition = array();
        $condition['refund_id'] = intval(input('param.refund_id'));
        $refund_list = $model_refund->getRefundList($condition);
        $refund = $refund_list[0];
        $this->assign('refund', $refund);
        $info['buyer'] = array();
        if (!empty($refund['pic_info'])) {
            $info = unserialize($refund['pic_info']);
        }
        $this->assign('pic_list', $info['buyer']);
        $this->setAdminCurItem('view');
        return $this->fetch('view');
    }

    /**
     * 退款退货原因
     */
    public function reason()
    {
        $model_refund = Model('refundreturn');
        $condition = array();
        $reason_list = $model_refund->getReasonList($condition, 10);
        $this->assign('reason_list', $reason_list);
        $this->assign('show_page', $model_refund->page_info->render());

        $this->setAdminCurItem('reason');
        return $this->fetch('reason');
    }

    /**
     * 新增退款退货原因
     *
     */
    public function add_reason()
    {
        $model_refund = Model('refundreturn');
        if (request()->post()) {
            $reason_array = array();
            $reason_array['reason_info'] = $_POST['reason_info'];
            $reason_array['sort'] = intval($_POST['sort']);
            $reason_array['update_time'] = time();

            $state = $model_refund->addReason($reason_array);
            if ($state) {
                $this->log('新增退款退货原因，编号' . $state);
                $this->success(lang('ds_common_save_succ'), 'refund/reason');
            }
            else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
        $this->setAdminCurItem('add_reason');
        return $this->fetch('add_reason');
    }

    /**
     * 编辑退款退货原因
     *
     */
    public function edit_reason()
    {
        $model_refund = Model('refundreturn');
        $condition = array();
        $condition['reason_id'] = intval(input('param.reason_id'));
        $reason_list = $model_refund->getReasonList($condition);
        $reason = $reason_list[$condition['reason_id']];
        if (request()->post()) {
            $reason_array = array();
            $reason_array['reason_info'] = $_POST['reason_info'];
            $reason_array['sort'] = intval($_POST['sort']);
            $reason_array['update_time'] = time();
            $state = $model_refund->editReason($condition, $reason_array);
            if ($state) {
                $this->log('编辑退款退货原因，编号' . $condition['reason_id']);
                $this->success(lang('ds_common_save_succ'), 'refund/reason');
            }
            else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
        $this->assign('reason', $reason);
        $this->setAdminCurItem('edit_reason');
        return $this->fetch('edit_reason');
    }

    /**
     * 删除退款退货原因
     *
     */
    public function del_reason()
    {
        $model_refund = Model('refundreturn');
        $condition = array();
        $condition['reason_id'] = intval(input('param.reason_id'));
        $state = $model_refund->delReason($condition);
        if ($state) {
            $this->log('删除退款退货原因，编号' . $condition['reason_id']);
            $this->success(lang('ds_common_del_succ'), 'refund/reason');
        }
        else {
            $this->error(lang('ds_common_del_fail'));
        }
    }

    /**
     * 微信退款
     *
     */
    public function wxpay()
    {
        $result = array('state' => 'false', 'msg' => '参数错误，微信退款失败');
        $refund_id = intval(input('param.refund_id'));
        $model_refund = Model('refundreturn');
        $condition = array();
        $condition['refund_id'] = $refund_id;
        $condition['refund_state'] = '1';
        $detail_array = $model_refund->getDetailInfo($condition); //退款详细
        if (!empty($detail_array) && in_array($detail_array['refund_code'], array('wxpay', 'wx_jsapi', 'wx_saoma'))) {
            $order = $model_refund->getPayDetailInfo($detail_array); //退款订单详细
            $refund_amount = $order['pay_refund_amount']; //本次在线退款总金额
            if ($refund_amount > 0) {
                $wxpay = $order['payment_config'];
                define('WXPAY_APPID', $wxpay['appid']);
                define('WXPAY_MCHID', $wxpay['mchid']);
                define('WXPAY_KEY', $wxpay['key']);
                $total_fee = $order['pay_amount'] * 100; //微信订单实际支付总金额(在线支付金额,单位为分)
                $refund_fee = $refund_amount * 100; //本次微信退款总金额(单位为分)
                $api_file = BASE_PATH . DS . 'api' . DS . 'refund' . DS . 'wxpay' . DS . 'WxPay.Api.php';
                include $api_file;
                $input = new WxPayRefund();
                $input->SetTransaction_id($order['trade_no']); //微信订单号
                $input->SetTotal_fee($total_fee);
                $input->SetRefund_fee($refund_fee);
                $input->SetOut_refund_no($detail_array['batch_no']); //退款批次号
                $input->SetOp_user_id(WxPayConfig::MCHID);
                $data = WxPayApi::refund($input);
                if (!empty($data) && $data['return_code'] == 'SUCCESS') {//请求结果
                    if ($data['result_code'] == 'SUCCESS') {//业务结果
                        $detail_array = array();
                        $detail_array['pay_amount'] = dsPriceFormat($data['refund_fee'] / 100);
                        $detail_array['pay_time'] = time();
                        $model_refund->editDetail(array('refund_id' => $refund_id), $detail_array);
                        $result['code'] = 'true';
                        $result['msg'] = '微信成功退款:' . $detail_array['pay_amount'];

                        $refund = $model_refund->getRefundReturnInfo(array('refund_id' => $refund_id));
                        $consume_array = array();
                        $consume_array['member_id'] = $refund['buyer_id'];
                        $consume_array['member_name'] = $refund['buyer_name'];
                        $consume_array['consume_amount'] = $detail_array['pay_amount'];
                        $consume_array['consume_time'] = time();
                        $consume_array['consume_remark'] = '微信在线退款成功（到账有延迟），退款退货单号：' . $refund['refund_sn'];
                        \mall\queue\QueueClient::push('addConsume', $consume_array);
                    }
                    else {
                        $result['msg'] = '微信退款错误,' . $data['err_code_des']; //错误描述
                    }
                }
                else {
                    $result['msg'] = '微信接口错误,' . $data['return_msg']; //返回信息
                }
            }
        }
        exit(json_encode($result));
    }

    /**
     * 支付宝退款
     *
     */
    public function alipay()
    {
        $refund_id = intval(input('param.refund_id'));
        $model_refund = Model('refundreturn');
        $condition = array();
        $condition['refund_id'] = $refund_id;
        $condition['refund_state'] = '1';
        $detail_array = $model_refund->getDetailInfo($condition); //退款详细
        if (!empty($detail_array) && $detail_array['refund_code'] == 'alipay') {
            $order = $model_refund->getPayDetailInfo($detail_array); //退款订单详细
            $refund_amount = $order['pay_refund_amount']; //本次在线退款总金额
            if ($refund_amount > 0) {
                $payment_config = $order['payment_config'];
                $alipay_config = array();
                $alipay_config['seller_email'] = $payment_config['alipay_account'];
                $alipay_config['partner'] = $payment_config['alipay_partner'];
                $alipay_config['key'] = $payment_config['alipay_key'];
                $api_file = BASE_PATH . DS . 'api' . DS . 'refund' . DS . 'alipay' . DS . 'alipay.class.php';
                include $api_file;
                $alipaySubmit = new AlipaySubmit($alipay_config);
                $parameter = getPara($alipay_config);
                $batch_no = $detail_array['batch_no'];
                $b_date = substr($batch_no, 0, 8);
                if ($b_date != date('Ymd')) {
                    $batch_no = date('Ymd') . substr($batch_no, 8); //批次号。支付宝要求格式为：当天退款日期+流水号。
                    $model_refund->editDetail(array('refund_id' => $refund_id), array('batch_no' => $batch_no));
                }
                $parameter['batch_no'] = $batch_no;
                $parameter['detail_data'] = $order['trade_no'] . '^' . $refund_amount . '^协商退款'; //数据格式为：原交易号^退款金额^理由
                $pay_url = $alipaySubmit->buildRequestParaToString($parameter);
                @header("Location: " . $pay_url);
            }
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList()
    {
        $menu_array = array(
            array(
                'name' => 'refund_manage', 'text' => '待处理', 'url' => url('Admin/Refund/refund_manage')
            ), array(
                'name' => 'refund_all', 'text' => '所有记录', 'url' => url('Admin/Refund/refund_all')
            ), array(
                'name' => 'reason', 'text' => '退款退货原因', 'url' => url('Admin/Refund/reason')
            ),
        );
        if (request()->action() == 'reason') {
            $menu_array[] = [
                'name' => 'add_reason', 'text' => '新增原因', 'url' =>url('Refund/add_reason')
            ];
        }
        return $menu_array;
    }
}

?>
