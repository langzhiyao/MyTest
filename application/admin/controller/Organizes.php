<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;


class Organizes extends AdminControl
{
    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/admin.lang.php');
    }
    /**
     * 获取分/子公司查看列表
     */
    protected function getAdminItemList() {
        $oid=$_GET['o_id'];
        $menu_array = array(
            array(
                'name' => 'company',
                'text' => '公司信息',
                'url' => url('Admin/Organizes/company',array('o_id'=>$oid))
            ),
            array(
                'name' => 'personnel',
                'text' => '公司人员信息',
                'url' => url('Admin/Organizes/personnel',array('o_id'=>$oid))
            ),
            array(
                'name' => 'schoolnum',
                'text' => '发展学校个数',
                'url' => url('Admin/Organizes/schoolnum',array('o_id'=>$oid))
            ),
            array(
                'name' => 'cameranum',
                'text' => '摄像头个数',
                'url' => url('Admin/Organizes/cameranum',array('o_id'=>$oid))
            ),
            array(
                'name' => 'membernum',
                'text' => '所属会员总数',
                'url' => url('Admin/Organizes/membernum',array('o_id'=>$oid))
            ),
            array(
                'name' => 'studentnum',
                'text' => '绑定学生总数',
                'url' => url('Admin/Organizes/studentnum',array('o_id'=>$oid))
            ),
            array(
                'name' => 'money',
                'text' => '资金交易',
                'url' => url('Admin/Organizes/money',array('o_id'=>$oid))
            ),
        );
        return $menu_array;
    }
    //分子公司信息
    public function company()
    {
        //分子公司列表
        $model_organize = Model('company');
        $oid=$_GET['o_id'];
        $organize_info = $model_organize->getOrganizeInfo(array('o_id' => $oid));
        $model_admin = Model('admin');
        $condition = array();
        $condition['admin_company_id']=$oid;
        $admin=$model_admin->getAdminList($condition);
        foreach($admin as $v){
            $admin_id.=$v['admin_id'].',';
        }
        $admin_id=substr($admin_id, 0, -1);
        $order = Model('Packagesorder');
        $condition = array();
        $condition['pkg_type']=1;
        $condition['delete_state'] = 0;
        $condition['order_state']=40;
        $condition['option_id']=array('in',$admin_id);
        $packlist=$order->getOrderList($condition);
        //看孩订单价格
        $num=0;
        foreach($packlist as $v){
            $num=$num+$v['order_amount'];
        }
        $this->assign('num',$num);
        $this->assign('organize_info', $organize_info);
        $this->setAdminCurItem('company');
        return $this->fetch();
    }
    //公司人员信息
    public function personnel(){
        $where = ' a.admin_del_status=1';
        $where .=' and a.admin_company_id='.$_GET['o_id'] ;
        $admin_list = db('admin')
                        ->alias('a')
                        ->join('__GADMIN__ g', 'g.gid = a.admin_gid', 'LEFT')
                        ->join('__COMPANY__ o', 'o.o_id = a.admin_company_id', 'LEFT')
                        ->where($where)
                        ->order('a.admin_login_time DESC')
                        ->paginate(10,false,['query' => request()->param()]);
        //获取该公司的超级管理员权限
        $company_role= db('company')
            ->alias('c')
            ->join('__GADMIN__ g', 'g.gid = c.o_role', 'LEFT')
            ->where("c.o_id='".$_GET['o_id']."'")
            ->field('c.o_role,g.gname')
            ->find();
        $this->assign('company_role',$company_role);
        $this->assign('admin_list', $admin_list->items());
        $this->assign('page', $admin_list->render());
        $this->setAdminCurItem('personnel');
        return $this->fetch();
    }
    //发展学校个数
    public function schoolnum(){
        $oid=$_GET['o_id'];
        $model_admin = Model('admin');
        $condition = array();
        $condition['admin_company_id']=$oid;
        $admin=$model_admin->getAdminList($condition);
        $model_school = model('School');
        $conditions = array();
        $list=array();
        foreach($admin as $v){
            $conditions['option_id']=$v['admin_id'];
            $conditions['isdel']=1;
            $list+=$model_school->getSchoolList($condition);
        }
        $num=count($list);
        $this->assign('num',$num);
        $this->assign('school', $list);
        $this->setAdminCurItem('schoolnum');
        return $this->fetch();
    }
    //所属摄像头个数
    public function cameranum(){
        $oid=$_GET['o_id'];
        $model_admin = Model('admin');
        $condition = array();
        $condition['admin_company_id']=$oid;
        $admin=$model_admin->getAdminList($condition);
        $model_school = model('School');
        $conditions = array();
        $list=array();
        foreach($admin as $v){
            $conditions['option_id']=$v['admin_id'];
            $conditions['isdel']=1;
            $list+=$model_school->getSchoolList($condition);
        }
        $model_camera = model('Camera');
        foreach($list as $k=>$v){
            $where['schoolid']=$v['schoolid'];
            $datas=$this->_conditions($where);
            if(empty($data)){
                $data=!empty($datas)?$datas:'';
            }else{
                if(!empty($datas['parentid'][1])) {
                    foreach ($datas['parentid'][1] as $v1) {
                        $data['parentid'][1][] = $v1;
                    }
                }
            }
        }
        if(!empty($data)){
            $cameraList = $model_camera->getCameraList($data, 10);
            $this->assign('page', $model_camera->page_info->render());
            $this->assign('cameraList', $cameraList);
        }

        $this->setAdminCurItem('cameranum');
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
        if (isset($where['classname']) && !empty($where['classname']) ) {
            $classname = $where['classname'];
            unset($where['classname']);
        }
        $where['res_group_id'] =array('gt',0);
        $Schoollist = $School->getAllAchool($where,'res_group_id');
        // p($where);exit;
        if (!empty($classname)) {
            $where['classname'] = array('like','%'.$classname.'%');
            unset($Schoollist);
        }
        $res = array();
        $Classlist = $Class->getAllClasses($where,'res_group_id');
        $sc_resids=array_column($Schoollist, 'res_group_id');
        if ($sc_resids) {
            $res = $sc_resids;
//            array_push($res, $sc_resids);
        }
        $cl_resids=array_column($Classlist, 'res_group_id');
        if ($cl_resids) {
//            array_push($res, $cl_resids);
            if(empty($res)){
                $res = $cl_resids;
            }else{
                $res = array_merge($res,$cl_resids);
            }
        }
        $ids = array_merge($sc_resids,$cl_resids);
        if ($ids) {
            return $ids;
        }else{
            return $res;
        }
    }
    //所属会员数
    public function membernum(){
        $oid=$_GET['o_id'];
        $model_admin = Model('admin');
        $condition = array();
        $condition['admin_company_id']=$oid;
        $admin=$model_admin->getAdminList($condition);
        $model_school = model('School');
        $conditions = array();
        $list=array();
        foreach($admin as $v){
            $conditions['option_id']=$v['admin_id'];
            $conditions['isdel']=1;
            $list+=$model_school->getAllAchool($conditions);
        }
        foreach($list as $v){
            $schoolid.=$v['schoolid'].',';
        }
        $schoolid=substr($schoolid, 0, -1);
        if($schoolid!='') {
            $where['s_del'] = 1;
            $where['s_schoolid']=array('in',$schoolid);
            $model_student = model('Student');
            $students= $model_student->getAllStudent($where);
            $member = Model('member');
            foreach($students as $v){
                if(!empty($v['s_ownerAccount'])) {
                    $wherees['is_owner']=$v['s_ownerAccount'];
                    $res=$member->getMemberList($wherees);
                    if(!in_array($v['s_ownerAccount'],$id)) {
                        $id[] = $v['s_ownerAccount'];
                    }
                    if(!empty($res)){
                        foreach($res as $v){
                            if(!in_array($v['member_id'],$id)) {
                                $id[] = $v['member_id'];
                            }
                        }
                    }
                }
            }
            $member_id=implode(",",$id);
            $wheres['is_del'] = 1;
            $wheres['member_id'] = array('in', $member_id);
            $member_list = $member->getMemberList($wheres, '*',15);
            $members=$member->getMemberList($wheres);
            foreach ($member_list as $k=>$v) {
                $classinfo = db('class')->where('classid', $v['classid'])->find();
                $member_list[$k]['classname'] = $classinfo['classname'];
                $school = db('school')->where('schoolid', $v['schoolid'])->find();
                $member_list[$k]['schoolname'] = $school['name'];
            }
            $this->assign('count',count($members));
            $this->assign('page', $member->page_info->render());
        }else{
            $member_list=array();
            $this->assign('count',0);
        }
        $this->assign('member_list', $member_list);
        $this->setAdminCurItem('membernum');
        return $this->fetch();
    }
    //绑定学生数
    public function studentnum(){
        $oid=$_GET['o_id'];
        $model_admin = Model('admin');
        $condition = array();
        $condition['admin_company_id']=$oid;
        $admin=$model_admin->getAdminList($condition);
        $model_school = model('School');
        $conditions = array();
        $list=array();
        foreach($admin as $v){
            $conditions['option_id']=$v['admin_id'];
            $conditions['isdel']=1;
            $list+=$model_school->getAllAchool($conditions);
        }
        foreach($list as $v){
            $schoolid.=$v['schoolid'].',';
        }
        $schoolid=substr($schoolid, 0, -1);
        $where['s_del'] = 1;
        $where['s_schoolid']=array('in',$schoolid);
        $model_student = model('Student');
        $student_list = $model_student->getStudentList($where, 15);
        $students= $model_student->getAllStudent($where);
        foreach ($student_list as $k=>$v){
            $schooltype = db('schooltype')->where('sc_id',$v['s_sctype'])->find();
            $student_list[$k]['typename'] = $schooltype['sc_type'];
            $classinfo = db('class')->where('classid',$v['s_classid'])->find();
            $student_list[$k]['classname'] = $classinfo['classname'];
            $school = db('school')->where('schoolid',$v['s_schoolid'])->find();
            $student_list[$k]['schoolname'] = $school['name'];
            $member=db('member')->where('member_id',$v['s_ownerAccount'])->find();
            $student_list[$k]['member_name']=$member['member_name'];
        }
        $this->assign('page', $model_student->page_info->render());
        $this->assign('student_list', $student_list);
        $this->assign('count',count($students));
        $this->setAdminCurItem('studentnum');
        return $this->fetch();
    }
    //资金交易
    public function money(){
        $oid = $_GET['o_id'];
        $company = db("company")->alias("c")
            ->join('__COMPANYBANKS__ com','c.o_id = com.company_id','LEFT')
            ->field("c.total_amount,c.freeze_amount,c.o_role,c.o_provinceid,c.o_cityid,c.o_areaid,com.bank_name,com.bank_card")
            ->where(array("o_id"=>$oid))->find();
        $condition = array();
        //1，县区代理；2，省级代理；3，市级代理；4，特约代理
        if($company['o_role']==1){
            $condition['member_provinceid'] = $company['o_provinceid'];
            $condition['member_cityid'] = $company['o_cityid'];
            $condition['member_areaid'] = $company['o_areaid'];
        }elseif($company['o_role']==2){
            $condition['member_provinceid'] = $company['o_provinceid'];
        }elseif($company['o_role']==3){
            $condition['member_provinceid'] = $company['o_provinceid'];
            $condition['member_cityid'] = $company['o_cityid'];
        }
        $result = db('adminpdlog')->where($condition)->select();
        $this->assign('company', $company);
        $this->assign('result', $result);
        $this->setAdminCurItem('money');
        return $this->fetch();
    }
    /**
     * 管理员添加
     */
    public function admin_add() {
        if(session('admin_is_super') !=1 && !in_array('1',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $admin_id = $this->admin_info['admin_id'];
        $admin_company_id = $_GET['o_id'];//添加账号所属公司
        if (!request()->isPost()) {
            //获取公司角色ID
            $company_role_id = db('company')->where('o_id="'.$admin_company_id.'"')->value('o_role');
            //获取所创建的角色
            $gadmin_list = db('gadmin')->field('gid,company_id,gname')->where('company_id= '.$admin_company_id.' AND gid>5')->select();
            $this->assign('gadmin_list',$gadmin_list);
            $this->assign('company_role_id',$company_role_id);
            $this->setAdminCurItem('admin_add');
            return $this->fetch('admin_add');
        } else {
            $model_admin = Model('admin');
            $param['admin_name'] = $_POST['admin_name'];
            $param['admin_password'] = md5($_POST['admin_password']);
            $param['create_uid'] = $admin_id;
            $param['admin_company_id'] = $admin_company_id;
            $param['admin_gid'] = trim($_POST['gid']);
            $rs = $model_admin->addAdmin($param);
            if ($rs) {
                $this->log(lang('ds_add').lang('limit_admin') . '[' . $_POST['admin_name'] . ']', 1);
//                $this->success(lang('ds_common_save_succ'), url('Admin/Company/index'));
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
        $admin_company_id = $_GET['o_id'];//修改账号所属公司
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
                $this->success(lang('admin_edit_success'), url('Admin/organizes/personnel?o_id='.$admin_company_id));
            } else {
                $this->error(lang('admin_edit_fail'), url('Admin/organizes/admin_edit?admin_id='.$admin_id.'&o_id='.$admin_company_id));
            }
        } else {
            //查询用户信息
            $admin_model = Model('admin');
            $admin = $admin_model->getOneAdmin($admin_id);
            if (!is_array($admin) || count($admin) <= 0) {
                $this->error(lang('admin_edit_admin_error'), url('Admin/organizes/personnel?o_id='.$admin_company_id));
            }
            //获取该公司的超级管理员权限
            $company_role_id = db('company')->where("o_id='".$admin_company_id."'")->value('o_role');
            //得到该公司创建的权限组
            $gadmin = db('gadmin')->field('gname,gid')->where("company_id = '".$admin_company_id."' AND gid>5")->select();
            $this->assign('gadmin', $gadmin);
            $this->assign('company_role_id',$company_role_id);
            $this->assign('admin', $admin);
            $this->setAdminCurItem('personnel');
            return $this->fetch('admin_edit');
        }
    }
}