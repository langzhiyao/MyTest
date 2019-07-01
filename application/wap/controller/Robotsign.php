<?php

namespace app\wap\controller;

use think\Lang;
class Robotsign extends MobileMall
{
    public function _initialize()
    {
        parent::_initialize();
        // Lang::load(APP_PATH . 'wap\lang\zh-cn\login.lang.php');
    }

    public function memberpush(){

        //使用极光推送
        $md= model('Jpush');
        $md->JPushInit();
        /**
         * member_id  用户id
         * alert 提示信息
         * title 提醒标题
         */
        $input = input();
        $phone = $input['member_mobile'];
        $member_id = db('member')->where('member_mobile',$phone)->value('member_id');
        $alert = '测试推送'.date('Y-m-d H:i:s',time());
        $pushResult = $md->MemberPush($member_id,$alert,$title='打卡提醒');
        if ($pushResult['code']==200) {
            if ($pushResult['result']['http_code']==200) {
                echo json_encode($pushResult['result']); exit;
            }else{
                echo $pushResult['error'].'<br><br>';
                echo $pushResult['ConnectionErr']['message'].'<br><br>';
                echo $pushResult['RequestErr']['message'].'<br><br>';
                exit;
            }
        }else{
            echo $pushResult['error'].'<br><br>';exit;
        }
    }

    public function SqlinTest(){
        p(input());
        $model = model('member');
        $member = $model->where(input())->select();
        p($model->getlastsql());
        p($member);
    }

    
    

 
}

?>
