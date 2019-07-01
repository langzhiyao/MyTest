<?php

namespace app\school\controller;

use think\Lang;
use think\Validate;

class Schoolinfo extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'school/lang/zh-cn/school.lang.php');
    }

    public function index(){
        $school_id = input('param.school_id');
        if (empty($school_id)) {
            $this->error(lang('param_error'));
        }
        $model_school = Model('school');
        $school_array = $model_school->getSchoolInfo(array("schoolid"=>$school_id));
        $this->assign('school_array', $school_array);
        //学校类型
        $schooltype = db('schooltype')->where('sc_enabled','1')->select();
        $typeids = explode(',',$school_array['typeid']);
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
        $model_class = model('Classes');
        $condition = array();
        $condition['schoolid'] = input('param.school_id');
        $condition['isdel'] = 1;
        $class_list = $model_class->getClasslList($condition, 10);
        //学校类型
        $schooltype = db('schooltype')->where('sc_enabled','1')->select();
        $this->assign('schooltype', $schooltype);
        //操作人
        foreach ($class_list as $key=>$item) {
            $admininfo = db('admin')->where(['admin_id'=>$item['option_id']])->find();
            $class_list[$key]['option_name'] = $admininfo['admin_name'];
        }
        $this->assign('page', $model_class->page_info->render());
        $this->assign('class_list', $class_list);
        $this->setAdminCurItem('list');
        return $this->fetch();
    }

    public function camera() {
        $schoolid = input('param.school_id');
        $model_camera = model('Camera');
        $cameraList = $model_camera->getCameraList(array('school_id'=>$schoolid), 10);
        $this->assign('page', $model_camera->page_info->render());
        $this->assign('cameraList', $cameraList);
        $this->setAdminCurItem('camera');
        return $this->fetch();
    }


    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $schoolid = $_GET['school_id'];
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '学校信息',
                'url' => url('School/Schoolinfo/index',array('school_id'=>$schoolid))
            ),
            array(
                'name' => 'list',
                'text' => '所属班级',
                'url' => url('School/Schoolinfo/lists',array('school_id'=>$schoolid))
            ),
            array(
                'name' => 'camera',
                'text' => '摄像头个数',
                'url' => url('School/Schoolinfo/camera',array('school_id'=>$schoolid))
            ),
        );
        return $menu_array;
    }

}

?>
