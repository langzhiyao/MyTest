<?php

class wxpay_h5
{
    const DEBUG = 0;

    protected $config;

    public function __construct()
    {
        $this->config = (object) array(
            'wxpay_appid' => '',
            'wxpay_partnerid' => '',
            'wxpay_partnerkey' => '',

            'notifyUrl' => WAP_SITE_URL . '/payment/wx_notify_h5.html',

            'orderSn' => date('YmdHis'),
            'orderInfo' => '',
            'orderFee' => 1,
            'orderAttach' => '_',
            // 'sceneInfo' =>json_encode(array('h5_info'=>array('type'=>'WAP','wap_url'=>APP_SITE_URL,'wap_name'=>config('site_name')))),
            'sceneInfo' =>json_encode(array('h5_info'=>array('type'=>'Wap','wap_url'=>config('url_domain_root'),'wap_name'=>config('site_name')))),
        );
    }



    public function setConfigs(array $params)
    {
        foreach ($params as $name => $value) {
            $this->config->$name = trim($value);
        }
    }

    public function get_payurl() {
        require_once APP_PATH .'wap/api/payment/wxpay_h5/lib/WxPay.Api.php';
        $Spbill_create_ip=$this->get_client_ip2();
        //统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody($this->config->orderInfo);
        $input->SetAttach($this->config->orderAttach);
        $input->SetOut_trade_no($this->config->orderSn);
        $input->SetTotal_fee($this->config->orderFee);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 3600));
        $input->SetGoods_tag('');
        $input->SetSpbill_create_ip($Spbill_create_ip);
        $input->SetDevice_info('WEB');
        $input->SetNotify_url($this->config->notifyUrl);
        $input->SetTrade_type("MWEB");
        switch ($_GET['apptype']) {
            case 'Android':
                $ecene='{"h5_info": {"type":"Android","app_name": "'.config('site_name').'","package_name": ""}}';
                break;
            case 'IOS':
                $ecene='{"h5_info": {"type":"IOS","app_name": "'.config('site_name').'","bundle_id": "com.ulinkbank.ussshop"}}';
                break;
            default:
                $ecene='{"h5_info": {"type":"Wap","wap_url": "'.APP_SITE_URL.'","wap_name": "'.config('site_name').'"}}';
                break;
        }
        $input->SetScene_info(json_encode($ecene));
        $input->SetProduct_id($this->config->orderSn);
        $result = WxPayApi::unifiedOrder($input);
        write_payment(json_encode($return),'wxpay_h5');

        return $result;
    }

    public function getOrderStateBysn($orderSn){
        require_once APP_PATH .'wap/api/payment/wxpay_h5/lib/WxPay.Api.php';

        $Query=new WxPayOrderQuery();
        $Query->SetOut_trade_no($orderSn);
        $result = WxPayApi::orderQuery($Query);
        $orderState=array();
        if ($result['return_code']=='SUCCESS') {
            if ($result['result_code'] == 'SUCCESS') {
                if ($result['trade_state'] == 'SUCCESS') {//支付成功
                    $orderState['attach']         =$result['attach'];
                    $orderState['out_trade_no']   =$result['out_trade_no'];
                    $orderState['transaction_id'] =$result['transaction_id'];
                    $orderState['cash_fee']       = $result['cash_fee']/100;
                }
            }
            $orderState['trade_state'] =$this->trade_state($result);
        }else{
            $orderState['error']='签名失败或者参数格式校验错误';
        }

        return $orderState;
    }
    public function trade_state($result){
        $type = $result['trade_state'];
        if(isset($result['err_code']))$type=$result['err_code'];
        $paystate=array();
        switch ($type) {
            case 'SUCCESS':
                $paystate['pay_state']= '支付成功';
                $paystate['state']= 1;
                break;
            case 'REFUND':
                $paystate['pay_state']= '转入退款';
                $paystate['state']= 2;
                break;
            case 'CLOSED':
                $paystate['pay_state']= '已关闭';
                $paystate['state']= 3;
                break;
            case 'REVOKED':
                $paystate['pay_state']= '已撤销（刷卡支付）';
                $paystate['state']= 4;
                break;
            case 'NOTPAY':
                $paystate['pay_state']= '订单未支付';
                $paystate['state']= 5;
                break;
            case 'USERPAYING':
                $paystate['pay_state']= '用户支付中';
                $paystate['state']= 6;
                break;
            case 'PAYERROR':
                $paystate['pay_state']= '支付失败(其他原因，如银行返回失败)';
                $paystate['state']= 7;
                break;
            case 'ORDERNOTEXIST':
                $paystate['pay_state']= '支付失败(订单不存在)';
                $paystate['state']= 8;
                break;
        }
        $paystate['trade_state']= $type;
        return $paystate;
    }



    /*mweb_url*/
    public function get_mweb_url(){
        $data = array();
        $data['appid']            = $this->config->wxpay_appid;
        $data['mch_id']           = $this->config->wxpay_partnerid;
        $data['nonce_str']        = md5(uniqid(mt_rand(), true));
        $data['body']             = $this->config->orderInfo;
        $data['attach']           = $this->config->orderAttach;
        $data['out_trade_no']     = $this->config->orderSn;
        $data['total_fee']        = $this->config->orderFee;
        $data['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['notify_url']       = $this->config->notifyUrl;
        $data['trade_type']       = 'MWEB';
        $data['scene_info']       = $this->config->sceneInfo;
        $sign                     = $this->sign($data);
        $data['sign']             = $sign;
        $result = $this->postXml('https://api.mch.weixin.qq.com/pay/unifiedorder', $data);


        if ($result['return_code'] != 'SUCCESS') {
            exception($result['return_msg']);
        }

        if ($result['result_code'] != 'SUCCESS') {
            exception("[{$result['err_code']}]{$result['err_code_des']}");
        }

        return $result['mweb_url'].'&redirect_url='.urlencode('vip.xiangjianhai.com://');
    }

    public function notify()
    {
        try {
            $data = $this->onNotify();
            $resultXml = $this->arrayToXml(array(
                                               'return_code' => 'SUCCESS',
                                           ));
            trace('notify'.json_encode($data),'debug');

        } catch (Exception $ex) {

            $data = null;
            $resultXml = $this->arrayToXml(array(
                                               'return_code' => 'FAIL',
                                               'return_msg' => $ex->getMessage(),
                                           ));

        }
        return array(
            $data,
            $resultXml,
        );
    }

    public function onNotify()
    {
        $d = $this->xmlToArray(file_get_contents('php://input'));

        if (empty($d)) {
            exception(__METHOD__);
        }

        if ($d['return_code'] != 'SUCCESS') {
            exception($d['return_msg']);
        }

        if ($d['result_code'] != 'SUCCESS') {
            exception("[{$d['err_code']}]{$d['err_code_des']}");
        }

        if (!$this->verify($d)) {
            exception("Invalid signature");
        }

        return $d;
    }

    public function verify(array $d)
    {
        if (empty($d['sign'])) {
            return false;
        }

        $sign = $d['sign'];
        unset($d['sign']);

        return $sign == $this->sign($d);
    }

    public function sign(array $data)
    {
        ksort($data);
        $a = array();
        foreach ($data as $k => $v) {
            if ((string) $v === '') {
                continue;
            }
            $a[] = "{$k}={$v}";
        }

        $a = implode('&', $a);
        $a .= '&key=' . $this->config->apiKey;

        return strtoupper(md5($a));
    }

    public function postXml($url, array $data)
    {
        // pack xml
        $xml = $this->arrayToXml($data);

        // curl post
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $response = curl_exec($ch);
        if (!$response) {
            exception('CURL Error: ' . curl_errno($ch));
        }
        curl_close($ch);

        // unpack xml
        return $this->xmlToArray($response);
    }

    public function arrayToXml(array $data)
    {
        $xml = "<xml>";
        foreach ($data as $k => $v) {
            if (is_numeric($v)) {
                $xml .= "<{$k}>{$v}</{$k}>";
            } else {
                $xml .= "<{$k}><![CDATA[{$v}]]></{$k}>";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    public function xmlToArray($xml)
    {
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    public function get_client_ip2(){
        static $ip  =   NULL;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }

        // IP地址合法验证
        $long = sprintf("%u",ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[0]  ;
    }
}