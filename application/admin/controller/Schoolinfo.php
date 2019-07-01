<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Schoolinfo extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/admin.lang.php');
        Lang::load(APP_PATH . 'admin/lang/zh-cn/school.lang.php');
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
    //公司人员信息
    public function personnel(){
        $where = ' a.admin_del_status=1';
        $where .=' and a.admin_school_id='.$_GET['school_id'] ;
        $admin_list = db('admin')
            ->alias('a')
            ->join('__GADMIN__ g', 'g.gid = a.admin_gid', 'LEFT')
            ->join('__SCHOOL__ s', 's.schoolid = a.admin_school_id', 'LEFT')
            ->where($where)
            ->order('a.admin_login_time DESC')
            ->paginate(10,false,['query' => request()->param()]);
        $this->assign('admin_list', $admin_list->items());
        $this->assign('page', $admin_list->render());
        $this->setAdminCurItem('personnel');
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
     * 管理员添加
     */
    public function admin_add() {
        if(session('admin_is_super') !=1 && !in_array('1',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $admin_id = $this->admin_info['admin_id'];
        $admin_school_id = $_GET['school_id'];//添加账号所属学校
        $admin_company_id = $_GET['company_id'];//添加账号所属学校的所属公司
        if (!request()->isPost()) {
            //获取所创建的角色
            $gadmin_list = db('gadmin')->field('gid,school_id,gname')->where('school_id= '.$admin_school_id.' AND company_id='.$admin_company_id.' AND gid>5')->select();
            $this->assign('gadmin_list',$gadmin_list);
            $this->setAdminCurItem('admin_add');
            return $this->fetch('admin_add');
        } else {
            $model_admin = Model('admin');
            $param['admin_name'] = $_POST['admin_name'];
            $param['admin_password'] = md5($_POST['admin_password']);
            $param['create_uid'] = $admin_id;
            $param['admin_company_id'] = $admin_company_id;
            $param['admin_school_id'] = $admin_school_id;
            $param['admin_gid'] = trim($_POST['gid']);
            $rs = $model_admin->addAdmin($param);
            if ($rs) {
                $this->log(lang('ds_add').lang('limit_admin') . '[' . $_POST['admin_name'] . ']', 1);
                echo json_encode(array('message'=>lang('ds_common_save_succ'),'status'=>200));exit;
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }
    /**
     * 管理员修改
     */
    public function admin_edit() {
        if(session('admin_is_super') !=1 && !in_array('3',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $admin_school_id = $_GET['school_id'];//账号所属学校
        $admin_id = intval(input('param.admin_id'));
        if (request()->isPost()) {
            //没有更改密码
            if ($_POST['new_pw'] != '') {
                $data['admin_password'] = md5($_POST['new_pw']);
            }
            $data['admin_name'] = trim($_POST['admin_name']);
            $data['admin_gid'] = intval($_POST['gid']);
            $data['admin_phone'] = trim($_POST['admin_phone']);
            $data['admin_true_name'] = trim($_POST['admin_truename']);
            $data['admin_department'] = trim($_POST['admin_department']);
            $data['admin_description'] = trim($_POST['admin_description']);
            //查询管理员信息
            $admin_model = Model('admin');
            $result = $admin_model->updateAdmin($data,$admin_id);
            if ($result >=0) {
                $this->log(lang('ds_edit').lang('limit_admin') . '[ID:' . $admin_id . ']', 1);
                $this->success(lang('admin_edit_success'), url('Admin/schoolinfo/personnel?school_id='.$admin_school_id));
            } else {
                $this->error(lang('admin_edit_fail'), url('Admin/schoolinfo/admin_edit?admin_id='.$admin_id.'&school_id='.$admin_school_id));
            }
        } else {
            //查询用户信息
            $admin_model = Model('admin');
            $admin = $admin_model->getOneAdmin($admin_id);
            if (!is_array($admin) || count($admin) <= 0) {
                $this->error(lang('admin_edit_admin_error'), url('Admin/schoolinfo/personnel?school_id='.$admin_school_id));
            }
            //得到该公司创建的权限组
            $gadmin = db('gadmin')->field('gname,gid')->where("school_id = '".$admin_school_id."' AND gid>5")->select();
            $this->assign('gadmin', $gadmin);
            $this->assign('admin', $admin);
            $this->setAdminCurItem('personnel');
            return $this->fetch('admin_edit');
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
                'url' => url('Admin/Schoolinfo/index',array('school_id'=>$schoolid))
            ),
            array(
                'name' => 'personnel',
                'text' => '学校管理员列表',
                'url' => url('Admin/Schoolinfo/personnel',array('school_id'=>$schoolid))
            ),
            array(
                'name' => 'list',
                'text' => '所属班级',
                'url' => url('Admin/Schoolinfo/lists',array('school_id'=>$schoolid))
            ),
            array(
                'name' => 'camera',
                'text' => '摄像头个数',
                'url' => url('Admin/Schoolinfo/camera',array('school_id'=>$schoolid))
            ),
        );
        return $menu_array;
    }

}

?>
