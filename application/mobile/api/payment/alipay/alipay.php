<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'service/AlipayTradeService.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'buildermodel/AlipayTradeWapPayContentBuilder.php';

class alipay
{
    //配置
    private $alipay_config = array();
    //商户订单号
    private $out_trade_no = "";
    //订单名称
    private $subject = "";
    //付款金额
    private $total_amount = "";
    //商品描述
    private $body = "";
    //超时事件
    private $timeout_express = "";
    //打开APP
    private $app_pay = "";

    public function __construct($param=array())
    {
        if(!empty($param)) {
            $this->alipay_config = array(
                //应用ID,您的APPID。
                'app_id' => $param['alipay_appid'],

                //商户私钥，您的原始格式RSA私钥
                'merchant_private_key' => $param['private_key'],

                //异步通知地址
                'notify_url' => MOBILE_SITE_URL . '/payment/notify.html',

                //同步跳转
                'return_url' => MOBILE_SITE_URL . '/payment/alipay_return_url.html',

                //编码格式
                'charset' => "UTF-8",

                //签名方式
                'sign_type' => "RSA2",

                //支付宝网关
                'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

                //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
                'alipay_public_key' =>$param['public_key'],
                //商户公钥
                'merchant_public_key' =>"MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlPXfE+mKzBC+NBgN68OORr2WtqHzhkNgbrqlfW4ClwHgRO/YABz2e7iHD4SFcFidFEUvKp7eQPWr39IwNOQ8tBYMzdIHTgebzuI36RaGO0ojEokm5QyIBNnutWuJVQ7AWD3gexqivn+Aoh0WA0pnXq7vI348EvkrQFRVkLDbMpd/FzwYQ8q4HCM/ffVnAN7gZ/kYLOuvc3LypwTkXZOUlZYvzCVg1d9nPxBXj5zxXV/lXDzPyIswX/99yONixC+RA2OCRmeiskEYaSrXN+WY8i7aBrFvLnHQ7IppYGWlhdhjc6YovrUnVR/7mY2ThkMsns9/o24tEUSljT8I/gGGoQIDAQAB"


            );
            if (isset($param['orderSn'])) {
                $this->out_trade_no = $param['orderSn'] . '-' . $param['orderAttach'];
                $this->subject = $param['orderInfo'];
                $this->total_amount = $param['orderFee'];
                $this->timeout_express = "1m";
                $this->body = "想见孩产品";
                $this->app_pay = 'Y';
            }
        }else{
            output_error('参数错误！');
        }
    }

    public function submit()
    {
        $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody($this->body);
        $payRequestBuilder->setSubject($this->subject);
        $payRequestBuilder->setOutTradeNo($this->out_trade_no);
        $payRequestBuilder->setTotalAmount($this->total_amount);
        $payRequestBuilder->setTimeExpress($this->timeout_express);
        $payRequestBuilder->setTimeExpress($this->app_pay);

        $payResponse = new AlipayTradeService($this->alipay_config);

//        halt($payRequestBuilder);

        $result = $payResponse->wapPay($payRequestBuilder, $this->alipay_config['return_url'], $this->alipay_config['notify_url']);

        return;

    }

    /**
     * 获取return信息
     */
    public function getReturnInfo()
    {
        $arr = $_GET;
        $alipaySevice = new AlipayTradeService($this->alipay_config);
        $result = $alipaySevice->check($arr);
        trace('result' . $result, 'debug');
        if ($result) {
            return true;
        }
        return false;
    }

    public function getNotifyInfo()
    {
        $arr = $_POST;
            $alipaySevice = new AlipayTradeService($this->alipay_config);
            $alipaySevice->writeLog(var_export($_POST, true));
            $result = $alipaySevice->check($arr);
            if ($result) {
                if ($arr['trade_status'] == 'TRADE_FINISHED' || $arr['trade_status'] == 'TRADE_SUCCESS') {
                    return array(
                        //商户订单号
                        'out_trade_no' => $arr['out_trade_no'], //支付宝交易号
                        'trade_no' => $arr['trade_no'],
                    );
                }
            }
        return false;
    }
}

