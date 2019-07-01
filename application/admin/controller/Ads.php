<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Ads extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/adv.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    /**
     *
     * 管理广告位
     */
    public function ap_manage() {
        if(session('admin_is_super') !=1 && !in_array('4',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $adv = model('adv');
        /**
         * 多选删除广告位
         */
        if (!request()->isPost()) {
            /**
             * 显示广告位管理界面
             */
            $condition = array();
            $orderby = '';
            $search_name = trim(input('get.search_name'));
            if ($search_name != '') {
                $condition['ap_name'] = $search_name;
            }
            $condition['is_show'] = 1;
            $ap_list = $adv->getApList($condition, '10', $orderby);
            $adv_list = $adv->getList();
            $this->assign('ap_list', $ap_list);
            $this->assign('adv_list', $adv_list);
            $this->assign('page', $adv->page_info->render());
            $this->setAdminCurItem('ap_manage');
            return $this->fetch('ap_manage');
        }
    }

    /**
     *
     * 修改广告位
     */
    public function ap_edit() {
        if(session('admin_is_super') !=1 && !in_array('3',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $ap_id = intval(input('param.ap_id'));

        if (!request()->isPost()) {
            $adv = Model('adv');
            $condition['ap_id'] = $ap_id;
            $ap = $adv->getOneAp($condition);
            $this->assign('ref_url', getReferer());
            $this->assign('ap', $ap);
            $this->setAdminCurItem('ap_manage');
            return $this->fetch('ap_form');
        } else {
            $adv = Model('adv');

            $param['ap_id'] = $ap_id;
            $param['ap_name'] = trim(input('post.ap_name'));
            $param['ap_intro'] = trim(input('post.ap_intro'));
            $param['ap_width'] = intval(trim(input('post.ap_width')));
            $param['ap_height'] = intval(trim(input('post.ap_height')));
            if (input('post.is_use') != '') {
                $param['is_use'] = intval(input('post.is_use'));
            }


            //验证数据  BEGIN
            $rule = [
                ['ap_name', 'require', lang('ap_can_not_null')],
                ['ap_width', 'require', lang('ap_width_must_num')],
                ['ap_height', 'require', lang('ap_height_must_num')],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($param);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END
            $result = $adv->ap_update($param);

            if ($result) {
                $this->log(lang('ap_change_succ') . '[' . input('post.ap_name') . ']', null);
                $this->success(lang('ap_change_succ'), input('post.ref_url'));
            } else {
                $this->error(lang('ap_change_fail'));
            }
        }
    }

    /**
     *
     * 新增广告位
     */
    public function ap_add() {
        if(session('admin_is_super') !=1 && !in_array('1',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        if (!request()->isPost()) {
            $this->setAdminCurItem('ap_add');
            return $this->fetch('ap_form');
        } else {
            $adv = Model('adv');

            $insert_array['ap_name'] = trim(input('post.ap_name'));
            $insert_array['ap_intro'] = trim(input('post.ap_intro'));
            $insert_array['is_use'] = intval(input('post.is_use'));
            $insert_array['ap_width'] = intval(input('post.ap_width'));
            $insert_array['ap_height'] = intval(input('post.ap_height'));

            //验证数据  BEGIN
            $rule = [
                ['ap_name', 'require', lang('ap_can_not_null')],
                ['ap_width', 'require', lang('ap_width_must_num')],
                ['ap_height', 'require', lang('ap_height_must_num')],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($insert_array);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END

            $result = $adv->ap_add($insert_array);

            if ($result) {
                $this->log(lang('ap_add_succ') . '[' . input('post.ap_name') . ']', null);
                $this->success(lang('ap_add_succ'), url('Admin/Ads/ap_manage'));
            } else {
                $this->error(lang('ap_add_fail'));
            }
        }
    }

    /**
     *
     * 删除广告位
     */
    public function ap_del() {
        if(session('admin_is_super') !=1 && !in_array('2',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $adv = Model('adv');
        /**
         * 删除一个广告
         */
        $ap_id = intval(input('param.ap_id'));
        $result = $adv->ap_del($ap_id);

        if (!$result) {
            $this->error(lang('ap_del_fail'));
        } else {
            $this->log(lang('ap_del_succ') . '[' . $ap_id . ']', null);
            $this->success(lang('ap_del_succ'));
        }
    }


    /**
     *
     * 获取UNIX时间戳
     */
    public function getunixtime($time) {
        $array = explode("-", $time);
        $unix_time = mktime(0, 0, 0, $array[1], $array[2], $array[0]);
        return $unix_time;
    }

    /**
     *
     * ajaxOp
     */
    public function ajax() {
        switch ($_GET['branch']) {
            case 'is_use':
                $adv_model = Model('adv');
                $param[trim($_GET['column'])] = intval($_GET['value']);
                $param['ap_id'] = intval($_GET['id']);
                $adv_model->ap_update($param);
                echo 'true';
                exit;
                break;
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'ap_manage',
                'text' => lang('ap_manage'),
                'url' => url('Admin/Ads/ap_manage')
            ),
        );
        if(session('admin_is_super') ==1 || in_array('1',$this->action)){
            $menu_array[] = array(
                'name' => 'ap_add',
                'text' => lang('ap_add'),
                'url' => url('Admin/Ads/ap_add')
            );
        }



        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit',
                'text' => '编辑',
                'url' => url('Admin/Article/edit')
            );
        }
        return $menu_array;
    }

}

?>
