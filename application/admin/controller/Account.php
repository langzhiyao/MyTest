<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Account extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/config.lang.php');
    }

    /**
     * QQ互联
     */
    function qq() {
        $model_config = model('config');
        if (!request()->isPost()) {
            $list_config = $model_config->getListConfig();
            $this->assign('list_config', $list_config);

            //输出子菜单
            $this->setAdminCurItem('qq');
            return $this->fetch('qq');
        } else {
            $update_array = array();
            $update_array['qq_isuse'] = $_POST['qq_isuse'];
            $update_array['qq_appcode'] = $_POST['qq_appcode'];
            $update_array['qq_appid'] = $_POST['qq_appid'];
            $update_array['qq_appkey'] = $_POST['qq_appkey'];

            //定义验证规则
            $rule = [
                ['qq_appid', 'require', lang('qq_appid_error')],
                ['qq_appkey', 'require', lang('qq_appkey_error')],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($update_array);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            $result = $model_config->updateConfig($update_array);
            if ($result === true) {
                $this->log(lang('ds_edit').lang('qqSettings'), 1);
                $this->success(lang('ds_common_save_succ'));
            } else {
                $this->log(lang('ds_edit').lang('qqSettings'), 0);
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * sina微博设置
     */
    public function sina() {
        $model_config = model('config');
        if (!request()->isPost()) {
            $list_config = $model_config->getListConfig();
            $this->assign('list_config', $list_config);

            //输出子菜单
            $this->setAdminCurItem('sina');
            return $this->fetch('sina');
        } else {
            $update_array = array();
            $update_array['sina_isuse'] = $_POST['sina_isuse'];
            $update_array['sina_wb_akey'] = $_POST['sina_wb_akey'];
            $update_array['sina_wb_skey'] = $_POST['sina_wb_skey'];
            $update_array['sina_appcode'] = $_POST['sina_appcode'];
            //定义验证规则
            $rule = [
                ['sina_wb_akey', 'require', lang('sina_wb_akey_error')],
                ['sina_wb_skey', 'require', lang('sina_wb_skey_error')],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($update_array);
            if (!$validate_result) {
                $this->error($validate->getError());
            }

            $result = $model_config->updateConfig($update_array);
            if ($result === true) {
                $this->log(lang('ds_edit').lang('sina_settings'), 1);
                $this->success(lang('ds_common_save_succ'));
            } else {
                $this->log(lang('ds_edit').lang('sina_settings'), 0);
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 手机短信设置
     */
    public function sms() {
        $model_config = model('config');
        if (!request()->isPost()) {
            $list_config = $model_config->getListConfig();
            $this->assign('list_config', $list_config);
            //输出子菜单
            $this->setAdminCurItem('sms');
            return $this->fetch('sms');
        } else {
            $update_array = array();
            $update_array['sms_register'] = $_POST['sms_register'];
            $update_array['sms_login'] = $_POST['sms_login'];
            $update_array['sms_password'] = $_POST['sms_password'];
            $result = $model_config->updateConfig($update_array);
            if ($result) {
                $this->log('编辑账号同步，手机短信设置');
                $this->success(lang('ds_common_save_succ'));
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 微信登录设置
     */
    public function wx() {
        $model_config = model('config');
        if (!request()->isPost()) {
            $list_config = $model_config->getListConfig();
            $this->assign('list_config', $list_config);
            //输出子菜单
            $this->setAdminCurItem('wx');
            return $this->fetch('wx');
        } else {
            $update_array = array();
            $update_array['weixin_isuse'] = $_POST['weixin_isuse'];
            $update_array['weixin_appid'] = $_POST['weixin_appid'];
            $update_array['weixin_secret'] = $_POST['weixin_secret'];
            $result = $model_config->updateConfig($update_array);
            if ($result) {
                $this->log('编辑账号同步，微信登录设置');
                $this->success(lang('ds_common_save_succ'));
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'qq',
                'text' => 'QQ互联',
                'url' => url('Admin/Account/qq')
            ),
            array(
                'name' => 'sina',
                'text' => '新浪互联',
                'url' => url('Admin/Account/sina')
            ),
            array(
                'name' => 'sms',
                'text' => '手机短信',
                'url' => url('Admin/Account/sms')
            ),
            array(
                'name' => 'wx',
                'text' => '微信登录',
                'url' => url('Admin/Account/wx')
            ),
        );
        return $menu_array;
    }

}

?>
