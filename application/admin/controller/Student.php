<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Student extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/school.lang.php');
        Lang::load(APP_PATH . 'admin/lang/zh-cn/admin.lang.php');
        Lang::load(APP_PATH . 'admin/lang/zh-cn/look.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    public function index() {
        if(session('admin_is_super') !=1 && !in_array(4,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $model_student = model('Student');
        $condition = array();
        //学生名字
        $studentname = input('param.studentname');
        if ($studentname) {
            $condition['s.s_name'] = array('like', "%" . $studentname . "%");
        }
        //学校
        $school = input('param.school');
        if ($school) {
            $condition['s.s_schoolid'] = $school;
        }
        $school_type = input('param.grade');
        if ($school_type) {
            $school_type = array($school_type);
            $condition['s.s_sctype'] = array('IN',$school_type);
        }
        $class_name = input('param.class');
        if ($class_name) {
            $condition['s.s_classid'] = $class_name;
        }
        //地区
        if(!empty($_GET['province'])){
            $province_id=input('param.province');
            $condition['s.s_provinceid']=$province_id;
        }
        if(!empty($_GET['city'])) {
            $city_id = input('param.o_cityid');
            $condition['s.s_cityid'] =$city_id;
        }
        if(!empty($_GET['area'])){
            $area_id = input('param.area');
            $condition['s.s_areaid'] =$area_id;
        }
        $condition['s.s_del'] = 1;
        $student_list = $model_student->getStudentList($condition, 15);
         //地区信息
        $region_list = db('area')->where('area_parent_id','0')->select();
        $condition_school['isdel'] = 1;
        $model_school = model('School');
        $scfield = 'schoolid,name';
        $school_list = $model_school->getAllAchool($condition_school,$scfield);

        $this->assign('region_list', $region_list);
        $this->assign('page', $model_student->page_info->render());
        $this->assign('student_list', $student_list);
        $this->assign('schoolList', $school_list);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    public function add() {
        if(session('admin_is_super') !=1 && !in_array(1,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        if (!request()->isPost()) {
            //地区信息
            $region_list = db('area')->where('area_parent_id','0')->select();
            $this->assign('region_list', $region_list);
            $this->setAdminCurItem('add');
            return $this->fetch();
        } else {
            $admininfo = $this->getAdminInfo();
            $schoolInfo = db('school')->where('schoolid',input('post.school'))->find();
            $model_student = model('Student');
            $data = array(
                's_name' => input('post.student_name'),
                's_sex' => input('post.member_sex'),
                's_classid' => input('post.class'),
                's_schoolid' => input('post.school'),
                's_sctype' => input('post.grade'),
                's_birthday' => input('post.student_birthday'),
                's_card' => input('post.student_idcard'),
                's_provinceid' => input('post.province'),
                's_cityid' => input('post.city'),
                's_areaid' => input('post.area'),
                's_region' => input('post.area_info'),
                's_remark' => input('post.student_desc'),
                'option_id' => $admininfo['admin_id'],
                'admin_company_id' => $schoolInfo['admin_company_id'],
                's_createtime' => date('Y-m-d H:i:s',time())
            );
            //获取班级识别码
            $class = db('class')->where(array('classid'=>input('post.class')))->field('classCard')->find();
            $data['classCard'] = $class['classCard'];

            //验证数据  END
            $result = $model_student->addStudent($data);
            if ($result) {
                $this->success(lang('school_student_add_succ'), 'Student/index');
            } else {
                $this->error(lang('school_student_add_fail'));
            }
        }
    }

    public function edit() {
        if(session('admin_is_super') !=1 && !in_array(3,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $student_id = input('param.student_id');
        if (empty($student_id)) {
            $this->error(lang('param_error'));
        }
        $model_student = Model('student');
        if (!request()->isPost()) {
            $condition['s_id'] = $student_id;
            $student_array = $model_student->getStudentInfo($condition);
            $this->assign('student_array', $student_array);
            //地区信息
            $region_list = db('area')->where('area_parent_id','0')->select();
            $this->assign('region_list', $region_list);
            $this->setAdminCurItem('edit');
            return $this->fetch();
        } else {
            $data = array(
                's_name' => input('post.student_name'),
                's_sex' => input('post.member_sex'),
                's_classid' => input('post.class'),
                's_schoolid' => input('post.school'),
                's_sctype' => input('post.grade'),
                's_birthday' => input('post.student_birthday'),
                's_card' => input('post.student_idcard'),
                's_provinceid' => input('post.province'),
                's_cityid' => input('post.city'),
                's_areaid' => input('post.area'),
                's_region' => input('post.area_info'),
                's_remark' => input('post.student_desc'),
                's_createtime' => date('Y-m-d H:i:s',time())
            );
            //获取班级识别码
            $class = db('class')->where(array('classid'=>input('post.class')))->field('classCard')->find();
            $data['classCard'] = $class['classCard'];
            //验证数据  END
            $result = $model_student->editStudent($data,array('s_id'=>$student_id));
            if ($result) {
                $this->success('编辑成功', 'Student/index');
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
             * 验证班级识别码是否正确
             */
            case 'check_unique_code':
                $classes = Model('classes');
                $condition['classid'] = input('param.class_name');
                $condition['classCard'] = input('param.classunique');
                $list = $classes->getClassInfo($condition);
                if (!empty($list)) {
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
            /**
             * 验证身份证是否重复（同一班级）
             */
            case 'check_user_cards':
                $model_student = model('Student');
                $condition['s_card'] = input('param.student_idcard');
                $condition['s_classid'] = input('param.class_name');
                $condition['s_del'] = 1;
                $list = $model_student->getStudentInfo($condition);
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
        if(session('admin_is_super') !=1 && !in_array(2,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $student_id = input('param.student_id');
        if (empty($student_id)) {
            $this->error(lang('param_error'));
        }
        $model_student = Model('student');
        $result = $model_student->editStudent(array('s_del'=>2),array('s_id'=>$student_id));
        if ($result) {
            $this->success(lang('ds_common_del_succ'), 'Student/index');
        } else {
            $this->error('删除失败');
        }
    }

    public function fand_schooltype(){
        $sc_id = intval(input('post.sc_id'));
        $Schooltype = model('Schooltype');
        $School = model('School');
        $schoolInfo = $School->getSchoolById($sc_id);
        if($schoolInfo){
            $Schooltype_list = $Schooltype->get_sctype_List(array('sc_id'=>array('in',$schoolInfo['typeid'])));
            echo json_encode($Schooltype_list);
        }
    }

    public function fand_classname(){
        $ty_id = intval(input('post.ty_id'));
        $sc_id = intval(input('post.sc_id'));
        $where = ['isdel'=>1,'schoolid'=>$sc_id,'typeid'=>$ty_id];
        if($ty_id && $sc_id){
            $classList = db('class')->where($where)->select();
            echo json_encode($classList);
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '管理',
                'url' => url('Admin/Student/index')
            ),
        );
        if(session('admin_is_super') ==1 || in_array(1,$this->action )){
            if (request()->action() == 'add' || request()->action() == 'index') {
                $menu_array[] = array(
                    'name' => 'add',
                    'text' => '添加学生',
                    'url' => url('Admin/Student/add')
                );
            }
        }

        if (request()->action() == 'edit') {
            $student_id=trim($_GET['student_id']);
            $menu_array[] = array(
                'name' => 'edit',
                'text' => '编辑',
                'url' => url('Admin/Student/edit',array('student_id'=>$student_id))
            );
        }
        return $menu_array;
    }

}

?>
