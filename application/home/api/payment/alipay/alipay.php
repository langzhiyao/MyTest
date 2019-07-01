<?php
require_once dirname(__FILE__) . '/service/AlipayTradeService.php';
require_once dirname(__FILE__) . '/buildermodel/AlipayTradePagePayContentBuilder.php';

class alipay
{
    private $config;
    private $out_trade_no; //商户订单号
    private $subject;  //订单名称
    private $total_amount; //付款金额
    private $body;  //商品描述

    public function __construct($payment_info = array(), $order_info = array())
    {
        if (!empty($payment_info)) {
            $this->config = array(
                //应用ID,您的APPID。
                'app_id' => $payment_info['payment_config']['alipay_appid'],

                //商户私钥
                'merchant_private_key' => $payment_info['payment_config']['private_key'],
            /*"MIIEpAIBAAKCAQEA5KcAlYqnDlA6QXAKLvjHo+ow2VlA9BpjAViwFTuLYwOt3ryayA2CEnMda9F5MzZ3Mv4Vg2LlIPrQTvWT+CHh4+5TkQow5qlNWxZSikhKYSwmMftlN/ObFD+BeUjLKdJnAMRhWmdUa5llNBRuV/psvg8DJ+EZMxEwKVfc2lAZqcq8zPun/ecTpSlxqs6rMnvBpnMnSmWsVoYQC8AXCKLtsoE3Ot8M1S/egy1tjb8rBAMwa/r8SfNuSl9O1YepB/+BvGAJ4OW0m026dH74u1wqiS5NuECmH3AFGf3TcZgi0SwmKsTzhqaSAWNQfCIJdBJ0YsCzW6OiYllAz+yf9RWRzQIDAQABAoIBAFwxvjmN24gY7zRdca243/6GukWZCGikjxEG6pDVHoHBBQVPdPV/BNhdlBpaLw1oQ63K52+/m3Wty/paaNxfBQ77lLRhsJAA6dD1cjiRp3QA8jGrFQf3cKs8Y/88S7bEQIX9qOdjzJVKF5VlO4y7y8bilLoquBdwMcQpykI4k/ByrIwzf1KIBD/jjlMoQyhI64g6BPVSChyeXvZuRY75AX39XoIfSPiyJhZ3/v2YRtpGnW4jDzysWnXOTTzj7zwPS/3ih4S8rjnpuW80hyFuVdKgOnzNjnag7C6DieOLaKNfRbjOI3A0DlTP9KMz6VpabSpb95kA7+xhcIsRnTLBKmECgYEA8rE//BAiuDLBM5RyG2CK5ksE3H46S5XbuRYjRCWnc16a5mFuue6/7nnxcTqDX5jmuFm2IuQ7E6G68f2S9cUE7t4kliXJmc7eM/c0blgcU6Zlpo16mfieKHrFlhShiRfTEmHAT1ovchhVanniWip7EvU4LwDuJrqdsY4YK34sQ7kCgYEA8TCrAOZwlEu5yFq2hQXHjMSxbUNvmF+gtFKYAlDTqBBZF40NcIkJOs9oNLs/tEJcHGrjXfLHRaT2hkHNlGfztF3iHfPb5PKz8mcHa5cw2ZRXWmaTcg5G33JRyFL2bKEjlw3Z7OqBiyVWsql0bevdMgwDvwYenZ4W++uT83VPMLUCgYEAiAjgo6pru7H/Z6kauMvJr8J3LBy5EmsiqUGGbQlqLhnmW6JbjW8NOGAz/NLelrQ/BzCKDk696ogqIMCRIp/X3wi3m039DfDNznUPd5Z98kmACvactTeNd4Uxwak4zn6DOnd+czxLAfovzqoZPY84Q20enAI6e4z5HXBXjfGAYEkCgYA8NGDLJbp+WfSy/WnIBKxOCB8d3hJyH5S2zlMhaNmcfxAuH8h2Cc7i/jjNyNva/CGP+mJs5hg12zqqQqy1WclsgW7a+S8vlCG05WDly2SnOy8e4rH1a3jnd9rQPV1Dumlu4EdAqzzQ5e4hRMlKUvDw+CzLNxTXVUn5clGJGQQ+bQKBgQDWpyT22EVZZCw5lqnux+Oljkf+eWSEr+u0v53Td43iHyKlrk3HTp8AMH0ku22Barp3uWU1CA/fBKoPx1rm7t4z4w5OTIsxKHFP98W/OVbR/v+VzjZGpHPAQyNVkWkT6ShRbpyA7JSk/yhy6sA832PUEIQ5d7AboVlvAVhfm0YLsw=="*/

                //异步通知地址
                'notify_url' => SHOP_SITE_URL.'/payment/alipay_notify_url', //通知URL,

                //同步跳转
                'return_url' => SHOP_SITE_URL . "/payment/alipay_return_url", //返回URL,


                //编码格式
                'charset' => "UTF-8",

                //签名方式
                'sign_type' => "RSA2",

                //支付宝网关
                'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

                //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
                'alipay_public_key' =>$payment_info['payment_config']['public_key'],
                  /* "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAofrXN5v5IpOIW07UdjYjx88efFfWuGiDV5itzlBDc5C8FHnlI8WDYGOz5Kne0JT1ZMRNjyyW5CAuE/4Nd89GM+36f4OSLt8xZzwt5x6l4PE1yJcT2GDDjkFBeOZA8WFqlcWt3FeLiHlvgioY5luHZrEmfm5//6oRqOjxzmQcj8kZooMvaD7vt+zdH8bQhfsRZqx/coRXftMHTvp15QQLzKJdCcbkccB9NtT5LkoL4lyXgB307ESnRNM1FOtGfHQWZDzXYjdNhKUdcqmlwJ12niumCkFu6EYS6CFp6O5fdsRYDuv425iXGk4cVula7yyvkaIyb+LPP7JuI9rW2KHd4wIDAQAB"*/
            );
        }
        if(!empty($order_info)){
            $this->out_trade_no = $order_info['pay_sn'];
            $this->subject = $order_info['subject'];
            $this->total_amount = $order_info['api_pay_amount'];
            $this->body = $order_info['order_type'];
        }
    }

    /**
     * 获取支付接口的请求地址
     *
     * @return string
     */
    public function get_payurl()
    {
        //构造参数
        $payRequestBuilder = new AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setBody($this->body);
        $payRequestBuilder->setSubject($this->subject);
        $payRequestBuilder->setTotalAmount($this->total_amount);
        $payRequestBuilder->setOutTradeNo($this->out_trade_no);

        $aop = new AlipayTradeService($this->config);

        /**
         * pagePay 电脑网站支付请求
         * @param $builder 业务参数，使用buildmodel中的对象生成。
         * @param $return_url 同步跳转地址，公网可以访问
         * @param $notify_url 异步通知地址，公网可以访问
         * @return $response 支付宝返回的信息
         */
        $response = $aop->pagePay($payRequestBuilder,$this->config['return_url'],$this->config['notify_url']);

        //输出表单
        var_dump($response);
    }

    public function return_verify(){
        $arr=input('param.');
        $alipaySevice = new AlipayTradeService($this->config);
        $alipaySevice->writeLog(var_export($arr,true));
        $result = $alipaySevice->check($arr);
        if($result){
            return true;
        }else{
            return false;
        }
    }

    public function notify_verify() {
        $arr=$_POST;
        $alipaySevice = new AlipayTradeService($this->config);
        $alipaySevice->writeLog('notify_verify'.var_export($arr,true));
        $result = $alipaySevice->check($arr);
        if($result){
            return true;
        }else{
            return false;
        }
    }
    /**
     *
     * 取得订单支付状态，成功或失败
     * @param array $param
     * @return array
     */
    public function getPayResult($param){
        return $param['trade_status'] == 'TRADE_SUCCESS';
    }
}
