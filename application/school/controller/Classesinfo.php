<?php

namespace app\school\controller;

use think\Lang;
use think\Validate;

class Classesinfo extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'school/lang/zh-cn/school.lang.php');
    }

    public function index(){
        $class_id = input('param.class_id');
        if (empty($class_id)) {
            $this->error(lang('param_error'));
        }
        $model_class = Model('Classes');
        $class_array = $model_class->getClassInfo(array("classid"=>$class_id));
        $this->assign('class_array', $class_array);
        //学校名称
        $schoolname = db('school')->where('schoolid',$class_array['schoolid'])->find();
        $this->assign('schoolname', $schoolname['name']);
        //学校类型
        $schooltype = db('schooltype')->where('sc_enabled','1')->select();
        $typeids = explode(',',$schoolname['typeid']);
        foreach ($schooltype as $k=>$v){
            foreach ($typeids as $key=>$item){
                if($item ==$v['sc_id']){
                    $type[$item] = $v['sc_type'];
                }
            }
        }
        $this->assign('schooltype', $type);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    public function lists() {
        $model_student = model('Student');
        $condition = array();
        $condition['s_classid'] = input('param.class_id');
        $condition['s_del'] = 1;
        $student_list = $model_student->getStudentList($condition, 10);
        //家长主账号
        foreach ($student_list as $key=>$item) {
            $memberinfo = db('member')->where(array('member_id'=>$item['s_ownerAccount']))->find();
            $student_list[$key]['member'] = $memberinfo['member_name'];
        }
        $this->assign('page', $model_student->page_info->render());
        $this->assign('student_list', $student_list);
        $this->setAdminCurItem('lists');
        return $this->fetch();
    }

    public function camera() {
        $class_id = input('param.class_id');
        $model_camera = model('Camera');
        $cameraList = $model_camera->getCameraList(array('class_id'=>$class_id), 10);
        $this->assign('page', $model_camera->page_info->render());
        $this->assign('camera_list', $cameraList);
        $this->setAdminCurItem('camera');
        return $this->fetch();
    }


    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $class_id = $_GET['class_id'];
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '班级信息',
                'url' => url('School/Classesinfo/index',array('class_id'=>$class_id))
            ),
            array(
                'name' => 'lists',
                'text' => '班级所属的学生',
                'url' => url('School/Classesinfo/lists',array('class_id'=>$class_id))
            ),
            array(
                'name' => 'camera',
                'text' => '班级所属的摄像头',
                'url' => url('School/Classesinfo/camera',array('class_id'=>$class_id))
            ),

        );
        return $menu_array;
    }

}

?>
