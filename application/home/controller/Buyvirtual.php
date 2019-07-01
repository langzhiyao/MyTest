<?php

namespace app\home\controller;


class Buyvirtual extends BaseMember
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }
    /**
     * 虚拟商品购买第一步
     */
    public function buy_step1() {
        $logic_buy_virtual = model('buyvirtual','logic');
        $result = $logic_buy_virtual->getBuyStep1Data(input('goods_id'), input('quantity'), session('member_id'));
        if (!$result['code']) {
            $this->error($result['msg']);
        }

       $this->assign('goods_info',$result['data']['goods_info']);
       $this->assign('store_info',$result['data']['store_info']);

       return $this->fetch($this->template_dir.'buy_virtual_step1');
    }

    /**
     * 虚拟商品购买第二步
     */
    public function buy_step2() {
        $logic_buy_virtual = model('buyvirtual','logic');
        $result = $logic_buy_virtual->getBuyStep2Data($_POST['goods_id'], $_POST['quantity'], session('member_id'));
        if (!$result['code']) {
            $this->error($result['msg']);
        }

        //处理会员信息
        $member_info = array_merge($this->member_info,$result['data']['member_info']);

       $this->assign('goods_info',$result['data']['goods_info']);
       $this->assign('store_info',$result['data']['store_info']);
       $this->assign('member_info',$member_info);
       return $this->fetch($this->template_dir.'buy_virtual_step2');
    }

    /**
     * 虚拟商品购买第三步
     */
    public function buy_step3() {
        $logic_buy_virtual = model('buyvirtual','logic');
        $_POST['order_from'] = 1;
        $result = $logic_buy_virtual->buyStep3($_POST,session('member_id'));
        if (!$result['code']) {
           $this->error($result['msg']);
        }
        //转向到商城支付页面
        $this->redirect('buyvirtual/pay',['order_id'=>$result['data']['order_id']]);
    }

    /**
     * 下单时支付页面
     */
    public function pay() {
        $order_id	= intval(input('param.order_id'));
        if ($order_id <= 0){
            $this->error('该订单不存在','membervrorder/index');
        }

        $model_vr_order = Model('vrorder');
        //取订单信息
        $condition = array();
        $condition['order_id'] = $order_id;
        $order_info = $model_vr_order->getOrderInfo($condition,'*',true);
        if (empty($order_info) || !in_array($order_info['order_state'],array(ORDER_STATE_NEW,ORDER_STATE_PAY))) {
            $this->error('未找到需要支付的订单','memberorder/index');
        }

        //重新计算在线支付金额
        $pay_amount_online = 0;
        //订单总支付金额
        $pay_amount = 0;

        $payed_amount = floatval($order_info['rcb_amount']) + floatval($order_info['pd_amount']);

        //计算所需要支付金额
        $diff_pay_amount = dsPriceFormat(floatval($order_info['order_amount'])-$payed_amount);

        //显示支付方式与支付结果
        if ($payed_amount > 0) {
            $payed_tips = '';
            if (floatval($order_info['rcb_amount']) > 0) {
                $payed_tips = '充值卡已支付：￥'.$order_info['rcb_amount'];
            }
            if (floatval($order_info['pd_amount']) > 0) {
                $payed_tips .= ' 预存款已支付：￥'.$order_info['pd_amount'];
            }
            $order_info['goods_price'] .= " ( {$payed_tips} )";
        }
       $this->assign('order_info',$order_info);

        //如果所需支付金额为0，转到支付成功页
        if ($diff_pay_amount == 0) {
            $this->redirect('buyvirtual/pay_ok',['order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id'],'order_amount'=>dsPriceFormat($order_info['order_amount'])]);
        }

       $this->assign('diff_pay_amount',dsPriceFormat($diff_pay_amount));

        //显示支付接口列表
        $model_payment = Model('payment');
        $condition = array();
        $payment_list = $model_payment->getPaymentOpenList($condition);
        if (!empty($payment_list)) {
            unset($payment_list['predeposit']);
            unset($payment_list['offline']);
        }
        if (empty($payment_list)) {
            $this->error('暂未找到合适的支付方式','membervrorder/index');
        }
       $this->assign('payment_list',$payment_list);

       return $this->fetch($this->template_dir.'buy_virtual_step3');
    }

    /**
     * 支付成功页面
     */
    public function pay_ok() {
        $order_sn	= input('param.order_sn');
        if (!preg_match('/^\d{18}$/',$order_sn)){
            $this->error('该订单不存在','membervrorder/index');
        }
       return $this->fetch($this->template_dir.'buy_virtual_step4');
    }
}