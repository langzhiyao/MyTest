<?php

namespace app\office\controller;

use think\Lang;
use think\Validate;

class Schoolinfo extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'Office/lang/zh-cn/school.lang.php');
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
        $where['schoolid']=$schoolid;
        $data=$this->_conditions($where);
        $cameraList = $model_camera->getCameraList($data, 10);
        $this->assign('page', $model_camera->page_info->render());
        $this->assign('cameraList', $cameraList);
        $this->setAdminCurItem('camera');
        return $this->fetch();
    }

    public function position(){
        $schoolid = input('param.school_id');
        $Model = model('Position');
        $positionlist = $Model->getpositionClass(['p.school_id'=>$schoolid],10);
        $this->assign('page', $Model->page_info->render());
        $this->assign('positionList', $positionlist);
        $this->assign('positionCount', $Model->getpositionCount(['school_id'=>$schoolid]));
        $this->setAdminCurItem('position');
        return $this->fetch();

    }

    

    public function getCameraNum(){
        //查询摄像头数量
        $pid = input('param.pid');
        $Model = model('Position');
        $position = $Model->getOneById($pid);
        //更新摄像头数量
        //
        echo json_encode(['num'=>3,'msg'=>'已更新']);
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
        if (isset($where['schoolid']) && $where['schoolid'] != 0 ) {
            $school = $this->getResGroupIds(array('schoolid'=>$where['schoolid']));
            $name = 'true';
            if ($school) {
                $res=array_merge($res, $school);
            }
        }
        if ($name == 'true') {
            $condition['parentid'] = array('in',$res);
        }
        return $condition;
    }
    /**
     * 查询学校和班级摄像头
     * @创建时间   2018-11-03T00:39:48+0800
     * @param  [type]                   $where [description]
     * @return [type]                          [description]
     */
    public function getResGroupIds($where){
        $School = model('School');
        $Class = model('Classes');
        $classname = '';
        if (isset($where['classid']) && !empty($where['classid']) ) {
            $classname = $where['classid'];
            unset($where['classid']);
        }
        $where['res_group_id'] =array('gt',0);
        $Schoollist = $School->getAllAchool($where,'res_group_id');
        if (!empty($classid)) {
            $where['classid'] = $classid;
        }
        $res = array();
        $Classlist = $Class->getAllClasses($where,'res_group_id');
        $sc_resids=array_column($Schoollist, 'res_group_id');
        if ($sc_resids) {
            array_push($res, $sc_resids);
        }
        $cl_resids=array_column($Classlist, 'res_group_id');
        if ($cl_resids) {
            array_push($res, $cl_resids);
        }
        $ids = array_merge($sc_resids,$cl_resids);
        if ($ids) {
            return $ids;
        }else{
            return $res;
        }
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
                'url' => url('Office/Schoolinfo/index',array('school_id'=>$schoolid))
            ),
            array(
                'name' => 'list',
                'text' => '所属班级',
                'url' => url('Office/Schoolinfo/lists',array('school_id'=>$schoolid))
            ),
            array(
                'name' => 'camera',
                'text' => '摄像头个数',
                'url' => url('Office/Schoolinfo/camera',array('school_id'=>$schoolid))
            ),
            array(
                'name' => 'position',
                'text' => '教学楼位置',
                'url' => url('Office/Schoolinfo/position',array('school_id'=>$schoolid))
            ),
        );
        return $menu_array;
    }

}

?>
