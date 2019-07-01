<?php
/**
 * 手机短信类
 */
namespace sendmsg;
use sendmsg\sdk\SmsApi;
class Sms
{
    /*
    
     * 发送手机短信
     * @param unknown $mobile 手机号
     * @param unknown $log_msg 短信内容
    */
    public function send($mobile,$log_msg, $content='')
    {   

        return $this->SendSmsMessage($mobile,$log_msg, $content);
    }

    /**
     * 253云通讯科技验证码发送
     * @param [type] $mobile  [手机号]
     * @param [type] $content [验证码]
     * @param [type] $log_msg  [发送内容]
     */
    private function SendSmsMessage($mobile,$log_msg, $content=''){
        $clapi  = new SmsApi();
        //设置您要发送的内容：其中“【】”中括号为运营商签名符号，多签名内容前置添加提交
        $result = $clapi->sendSMS($mobile,$log_msg );
        if(!is_null(json_decode($result))){
            
            $output=json_decode($result,true);

            if(isset($output['code'])  && $output['code']=='0'){
                return true;
                // echo '发送成功';
            }else{
                return false;
                // echo $output['errorMsg'];
            }
        }else{

                return false;
                // echo $result; 
        }
    }

    private function sendTemplateSMS($mobile,$datas,$tempId){
     // 初始化REST SDK
     $rest = new SmsApi();

     // 发送模板短信
     $datas=array($datas);
     
     $result = $rest->sendTemplateSMS($mobile,$datas,$tempId);
     if($result == NULL ) {
        return false;
         // echo "result error!";
         // break;
     }
     if($result->statusCode!=0) {
        return false;
         // echo "error code :" . $result->statusCode . "<br>";
         // echo "error msg :" . $result->statusMsg . "<br>";
         //TODO 添加错误处理逻辑
     }else{
        return true;
         // echo "Sendind TemplateSMS success!<br/>";
         // // 获取返回信息
         // $smsmessage = $result->TemplateSMS;
         // echo "dateCreated:".$smsmessage->dateCreated."<br/>";
         // echo "smsMessageSid:".$smsmessage->smsMessageSid."<br/>";
         //TODO 添加成功处理逻辑
     }
}



    /*
    您于{$send_time}绑定手机号，验证码是：{$verify_code}。【{$site_name}】
    -1	没有该用户账户
    -2	接口密钥不正确 [查看密钥]不是账户登陆密码
    -21	MD5接口密钥加密不正确
    -3	短信数量不足
    -11	该用户被禁用
    -14	短信内容出现非法字符
    -4	手机号格式不正确
    -41	手机号码为空
    -42	短信内容为空
    -51	短信签名格式不正确接口签名格式为：【签名内容】
    -6	IP限制
   大于0 短信发送数量
    http://utf8.api.smschinese.cn/?Uid=本站用户名&Key=接口安全秘钥&smsMob=手机号码&smsText=验证码:8888
    */
    private function mysend_sms1($mobile, $content)
    {
        $user_id = urlencode(config('mobile_username')); // 这里填写用户名
        $mobile_key = urlencode(config('mobile_key')); // 这里填接口安全密钥
        if (!$mobile || !$content || !$user_id || !$mobile_key)
            return false;
        if (is_array($mobile)) {
            $mobile = implode(",", $mobile);
        }
        $mobile=urlencode($mobile);
        $content=urlencode($content);
        $url = "http://utf8.api.smschinese.cn/?Uid=" . $user_id . "&Key=" . $mobile_key . "&smsMob=" . $mobile . "&smsText=" . $content;
        if (function_exists('file_get_contents')) {
            $res = file_get_contents($url);
        }
        else {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $res = curl_exec($ch);
            curl_close($ch);
        }
        //file_put_contents('smsres.txt',$res);
        if ($res >0) {
            return true;
        }
        return false;

    }

    /**
     * http://www.yunpian.com/
     * 发送手机短信
     * @param unknown $mobile 手机号
     * @param unknown $content 短信内容
     * 0    OK    调用成功，该值为null    无需处理
     * 1    请求参数缺失    补充必须传入的参数    开发者
     * 2    请求参数格式错误    按提示修改参数值的格式    开发者
     * 3    账户余额不足    账户需要充值，请充值后重试    开发者
     * 4    关键词屏蔽    关键词屏蔽，修改关键词后重试    开发者
     * 5    未找到对应id的模板    模板id不存在或者已经删除    开发者
     * 6    添加模板失败    模板有一定的规范，按失败提示修改    开发者
     * 7    模板不可用    审核状态的模板和审核未通过的模板不可用    开发者
     * 8    同一手机号30秒内重复提交相同的内容    请检查是否同一手机号在30秒内重复提交相同的内容    开发者
     * 9    同一手机号5分钟内重复提交相同的内容超过3次    为避免重复发送骚扰用户，同一手机号5分钟内相同内容最多允许发3次    开发者
     * 10    手机号黑名单过滤    手机号在黑名单列表中（你可以把不想发送的手机号添加到黑名单列表）    开发者
     * 11    接口不支持GET方式调用    接口不支持GET方式调用，请按提示或者文档说明的方法调用，一般为POST    开发者
     * 12    接口不支持POST方式调用    接口不支持POST方式调用，请按提示或者文档说明的方法调用，一般为GET    开发者
     * 13    营销短信暂停发送    由于运营商管制，营销短信暂时不能发送    开发者
     * 14    解码失败    请确认内容编码是否设置正确    开发者
     * 15    签名不匹配    短信签名与预设的固定签名不匹配    开发者
     * 16    签名格式不正确    短信内容不能包含多个签名【 】符号    开发者
     * 17    24小时内同一手机号发送次数超过限制    请检查程序是否有异常或者系统是否被恶意攻击    开发者
     * -1    非法的apikey    apikey不正确或没有授权    开发者
     * -2    API没有权限    用户没有对应的API权限    开发者
     * -3    IP没有权限    访问IP不在白名单之内，可在后台"账户设置->IP白名单设置"里添加该IP    开发者
     * -4    访问次数超限    调整访问频率或者申请更高的调用量    开发者
     * -5    访问频率超限    短期内访问过于频繁，请降低访问频率    开发者
     * -50 未知异常    系统出现未知的异常情况    技术支持
     * -51 系统繁忙    系统繁忙，请稍后重试    技术支持
     * -52 充值失败    充值时系统出错    技术支持
     * -53 提交短信失败    提交短信时系统出错    技术支持
     * -54 记录已存在    常见于插入键值已存在的记录    技术支持
     * -55 记录不存在    没有找到预期中的数据    技术支持
     * -57 用户开通过固定签名功能，但签名未设置    联系客服或技术支持设置固定签名    技术支持
     */
    private function mysend_yunpian($mobile, $content)
    {
        $yunpian = 'yunpian';
        $plugin = str_replace('\\', '', str_replace('/', '', str_replace('.', '', $yunpian)));
        if (!empty($plugin)) {
            define('PLUGIN_ROOT', BASE_DATA_PATH . DS . 'api/smsapi');
            require_once(PLUGIN_ROOT . DS . $plugin . DS . 'Send.php');
            return send_sms($content, $mobile);
        }
        else {
            return false;
        }
    }


    /**
     * 请求接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int $ipost [是否采用POST形式]
     * @return  string
     */
    private function juhecurl($url,$params=false,$ispost=0){
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
        curl_setopt( $ch, CURLOPT_USERAGENT , 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22' );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 30 );
        curl_setopt( $ch, CURLOPT_TIMEOUT , 30);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
        if( $ispost )
        {
            curl_setopt( $ch , CURLOPT_POST , true );
            curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
            curl_setopt( $ch , CURLOPT_URL , $url );
        }
        else
        {
            if($params){
                curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
            }else{
                curl_setopt( $ch , CURLOPT_URL , $url);
            }
        }
        $response = curl_exec( $ch );
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
        $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
        curl_close( $ch );
        return $response;
    }


}
