<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Classes extends AdminControl {

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
        $model_class = model('Classes');
        $condition = array();
        //学校名称
        $classname = input('param.school_index_classname');
        if ($classname) {
            $condition['c.classname'] = array('like', "%" . $classname . "%");
        }
        //学校
        $school = input('param.school');
        if ($school) {
            $condition['c.schoolid'] = $school;
        }
        $school_type = input('param.grade');
        if ($school_type) {
            $school_type = array($school_type);
            $condition['c.typeid'] = array('IN',$school_type);
        }
        $class_name = input('param.class');
        if ($class_name) {
            $condition['c.classid'] = $class_name;
        }

        //地区
        if(!empty($_GET['province'])){
            $province_id=input('param.province');
            $condition['c.school_provinceid']=$province_id;
        }
        if(!empty($_GET['city'])) {
            $city_id = input('param.o_cityid');
            $condition['c.school_cityid'] =$city_id;
        }
        if(!empty($_GET['area'])){
            $area_id = input('param.area');
            $condition['c.school_areaid'] =$area_id;
        }
        $condition['c.isdel'] = 1;
        $class_list = $model_class->getClasslList($condition, 15);
        // 地区信息
        $region_list = db('area')->where('area_parent_id','0')->select();
        //学校
        $condition_school['isdel'] = 1;
        $model_school = model('School');
        $school_list = $model_school->getAllAchool($condition_school,'schoolid,name');
        $this->assign('schoolList', $school_list);
        $this->assign('region_list', $region_list);
        $this->assign('page', $model_class->page_info->render());
        $this->assign('class_list', $class_list);
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
            $schoolInfo = db('school')->where('schoolid', input('post.order_state'))->find();
            $model_classes = model('Classes');
            $position_id = input('post.position');
            if (empty($position_id)) {
                $this->error('请绑定房间位置');
            }
            $data = array(
                'typeid' => input('post.grade'),
                'schoolid' => input('post.school'),
                'position_id' => $position_id,
                'classname' => input('post.school_class_name'),
                'desc' => input('post.class_desc'),
                'option_id' => $admininfo['admin_id'],
                'admin_company_id' => $schoolInfo['admin_company_id'],
                'createtime' => date('Y-m-d H:i:s', time())
            );
            $schoolinfo = db('school')->find(array("schoolid" => input('post.school')));
            $data['school_provinceid'] = $schoolinfo['provinceid'];
            $data['school_cityid'] = $schoolinfo['cityid'];
            $data['school_areaid'] = $schoolinfo['areaid'];
            $data['school_region'] = $schoolinfo['region'];
            //学校识别码
            $classcard = $schoolInfo['schoolCard'] . ($model_classes->getNumber($schoolInfo['schoolCard']));
            $data['classCard'] = $classcard;
            //生成二维码
            import('qrcode.index', EXTEND_PATH);
            $PhpQRCode = new \PhpQRCode();
            $PhpQRCode->set('pngTempDir', BASE_UPLOAD_PATH . DS . ATTACH_STORE . DS . 'class' . DS . $schoolInfo['schoolCard'] . DS);
            // 生成商品二维码
            $PhpQRCode->set('date', $classcard);
            $PhpQRCode->set('pngTempName', $classcard . '.png');
            $qr = $PhpQRCode->init();
            $data['qr'] = '/home/store/class/' . $schoolInfo['schoolCard'] . '/' . $qr;
            //验证数据  END
            //判断位置是否被绑定
            $is_bind = db('position')->where(array('position_id' => $position_id))->find();
            if (!empty($is_bind)) {
                if ($is_bind['is_bind'] == 1) {
                    //更改房间位置状态
                    $now = db('position')->where(array('position_id' => $position_id))->update(array('is_bind' => 2, 'create_time' => time()));
                    $result = $model_classes->addClasses($data);
                    if ($result && $now) {
                        $model_classes->commit();
                        $this->success('保存成功', 'Classes/index');
                    } else {
                        $model_classes->rollback();
                        $this->error('保存失败', 'Classes/add');
                    }
                } else {
                    //更改房间位置状态
                    $old_result = $model_classes->editClass(array('position_id' => $position_id), array('position_id' => 0));
                    $result = $model_classes->addClasses($data);
                    if ($result && $old_result) {
                        $model_classes->commit();
                        $this->success('更换成功', 'Classes/index');
                    } else {
                        $model_classes->rollback();
                        $this->error('更换失败', 'Classes/add');
                    }
                }
            }
        }
    }

    public function edit() {
        if(session('admin_is_super') !=1 && !in_array(3,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $class_id = input('param.class_id');
        if (empty($class_id)) {
            $this->error(lang('param_error'));
        }
        $model_class = Model('classes');
        if (!request()->isPost()) {
            $condition['classid'] = $class_id;
            $class_array = $model_class->getClassInfo($condition);
//            halt($class_array);
            //地区信息
            $region_list = db('area')->where('area_parent_id','0')->select();
            $this->assign('region_list', $region_list);
            $this->assign('class_array', $class_array);
            $this->setAdminCurItem('edit');
            return $this->fetch();
        } else {
            //开启事物
            $model_class->startTrans();

            //原来信息
            $res = $model_class->getClassInfo(array('classid'=>$class_id));

            $position_id = input('post.position');
            if(empty($position_id)){
                $this->error('请绑定房间位置');
            }
            $data = array(
                'typeid' => input('post.grade'),
                'position_id' => $position_id,
                'classname' => input('post.school_class_name'),
                'desc' => input('post.class_desc'),
                'createtime' => date('Y-m-d H:i:s',time())
            );
            //判断位置是否被绑定
            $is_bind = db('position')->where(array('position_id'=>$position_id))->find();
            if(!empty($is_bind)){
                if($is_bind['is_bind'] == 1){
                    //更改房间位置状态
                    $now = db('position')->where(array('position_id'=>$position_id))->update(array('is_bind'=>2,'create_time'=>time()));
                    $old_now = db('position')->where(array('position_id'=>$res['position_id']))->update(array('is_bind'=>1,'create_time'=>time()));
                    $result = $model_class->editClass($data,array('classid'=>$class_id));
                    if ($result && $now && $old_now) {
                        $model_class->commit();
                        $this->success('编辑成功', 'Classes/index');
                    } else {
                        $model_class->rollback();
                        $this->error('编辑失败','Classes/edit?class_id="'.$class_id.'"');
                    }
                }else{
                    //更改房间位置状态
                    $old_result = $model_class->editClass(array('position_id'=>$res['position_id']),array('position_id'=>$position_id));
                    $result = $model_class->editClass($data,array('classid'=>$class_id));
                    if ($result && $old_result) {
                        $model_class->commit();
                        $this->success('更换成功', 'Classes/index');
                    } else {
                        $model_class->rollback();
                        $this->error('更换失败','Classes/edit?class_id="'.$class_id.'"');
                    }
                }
            }else{
                $this->error('该房间位置不存在，无法绑定');
            }
        }
    }

    public function addstudent(){
        if(session('admin_is_super') !=1 && !in_array(11,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $class_id = input('get.class_id');
        if (!request()->isPost()) {
            $this->setAdminCurItem('addstudent');
            $this->assign('class_id', $class_id);
            return $this->fetch();
        } else {
            $admininfo = $this->getAdminInfo();
            $classinfo = db('class')->where('classid',$class_id)->find();
            $model_student = model('Student');
            $data = array(
                's_name' => input('post.student_name'),
                's_sex' => input('post.student_sex'),
                's_birthday' => input('post.student_birthday'),
                's_card' => input('post.student_idcard'),
                's_remark' => input('post.student_desc'),
                'option_id' => $admininfo['admin_id'],
                'admin_company_id' => $classinfo['admin_company_id'],
                's_createtime' => date('Y-m-d H:i:s',time())
            );
            $data['s_provinceid'] = $classinfo['school_provinceid'];
            $data['s_cityid'] = $classinfo['school_cityid'];
            $data['s_areaid'] = $classinfo['school_areaid'];
            $data['s_region'] = $classinfo['school_region'];
            $data['s_schoolid'] = $classinfo['schoolid'];
            $data['s_sctype'] = $classinfo['typeid'];
            $data['s_classid'] = $classinfo['classid'];
            //验证数据  END
            $result = $model_student->addStudent($data);
            if ($result) {
                $this->success(lang('school_class_studentadd_succ'), 'Classes/index');
            } else {
                $this->error(lang('school_class_studentadd_fail'));
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
            case 'check_user_name':
                $model_class = Model('classes');
                $condition['classname'] = input('param.class_name');
                $condition['schoolid'] = input('param.school_id');
                $condition['isdel'] = 1;
                $list = $model_class->getClassInfo($condition);
                if (empty($list)) {
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
        $class_id = input('param.class_id');
        if (empty($class_id)) {
            $this->error(lang('param_error'));
        }
        $students = db('student')->where(['s_classid'=>$class_id,'s_del'=>1])->limit(1)->find();
        if($students){
            $this->error('该班级下有学生在使用，不能删除，请将使用的学生移除后进行删除');
        }
        $model_class = Model('classes');
        $result = $model_class->editClass(array('isdel'=>2),array('classid'=>$class_id));
        if ($result) {
            $this->success(lang('ds_common_del_succ'), 'Classes/index');
        } else {
            $this->error('删除失败');
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
                'url' => url('Admin/Classes/index')
            ),
        );
        if(session('admin_is_super') ==1 || in_array(1,$this->action )){
            if (request()->action() == 'add' || request()->action() == 'index') {
                $menu_array[] = array(
                    'name' => 'add',
                    'text' => '添加班级',
                    'url' => url('Admin/Classes/add')
                );
            }
        }

        if (request()->action() == 'edit') {
            $class_id=trim($_GET['class_id']);
            $menu_array[] = array(
                'name' => 'edit',
                'text' => '编辑',
                'url' => url('Admin/Classes/edit',array('class_id'=>$class_id))
            );
        }
        if (request()->action() == 'addstudent') {
            $class_id=trim($_GET['class_id']);
            $menu_array[] = array(
                'name' => 'addstudent',
                'text' => '添加学生',
                'url' => url('Admin/Classes/addstudent',array('class_id'=>$class_id))
            );
        }
        return $menu_array;
    }

}

?>
