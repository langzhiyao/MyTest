<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Message extends AdminControl {

    public function _initialize() {
        parent::_initialize();

        Lang::load(APP_PATH . 'admin/lang/zh-cn/message.lang.php');
    }

    /**
     * 邮件设置
     */
    public function email() {
        $model_config = Model('config');
        if (!(request()->isPost())) {
            $list_config = $model_config->getListConfig();
            $this->assign('list_config', $list_config);

            $this->setAdminCurItem('email');
            return $this->fetch('email');
        } else {
            $update_array = array();
            $update_array['email_host'] = input('post.email_host');
            $update_array['email_port'] = input('post.email_port');
            $update_array['email_addr'] = input('post.email_addr');
            $update_array['email_id'] = input('post.email_id');
            $update_array['email_pass'] = input('post.email_pass');

            $result = $model_config->updateConfig($update_array);
            if ($result === true) {
                $this->log(lang('ds_edit').lang('email_set'), 1);
                $this->success(lang('ds_common_save_succ'));
            } else {
                $this->log(lang('ds_edit').lang('email_set'), 0);
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 短信平台设置
     */
    public function mobile() {
        $model_config = Model('config');
        if (!(request()->isPost())) {
            $list_config = $model_config->getListConfig();
            $this->assign('list_config', $list_config);

            $this->setAdminCurItem('mobile');
            return $this->fetch('mobile');
        } else {
            $update_array = array();
            // $update_array['mobile_username'] = input('post.mobile_username');
            // $update_array['mobile_pwd'] = input('post.mobile_pwd');
            // $update_array['mobile_key'] = input('post.mobile_key');
            // $update_array['mobile_memo'] = input('post.mobile_memo');

            $update_array['mobile_AccountSid']   = input('post.mobile_AccountSid');
            $update_array['mobile_AccountToken'] = input('post.mobile_AccountToken');
            $update_array['mobile_AppId']        = input('post.mobile_AppId');
            $update_array['mobile_ServerIP']     = input('post.mobile_ServerIP');
            $update_array['mobile_ServerPort']   = input('post.mobile_ServerPort');
            $update_array['mobile_SoftVersion']  = input('post.mobile_SoftVersion');
            $update_array['mobile_signature']    = input('post.mobile_signature');
            $update_array['mobile_memo']         = input('post.mobile_memo');
            $result = $model_config->updateConfig($update_array);
            if ($result === true) {
                $this->log(lang('ds_edit').lang('mobile_set'), 1);
                $this->success(lang('ds_common_save_succ'));
            } else {
                $this->log(lang('ds_edit').lang('mobile_set'), 0);
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 邮件模板列表
     */
    public function email_tpl() {
        $model_templates = Model('mailtemplates');
        $templates_list = $model_templates->getTplList();
        $this->assign('templates_list', $templates_list);
        $this->setAdminCurItem('email_tpl');
        return $this->fetch('email_tpl');
    }

    /**
     * 编辑邮件模板
     */
    public function email_tpl_edit() {
        $model_templates = Model('mailtemplates');
        if (!request()->isPost()) {
            if (!(input('param.code'))) {
                $this->error(lang('mailtemplates_edit_code_null'));
            }
            $templates_array = $model_templates->getTplInfo(array('code' => input('param.code')));
            $this->assign('templates_array', $templates_array);
            $this->setAdminCurItem('email_tpl_edit');
            return $this->fetch('email_tpl_edit');
        } else {
            $obj_validate = new Validate();
            $data = array(
                'code'=>input('post.code'),
                'title'=>input('post.title'),
                'content'=>input('post.content'),
            );
            $rule=array(
                array('code','require',lang('mailtemplates_edit_no_null')),
                array('title','require',lang('mailtemplates_edit_title_null')),
                array('content','require',lang('mailtemplates_edit_content_null'))
            );
            $error = $obj_validate->check($data,$rule);
            if (!$error) {
                $this->error($obj_validate->getError());
            } else {
                $update_array = array();
                $update_array['code'] = input('post.code');
                $update_array['title'] = input('post.title');
                $update_array['content'] = input('post.content');
                $result = $model_templates->editTpl($update_array, array('code' => input('post.code')));
                if ($result) {
                    $this->log(lang('ds_edit').lang('email_tpl'), 1);
                    $this->success(lang('mailtemplates_edit_succ'), 'Admin/Message/email_tpl');
                } else {
                    $this->log(lang('ds_edit').lang('email_tpl'), 0);
                    $this->error(lang('mailtemplates_edit_fail'));
                }
            }
        }
    }

    /**
     * 测试邮件发送
     *
     * @param
     * @return
     */
    public function email_testing() {
        /**
         * 读取语言包
         */

        $email_host = trim(input('post.email_host'));
        $email_port = trim(input('post.email_port'));
        $email_addr = trim(input('post.email_addr'));
        $email_id = trim(input('post.email_id'));
        $email_pass = trim(input('post.email_pass'));

        $email_test = trim(input('post.email_test'));
        $subject = lang('test_email');
        $site_url = SHOP_SITE_URL;

        $site_title = config('site_name');
        $message = '<p>' . lang('this_is_to') . "<a href='" . $site_url . "' target='_blank'>" . $site_title . '</a>' . lang('test_email_set_ok') . '</p>';

        $obj_email = new \sendmsg\Email();
        $obj_email->set('email_server', $email_host);
        $obj_email->set('email_port', $email_port);
        $obj_email->set('email_user', $email_id);
        $obj_email->set('email_password', $email_pass);
        $obj_email->set('email_from', $email_addr);
        $obj_email->set('site_name', $site_title);
        $result = $obj_email->send($email_test, $subject, $message);
        if ($result === false) {
            $data['msg'] = lang('test_email_send_fail');
      return $data;
        } else {
            $data['msg'] = lang('test_email_send_ok');
           return $data;
        }
    }

    /**
     * 商家消息模板
     */
    public function seller_tpl() {
        $mstpl_list = Model('storemsgtpl')->getStoreMsgTplList(array());
        $this->assign('mstpl_list', $mstpl_list);
        $this->setAdminCurItem('seller_tpl');
        return $this->fetch('seller_tpl');
    }

    /**
     * 商家消息模板编辑
     */
    public function seller_tpl_edit() {
        if (!request()->isPost()) {
            $code = trim(input('param.code'));
            if (empty($code)) {
                $this->error(lang('param_error'));
            }
            $where = array();
            $where['smt_code'] = $code;
            $smtpl_info = Model('storemsgtpl')->getStoreMsgTplInfo($where);
            $this->assign('smtpl_info', $smtpl_info);
            $this->setAdminCurItem('seller_tpl_edit');
            return $this->fetch('seller_tpl_edit');
        } else {
            $code = trim(input('post.code'));
            $type = trim(input('post.type'));
            if (empty($code) || empty($type)) {
                $this->error(lang('param_error'));
            }
            switch ($type) {
                case 'message':
                    $this->seller_tpl_update_message();
                    break;
                case 'short':
                    $this->seller_tpl_update_short();
                    break;
                case 'mail':
                    $this->seller_tpl_update_mail();
                    break;
            }
        }
    }

    /**
     * 商家消息模板更新站内信
     */
    private function seller_tpl_update_message() {
        $message_content = trim(input('post.message_content'));
        if (empty($message_content)) {
            $this->error('请填写站内信模板内容。');
        }
        // 条件
        $where = array();
        $where['smt_code'] = trim(input('post.code'));
        // 数据
        $update = array();
        $update['smt_message_switch'] = intval(input('post.message_switch'));
        $update['smt_message_content'] = $message_content;
        $update['smt_message_forced'] = intval(input('post.message_forced'));
        $result = Model('storemsgtpl')->editStoreMsgTpl($where, $update);
        $this->seller_tpl_update_showmessage($result);
    }

    /**
     * 商家消息模板更新短消息
     */
    private function seller_tpl_update_short() {
        $short_content = trim(input('post.short_content'));
        if (empty($short_content)) {
            $this->error('请填写短消息模板内容。');
        }
        // 条件
        $where = array();
        $where['smt_code'] = trim(input('post.code'));
        // 数据
        $update = array();
        $update['smt_short_switch'] = intval(input('post.short_switch'));
        $update['smt_short_content'] = $short_content;
        $update['smt_short_forced'] = intval(input('post.short_forced'));
        $result = Model('storemsgtpl')->editStoreMsgTpl($where, $update);
        $this->seller_tpl_update_showmessage($result);
    }

    /**
     * 商家消息模板更新邮件
     */
    private function seller_tpl_update_mail() {
        $mail_subject = trim(input('post.mail_subject'));
        $mail_content = trim(input('post.mail_content'));
        if ((empty($mail_subject) || empty($mail_content))) {
            $this->error('请填写邮件模板内容。');
        }
        // 条件
        $where = array();
        $where['smt_code'] = trim(input('post.code'));
        // 数据
        $update = array();
        $update['smt_mail_switch'] = intval(input('post.mail_switch'));
        $update['smt_mail_subject'] = $mail_subject;
        $update['smt_mail_content'] = $mail_content;
        $update['smt_mail_forced'] = intval(input('post.mail_forced'));
        $result = Model('storemsgtpl')->editStoreMsgTpl($where, $update);
        $this->seller_tpl_update_showmessage($result);
    }

    private function seller_tpl_update_showmessage($result) {
        if ($result) {
            $this->success(lang('ds_common_op_succ'), url('Admin/Message/seller_tpl'));
        } else {
            $this->error(lang('ds_common_op_fail'));
        }
    }

    /**
     * 用户消息模板
     */
    public function member_tpl() {
        $mmtpl_list = Model('membermsgtpl')->getMemberMsgTplList(array());
        $this->assign('mmtpl_list', $mmtpl_list);
        $this->setAdminCurItem('member_tpl');
        return $this->fetch('member_tpl');
    }

    /**
     * 用户消息模板编辑
     */
    public function member_tpl_edit() {
        if (!request()->isPost()) {
            $code = trim(input('param.code'));
            if (empty($code)) {
                $this->error(lang('param_error'));
            }
            $where = array();
            $where['mmt_code'] = $code;
            $mmtpl_info = Model('membermsgtpl')->getMemberMsgTplInfo($where);
            $this->assign('mmtpl_info', $mmtpl_info);
            $this->setAdminCurItem('member_tpl_edit');
            return $this->fetch('member_tpl_edit');
        } else {
            $code = trim(input('post.code'));
            $type = trim(input('post.type'));
            if (empty($code) || empty($type)) {
                $this->error(lang('param_error'));
            }
            switch ($type) {
                case 'message':
                    $this->member_tpl_update_message();
                    break;
                case 'short':
                    $this->member_tpl_update_short();
                    break;
                case 'mail':
                    $this->member_tpl_update_mail();
                    break;
            }
        }
    }

    /**
     * 商家消息模板更新站内信
     */
    private function member_tpl_update_message() {
        $message_content = trim(input('post.message_content'));
        if (empty($message_content)) {
            $this->error('请填写站内信模板内容。');
        }
        // 条件
        $where = array();
        $where['mmt_code'] = trim(input('post.code'));
        // 数据
        $update = array();
        $update['mmt_message_switch'] = intval(input('post.message_switch'));
        $update['mmt_message_content'] = $message_content;
        $result = Model('membermsgtpl')->editMemberMsgTpl($where, $update);
        $this->member_tpl_update_showmessage($result);
    }

    /**
     * 商家消息模板更新短消息
     */
    private function member_tpl_update_short() {
        $short_content = trim(input('post.short_content'));
        if (empty($short_content)) {
            $this->error('请填写短消息模板内容。');
        }
        // 条件
        $where = array();
        $where['mmt_code'] = trim(input('post.code'));
        // 数据
        $update = array();
        $update['mmt_short_switch'] = intval(input('post.short_switch'));
        $update['mmt_short_content'] = $short_content;
        $result = Model('membermsgtpl')->editMemberMsgTpl($where, $update);
        $this->member_tpl_update_showmessage($result);
    }

    /**
     * 商家消息模板更新邮件
     */
    private function member_tpl_update_mail() {
        $mail_subject = trim(input('post.mail_subject'));
        $mail_content = trim(input('post.mail_content'));
        if ((empty($mail_subject) || empty($mail_content))) {
            $this->error('请填写邮件模板内容。');
        }
        // 条件
        $where = array();
        $where['mmt_code'] = trim(input('post.code'));
        // 数据
        $update = array();
        $update['mmt_mail_switch'] = intval(input('post.mail_switch'));
        $update['mmt_mail_subject'] = $mail_subject;
        $update['mmt_mail_content'] = $mail_content;
        $result = Model('membermsgtpl')->editMemberMsgTpl($where, $update);
        $this->member_tpl_update_showmessage($result);
    }

    private function member_tpl_update_showmessage($result) {
        if ($result) {
            $this->success(lang('ds_common_op_succ'), url('Admin/message/member_tpl'));
        } else {
            $this->error(lang('ds_common_op_fail'));
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'email',
                'text' => '邮件设置',
                'url' => url('Admin/Message/email')
            ),
            array(
                'name' => 'mobile',
                'text' => '短信平台设置',
                'url' => url('Admin/Message/mobile')
            ),
            array(
                'name' => 'seller_tpl',
                'text' => '商家消息模板',
                'url' => url('Admin/Message/seller_tpl')
            ),
            array(
                'name' => 'member_tpl',
                'text' => '用户消息模板',
                'url' => url('Admin/Message/member_tpl')
            ),
            array(
                'name' => 'email_tpl',
                'text' => '其他模板',
                'url' => url('Admin/Message/email_tpl')
            ),
        );
        if (request()->action() == 'seller_tpl_edit') {
            $menu_array[] = array(
                'name' => 'seller_tpl_edit',
                'text' => '编辑商家消息模板',
                'url' => "javascript:void(0)"
            );
        }
        if (request()->action() == 'member_tpl_edit') {
            $menu_array[] = array(
                'name' => 'member_tpl_edit',
                'text' => '编辑用户消息模板',
                'url' => "javascript:void(0)"
            );
        }
        if (request()->action() == 'email_tpl_edit') {
            $menu_array[] = array(
                'name' => 'email_tpl_edit',
                'text' => '编辑其他消息模板',
                'url' => "javascript:void(0)"
            );
        }
        

        return $menu_array;
    }

}

?>
