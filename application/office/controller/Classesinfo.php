<?php

namespace app\office\controller;

use think\Lang;
use think\Model;
use think\Validate;

class Classesinfo extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'office/lang/zh-cn/school.lang.php');
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
        $where['classid']=$class_id;
        $data=$this->_conditions($where);
        $cameraList = $model_camera->getCameraList($data, 10);
        $this->assign('page', $model_camera->page_info->render());
        $this->assign('camera_list', $cameraList);
        $this->setAdminCurItem('camera');
        return $this->fetch();
    }

    /**
     * 摄像头查询过滤
     * @创建时间   2018-11-03T00:39:28+0800
     * @param  [type]                   $where [description]
     * @return [type]                          [description]
     */
    public function _conditions($where){
        $res = array();
        $name = false;
        $class_model = Model('Classes');
        if (isset($where['classid']) && !empty($where['classid']) ) {
            $class = $class_model->getClassInfo(array('classid'=>$where['classid']));
            $condition['parentid'] = $class['res_group_id'];
        }
        return $condition;
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
                'url' => url('Office/Classesinfo/index',array('class_id'=>$class_id))
            ),
            array(
                'name' => 'lists',
                'text' => '班级所属的学生',
                'url' => url('Office/Classesinfo/lists',array('class_id'=>$class_id))
            ),
            array(
                'name' => 'camera',
                'text' => '班级所属的摄像头',
                'url' => url('Office/Classesinfo/camera',array('class_id'=>$class_id))
            ),

        );
        return $menu_array;
    }

}

?>
