<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;
use vomont\Vomont;

class Revisitclass extends AdminControl
{
    public function _initialize(){
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/look.lang.php');
        //获取省份
        $province = db('area')->field('area_id,area_parent_id,area_name')->where('area_parent_id=0')->select();
        //获取学校
        $school = db('school')->field('schoolid,name')->select();
//获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
        $this->assign('action',$action);
        $this->assign('school',$school);
        $this->assign('province',$province);
    }
    /**
     * 获取分/子公司查看列表
     */
    protected function getAdminItemList(){
        $menu_array = array(
            array(
                'name' => 'revisitclass',
                'text' => '重温课堂',
                'url' => url('Admin/Revisitclass/revisitclass')
            ),
        );
        return $menu_array;
    }
    /**
     * @desc 重温课堂回放
     * @author 郎志耀
     * @time 20180926
     */
    public function revisitclass(){
        if(session('admin_is_super') !=1 && !in_array('4',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $where['is_classroom'] =2;
        $page_count = intval(input('post.page_count')) ? intval(input('post.page_count')) : 15;//每页的条数
        $start = intval(input('post.page')) ? (intval(input('post.page'))-1)*$page_count : 0;//开始页数

        //查询已安装的摄像头
        $model_camera=Model('camera');
        $list =$model_camera->getCameraLists($where,'c.*,a.classname,a.school_region,s.name',$page_count);
        $this->assign('list',$list);
        $this->assign('page', $model_camera->page_info->render());
        $this->setAdminCurItem('revisitclass');
        return $this->fetch('revisitclass');
    }
    /**
     * 重温课堂视频列表
    */
    public function lists(){
        $id=$_GET['id'];
        $time=$_GET['time'];
        $begintime=$time.' 07:00:00';
        $endtime=$time.' 17:30:00';
        $begin=strtotime($begintime);
        $end=strtotime($endtime);
        $id=$id.",";
        $vlink = new Vomont();
        $res= $vlink->SetLogin();
        $accountid=$res['accountid'];
        $res=$vlink->Videotape($accountid,$id,$begin,$end);
        //print_r($res);exit;
        $this->assign('res',$res);
        $this->assign('time',$time);
        $this->setAdminCurItem('lists');
        return $this->fetch();
    }

}