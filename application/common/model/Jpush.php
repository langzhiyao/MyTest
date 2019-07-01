<?php

namespace app\common\model;
vendor('jpush.autoload');
use JPush\Client;
use think\Model;
class Jpush extends Model {

    private $AppKey;
    private $MaterSecret;
    private $Client;

    public function __construct()
    {   
        $config = config('JPushConfig');
        $this->AppKey = $config['AppKey'];
        $this->MaterSecret =$config['MaterSecret'];
        
    }

    public function JPushInit(){
        $this->Client =new Client($this->AppKey,$this->MaterSecret);
    }

    /**
     * 给单个人发送极光信息
     * @创建时间  2018-11-14T15:23:21+0800
     * @param [int]                    $memberid [description]
     * @param string                   $alert    [提示消息]
     * @param string                   $mtitle   [记录title]
     */
    public function MemberPush($memberid,$alert='',$mtitle='打卡提醒'){
        $error = [];
        $error['code'] = 300;
        if(!$memberid || !is_numeric($memberid)){
            $error['error']= '用户ID参数错误！';
            return $error;
        }
        if(empty($alert)){
            $error['error']= '提示消息不能为空！';
            return $error;
        }
        //获取当前登陆平台类型--暂不需要
        // $client_type = db('mbusertoken')->where('member_id',$memberid)->value('client_type');
        //获取登陆人当前手机唯一极光ID
        $registrationID = db('memberjpush')->where('member_id',$memberid)->value('registrationID');
        if(!$registrationID){
            $error['error']= '获取不到当前用户手机信息！可以停止推送';
            return $error;
        }
        //设置推送平台，all ，苹果，安卓，winPhone
        $platform = array('ios', 'android');
        // $alert = '您的孩子二霞于xx-xx-xx xx:xx:xx 在某某学校打卡成功！';
        $regId = [$registrationID];
        $ios_notification = array(
            'sound' => $mtitle,
            'badge' => 2,
            'content-available' => true,
            'category' => 'jiguang',
            'extras' => array(
                'action' => 'RobotSign',
                'jiguang'
            ),
        );
        $android_notification = array(
            'title' => $mtitle,
            'builder_id' => 2,
            'extras' => array(
                'action' => 'RobotSign',
                'jiguang'
            ),
        );
        $options=array(
            //表示推送序号，纯粹用来作为 API 调用标识，
            // API 返回时被原样返回，以方便 API 调用方匹配请求与返回
            'sendno'            => 100,
            // 推送当前用户不在线时，为该用户保留多长时间的离线消息，以便其上线时再次推送。
            // 默认 86400 （1 天），最长 10 天。设置为 0 表示不保留离线消息，只有推送当前在线的用户可以收到
            'time_to_live'      => 86400 ,//表示离线消息保留时长(秒)，
            // True 表示推送生产环境，False 表示要推送开发环境；如果不指定则默认为推送生产环境
            'apns_production'   => TRUE,
            // big_push_duration: 表示定速推送时长(分钟)，又名缓慢推送，把原本尽可能快的推送速度，降低下来，
            // 给定的 n 分钟内，均匀地向这次推送的目标用户推送。最大值为1400.未设置则不是定速推送
            'big_push_duration' => 1
        );
        $push_payload = $this->Client->push()
            ->setPlatform($platform)
            ->addRegistrationId($regId)
            ->iosNotification($alert, $ios_notification)
            ->androidNotification($alert, $android_notification)
            ->options($options);
        try {
            $response = $push_payload->send();
            $error['code'] = 200;
        } catch (\JPush\Exceptions\APIConnectionException $e) {
            $A = json_encode($e);
            $error['ConnectionErr'] =json_decode($A,TRUE);
            $error['error'] ='连接失败！';
        } catch (\JPush\Exceptions\APIRequestException $e) {
            $A = json_encode($e);
            $error['RequestErr'] = json_decode($A,TRUE);
            $error['error'] ='请求失败！';
        }
        if($error['code']==200 && $response['http_code']==200){
            $model_member = Model('member');
            $member_info = $model_member->getMemberInfoByID($memberid);
            // 记录推送信息并发送系统提示
            $this->SysMessage($member_info,$alert,$mtitle); 
            //记录推送消息
            $error['result'] = $response;
        }
        return $error;
        
    }

    /**
     * 记录推送信息并发送系统提示
     * @创建时间  2018-11-14T15:05:11+0800
     * @param [type]                   $member_info [description]
     * @param [type]                   $msg_content [description]
     * @param string                   $title       [description]
     */
    public function SysMessage($member_info,$msg_content,$title='打卡提醒'){
        //发送站内信
        $model_message = Model('message');
        $insert_arr = array();
        $insert_arr['from_member_id'] = 0;
        $insert_arr['member_id'] = $member_info['member_id'];
        $insert_arr['to_member_name'] = $member_info['member_name'];
        $insert_arr['message_title'] = $title;
        $insert_arr['msg_content'] = $msg_content;
        $insert_arr['message_type'] = 1;
        $model_message->saveMessage($insert_arr);
    }
    
    
}

?>
