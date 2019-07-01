<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Vrrefund extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/vrrefund.lang.php');
        $this->getRefundStateArray();
    }

    /**
     * 向模板页面输出退款状态
     *
     * @param
     * @return array
     */
    public function getRefundStateArray($type = 'all') {
        $admin_array = array(
            '1' => '待审核',
            '2' => '同意',
            '3' => '不同意'
        ); //退款状态:1为待审核,2为同意,3为不同意
        $this->assign('admin_array', $admin_array);

        $state_data = array(
            'admin' => $admin_array
        );
        if ($type == 'all')
            return $state_data; //返回所有
        return $state_data[$type];
    }

    /**
     * 待处理列表
     */
    public function refund_manage() {
        $model_vr_refund = Model('vrrefund');
        $condition = array();
        $condition['admin_state'] = '1'; //状态:1为待审核,2为同意,3为不同意

        $keyword_type = array('order_sn', 'refund_sn', 'store_name', 'buyer_name', 'goods_name');
        $key = input('get.key');
        $type = input('get.type');
        if (trim($key) != '' && in_array($type, $keyword_type)) {
            $condition[$type] = array('like', '%' . $type . '%');
        }

        $add_time_from = trim(input('get.add_time_from'));
        $add_time_to = trim(input('get.add_time_to'));
        if ($add_time_from != '' || $add_time_to != '') {
            $add_time_from = strtotime($add_time_from);
            $add_time_to = strtotime($add_time_to);
            if ($add_time_from !== false || $add_time_to !== false) {
                $condition['add_time'] = array('between', array($add_time_from, $add_time_to));
            }
        }
        $refund_list = $model_vr_refund->getRefundList($condition, 10);

        $this->assign('refund_list', $refund_list);
        $this->assign('show_page', $model_vr_refund->page_info->render());
        $this->setAdminCurItem('refund_manage');
        return $this->fetch('vr_refund_manage_list');
    }

    /**
     * 所有记录
     */
    public function refund_all() {
        $model_vr_refund = Model('vrrefund');
        $condition = array();

        $keyword_type = array('order_sn', 'refund_sn', 'store_name', 'buyer_name', 'goods_name');
        $key = input('get.key');
        $type = input('get.type');
        if (trim($key) != '' && in_array($type, $keyword_type)) {
            $condition[$type] = array('like', '%' . $key . '%');
        }
        $add_time_from = trim(input('get.add_time_from'));
        $add_time_to = trim(input('get.add_time_to'));
        if ($add_time_from != '' || $add_time_to != '') {
            $add_time_from = strtotime($add_time_from);
            $add_time_to = strtotime($add_time_to);
            if ($add_time_from !== false || $add_time_to !== false) {
                $condition['add_time'] = array('between', array($add_time_from, $add_time_to));
            }
        }
        $refund_list = $model_vr_refund->getRefundList($condition, 10);
        $this->assign('refund_list', $refund_list);
        $this->assign('show_page', $model_vr_refund->page_info->render());
        $this->setAdminCurItem('refund_all');
        return $this->fetch('vr_refund_all_list');
    }

    /**
     * 审核页
     *
     */
    public function edit() {
        $refund_id = intval(input('param.refund_id'));
        $model_vr_refund = Model('vrrefund');
        $refund = db('vrrefund')->where('refund_id', $refund_id)->find();
        if (!(request()->isPost())) {
            $this->assign('refund', $refund);
            $code_array = explode(',', $refund['code_sn']);
            $this->assign('code_array', $code_array);
            $this->setAdminCurItem('vr_refund_edit');
            return $this->fetch('vr_refund_edit');
        } else {
            if ($refund['admin_state'] != '1') {//检查状态,防止页面刷新不及时造成数据错误
                $this->error(lang('ds_common_save_fail'));
            }
            $refund['admin_time'] = time();
            $refund['admin_state'] = '2';
            if ($_POST['admin_state'] == '3') {
                $refund['admin_state'] = '3';
            }
            $refund['admin_message'] = $_POST['admin_message'];
            $state = $model_vr_refund->editOrderRefund($refund);
            if ($state) {
                // 发送买家消息
                $param = array();
                $param['code'] = 'refund_return_notice';
                $param['member_id'] = $refund['buyer_id'];
                $param['param'] = array(
                    'refund_url' => url('Admin/Membervrrefund/view',['refund_id'=>$refund['refund_id']]),
                    'refund_sn' => $refund['refund_sn']
                );
                \mall\queue\QueueClient::push('sendMemberMsg', $param);

                $this->log('虚拟订单退款审核，退款编号' . $refund['refund_sn']);
                $this->success(lang('ds_common_save_succ'), url('Admin/vrrefund/refund_manage'));
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 查看页
     *
     */
    public function view() {
        $model_vr_refund = Model('vrrefund');
        $refund_id = intval(input('get.refund_id'));
        $refund = db('vrrefund')->where('refund_id',$refund_id)->find();
        $this->assign('refund', $refund);
        $code_array = explode(',', $refund['code_sn']);
        $this->assign('code_array', $code_array);
        $this->setAdminCurItem('view');
        return $this->fetch('vr_refund_view');
    }

    /**
     * 微信退款 v3-b12
     *
     */
    public function wxpay() {
        $result = array('state' => 'false', 'msg' => '参数错误，微信退款失败');
        $refund_id = intval($_GET['refund_id']);
        $model_refund = Model('vrrefund');
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

                        $refund = $model_refund->getRefundInfo(array('refund_id' => $refund_id));
                        $consume_array = array();
                        $consume_array['member_id'] = $refund['buyer_id'];
                        $consume_array['member_name'] = $refund['buyer_name'];
                        $consume_array['consume_amount'] = $detail_array['pay_amount'];
                        $consume_array['consume_time'] = time();
                        $consume_array['consume_remark'] = '微信在线退款成功（到账有延迟），虚拟退款单号：' . $refund['refund_sn'];
                        \mall\queue\QueueClient::push('addConsume', $consume_array);
                    } else {
                        $result['msg'] = '微信退款错误,' . $data['err_code_des']; //错误描述
                    }
                } else {
                    $result['msg'] = '微信接口错误,' . $data['return_msg']; //返回信息
                }
            }
        }
        exit(json_encode($result));
    }

    /**
     * 支付宝退款 v3-b12
     *
     */
    public function alipay() {
        $refund_id = intval($_GET['refund_id']);
        $model_refund = Model('vrrefund');
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
                $parameter['notify_url'] = ADMIN_SITE_URL . "/api/refund/alipay/vr_notify_url.php";
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
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'refund_manage',
                'text' => '待审核',
                'url' => url('Admin/Vrrefund/refund_manage')
            ),
            array(
                'name' => 'refund_all',
                'text' => '所有记录',
                'url' => url('Admin/Vrrefund/refund_all')
            ),
        );
        return $menu_array;
    }
}
