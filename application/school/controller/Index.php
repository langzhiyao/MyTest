<?php

namespace app\school\controller;

use think\View;
use think\Lang;

class Index extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'school/lang/zh-cn/index.lang.php');
        if(!config('site_state')) {
            echo config('closed_reason');
            exit;
        }
    }

    public function index() {
        $this->assign('admin_info', $this->getAdminInfo());
        return $this->fetch();
    }

    /**
     * 修改密码
     */
    public function modifypw() {
        if (request()->isPost()) {
            $new_pw = trim(input('post.new_pw'));
            $new_pw2 = trim(input('post.new_pw2'));
            $old_pw = trim(input('post.old_pw'));
            if ($new_pw !== $new_pw2) {
                $this->error(lang('index_modifypw_repeat_error'));
            }
            $admininfo = $this->getAdminInfo();
            //查询管理员信息
            $admin_model = Model('admin');
            $admininfo = $admin_model->getOneAdmin($admininfo['admin_id']);
            if (!is_array($admininfo) || count($admininfo) <= 0) {
                $this->error(lang('index_modifypw_admin_error'));
            }
            //旧密码是否正确
            if ($admininfo['admin_password'] != md5($old_pw)) {
               $this->error(lang('index_modifypw_oldpw_error'));
            }
            $new_pw = md5($new_pw);
            $result = $admin_model->updateAdmin(array('admin_password' => $new_pw),$admininfo['admin_id']);
            if ($result) {
                $this->success(lang('index_modifypw_success'));
            } else {
                $this->error(lang('index_modifypw_fail'));
            }
        } else {
            return $this->fetch();
        }
    }

}

