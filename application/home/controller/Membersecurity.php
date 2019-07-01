<?php

namespace app\home\controller;

use think\Lang;
use think\Validate;

class Membersecurity extends BaseMember
{

    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/memberpoints.lang.php');
    }

    public function index()
    {
        $member_info = $this->member_info;
        $member_info['security_level'] = Model('member')->getMemberSecurityLevel($member_info);
        $this->assign('member_info', $member_info);
        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('member_security');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('index');
        return $this->fetch($this->template_dir . 'index');
    }

    /**
     * 绑定邮箱 - 发送邮件
     */
    public function send_bind_email()
    {
        $email = input('param.email');
        //验证数据  BEGIN
        $rule = [
            ['email', 'email', '请正确填写邮箱'],
        ];
        $validate = new Validate();
        $validate_result = $validate->check(array('email' => $email), $rule);
        if (!$validate_result) {
            showValidateError($validate->getError());
        }
        //验证数据  END

        $model_member = Model('member');
        $condition = array();
        $condition['member_email'] = $email;
        $condition['member_id'] = array('neq', session('member_id'));
        $member_info = $model_member->getMemberInfo($condition, 'member_id');
        if ($member_info) {
            showDialog('该邮箱已被使用');
        }
        $seed = random(6);
        $data = array();
        $data['auth_code'] = $seed;
        $data['send_acode_time'] = TIMESTAMP;
        $update = $model_member->editMemberCommon($data, array('member_id' => session('member_id')));
        if (!$update) {
            showDialog('系统发生错误，如有疑问请与管理员联系');
        }
        $uid = base64_encode(encrypt(session('member_id') . ' ' . $email));
        $verify_url = config('url_domain_root') . 'index.php/Home/Login/bind_email.html?uid=' . $uid . '&hash=' . md5($seed);

        $model_tpl = Model('mailtemplates');
        $tpl_info = $model_tpl->getTplInfo(array('code' => 'bind_email'));
        $param = array();
        $param['site_name'] = config('site_name');
        $param['user_name'] = session('member_name');
        $param['verify_url'] = $verify_url;
        $subject = ncReplaceText($tpl_info['title'], $param);
        $message = ncReplaceText($tpl_info['content'], $param);

        $ob_email = new \sendmsg\Email();
        $result = $ob_email->send_sys_email($email, $subject, $message);
        if ($result) {
            $data = array();
            $data['member_email'] = $email;
            $data['member_email_bind'] = 0;
            $update = $model_member->editMember(array('member_id' => session('member_id')), $data);
            if (!$update) {
                showDialog('系统发生错误，如有疑问请与管理员联系');
            }
            showDialog('验证邮件已经发送至您的邮箱，请于24小时内登录邮箱并完成验证！', url('Home/Membersecurity/index'), 'succ', '', 5);
        }
        else {
            showDialog('系统发生错误，如有疑问请与管理员联系');
        }
    }

    public function auth()
    {
        $model_member = model('member');
        $type = input('param.type');
        if (!request()->isPost()) {
            if (!in_array($type, array('modify_pwd', 'modify_mobile', 'modify_email', 'modify_paypwd', 'pd_cash'))) {
                $this->redirect('Membersecurity/index');
            }
            //继承父类的member_info
            $member_info = $this->member_info;
            if (!$member_info) {
                $member_info = $model_member->getMemberInfo(array('member_id' => session('member_id')), 'member_email,member_email_bind,member_mobile,member_mobile_bind');
            }
            //第一次绑定邮箱，不用发验证码，直接进下一步
            //第一次绑定手机，不用发验证码，直接进下一步
            if (($type == 'modify_email' && $member_info['member_email_bind'] == '0') || ($type == 'modify_mobile' && $member_info['member_mobile_bind'] == '0')) {
                session('auth_' . $type, TIMESTAMP);
                /* 设置买家当前菜单 */
                $this->setMemberCurMenu('member_security');
                /* 设置买家当前栏目 */
                $this->setMemberCurItem($type);
                echo $this->fetch($this->template_dir . $type);
                exit;
            }

            //修改密码、设置支付密码时，必须绑定邮箱或手机
            if (in_array($type, array(
                    'modify_pwd', 'modify_paypwd'
                )) && $member_info['member_email_bind'] == '0' && $member_info['member_mobile_bind'] == '0') {
                $this->error('请先绑定邮箱或手机', 'membersecurity/index');
            }
            $this->assign('member_info', $member_info);
            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('member_security');
            /* 设置买家当前栏目 */
            $this->setMemberCurItem($type);
            return $this->fetch($this->template_dir . 'auth');
        }
        else {
            if (!in_array($type, array('modify_pwd', 'modify_mobile', 'modify_email', 'modify_paypwd', 'pd_cash'))) {
                $this->redirect(url('Home/Membersecurity/index'));
            }
            $member_common_info = $model_member->getMemberCommonInfo(array('member_id' => session('member_id')));
            if (empty($member_common_info) || !is_array($member_common_info)) {
                $this->error('验证失败');
            }
            if ($member_common_info['auth_code'] != $_POST['auth_code'] || TIMESTAMP - $member_common_info['send_acode_time'] > 1800) {
                $this->error('验证码已被使用或超时，请重新获取验证码', url('Home/Membersecurity/index'));
            }
            $data = array();
            $data['auth_code'] = '';
            $data['send_acode_time'] = 0;
            $update = $model_member->editMemberCommon($data, array('member_id' => session('member_id')));
            if (!$update) {
                $this->error('系统发生错误，如有疑问请与管理员联系', SHOP_SITE_URL);
            }
            session('auth_' . $type, TIMESTAMP);

            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('member_security');
            /* 设置买家当前栏目 */
            $this->setMemberCurItem($type);
           /* if ($type == 'pd_cash') {
                return $this->fetch($this->template_dir.'pd_cash_add');
            }
            else {}*/
                return $this->fetch($this->template_dir . $type);

        }
    }

    /**
     * 统一发送身份验证码
     */
    public function send_auth_code()
    {
        $type = input('param.type');
        if (!in_array($type, array('email', 'mobile')))
            exit();

        $model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID(session('member_id'), 'member_email,member_mobile');

        $verify_code = rand(100, 999) . rand(100, 999);
        $data = array();
        $data['auth_code'] = $verify_code;
        $data['send_acode_time'] = time();
        $update = $model_member->editMemberCommon($data, array('member_id' => session('member_id')));


        if (!$update) {
            exit(json_encode(array('state' => 'false', 'msg' => '系统发生错误，如有疑问请与管理员联系')));
        }

        $model_tpl = Model('mailtemplates');
        $tpl_info = $model_tpl->getTplInfo(array('code' => 'authenticate'));

        $param = array();
        $param['send_time'] = date('Y-m-d H:i', TIMESTAMP);
        $param['verify_code'] = $verify_code;
        $param['site_name'] = config('site_name');
        $subject = ncReplaceText($tpl_info['title'], $param);
        $message = ncReplaceText($tpl_info['content'], $param);
        if ($type == 'email') {
            $email = new \sendmsg\Email();
            $result = $email->send_sys_email($member_info["member_email"], $subject, $message);
        }
        elseif ($type == 'mobile') {
            $sms = new \sendmsg\Sms();
            p($message);exit;
            $result = $sms->send($member_info["member_mobile"], $message);
        }
        if ($result) {
            exit(json_encode(array('state' => 'true', 'msg' => '验证码已发出，请注意查收')));
        }
        else {
            exit(json_encode(array('state' => 'false', 'msg' => '验证码发送失败')));
        }
    }

    /**
     * 修改密码
     */
    public function modify_pwd()
    {
        $model_member = Model('member');

        //身份验证后，需要在30分钟内完成修改密码操作
        if (TIMESTAMP - session('auth_modify_pwd') > 1800) {
            showDialog('操作超时，请重新获得验证码', url('Home/Membersecurity/auth', ['type' => 'modify_pwd']), 'html', 'error');
        }

        if (!request()->isPost())
            exit();
        $data = array(
            'password' => input('post.password'), 'confirm_password' => input('post.confirm_password'),
        );
        //验证数据  BEGIN
        $rule = [
            ['password', 'require', '请正确输入密码'], ['confirm_password', 'require', '请正确输入密码'],
        ];
        $validate = new Validate();
        $validate_result = $validate->check($data, $rule);
        if (!$validate_result) {
            showDialog($validate->getError());
        }
        //验证数据  END

        if ($data['password'] != $data['confirm_password']) {
            showDialog('两次密码不一致');
        }

        $update = $model_member->editMember(array('member_id' => session('member_id')), array('member_password' => md5($data['password'])));
        $message = $update ? '密码修改成功' : '密码修改失败';
        session('auth_modify_pwd', NULL);
        showDialog($message, url('Home/Membersecurity/index'), $update ? 'succ' : 'error');
    }

    /**
     * 设置支付密码
     */
    public function modify_paypwd()
    {
        $model_member = Model('member');

        //身份验证后，需要在30分钟内完成修改密码操作
        if (TIMESTAMP - session('auth_modify_paypwd') > 1800) {
            $this->error('操作超时，请重新获得验证码', url('Home/Membersecurity/auth', ['type' => 'modify_paypwd']));
        }
        if (!request()->isPost())
            exit();
        $data = array(
            'password' => input('post.password'), 'confirm_password' => input('post.confirm_password'),
        );

        //验证数据  BEGIN
        $rule = [
            ['password', 'require', '请正确输入密码'], ['confirm_password', 'require', '请正确输入密码'],
        ];
        $validate = new Validate();
        $validate_result = $validate->check($data, $rule);
        if (!$validate_result) {
            showDialog($validate->getError());
        }
        //验证数据  END

        if ($data['password'] != $data['confirm_password']) {
            showDialog('两次密码不一致');
        }

        $update = $model_member->editMember(array('member_id' => session('member_id')), array('member_paypwd' => md5($data['password'])));
        $message = $update ? '密码设置成功' : '密码设置失败';
        session('auth_modify_paypwd', NULL);
        showDialog($message, url('Home/Membersecurity/index'), $update ? 'succ' : 'error');
    }

    /**
     * 绑定手机
     */
    public function modify_mobile()
    {
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID(session('member_id'), 'member_mobile_bind');
        if (request()->isPost()) {
            $data = array(
                'mobile' => input('post.mobile'), 'vcode' => input('post.vcode'),
            );

            //验证数据  BEGIN
            $rule = [
                ['mobile', 'require', '请正确填写手机号'], ['vcode', 'require', '请正确填写手机验证码'],
            ];
            $validate = new Validate();
            $validate_result = $validate->check($data, $rule);
            if (!$validate_result) {
                showDialog($validate->getError());
            }
            //验证数据  END

            $condition = array();
            $condition['member_id'] = session('member_id');
            $condition['auth_code'] = intval($data['vcode']);
            $member_common_info = $model_member->getMemberCommonInfo($condition, 'send_acode_time');
            if (!$member_common_info) {
                showDialog('手机验证码错误，请重新输入');
            }
            if (TIMESTAMP - $member_common_info['send_acode_time'] > 1800) {
                showDialog('手机验证码已过期，请重新获取验证码');
            }
            $data = array();
            $data['auth_code'] = '';
            $data['send_acode_time'] = 0;
            $update = $model_member->editMemberCommon($data, array('member_id' => session('member_id')));
            if (!$update) {
                showDialog('系统发生错误，如有疑问请与管理员联系');
            }
            $update = $model_member->editMember(array('member_id' => session('member_id')), array('member_mobile_bind' => 1));
            showDialog('手机号绑定成功', url('Home/Membersecurity/index'), 'succ');
        }
    }

    /**
     * 修改手机号 - 发送验证码
     */
    public function send_modify_mobile()
    {
        $mobile = input('param.mobile');
        //验证数据  BEGIN
        $rule = [
            ['mobile', 'require', '请正确填写手机号码'],
        ];
        $validate = new Validate();
        $validate_result = $validate->check(array('mobile' => $mobile), $rule);
        if (!$validate_result) {
            exit(json_encode(array('state' => 'false', 'msg' => $validate->getError())));
        }
        //验证数据  END
        $model_member = Model('member');
        $condition = array();
        $condition['member_mobile'] = $mobile;
        $condition['member_id'] = array('neq', session('member_id'));
        $member_info = $model_member->getMemberInfo($condition, 'member_id');
        if ($member_info) {
            exit(json_encode(array('state' => 'false', 'msg' => '该手机号已被使用，请更换其它手机号')));
        }
        $update = $model_member->editMember(array('member_id' => session('member_id')), array('member_mobile' => $mobile));
        if (!$update) {
            exit(json_encode(array('state' => 'false', 'msg' => '修改的手机与原手机相同，如有疑问请与管理员联系')));
        }

        $verify_code = rand(100, 999) . rand(100, 999);

        $data = array();
        $data['auth_code'] = $verify_code;
        $data['send_acode_time'] = time();

        $update = $model_member->editMemberCommon($data, array('member_id' => session('member_id')));

        if (!$update) {
            exit(json_encode(array('state' => 'false', 'msg' => '系统更新信息发生错误，如有疑问请与管理员联系')));
        }

        $model_tpl = Model('mailtemplates');
        $tpl_info = $model_tpl->getTplInfo(array('code' => 'modify_mobile'));
        $param = array();
        $param['site_name'] = config('site_name');
        $param['send_time'] = date('Y-m-d H:i', TIMESTAMP);
        $param['verify_code'] = $verify_code;
        $message = ncReplaceText($tpl_info['content'], $param);
        $sms = new \sendmsg\Sms();
        $result = $sms->send($mobile,$message);

        if ($result) {
            exit(json_encode(array('state' => 'true', 'msg' => '发送成功')));
        }
        else {
            exit(json_encode(array('state' => 'false', 'msg' => '发送失败')));
        }
    }
    

    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_type 导航类型
     * @param string $menu_key 当前导航的menu_key
     * @return
     */
    protected function getMemberItemList()
    {
        $menu_name = request()->action();
        switch ($menu_name) {
            case 'index':
                $menu_array = array(
                    array(
                        'name' => 'index', 'text' => '账户安全',
                        'url' => url('Membersecurity/index')
                    )
                );
                return $menu_array;
                break;
            case 'modify_pwd':
                $menu_array = array(
                    array(
                        'name' => 'index', 'text' => '账户安全',
                        'url' => url('Membersecurity/index')
                    ), array(
                        'name' => 'modify_pwd', 'text' => '修改登录密码',
                        'url' => url('membersecurity/auth',['type'=>'modify_pwd'])
                    ),
                );
                return $menu_array;
                break;
            case 'modify_email':
                $menu_array = array(
                    array(
                        'name' => 'index', 'text' => '账户安全',
                        'url' => url('membersecurity/index')
                    ), array(
                        'name' => 'modify_email', 'text' => '邮箱验证',
                        'url' => url('membersecurity/auth',['type'=>'modify_email'])
                    ),
                );
                return $menu_array;
                break;
            case 'modify_mobile':
                $menu_array = array(
                    array(
                        'name' => 'index', 'text' => '账户安全',
                        'url' => url('membersecurity/index')
                    ), array(
                        'name' => 'modify_mobile', 'text' => '手机验证',
                        'url' => url('membersecurity/auth',['type'=>'modify_mobile'])
                    ),
                );
                return $menu_array;
                break;
            case 'modify_paypwd':
                $menu_array = array(
                    array(
                        'name' => 'index', 'text' => '账户安全',
                        'url' => url('membersecurity/index')
                    ), array(
                        'name' => 'modify_paypwd', 'text' => '设置支付密码',
                        'url' => url('membersecurity/auth',['type'=>'modify_paypwd'])
                    ),
                );
                return $menu_array;
                break;
            case 'auth':
                $menu_array = array(
                    array(
                        'name' => 'loglist', 'text' => '账户余额',
                        'url' => url('predeposit/pd_log_list')
                    ), array(
                        'name' => 'recharge_list', 'text' => '充值明细',
                        'url' =>  url('predeposit/index')
                    ), array(
                        'name' => 'cashlist', 'text' => '余额提现',
                        'url' => url('predeposit/pd_cash_list')
                    ), array(
                        'name' => 'pd_cash', 'text' => '提现申请',
                        'url' => url('membersecurity/auth',['type'=>'pd_cash'])
                    ),
                );
                return $menu_array;
                break;
        }
    }

}

?>
