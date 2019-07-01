<?php

namespace app\home\controller;

use think\Lang;
use think\Validate;

class Login extends BaseMall {
    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/login.lang.php');
    }
    /**
     * 用户登录
     * @return type\
     */
    public function login() {

        $model_member = model('member');
        $inajax = input('param.inajax');
        //检查登录状态
        $model_member->checkloginMember();
        if (!request()->isPost()) {
            if ($inajax) {
                return $this->fetch($this->template_dir . 'login_inajax');
            } else {
                return $this->fetch($this->template_dir . 'login');
            }
        } else {
            $data = array(
                'member_name' => input('post.member_name'),
                'member_password' => input('post.member_password'),
            );
            //验证数据  BEGIN
            $rule = [
                ['member_name', 'require', '账户为必填'],
                ['member_password', 'require', '密码为必填']
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($data);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END
            $map = array(
                'member_name' => $data['member_name'],
                'member_password' => md5($data['member_password']),
            );
            $member_info = $model_member->getMemberInfo($map);
            if (empty($member_info) && preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/i', $data['member_name'])) {
                //根据会员名没找到时查手机号
                $map = array();
                $map['member_mobile'] = $data['member_name'];
                $map['member_password'] = md5($data['member_password']);
                $member_info = db('member')->where($map)->find();
            }
            if (empty($member_info) && (strpos($data['member_name'], '@') > 0)) {
                //按邮箱和密码查询会员
                $map = array();
                $map['member_email'] = $data['member_name'];
                $map['member_password'] = md5($data['member_password']);
                $member_info = db('member')->where($map)->find();
            }
            if ($member_info) {
                $member_gradeinfo = $model_member->getOneMemberGrade(intval($member_info['member_exppoints']));
                $member_info = array_merge($member_info, $member_gradeinfo);
                //执行登录,赋值操作
                $model_member->createSession($member_info);
                if(null!==input('param.ref_url')){
                    $this->redirect(input('param.ref_url'));
                }
                $this->success('登录成功', 'Member/index');
            } else {
                $this->error('登录失败');
            }
        }
    }

    public function logout() {
        session(null);
        $this->redirect('Index/index');
    }

    /**
     * 会员注册页面
     *
     * @param
     * @return
     */
    public function register() {
        $model_member = Model('member');
        $model_member->checkloginMember();
        $inviter_id=intval(input('get.inviter_id'));
        $member=db('member')->where('member_id',$inviter_id)->field('member_id,member_name')->find();
        $this->assign('member',$member);
        return $this->fetch($this->template_dir . 'register');
    }

    /**
     * 会员添加操作
     *
     * @param
     * @return
     */
    public function usersave() {
        $model_member = Model('member');
        $model_member->checkloginMember();
        if (input('post.member_password') != input('post.member_password_confirm')) {
            $this->error('两次密码不一致');
        }

        $data = array(
            'member_name' => input('post.member_name'),
            'member_password' => input('post.member_password'),
            'member_password_confirm' => input('post.member_password_confirm'),
        );
        //是否开启验证码
        if (config('sms_register')) {
            $sms_mobile = trim(input('sms_mobile'));
            $sms_captcha = trim(input('sms_captcha'));
            if (strlen($sms_mobile) != 11 || strlen($sms_captcha) != 6) {
                $this->error('手机号或手机验证码长度不正确');
            }
            //判断验证码是否正确
            if ($sms_captcha != session('sms_captcha')) {
                $this->error('验证码错误');
            }
            if ($sms_mobile != session('sms_mobile')) {
                $this->error('手机号与接收号不一致');
            }
            //检测手机号是否被注册
            $check_member_mobile = $model_member->getMemberInfo(array('member_mobile' => $sms_mobile));
            if (!empty($check_member_mobile)) {
                $this->error('手机号已被注册');
            }
            $sms_condition = array(
                'log_phone' => $sms_mobile,
                'log_captcha' => $sms_captcha,
                'log_type' => '1',
            );
            $model_sms_log = Model('smslog');
            $sms_log = $model_sms_log->getSmsInfo($sms_condition);
            if (empty($sms_log) || ($sms_log['add_time'] < TIMESTAMP - 1800)) {//半小时内进行验证为有效
                $this->error('动态码错误或已过期，重新输入');
            }


            $data['member_mobile'] = $sms_mobile;
            $data['member_mobile_bind'] = 1;
        }
        //验证数据  BEGIN
        $rule = [
            ['member_name', 'require|length:3,15', '账户为必填|帐号长度必须为1-15之间'],
            ['member_password', 'require|length:6,20', '密码为必填|密码长度必须为6-20之间']
        ];
        $validate = new Validate($rule);
        $validate_result = $validate->check($data);
        if (!$validate_result) {
            $this->error($validate->getError());
        }
        //验证数据  END
        $inviter_id = intval(input('param.inviter_id'));
        if($inviter_id){
            $data['inviter_id'] = $inviter_id;
        }else{
            $data['inviter_id'] = intval(base64_decode(cookie('uid'))) / 1;
        }
        $member_info = $model_member->register($data);
        if (!isset($member_info['error'])) {
            $model_member->createSession($member_info, true);
            $ref_url = url('Home/Member/index');
            if (strstr(input('post.ref_url'), 'logout') === false && !empty(input('post.ref_url'))) {
                $ref_url = input('post.ref_url');
            }
            $this->redirect($ref_url);
        } else {
            $this->error($member_info['error']);
        }
    }

    /**
     * 会员名称检测
     *
     * @param
     * @return
     */
    public function check_member() {
        $member_name = input('param.member_name');
        $model_member = Model('member');
        if(empty($member_name)){
            return 'false';
        }
        $check_member_name = $model_member->getMemberInfo(array('member_name' => $member_name));
        if (is_array($check_member_name) && count($check_member_name) > 0) {
            echo 'false';
        } else {
            echo 'true';
        }
    }

    /**
     * 电子邮箱检测
     *
     * @param
     * @return
     */
    public function check_email() {
        $model_member = Model('member');
        $check_member_email = $model_member->getMemberInfo(array('member_email' => input('param.email')));
        if (is_array($check_member_email) && count($check_member_email) > 0) {
            echo 'false';
        } else {
            echo 'true';
        }
    }

    /**
     * 忘记密码页面
     */
    public function forget_password() {
        /**
         * 读取语言包
         */
        $_pic = @unserialize(config('login_pic'));
        if ($_pic[0] != '') {
            $this->assign('lpic', UPLOAD_SITE_URL . '/' . ATTACH_LOGIN . '/' . $_pic[array_rand($_pic)]);
        } else {
            $this->assign('lpic', UPLOAD_SITE_URL . '/' . ATTACH_LOGIN . '/' . rand(1, 4) . '.jpg');
        }
        $this->assign('html_title', config('site_name') . ' - ' . lang('login_index_find_password'));
        return $this->fetch($this->template_dir . 'find_password');
    }

    /**
     * 找回密码的发邮件处理
     */
    public function find_password() {

        $result = chksubmit(true, true, 'num');
        if ($result !== false) {
            if ($result === -11) {
                showDialog('非法提交');
            } elseif ($result === -12) {
                showDialog('验证码错误');
            }
        }

        if (empty($_POST['username'])) {
            showDialog(lang('login_password_input_username'));
        }

        if (process::islock('forget')) {
            showDialog(lang('ds_common_op_repeat'), 'reload');
        }

        $member_model = Model('member');
        $member = $member_model->getMemberInfo(array('member_name' => $_POST['username']));
        if (empty($member) or ! is_array($member)) {
            process::addprocess('forget');
            showDialog(lang('login_password_username_not_exists'), 'reload');
        }

        if (empty($_POST['email'])) {
            showDialog(lang('login_password_input_email'), 'reload');
        }

        if (strtoupper($_POST['email']) != strtoupper($member['member_email'])) {
            process::addprocess('forget');
            showDialog(lang('login_password_email_not_exists'), 'reload');
        }
        process::clear('forget');
        //产生密码
        $new_password = random(15);
        if (!($member_model->editMember(array('member_id' => $member['member_id']), array('member_password' => md5($new_password))))) {
            showDialog(lang('login_password_email_fail'), 'reload');
        }

        $model_tpl = Model('mailtemplates');
        $tpl_info = $model_tpl->getTplInfo(array('code' => 'reset_pwd'));
        $param = array();
        $param['site_name'] = config('site_name');
        $param['user_name'] = $_POST['username'];
        $param['new_password'] = $new_password;
        $param['site_url'] = SHOP_SITE_URL;
        $subject = ncReplaceText($tpl_info['title'], $param);
        $message = ncReplaceText($tpl_info['content'], $param);

        $email = new Email();
        $result = $email->send_sys_email($_POST["email"], $subject, $message);
        showDialog('新密码已经发送至您的邮箱，请尽快登录并更改密码！', '', 'succ', '', 5);
    }

    /**
     * 邮箱绑定验证
     */
    public function bind_email() {

        $model_member = Model('member');
        $uid = @base64_decode(input('param.uid'));
        $uid = decrypt($uid, '');
        list($member_id, $member_email) = explode(' ', $uid);
        if (!is_numeric($member_id)) {
            $this->error('验证失败', SHOP_SITE_URL);
        }

        $member_info = $model_member->getMemberInfo(array('member_id' => $member_id), 'member_email');
        if ($member_info['member_email'] != $member_email) {
            $this->error('验证失败', SHOP_SITE_URL);
        }

        $member_common_info = $model_member->getMemberCommonInfo(array('member_id' => $member_id));
        if (empty($member_common_info) || !is_array($member_common_info)) {
            $this->error('验证失败', SHOP_SITE_URL);
        }
        $hash=array_keys($_GET);
        if (md5($member_common_info['auth_code']) != $_GET[$hash['1']] || TIMESTAMP - $member_common_info['send_acode_time'] > 24 * 3600) {
            $this->error('验证失败', SHOP_SITE_URL);
        }

        $update = $model_member->editMember(array('member_id' => $member_id), array('member_email_bind' => 1));
        if (!$update) {
            $this->error('系统发生错误，如有疑问请与管理员联系', SHOP_SITE_URL);
        }
        //验证完成清空验证数据
        $data = array();
        $data['auth_code'] = '';
        $data['send_acode_time'] = 0;
        $model_member->editMemberCommon($data, array('member_id' => session('member_id')));
        $this->success('邮箱设置成功', url('Membersecurity/index'));
    }

}

?>
