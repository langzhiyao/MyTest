<?php

class alipay_app {

    function __construct(){
        
    }
    
    public function getSubmitUrl($param){    
        require_once APP_PATH .'wap/api/payment/alipay_app/lib/AlipayTradeService.php';
        require_once APP_PATH .'wap/api/payment/alipay_app/lib/config.php';    
        require_once APP_PATH .'wap/api/payment/alipay_app/lib/AlipayTradeWapPayContentBuilder.php';
        
        if (!empty($param)){
            //商户订单号，商户网站订单系统中唯一订单号，必填
            $out_trade_no = "{$param['orderSn']}-{$param['order_type']}";

            //订单名称，必填
            $subject = $param['orderInfo'];

            //付款金额，必填
            $total_amount = $param['orderFee'];

            //商品描述，可空
            $body = $param['orderSn'];

            //超时时间
            $timeout_express="1m";

            $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
            $payRequestBuilder->setBody($body);
            $payRequestBuilder->setSubject($subject);
            $payRequestBuilder->setOutTradeNo($out_trade_no);
            $payRequestBuilder->setTotalAmount($total_amount);
            $payRequestBuilder->setTimeExpress($timeout_express);

            $payResponse = new AlipayTradeService($config);

            if($param['notifyUrl'] && $param['return_url']){
                $result=$payResponse->wapPay($payRequestBuilder,$param['return_url'],$param['notifyUrl']);
            }else{
                $result=$payResponse->wapPay($payRequestBuilder,$config['return_url'],$config['notify_url']);
            }
            write_payment(json_encode($result),'alipay_app');

            return ;
        }else{
            output_error('参数错误1！');
        }
    }

    // public function getOrderStateBysn($orderSn){
    //     require_once APP_PATH .'wap/api/payment/alipay_app/lib/AlipayTradeQueryContentBuilder.php';
    //     require_once APP_PATH .'wap/api/payment/alipay_app/lib/AlipayTradeService.php';
    //     require_once APP_PATH .'wap/api/payment/alipay_app/lib/config.php';
    //     if (!empty($orderSn)){

    //         //商户订单号和支付宝交易号不能同时为空。 trade_no、  out_trade_no如果同时存在优先取trade_no
    //         //商户订单号，和支付宝交易号二选一
    //         $out_trade_no = trim($orderSn);


    //         $RequestBuilder = new AlipayTradeQueryContentBuilder();
    //         // $RequestBuilder->setTradeNo($trade_no);
    //         $RequestBuilder->setOutTradeNo($out_trade_no);

    //         $Response = new AlipayTradeService($config);
    //         $result=$Response->Query($RequestBuilder);
    //         return ;
    //     }else{
    //         return 2222;
    //     }
    // }
    public function getOrderStateBysn1($orderSn){
            require_once APP_PATH .'wap/api/payment/alipay_app/lib/config.php';
            require_once APP_PATH .'wap/api/payment/alipay_app/lib/AlipayTradeService.php';
            require_once APP_PATH .'wap/api/payment/alipay_app/lib/AlipayTradeQueryContentBuilder.php';

            //商户订单号，商户网站订单系统中唯一订单号
            $out_trade_no = $orderSn;

            //支付宝交易号
            // $trade_no = trim($_POST['WIDTQtrade_no']);
            //请二选一设置
            //构造参数
            $RequestBuilder = new AlipayTradeQueryContentBuilder();

            $RequestBuilder->setOutTradeNo($out_trade_no);
            // $RequestBuilder->setTradeNo($trade_no);

            $aop = new AlipayTradeService($config);
            
            /**
             * alipay.trade.query (统一收单线下交易查询)
             * @param $builder 业务参数，使用buildmodel中的对象生成。
             * @return $response 支付宝返回的信息
             */
            $response = $aop->Query($RequestBuilder);
            p($response);exit;
            $resultCode = $response->code;
            if(!empty($response) && $resultCode == 10000){
                return $response = $this->object_array($response);
            } else {
                return false;
            }
            
    }

    public function getOrderStateBysn($orderSn){
        require_once APP_PATH .'wap/api/payment/alipay_app/lib/config.php';
        require_once APP_PATH .'wap/api/payment/alipay_app/lib/AlipayTradeService.php';
        require_once APP_PATH .'wap/api/payment/alipay_app/lib/AlipayTradeQueryContentBuilder.php';
        $aop = new AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $config['app_id'];
        $aop->rsaPrivateKey = $config['merchant_private_key'];
        $aop->alipayrsaPublicKey=$config['merchant_public_key'];
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset=$config['charset'];
        $aop->format='json';
        $request = new AlipayTradeQueryRequest ();
        $bizcontent= json_encode(array(
            'out_trade_no'=>$orderSn,
            'trade_no'=>'',
            'org_pid'=>'',
        ));
        $request->setBizContent($bizcontent);
        $result = $aop->execute ( $request); 
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        p($result);exit;
        if(!empty($resultCode)&&$resultCode == 10000){
        echo "成功";
        } else {
        echo "失败";
        }
    }

    function get_payform($param) {

        require_once APP_PATH .ATTACH_MOBILE.'/api/payment/alipay_app/AopClient.php';
        $aop = new \AopClient();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $param['app_alipay_appid'];
        $aop->rsaPrivateKey = $param['app_private_key'];
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = $param['app_public_key'];
//实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay

        require_once APP_PATH .ATTACH_MOBILE.'/api/payment/alipay_app/request/AlipayTradeAppPayRequest.php';
        $request = new \AlipayTradeAppPayRequest();
        $bizcontent = "{\"body\":\"{$param['orderSn']}\","
                . "\"subject\":\"{$param['orderInfo']}\","
                . "\"out_trade_no\":\"{$param['orderSn']}-{$param['order_type']}\","
                . "\"total_amount\":\"{$param['orderFee']}\","
                . "\"product_code\":\"QUICK_MSECURITY_PAY\""
                . "}";
        trace('bizcontent'.$bizcontent,'debug');
//        $request->setNotifyUrl(MOBILE_SITE_URL . '/payment/alipay_notify_app');
        $request->setNotifyUrl($param['notifyUrl']);
        $request->setBizContent($bizcontent);
//这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);

//htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
//        echo htmlspecialchars($response); //就是orderString 可以直接给客户端请求，无需再做处理。

        output_data(array('content'=>$response,'orderSn'=>$param['orderSn']));
    }

    function verify_notify($param) {
        require_once APP_PATH .'wap/api/payment/alipay_app/lib/config.php';
        require_once APP_PATH .'wap/api/payment/alipay_app/lib/AopClient.php';
        $aop = new \AopClient();
        
        $aop->alipayrsaPublicKey = $config['merchant_public_key'];
        $flag = $aop->rsaCheckV1($param, NULL, "RSA2");
        if ($flag) {
            $notify_result = array(
                'out_trade_no' => $param["out_trade_no"], #商户订单号
                'trade_no' => $param['trade_no'], #交易凭据单号
                'total_fee' => $param["total_amount"], #涉及金额
                'trade_status' => '1',
            );
        } else {
            $notify_result = array(
                'trade_status' => '0',
            );
        }
        return $notify_result;
    }

}

?>
