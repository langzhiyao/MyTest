<?php

namespace app\school\controller;

use think\Lang;
use think\Validate;
/**
 * 年级展示
 */
class Classtype extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'school/lang/zh-cn/admin.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name = strtolower(end(explode('\\',__CLASS__)));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('school_admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    public function classtype_manage(){
        if(session('school_admin_is_super') !=1 && !in_array(4,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $ClassType = model('Classtype');
        $condition = array();   
        $sc_id = intval(input('sc_id'));
        if ($sc_id) {
            $condition ='a.sc_id ='.$sc_id ;
        }
        $Sctype = model('Schooltype');
        $sc_list = $Sctype->get_sctype_List(array('sc_enabled'=>1), '30' ,'sc_sort asc');
        $class_list = $ClassType->get_classtype_List($condition, '10' ,'a.sc_id asc,a.cl_sort asc');
        // p($sc_list);exit;
        $this->assign('sc_id', $sc_id);
        $this->assign('class_list', $class_list);
        $this->assign('sc_list', $sc_list);
        $this->assign('page', $ClassType->page_info->render());
        $this->setAdminCurItem('classtype_manage');
        return $this->fetch('classtype');
    }

    public function type(){

        $this->setAdminCurItem('classtype_manage');
        return $this->fetch('type');
    }

    public function classtype_edit(){
        if(session('school_admin_is_super') !=1 && !in_array(3,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        if (request()->isPost()) {
            $ClassType = Model('Classtype');
            $param =array();            
            $param['cl_sort']  = intval(input('post.cl_sort'));
            $param['sc_id']  = intval(input('post.sc_id'));
            $param['cl_type']   = trim(input('post.cl_type'));
            $param['cl_enabled']   = 1;
            $param['up_time']   = time();
            switch (input('actions')) {
                case 'edit':
                    $res = db('Classtype')->where('cl_id != "'.intval(input("param.cl_id")).'" AND sc_id="'.intval(input("post.sc_id")).'" AND cl_type="'.trim(input("post.cl_type")).'"')->find();
                    if($res){
                        $this->log(lang('name_isset') . '[' . input('post.cl_type') . ']', null);
                        echo json_encode(['m'=>true,'ms'=>lang('name_isset')]);
                    }else {
                        $param['cl_id'] = intval(input('param.cl_id'));
                        $result = $ClassType->classtype_update($param);
                        if ($result) {
                            $this->log(lang('cl_edit_succ') . '[' . input('post.cl_type') . ']', null);
                            echo json_encode(['m' => true, 'ms' => lang('cl_edit_succ')]);
                        }
                    }
                    break;                
                default:
                     $res = db('Classtype')->where('sc_id="'.intval(input("post.sc_id")).'" AND cl_type="'.trim(input("post.cl_type")).'"')->find();
                     if($res){
                        $this->log(lang('name_isset') . '[' . input('post.cl_type') . ']', null);
                        echo json_encode(['m'=>true,'ms'=>lang('name_isset')]);
                    }else {
                        $result = $ClassType->classtype_add($param);
                        if ($result) {
                            $this->log(lang('cl_add_succ') . '[' . input('post.cl_type') . ']', null);
                            echo json_encode(['m' => true, 'ms' => lang('cl_add_succ')]);
                        }
                    }
                    break;
            }
            exit;

        }
    }

    /**
     *
     * 删除年级
     */
    public function classtype_del() {
        if(session('school_admin_is_super') !=1 && !in_array(2,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $ClassType = Model('Classtype');
        /**
         * 删除年级
         */
        $cl_id = intval(input('param.cl_id'));
        $result = $ClassType->classtype_del($cl_id);

        if (!$result) {
            $this->error(lang('cl_del_fail'));
        } else {
            $this->log(lang('cl_del_succ') . '[' . $cl_id . ']', null);
            $this->success(lang('cl_del_succ'));
        }
    }



    /**
     *
     * ajaxOp
     */
    public function ajax() {
        switch ($_GET['branch']) {
            case 'cl_enabled':
                $ClassType = Model('Classtype');
                $param[trim($_GET['column'])] = intval($_GET['value']);
                $param['cl_id'] = intval($_GET['id']);
                $ClassType->classtype_update($param);
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
                'name' => 'classtype_manage',
                'text' => lang('classtype_manage'),
                'url' => url('School/Classtype/classtype_manage')
            )
        );
        
        return $menu_array;
    }

}

?>
