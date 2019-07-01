<?php

namespace app\school\controller;

use think\Lang;
use think\Validate;

class Student extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'school/lang/zh-cn/school.lang.php');
        Lang::load(APP_PATH . 'school/lang/zh-cn/admin.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name = strtolower(end(explode('\\',__CLASS__)));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('school_admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    public function index() {
        if(session('school_admin_is_super') !=1 && !in_array(4,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $model_student = model('Student');
        $condition = array();

        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_id']!=1){
            if(!empty($admininfo['admin_school_id'])){
                $condition['s_schoolid'] = $admininfo['admin_school_id'];
            }else{
                $model_company = Model("Company");
                $condition = $model_company->getCondition($admininfo['admin_company_id'],"student");
            }
        }
        $studentname = input('param.studentname');//学生名字
        if ($studentname) {
            $condition['s_name'] = array('like', "%" . $studentname . "%");
        }
        $schooltype = input('param.school_type');//学校类型
        if ($schooltype) {
            $condition['s_sctype'] = $schooltype;
        }
        $class_name = input('param.class_name');
        if ($class_name) {
            $condition['s_classid'] = $class_name;
        }
        $school_name = input('param.school_name');
        if ($school_name) {
            $condition['s_schoolid'] = $school_name;
        }
        $student_status = input('param.student_status');//绑定状态
        if ($student_status==1) {
            $condition['s_ownerAccount'] = array('eq',0);
        }elseif($student_status==2){
            $condition['s_ownerAccount'] = array('neq',0);
        }
        $area_id = input('param.area_id');//地区
        if($area_id){
            $region_info = db('area')->where('area_id',$area_id)->find();
            if($region_info['area_deep']==1){
                $condition['s_provinceid'] = $area_id;
            }elseif($region_info['area_deep']==2){
                $condition['s_cityid'] = $area_id;
            }else{
                $condition['s_areaid'] = $area_id;
            }
        }
        $condition['s_del'] = 1;
        $student_list = $model_student->getStudentList($condition, 15);
         //地区信息
        $region_list = db('area')->where('area_parent_id','0')->select();
        $this->assign('region_list', $region_list);
        $address = array(
            'true_name' => '',
            'area_id' => '',
            'city_id' => '',
            'address' => '',
            'tel_phone' => '',
            'mob_phone' => '',
            'is_default' => '',
            'area_info'=>''
        );
        $this->assign('address', $address);
        //全部学校
        if($admininfo['admin_id']!=1){
            if(!empty($admininfo['admin_school_id'])){
                $condition_school['schoolid'] = $admininfo['admin_school_id'];
            }else{
                $model_company = Model("Company");
                $condition_school = $model_company->getCondition($admininfo['admin_company_id']);
            }
        }
        $condition_school['isdel'] = 1;
        $model_school = model('School');
        $scfield = 'schoolid,name';
        $school_list = $model_school->getAllAchool($condition_school,$scfield);
        $schoolLists = array_column($school_list,NULL,'schoolid');

        //全部班级
        $model_class = model('Classes');
        $condition_class['isdel'] = 1;
        $class_list = $model_class->getAllClasses($condition_class);
        $classLists = array_column($class_list,NULL,'classid');

        $model_schooltype = model('Schooltype');
        $schooltype = $model_schooltype->get_sctype_List(array('sc_enabled'=>1));
        $schooltypeList=array_column($schooltype,NULL,'sc_id');
        $this->assign('schooltype', $schooltype);

        foreach ($class_list as $k=>$v){
            $class_list[$k]['typename'] = $schooltypeList[$v['typeid']]['sc_type'];
        }
        foreach ($student_list as $k=>$v){
            $student_list[$k]['typename'] = $schooltypeList[$v['s_sctype']]['sc_type'];
            $student_list[$k]['classname'] = $classLists[$v['s_classid']]['classname'];
            $student_list[$k]['schoolname'] = $schoolLists[$v['s_schoolid']]['name'];
        }
        $this->assign('page', $model_student->page_info->render());
        $this->assign('student_list', $student_list);
        $this->assign('schoolList', $school_list);
        $this->assign('classList', $class_list);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    public function add() {
        if(session('school_admin_is_super') !=1 && !in_array(1,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        if (!request()->isPost()) {
            //地区信息
            $region_list = db('area')->where('area_parent_id','0')->select();
            $this->assign('region_list', $region_list);
            $address = array(
                'true_name' => '',
                'area_id' => '',
                'city_id' => '',
                'address' => '',
                'tel_phone' => '',
                'mob_phone' => '',
                'is_default' => '',
                'area_info'=>''
            );
            $this->assign('address', $address);
            //学校类型
            $model_schooltype = model('Schooltype');
            $schooltype = $model_schooltype->get_sctype_List(array('sc_enabled'=>1));
            $this->assign('schooltype', $schooltype);
            $this->setAdminCurItem('add');
            return $this->fetch();
        } else {
            $admininfo = $this->getAdminInfo();
            $schoolInfo = db('school')->where('schoolid',input('post.order_state'))->find();
            $model_student = model('Student');
            $data = array(
                's_name' => input('post.student_name'),
                's_sex' => input('post.member_sex'),
                's_classid' => input('post.class_name'),
                's_schoolid' => input('post.order_state'),
                's_sctype' => input('post.classtype'),
                's_birthday' => input('post.student_birthday'),
                's_card' => input('post.student_idcard'),
                's_areaid' => input('post.area_id'),
                's_region' => input('post.area_info'),
                's_remark' => input('post.student_desc'),
                'option_id' => $admininfo['admin_id'],
                'admin_company_id' => $schoolInfo['admin_company_id'],
                's_createtime' => date('Y-m-d H:i:s',time())
            );
            $city_id = db('area')->where('area_id',input('post.area_id'))->find();
            $data['s_cityid'] = $city_id['area_parent_id'];
            $province_id = db('area')->where('area_id',$city_id['area_parent_id'])->find();
            $data['s_provinceid'] = $province_id['area_parent_id'];
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
        if(session('school_admin_is_super') !=1 && !in_array(3,$this->action )){
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
            $address = array(
                'true_name' => '',
                'area_id' => '',
                'city_id' => '',
                'address' => '',
                'tel_phone' => '',
                'mob_phone' => '',
                'is_default' => '',
                'area_info'=>''
            );
            $this->assign('address', $address);
            //学校名称
            $schoolList = db('school')->where('areaid',$student_array['s_areaid'])->select();
            $this->assign('schoolList', $schoolList);
            //学校类型
            $Schooltype = model('Schooltype');
            $School = model('School');
            $schoolInfo = $School->getSchoolById($student_array['s_schoolid']);
            $typeInfo = $Schooltype->get_sctype_List(array('sc_id'=>array('in',$schoolInfo['typeid'])));
            $this->assign('typeInfo', $typeInfo);
            //班级名称
            $classInfo = db('class')->where(array('schoolid'=>$student_array['s_schoolid'],'typeid'=>$student_array['s_sctype']))->select();
            $this->assign('classInfo', $classInfo);
            $this->setAdminCurItem('edit');
            return $this->fetch();
        } else {
            $data = array(
                's_name' => input('post.student_name'),
                's_sex' => input('post.member_sex'),
                's_classid' => input('post.class_name'),
                's_schoolid' => input('post.order_state'),
                's_sctype' => input('post.classtype'),
                's_birthday' => input('post.student_birthday'),
                's_card' => input('post.student_idcard'),
                's_areaid' => input('post.area_id'),
                's_region' => input('post.area_info'),
                's_remark' => input('post.student_desc'),
                's_createtime' => date('Y-m-d H:i:s',time())
            );
            $city_id = db('area')->where('area_id',input('post.area_id'))->find();
            $data['s_cityid'] = $city_id['area_parent_id'];
            $province_id = db('area')->where('area_id',$city_id['area_parent_id'])->find();
            $data['s_provinceid'] = $province_id['area_parent_id'];
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
        if(session('school_admin_is_super') !=1 && !in_array(2,$this->action )){
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
                'url' => url('School/Student/index')
            ),
        );
        if(session('school_admin_is_super') ==1 || in_array(1,$this->action )){
            if (request()->action() == 'add' || request()->action() == 'index') {
                $menu_array[] = array(
                    'name' => 'add',
                    'text' => '添加学生',
                    'url' => url('School/Student/add')
                );
            }
        }

        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit',
                'text' => '编辑',
                'url' => url('School/Student/edit')
            );
        }
        return $menu_array;
    }

}

?>
