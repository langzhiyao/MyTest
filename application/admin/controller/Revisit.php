<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;
/**
 *
 */
class Revisit extends AdminControl {
    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/pkgs.lang.php');
        Lang::load(APP_PATH . 'admin/lang/zh-cn/admin.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        // halt($perm_id);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    /**
     *
     * 管理
     */
    public function index() {
        if(session('admin_is_super') !=1 && !in_array(4,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        if (!request()->isPost()) {
            $revisit_list = db('revisit')->order('pkg_sort ASC')->select();
            $this->assign('pkg_list', $revisit_list);

            $this->setAdminCurItem('revisit_manage');
            return $this->fetch('index');
        }
    }


    /**
     *
     *
     */
    public function edit() {
        if(session('admin_is_super') !=1 && !in_array(3,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $pkg_id = intval(input('param.pkg_id'));
        if (request()->isPost()) {
            $rule = [
                ['pkg_name', 'require', lang('pkg_name_err')],
                ['pkg_price', 'require', lang('pkg_price_err')],
                ['pkg_length', 'require', lang('pkg_length_err')],
            ];
            //验证数据  BEGIN
            $validate = new Validate($rule);
            $param =array();
            $param['pkg_name']   = trim(input('post.pkg_name'));
            $param['pkg_sort']   = intval(trim(input('post.pkg_sort')));
            $param['pkg_cprice']  = trim(input('post.pkg_cprice'));
            $param['pkg_price']  = trim(input('post.pkg_price'));
            $param['pkg_length'] = intval(trim(input('post.pkg_length')));
            $param['pkg_axis']   = trim(input('post.pkg_axis'));
            $param['pkg_desc']   = trim(input('post.pkg_desc'));
            $param['up_time']    = time();
            $validate_result     = $validate->check($param);
            if (input('post.pkg_enabled') != '') {
                $param['pkg_enabled'] = intval(input('post.pkg_enabled'));
            }
            if (!$pkg_id) {//新增
                if (!$validate_result)$this->error($validate->getError());
                //验证数据  END
                $result = db('revisit')->insertGetId($param);
                if ($result) {
                    $this->log(lang('pkg_add_succ') . '[' . input('post.pkg_name') . ']', null);
                    $this->success(lang('pkg_add_succ'), url('Admin/Revisit/index'));
                } else {
                    $this->error(lang('pkg_add_fail'));
                }
            }else{//编辑
//                $param['pkg_id'] = $pkg_id;
                if (!$validate_result)$this->error($validate->getError());
                //验证数据  END
                $result = db('revisit')->where('pkg_id="'.$pkg_id.'"')->update($param);
                if ($result) {
                    $this->log(lang('pkg_edit_succ') . '[' . input('post.pkg_name') . ']', null);
                    $this->success(lang('pkg_edit_succ'), url('Admin/Revisit/index'));
                } else {
                    $this->error(lang('pkg_edit_fail'));
                }
            }

        }
        $condition['pkg_id'] = $pkg_id;

        $pkg_info = db('revisit')->where($condition)->find();

        $this->assign('axis_list', config('pkgs_list'));
        $this->assign('ref_url', getReferer());
        $this->assign('pkg_info', $pkg_info);
        $this->setAdminCurItem('revisit_manage');
        return $this->fetch('edit');
    }



    /**
     *
     * 删除套餐
     */
    public function del() {
        if(session('admin_is_super') !=1 && !in_array(2,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        /**
         * 删除套餐
         */
        $pkg_id = intval(input('param.pkg_id'));
        $condition['pkg_id'] = $pkg_id;
        $result = db('revisit')->where($condition)->delete();

        if (!$result) {
            $this->error(lang('pkg_del_fail'));
        } else {
            $this->log(lang('pkg_del_succ') . '[' . $pkg_id . ']', null);
            $this->success(lang('pkg_del_succ'));
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'revisit_manage',
                'text' => lang('revisit_manage'),
                'url' => url('Admin/Revisit/index')
            )
        );
        return $menu_array;
    }

}