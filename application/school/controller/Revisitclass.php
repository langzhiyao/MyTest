<?php

namespace app\school\controller;

use think\Lang;
use think\Validate;

class Revisitclass extends AdminControl
{
    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'school/lang/zh-cn/look.lang.php');
        //获取省份
        $province = db('area')->field('area_id,area_parent_id,area_name')->where('area_parent_id=0')->select();
        //获取学校
        $school = db('school')->field('schoolid,name')->select();
//获取当前角色对当前子目录的权限
        $class_name = strtolower(end(explode('\\',__CLASS__)));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('school_admin_gid') ,$perm_id);
        $this->assign('action',$action);

        $this->assign('school',$school);
        $this->assign('province',$province);
    }

    /**
     * @desc 重温课堂回放
     * @author 郎志耀
     * @time 20180926
     */
    public function revisitclass(){
        if(session('school_admin_is_super') !=1 && !in_array('4',$this->action)){
            $this->error(lang('ds_assign_right'));
        }

        $this->setAdminCurItem('revisitclass');
        return $this->fetch('revisitclass');
    }


}