<?php

namespace app\admin\controller;

use think\Controller;
use think\Lang;
use think\Validate;

class Login extends Controller {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/login.lang.php');
    }

    public function index() {
        if (session('admin_id')) {
            $this->success('已经登录', 'Admin/Index/index');
        }
        if (request()->isPost()) {

            $admin_name = input('post.admin_name');
            $admin_password = input('post.admin_password');
            $captcha = input('post.captcha');

            $data = array(
                'admin_name' => $admin_name,
                'admin_password' => $admin_password,
                'captcha' => $captcha,
            );

            //验证数据  BEGIN
            $rule = [
                ['admin_name', 'require|min:5', '帐号为必填|帐号长度至少为5位'],
                ['admin_password', 'require|min:6', '密码为必填|帐号长度至少为6位'],
                ['captcha', 'require|min:3', '验证码为必填|帐号长度至少为3位'],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($data);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END
            if (!captcha_check(input('post.captcha'))) {
                //验证失败
                $this->error('验证码错误');
            }

            $condition['admin_name'] = $admin_name;
            $condition['admin_password'] = md5($admin_password);
            $condition['admin_del_status'] = 1;
            $admin_info = db('admin')->where($condition)->find();
            if (is_array($admin_info) and !empty($admin_info)) {
                if($admin_info['admin_status'] == 1){
                    if(!empty($admin_info['admin_gid']) || $admin_info['admin_is_super'] == 1){
                        if($admin_info['admin_company_id']!=1 && $admin_info['admin_school_id'] == 0){
                            $this->success('该账号不属于总后台账号，请确认账号角色！！！');
                        }else if($admin_info['admin_company_id']!=1 && $admin_info['admin_school_id'] != 0){
                            $this->success('该账号不属于总后台账号，请确认账号角色！！！');
                        }else if($admin_info['admin_company_id']==1 && $admin_info['admin_school_id'] != 0){
                            $this->success('该账号不属于总后台账号，请确认账号角色！！！');
                        }else{
                            //更新 admin 最新信息
                            $update_info = array(
                                'admin_login_num' => ($admin_info['admin_login_num'] + 1),
                                'admin_login_time' => TIMESTAMP
                            );
                            db('admin')->where('admin_id', $admin_info['admin_id'])->update($update_info);

                            //设置 session
                            session('admin_id', $admin_info['admin_id']);
                            session('admin_name', $admin_info['admin_name']);
                            session('admin_gid', $admin_info['admin_gid']);
                            session('admin_is_super', $admin_info['admin_is_super']);
                            session('admin_company_id', $admin_info['admin_company_id']);
                            session('admin_school_id', $admin_info['admin_school_id']);
                            session('login_identity', 'admin');

                            $this->success('登录成功', 'Admin/Index/index');
                        }
                    }else{
                        $this->success('该会员没有角色，请联系超级管理员');
                    }
                }else{
                    $this->success('帐号已被禁用，请联系超级管理员');
                }
            } else {
                $this->success('帐号密码错误');
            }
        } else {
            return $this->fetch();
        }
    }

    public function logout() {
        //设置 session
        session(null);
        $this->success('退出成功', 'Admin/Login/index');
        exit;
    }

}

?>
