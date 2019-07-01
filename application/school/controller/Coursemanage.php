<?php

namespace app\school\controller;

use think\Lang;
use think\Model;
use think\Validate;

class Coursemanage extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'school/lang/zh-cn/school.lang.php');
        Lang::load(APP_PATH . 'school/lang/zh-cn/admin.lang.php');
    }

    public function index() {
        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_gid']!=5){
            $this->error(lang('ds_assign_right'));
        }
        $model_arrangement = model('Arrangement');
        $condition = array();
        if($admininfo['admin_id']!=1){
            if(!empty($admininfo['admin_school_id'])){
                $condition['schoolid'] = $admininfo['admin_school_id'];
            }
        }
        $condition['isdel'] = 1;
        $course_list = $model_arrangement->getList($condition);
        $model_schooltype = model('Schooltype');
        $schooltype = $model_schooltype->get_sctype_List(array('sc_enabled'=>1));
        $school = db('class')->field("classid,classname")->select();
        foreach($course_list as $key=>$value){
            foreach($schooltype as $k=>$v){
                if($value['type']==$v['sc_id']){
                    $course_list[$key]['type'] = $v['sc_type'];
                }
            }
            foreach($school as $k2=>$v2){
                if($value['classid']==$v2['classid']){
                    $course_list[$key]['schoolname'] = $v2['classname'];
                }
            }
        }
        $this->assign('course_list', $course_list);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    public function add() {
        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_gid']!=5){
            $this->error(lang('ds_assign_right'));
        }
        if($admininfo['admin_id']!=1){
            if(!empty($admininfo['admin_school_id'])){
                $schoolid = $admininfo['admin_school_id'];
            }
        }else{
            $schoolid = 6;
        }
        if (!request()->isPost()) {
            //学校类型
            $schoolInfo = db('school')->where(array('schoolid'=>$schoolid))->find();
            $type = explode(',',$schoolInfo['typeid']);
            $model_schooltype = model('Schooltype');
            $schooltype = $model_schooltype->get_sctype_List(array('sc_enabled'=>1));
            foreach($type as $key=>$val){
               foreach($schooltype as $k=>$v){
                    if($val==$v['sc_id']){
                        $types[$key]['typeid'] = $val;
                        $types[$key]['typename'] = $v['sc_type'];
                    }
               }
            }
            $this->assign('schooltype', $types);
            $this->assign('schoolid', $schoolid);
            $this->setAdminCurItem('add');
            return $this->fetch();
        } else {
            $data = $_POST;
            foreach($data['mor_txt'] as $k=>$v){
                if($data['mor_txt'][0]!=""){
                    $content['Monday']['morning'][$k]['content'] = $data['mor_txt'][$k];
                    $content['Monday']['morning'][$k]['time'] = $data['mor_startTime'][$k]."-".$data['mor_endTime'][$k];
                    $content['Monday']['morning'][$k]['startTime'] = $data['mor_startTime'][$k];
                    $content['Monday']['morning'][$k]['endTime'] = $data['mor_endTime'][$k];
                }
            }
            foreach($data['after_txt'] as $k=>$v){
                if($data['after_txt'][0]!=""){
                    $content['Monday']['afternoon'][$k]['content'] = $data['after_txt'][$k];
                    $content['Monday']['afternoon'][$k]['time'] = $data['after_startTime'][$k]."-".$data['after_endTime'][$k];
                    $content['Monday']['afternoon'][$k]['startTime'] = $data['after_startTime'][$k];
                    $content['Monday']['afternoon'][$k]['endTime'] = $data['after_endTime'][$k];
                }
            }
            foreach($data['mor_cls'] as $k=>$v){
                if($data['mor_cls'][0]!=""){
                    $content['Tuesday']['morning'][$k]['content'] = $data['mor_cls'][$k];
                    $content['Tuesday']['morning'][$k]['time'] = $data['mor_cls_startTime'][$k]."-".$data['mor_cls_endTime'][$k];
                    $content['Tuesday']['morning'][$k]['startTime'] = $data['mor_cls_startTime'][$k];
                    $content['Tuesday']['morning'][$k]['endTime'] = $data['mor_cls_endTime'][$k];
                }
            }
            foreach($data['after_cls'] as $k=>$v){
                if($data['after_cls'][0]!=""){
                    $content['Tuesday']['afternoon'][$k]['content'] = $data['after_cls'][$k];
                    $content['Tuesday']['afternoon'][$k]['time'] = $data['after_cls_startTime'][$k]."-".$data['after_cls_endTime'][$k];
                    $content['Tuesday']['afternoon'][$k]['startTime'] = $data['after_cls_startTime'][$k];
                    $content['Tuesday']['afternoon'][$k]['endTime'] = $data['after_cls_endTime'][$k];
                }
            }
            foreach($data['mor_wes_txt'] as $k=>$v){
                if($data['mor_wes_txt'][0]!=""){
                    $content['Wednesday']['morning'][$k]['content'] = $data['mor_wes_txt'][$k];
                    $content['Wednesday']['morning'][$k]['time'] = $data['mor_wes_startTime'][$k]."-".$data['mor_wes_endTime'][$k];
                    $content['Wednesday']['morning'][$k]['startTime'] = $data['mor_wes_startTime'][$k];
                    $content['Wednesday']['morning'][$k]['endTime'] = $data['mor_wes_endTime'][$k];
                }
            }
            foreach($data['after_wes_txt'] as $k=>$v){
                if($data['after_wes_txt'][0]!=""){
                    $content['Wednesday']['afternoon'][$k]['content'] = $data['after_wes_txt'][$k];
                    $content['Wednesday']['afternoon'][$k]['time'] = $data['after_wes_startTime'][$k]."-".$data['after_wes_endTime'][$k];
                    $content['Wednesday']['afternoon'][$k]['startTime'] = $data['after_wes_startTime'][$k];
                    $content['Wednesday']['afternoon'][$k]['endTime'] = $data['after_wes_endTime'][$k];
                }
            }
            foreach($data['mor_tus_txt'] as $k=>$v){
                if($data['mor_tus_txt'][0]!=""){
                    $content['Thursday']['morning'][$k]['content'] = $data['mor_tus_txt'][$k];
                    $content['Thursday']['morning'][$k]['time'] = $data['mor_tus_startTime'][$k]."-".$data['mor_tus_endTime'][$k];
                    $content['Thursday']['morning'][$k]['startTime'] = $data['mor_tus_startTime'][$k];
                    $content['Thursday']['morning'][$k]['endTime'] = $data['mor_tus_endTime'][$k];
                }
            }
            foreach($data['after_tus_txt'] as $k=>$v){
                if($data['after_tus_txt'][0]!=""){
                    $content['Thursday']['afternoon'][$k]['content'] = $data['after_tus_txt'][$k];
                    $content['Thursday']['afternoon'][$k]['time'] = $data['after_tus_startTime'][$k]."-".$data['after_tus_endTime'][$k];
                    $content['Thursday']['afternoon'][$k]['startTime'] = $data['after_tus_startTime'][$k];
                    $content['Thursday']['afternoon'][$k]['endTime'] = $data['after_tus_endTime'][$k];
                }
            }
            foreach($data['mor_fri_txt'] as $k=>$v){
                if($data['mor_fri_txt'][0]!=""){
                    $content['Friday']['morning'][$k]['content'] = $data['mor_fri_txt'][$k];
                    $content['Friday']['morning'][$k]['time'] = $data['mor_fri_startTime'][$k]."-".$data['mor_fri_endTime'][$k];
                    $content['Friday']['morning'][$k]['startTime'] = $data['mor_fri_startTime'][$k];
                    $content['Friday']['morning'][$k]['endTime'] = $data['mor_fri_endTime'][$k];
                }
            }
            foreach($data['after_fri_txt'] as $k=>$v){
                if($data['after_fri_txt'][0]!=""){
                    $content['Friday']['afternoon'][$k]['content'] = $data['after_fri_txt'][$k];
                    $content['Friday']['afternoon'][$k]['time'] = $data['after_fri_startTime'][$k]."-".$data['after_fri_endTime'][$k];
                    $content['Friday']['afternoon'][$k]['startTime'] = $data['after_fri_startTime'][$k];
                    $content['Friday']['afternoon'][$k]['endTime'] = $data['after_fri_endTime'][$k];
                }
            }
            foreach($data['mor_fes_txt'] as $k=>$v){
                if($data['mor_fes_txt'][0]!=""){
                    $content['Saturday']['morning'][$k]['content'] = $data['mor_fes_txt'][$k];
                    $content['Saturday']['morning'][$k]['time'] = $data['mor_fes_startTime'][$k]."-".$data['mor_fes_endTime'][$k];
                    $content['Saturday']['morning'][$k]['startTime'] = $data['mor_fes_startTime'][$k];
                    $content['Saturday']['morning'][$k]['endTime'] = $data['mor_fes_endTime'][$k];
                }
            }
            foreach($data['after_fes_txt'] as $k=>$v){
                if($data['after_fes_txt'][0]!=""){
                    $content['Saturday']['afternoon'][$k]['content'] = $data['after_fes_txt'][$k];
                    $content['Saturday']['afternoon'][$k]['time'] = $data['after_fes_startTime'][$k]."-".$data['after_fes_endTime'][$k];
                    $content['Saturday']['afternoon'][$k]['startTime'] = $data['after_fes_startTime'][$k];
                    $content['Saturday']['afternoon'][$k]['endTime'] = $data['after_fes_endTime'][$k];
                }
            }
            foreach($data['mor_sun_txt'] as $k=>$v){
                if($data['mor_sun_txt'][0]!=""){
                    $content['Sunday']['morning'][$k]['content'] = $data['mor_sun_txt'][$k];
                    $content['Sunday']['morning'][$k]['time'] = $data['mor_sun_startTime'][$k]."-".$data['mor_sun_endTime'][$k];
                    $content['Sunday']['morning'][$k]['startTime'] = $data['mor_sun_startTime'][$k];
                    $content['Sunday']['morning'][$k]['endTime'] = $data['mor_sun_endTime'][$k];
                }
            }
            foreach($data['after_sun_txt'] as $k=>$v){
                if($data['after_sun_txt'][0]!=""){
                    $content['Sunday']['afternoon'][$k]['content'] = $data['after_sun_txt'][$k];
                    $content['Sunday']['afternoon'][$k]['time'] = $data['after_sun_startTime'][$k]."-".$data['after_sun_endTime'][$k];
                    $content['Sunday']['afternoon'][$k]['startTime'] = $data['after_sun_startTime'][$k];
                    $content['Sunday']['afternoon'][$k]['endTime'] = $data['after_sun_endTime'][$k];
                }
            }
            $schoolInfo = db('school')->where('schoolid',$schoolid)->find();
            $admininfo = $this->getAdminInfo();
            $data = array(
                'schoolid' => $_POST['schoolid'],
                'classid' => input('post.class_name'),
                'type' => input('post.school_type'),
                'content' => json_encode($content),
                'option_id' => $admininfo['admin_id'],
                'admin_company_id' => $schoolInfo['admin_company_id'],
                'desc' => input('post.desc'),
                'addtime' => time()
            );
            $model_arrangement = model('Arrangement');
            //验证数据  END
            $result = $model_arrangement->arrangement_add($data);
            if ($result) {
                $this->success(lang('school_course_add_suss'), 'Coursemanage/index');
            } else {
                $this->error(lang('school_course_add_fail'));
            }
        }
    }

    public function edit() {
        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_gid']!=5){
            $this->error(lang('ds_assign_right'));
        }
        $course_id = input('param.id');
        $schoolid = input('param.schoolid');
        if (empty($course_id)) {
            $this->error(lang('param_error'));
        }
        $model_school = Model('school');
        if (!request()->isPost()) {
            //学校类型
            $schoolInfo = db('school')->where(array('schoolid'=>$schoolid))->find();
            $type = explode(',',$schoolInfo['typeid']);
            $model_schooltype = model('Schooltype');
            $schooltype = $model_schooltype->get_sctype_List(array('sc_enabled'=>1));
            foreach($type as $key=>$val){
                foreach($schooltype as $k=>$v){
                    if($val==$v['sc_id']){
                        $types[$key]['typeid'] = $val;
                        $types[$key]['typename'] = $v['sc_type'];
                    }
                }
            }
            $arrangement_model = Model('Arrangement');
            $course_list = $arrangement_model->getOneInfo(array('id'=>$course_id));
            $class_list = db('class')->where(array('schoolid'=>$schoolid,'typeid'=>$course_list['type']))->select();
            $class_list['content'] = json_decode($course_list['content'],true);
            $this->assign('schooltype', $types);
            $this->assign('schoolid', $schoolid);
            $this->assign('course_list', $course_list);
            $this->assign('class_list', $class_list);
            $this->setAdminCurItem('edit');
            return $this->fetch();
        } else {
            $data = $_POST;
            foreach($data['mor_txt'] as $k=>$v){
                if($data['mor_txt'][0]!=""){
                    $content['Monday']['morning'][$k]['content'] = $data['mor_txt'][$k];
                    $content['Monday']['morning'][$k]['time'] = $data['mor_startTime'][$k]."-".$data['mor_endTime'][$k];
                    $content['Monday']['morning'][$k]['startTime'] = $data['mor_startTime'][$k];
                    $content['Monday']['morning'][$k]['endTime'] = $data['mor_endTime'][$k];
                }
            }
            foreach($data['after_txt'] as $k=>$v){
                if($data['after_txt'][0]!=""){
                    $content['Monday']['afternoon'][$k]['content'] = $data['after_txt'][$k];
                    $content['Monday']['afternoon'][$k]['time'] = $data['after_startTime'][$k]."-".$data['after_endTime'][$k];
                    $content['Monday']['afternoon'][$k]['startTime'] = $data['after_startTime'][$k];
                    $content['Monday']['afternoon'][$k]['endTime'] = $data['after_endTime'][$k];
                }
            }
            foreach($data['mor_cls'] as $k=>$v){
                if($data['mor_cls'][0]!=""){
                    $content['Tuesday']['morning'][$k]['content'] = $data['mor_cls'][$k];
                    $content['Tuesday']['morning'][$k]['time'] = $data['mor_cls_startTime'][$k]."-".$data['mor_cls_endTime'][$k];
                    $content['Tuesday']['morning'][$k]['startTime'] = $data['mor_cls_startTime'][$k];
                    $content['Tuesday']['morning'][$k]['endTime'] = $data['mor_cls_endTime'][$k];
                }
            }
            foreach($data['after_cls'] as $k=>$v){
                if($data['after_cls'][0]!=""){
                    $content['Tuesday']['afternoon'][$k]['content'] = $data['after_cls'][$k];
                    $content['Tuesday']['afternoon'][$k]['time'] = $data['after_cls_startTime'][$k]."-".$data['after_cls_endTime'][$k];
                    $content['Tuesday']['afternoon'][$k]['startTime'] = $data['after_cls_startTime'][$k];
                    $content['Tuesday']['afternoon'][$k]['endTime'] = $data['after_cls_endTime'][$k];
                }
            }
            foreach($data['mor_wes_txt'] as $k=>$v){
                if($data['mor_wes_txt'][0]!=""){
                    $content['Wednesday']['morning'][$k]['content'] = $data['mor_wes_txt'][$k];
                    $content['Wednesday']['morning'][$k]['time'] = $data['mor_wes_startTime'][$k]."-".$data['mor_wes_endTime'][$k];
                    $content['Wednesday']['morning'][$k]['startTime'] = $data['mor_wes_startTime'][$k];
                    $content['Wednesday']['morning'][$k]['endTime'] = $data['mor_wes_endTime'][$k];
                }
            }
            foreach($data['after_wes_txt'] as $k=>$v){
                if($data['after_wes_txt'][0]!=""){
                    $content['Wednesday']['afternoon'][$k]['content'] = $data['after_wes_txt'][$k];
                    $content['Wednesday']['afternoon'][$k]['time'] = $data['after_wes_startTime'][$k]."-".$data['after_wes_endTime'][$k];
                    $content['Wednesday']['afternoon'][$k]['startTime'] = $data['after_wes_startTime'][$k];
                    $content['Wednesday']['afternoon'][$k]['endTime'] = $data['after_wes_endTime'][$k];
                }
            }
            foreach($data['mor_tus_txt'] as $k=>$v){
                if($data['mor_tus_txt'][0]!=""){
                    $content['Thursday']['morning'][$k]['content'] = $data['mor_tus_txt'][$k];
                    $content['Thursday']['morning'][$k]['time'] = $data['mor_tus_startTime'][$k]."-".$data['mor_tus_endTime'][$k];
                    $content['Thursday']['morning'][$k]['startTime'] = $data['mor_tus_startTime'][$k];
                    $content['Thursday']['morning'][$k]['endTime'] = $data['mor_tus_endTime'][$k];
                }
            }
            foreach($data['after_tus_txt'] as $k=>$v){
                if($data['after_tus_txt'][0]!=""){
                    $content['Thursday']['afternoon'][$k]['content'] = $data['after_tus_txt'][$k];
                    $content['Thursday']['afternoon'][$k]['time'] = $data['after_tus_startTime'][$k]."-".$data['after_tus_endTime'][$k];
                    $content['Thursday']['afternoon'][$k]['startTime'] = $data['after_tus_startTime'][$k];
                    $content['Thursday']['afternoon'][$k]['endTime'] = $data['after_tus_endTime'][$k];
                }
            }
            foreach($data['mor_fri_txt'] as $k=>$v){
                if($data['mor_fri_txt'][0]!=""){
                    $content['Friday']['morning'][$k]['content'] = $data['mor_fri_txt'][$k];
                    $content['Friday']['morning'][$k]['time'] = $data['mor_fri_startTime'][$k]."-".$data['mor_fri_endTime'][$k];
                    $content['Friday']['morning'][$k]['startTime'] = $data['mor_fri_startTime'][$k];
                    $content['Friday']['morning'][$k]['endTime'] = $data['mor_fri_endTime'][$k];
                }
            }
            foreach($data['after_fri_txt'] as $k=>$v){
                if($data['after_fri_txt'][0]!=""){
                    $content['Friday']['afternoon'][$k]['content'] = $data['after_fri_txt'][$k];
                    $content['Friday']['afternoon'][$k]['time'] = $data['after_fri_startTime'][$k]."-".$data['after_fri_endTime'][$k];
                    $content['Friday']['afternoon'][$k]['startTime'] = $data['after_fri_startTime'][$k];
                    $content['Friday']['afternoon'][$k]['endTime'] = $data['after_fri_endTime'][$k];
                }
            }
            foreach($data['mor_fes_txt'] as $k=>$v){
                if($data['mor_fes_txt'][0]!=""){
                    $content['Saturday']['morning'][$k]['content'] = $data['mor_fes_txt'][$k];
                    $content['Saturday']['morning'][$k]['time'] = $data['mor_fes_startTime'][$k]."-".$data['mor_fes_endTime'][$k];
                    $content['Saturday']['morning'][$k]['startTime'] = $data['mor_fes_startTime'][$k];
                    $content['Saturday']['morning'][$k]['endTime'] = $data['mor_fes_endTime'][$k];
                }
            }
            foreach($data['after_fes_txt'] as $k=>$v){
                if($data['after_fes_txt'][0]!=""){
                    $content['Saturday']['afternoon'][$k]['content'] = $data['after_fes_txt'][$k];
                    $content['Saturday']['afternoon'][$k]['time'] = $data['after_fes_startTime'][$k]."-".$data['after_fes_endTime'][$k];
                    $content['Saturday']['afternoon'][$k]['startTime'] = $data['after_fes_startTime'][$k];
                    $content['Saturday']['afternoon'][$k]['endTime'] = $data['after_fes_endTime'][$k];
                }
            }
            foreach($data['mor_sun_txt'] as $k=>$v){
                if($data['mor_sun_txt'][0]!=""){
                    $content['Sunday']['morning'][$k]['content'] = $data['mor_sun_txt'][$k];
                    $content['Sunday']['morning'][$k]['time'] = $data['mor_sun_startTime'][$k]."-".$data['mor_sun_endTime'][$k];
                    $content['Sunday']['morning'][$k]['startTime'] = $data['mor_sun_startTime'][$k];
                    $content['Sunday']['morning'][$k]['endTime'] = $data['mor_sun_endTime'][$k];
                }
            }
            foreach($data['after_sun_txt'] as $k=>$v){
                if($data['after_sun_txt'][0]!=""){
                    $content['Sunday']['afternoon'][$k]['content'] = $data['after_sun_txt'][$k];
                    $content['Sunday']['afternoon'][$k]['time'] = $data['after_sun_startTime'][$k]."-".$data['after_sun_endTime'][$k];
                    $content['Sunday']['afternoon'][$k]['startTime'] = $data['after_sun_startTime'][$k];
                    $content['Sunday']['afternoon'][$k]['endTime'] = $data['after_sun_endTime'][$k];
                }
            }
            $schoolInfo = db('school')->where('schoolid',$schoolid)->find();
            $admininfo = $this->getAdminInfo();
            $data = array(
                'schoolid' => $_POST['schoolid'],
                'classid' => input('post.class_name'),
                'type' => input('post.school_type'),
                'content' => json_encode($content),
                'option_id' => $admininfo['admin_id'],
                'admin_company_id' => $schoolInfo['admin_company_id'],
                'desc' => input('post.desc'),
                'addtime' => time()
            );
            $id = input('post.id');
            //验证数据  END
            $model_arrangement = model('Arrangement');
            $result = $model_arrangement->update1($data,$id);
            if ($result) {
                $this->success('编辑成功', 'Coursemanage/index');
            } else {
                $this->error('编辑失败');
            }
        }
    }


    /**
     * ajax操作
     */
    public function ajax() {
        $branch = input('param.branch');

        switch ($branch) {
            /**
             * 验证班级名是否重复
             */
            case 'check_class_name':
                $arrangement_model = Model('Arrangement');
                $condition['classid'] = input('param.class_name');
                $condition['schoolid'] = input('param.schoolid');
                $list = $arrangement_model->getOneInfo($condition);
                if (empty($list)) {
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
        }
    }

    /**
     * 重要提示，删除会员 要先确定删除店铺,然后删除会员以及会员相关的数据表信息。这个后期需要完善。
     */
    public function drop() {
        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_gid']!=5){
            $this->error(lang('ds_assign_right'));
        }
        $school_id = input('param.school_id');
        if (empty($school_id)) {
            $this->error(lang('param_error'));
        }
        $classes = db('class')->where(['schoolid'=>$school_id,'isdel'=>1])->limit(1)->find();
        if($classes){
            $this->error('该学校下存在正在使用的班级，不能删除，请将使用的班级移除后进行删除');
        }
        $model_school = Model('school');
        $result = $model_school->editSchool(array('isdel'=>2),array('schoolid'=>$school_id));
        if ($result) {
            $this->success(lang('ds_common_del_succ'), 'School/member');
        } else {
            $this->error('删除失败');
        }
    }

    public function course(){
        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_gid']!=5){
            $this->error(lang('ds_assign_right'));
        }
        $course_id = input('param.course_id');
        $model = Model('Arrangement');
        $course = $model->getOneById($course_id);
        $course['content'] = json_decode($course['content'],true);
        $this->assign('course', $course);
        $this->setAdminCurItem('course');
        return $this->fetch();
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '管理',
                'url' => url('School/Coursemanage/index')
            ),
        );
        if (request()->action() == 'add' || request()->action() == 'index') {
            $menu_array[] = array(
                'name' => 'add',
                'text' => '添加课程',
                'url' => url('School/Coursemanage/add')
            );
        }
        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit',
                'text' => '编辑',
                'url' => url('School/School/edit')
            );
        }
        return $menu_array;
    }

}

?>
