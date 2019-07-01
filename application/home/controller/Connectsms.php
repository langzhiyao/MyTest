<?php

/*
 * 手机验证码
 */

namespace app\home\controller;

use think\Lang;

class Connectsms extends BaseMall {
    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/login.lang.php');
    }
    /**
     * 短信动态码
     */
    public function get_captcha() {
        header("Content-Type: text/html;charset=utf-8");
        $sms_mobile = input('param.sms_mobile');
        if (strlen($sms_mobile) == 11) {
            $log_type = input('param.type'); //短信类型:1为注册,2为登录,3为找回密码
            $model_sms_log = Model('smslog');
            $condition = array();
            $begin_add_time = strtotime(date('Y-m-d', time()));
            $end_add_time = strtotime(date('Y-m-d', time())) + 24 * 3600;
            //同一IP 每天只能发送10条短信
            $condition = array();
            $condition['log_ip'] = request()->ip();
            $condition['add_time'] = array('between', array($begin_add_time, $end_add_time));
            if ($model_sms_log->getSmsCount($condition) > 2000) {
                echo '同一IP地址一天内只能发送20条短信，请勿多次获取动态码！';
                exit;
            }
            //同一手机号,每天只能发送5条短信
            $condition = array();
            $condition['log_phone'] = $sms_mobile;
            $condition['add_time'] = array('between', array($begin_add_time, $end_add_time));
            if ($model_sms_log->getSmsCount($condition) > 5000) {
                echo '同一手机一天内只能发送5条短信，请勿多次获取动态码！';
                exit;
            }

            $log_array = array();
            $model_member = Model('member');
            $member = $model_member->getMemberInfo(array('member_mobile' => $sms_mobile));
            $sms_captcha = rand(100000, 999999);
            $log_msg = '【' . config('site_name') . '】您于' . date("Y-m-d");
            $sms_tpl = config('sms_tpl');
            switch ($log_type) {
                case '1':
                    if (config('sms_register') != 1) {
                        echo '系统没有开启手机注册功能';
                        exit;
                    }
                    if (!empty($member)) {
                        //检查手机号是否已被注册
                        echo '当前手机号已被注册，请更换其他号码。';
                        exit;
                    }
                    $log_msg .= '申请注册会员，动态码：' . $sms_captcha . '。';
                    $tempId = $sms_tpl['sms_register'];
                    break;
                case '2':
                    if (config('sms_login') != 1) {
                        echo '系统没有开启手机登录功能';
                        exit;
                    }
                    if (empty($member)) {
                        //检查手机号是否已绑定会员
                        echo '当前手机号未注册，请检查号码是否正确。';
                        exit;
                    }
                    $log_msg .= '申请登录，动态码：' . $sms_captcha . '。';
                    $log_array['member_id'] = $member['member_id'];
                    $log_array['member_name'] = $member['member_name'];
                    $tempId = $sms_tpl['sms_login'];
                    break;
                case '3':
                    if (config('sms_password') != 1) {
                        echo '系统没有开启手机找回密码功能';
                        exit;
                    }
                    if (empty($member)) {
                        //检查手机号是否已绑定会员
                        echo '当前手机号未注册，请检查号码是否正确。';
                        exit;
                    }
                    $log_msg .= '申请重置登录密码，动态码：' . $sms_captcha . '。';
                    $log_array['member_id'] = $member['member_id'];
                    $log_array['member_name'] = $member['member_name'];
                    $tempId = $sms_tpl['sms_password'];
                    break;
                default:
                    echo '参数错误';
                    exit;
                    break;
            }
            $sms = new \sendmsg\Sms();
            $result = $sms->send($sms_mobile,$sms_captcha,$tempId);
            if ($result) {
                session('sms_mobile', $sms_mobile);
                session('sms_captcha', $sms_captcha);
                $log_array['log_phone'] = $sms_mobile;
                $log_array['log_captcha'] = $sms_captcha;
                $log_array['log_ip'] = request()->ip();
                $log_array['log_msg'] = $log_msg;
                $log_array['log_type'] = $log_type;
                $log_array['add_time'] = time();
                $model_sms_log->addSms($log_array);
                echo 'true';
                exit;
            } else {
                echo '手机短信发送失败';
                exit;
            }
        } else {
            echo '手机号长度不正确';
            exit;
        }
    }

    /**
     * 验证注册动态码
     */
    public function check_captcha() {
        $state = '验证失败';
        $phone = $_GET['phone'];
        $captcha = $_GET['sms_captcha'];
        if (strlen($phone) == 11 && strlen($captcha) == 6) {
            $state = 'true';
            $condition = array();
            $condition['log_phone'] = $phone;
            $condition['log_captcha'] = $captcha;
            $condition['log_type'] = 1;
            $model_sms_log = Model('sms_log');
            $sms_log = $model_sms_log->getSmsInfo($condition);
            if (empty($sms_log) || ($sms_log['add_time'] < TIMESTAMP - 1800)) {//半小时内进行验证为有效
                $state = '动态码错误或已过期，重新输入';
            }
        }
        exit($state);
    }

    /**
     * 登录
     */
    public function login() {
        if (checkSeccode($_POST['nchash'], $_POST['captcha'])) {
            if (config('sms_login') != 1) {
                showDialog('系统没有开启手机登录功能', '', 'error');
            }
            $phone = $_POST['phone'];
            $captcha = $_POST['sms_captcha'];
            $condition = array();
            $condition['log_phone'] = $phone;
            $condition['log_captcha'] = $captcha;
            $condition['log_type'] = 2;
            $model_sms_log = Model('sms_log');
            $sms_log = $model_sms_log->getSmsInfo($condition);
            if (empty($sms_log) || ($sms_log['add_time'] < TIMESTAMP - 1800)) {//半小时内进行验证为有效
                showDialog('动态码错误或已过期，重新输入', '', 'error');
            }
            $model_member = Model('member');
            $member = $model_member->getMemberInfo(array('member_mobile' => $phone)); //检查手机号是否已被注册
            if (!empty($member)) {
                $model_member->createSession($member); //自动登录
                $reload = $_POST['ref_url'];
                if (empty($reload)) {
                    $reload = url('member/home');
                }
                showDialog('登录成功', $reload, 'succ');
            }
        }
    }

    /**
     * 找回密码
     */
    public function find_password() {

        if (config('sms_password') != 1) {
            showDialog('系统没有开启手机找回密码功能', '', 'error');
        }
        $sms_mobile = trim(input('sms_mobile'));
        $sms_captcha = trim(input('sms_captcha'));
        $member_password = trim(input('member_password'));
        //判断验证码是否正确
        if ($sms_captcha != session('sms_captcha')) {
            showDialog('验证码错误', '', 'error');
        }
        if ($sms_mobile != session('sms_mobile')) {
            showDialog('手机号与接收号不一致', '', 'error');
        }
        
        $condition = array();
        $condition['log_phone'] = $sms_mobile;
        $condition['log_captcha'] = $sms_captcha;
        $condition['log_type'] = 3;
        $model_sms_log = Model('smslog');
        $sms_log = $model_sms_log->getSmsInfo($condition);
        if (empty($sms_log) || ($sms_log['add_time'] < TIMESTAMP - 1800)) {//半小时内进行验证为有效
            showDialog('动态码错误或已过期，重新输入', '', 'error');
        }

        $model_member = Model('member');
        $member = $model_member->getMemberInfo(array('member_mobile' => $sms_mobile)); //检查手机号是否已被注册
        if (!empty($member)) {
            $model_member->editMember(array('member_id' => $member['member_id']), array('member_password' => md5($member_password)));
            $model_member->createSession($member); //自动登录
            showDialog('密码修改成功', url('Home/Memberinformation/index'), 'succ');
        }
    }

}
