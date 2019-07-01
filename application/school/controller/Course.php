<?php

namespace app\school\controller;

use think\Lang;
use think\Validate;
/**
 * 套餐展示
 */
class Course extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'school/lang/zh-cn/pkgs.lang.php');
        Lang::load(APP_PATH . 'school/lang/zh-cn/admin.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name = strtolower(end(explode('\\',__CLASS__)));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('school_admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    public function course_manage(){
        if(session('school_admin_is_super') !=1 && !in_array(4,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $Course = model('Course');
        $condition = array();        
        $sc_list = $Course->get_course_List($condition, '10' ,'co_sort asc');
        $this->assign('course_list', $sc_list);
        $this->assign('page', $Course->page_info->render());
        $this->setAdminCurItem('course_manage');
        return $this->fetch('course');
    }

    public function course_edit(){
        if(session('school_admin_is_super') !=1 && !in_array(3,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        if (request()->isPost()) {
            $Course = Model('Course');
            $param =array();            
            $param['co_sort']  = intval(input('post.co_sort'));
            $param['co_type']   = trim(input('post.co_type'));
            $param['co_enabled']   = 1;
            $param['up_time']   = time();
            switch (input('actions')) {
                case 'edit':
                    $res = db('Course')->where('co_id != "'.intval(input("param.co_id")).'" AND co_type="'.trim(input("post.co_type")).'" ')->find();
                    if($res){
                        $this->log(lang('name_isset') . '[' . input('post.cl_type') . ']', null);
                        echo json_encode(['m'=>true,'ms'=>lang('name_isset')]);
                    }else {
                        $param['co_id'] = intval(input('param.co_id'));
                        $result = $Course->course_update($param);
                        if ($result) {
                            $this->log(lang('co_edit_succ') . '[' . input('post.co_type') . ']', null);
                            echo json_encode(['m' => true, 'ms' => lang('co_edit_succ')]);
                        }
                    }
                    break;                
                default:
                    $res = db('Course')->where(' co_type="'.trim(input("post.co_type")).'" ')->find();
                    if($res){
                        $this->log(lang('name_isset') . '[' . input('post.cl_type') . ']', null);
                        echo json_encode(['m'=>true,'ms'=>lang('name_isset')]);
                    }else {
                        $result = $Course->course_add($param);
                        if ($result) {
                            $this->log(lang('co_add_succ') . '[' . input('post.co_type') . ']', null);
                            echo json_encode(['m' => true, 'ms' => lang('co_add_succ')]);
                        }
                    }
                    break;
            }
            exit;

        }
    }

    /**
     *
     * 删除套餐
     */
    public function course_del() {
        if(session('school_admin_is_super') !=1 && !in_array(2,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $Course = Model('Course');
        /**
         * 删除套餐
         */
        $co_id = intval(input('param.co_id'));
        $result = $Course->course_del($co_id);

        if (!$result) {
            $this->error(lang('co_del_fail'));
        } else {
            $this->log(lang('co_del_succ') . '[' . $co_id . ']', null);
            $this->success(lang('co_del_succ'));
        }
    }



    /**
     *
     * ajaxOp
     */
    public function ajax() {
        switch ($_GET['branch']) {
            case 'co_enabled':
                $Course = Model('Course');
                $param[trim($_GET['column'])] = intval($_GET['value']);
                $param['co_id'] = intval($_GET['id']);
                $Course->course_update($param);
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
                'name' => 'course_manage',
                'text' => lang('course_manage'),
                'url' => url('School/Course/course_manage')
            )
        );
        
        return $menu_array;
    }

}

?>
