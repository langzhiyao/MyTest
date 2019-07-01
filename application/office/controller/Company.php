<?php

namespace app\office\controller;

use think\Lang;
use think\Validate;


class Company extends AdminControl {

    const EXPORT_SIZE = 1000;

    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'office/lang/zh-cn/admin.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
//        halt($perm_id);
        $this->action = $action = $this->get_role_perms(session('office_gid') ,$perm_id);
        $this->assign('action',$action);
    }
    /**
     *
     * 分子公司管理列表
     */
    public function index()
    {
        if(session('office_is_super') !=1 && !in_array(4,$this->action)){
            $this->error(lang('ds_assign_right'));
        }
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
        //分子公司列表
        $model_organize = Model('company');
        $condition = array();
        $condition['o_del']=1;
        //判断登陆账号的所属公司
        $adminUser = db('admin')->field('admin_company_id')->where('admin_id = "'.session("admin_id").'"')->find();
        if($adminUser['admin_company_id'] != 1){
            //查询所属地区公司
//            $condition['o_id'] = $adminUser['admin_company_id'];
            $company = db('company')->field('o_id,o_role,o_provinceid,o_cityid,o_areaid')->where('o_id="'.$adminUser['admin_company_id'].'"')->find();
            if(!empty($company)){
                $roleID = $company['o_role'];
                switch ($roleID){
                    case 1://县区代理
                        $condition['o_provinceid']=$company['o_provinceid'];
                        $condition['o_cityid']=$company['o_cityid'];
                        $condition['o_areaid']=$company['o_areaid'];
                        break;
                    case 2://省代理商
                        $condition['o_provinceid']=$company['o_provinceid'];
                        break;
                    case 3://市代理商
                        $condition['o_provinceid']=$company['o_provinceid'];
                        $condition['o_cityid']=$company['o_cityid'];
                        break;
                    case 4://特约代理商
                        $condition['o_id']=$company['o_id'];
                        break;
                }
            }else{
                $this->error('该公司不存在/已被删除',url('office/Dashboard/index'));
            }
        }

        if (!empty($_POST['search_organize_name'])) {
            $o_name=input('param.search_organize_name');
            $condition['o_name']=array('like', '%' . trim($o_name) . '%');
            $this->assign('search_organize_name',$o_name);
        }
        if(!empty($_POST['o_provinceid'])){
            $o_provinceid=input('param.o_provinceid');
            $condition['o_provinceid']=$o_provinceid;
            $this->assign('o_provinceid',$o_provinceid);
        }
        if(!empty($_POST['o_cityid'])) {
            if ($_POST['dep'] == 2 || $_POST['dep'] == 3){
                $o_cityid = input('param.o_cityid');
                $condition['o_cityid'] =$o_cityid;
                $this->assign('o_cityid', $o_cityid);
            }
        }
        if(!empty($_POST['area_id'])){
            if ($_POST['dep'] == 3) {
                $area_id = input('param.area_id');
                $condition['o_areaid'] =$area_id;
                $this->assign('area_id', $area_id);
            }
        }
        $organize_list = $model_organize->getOrganizeList($condition, "*",15);
        $this->assign('page', $model_organize->page_info->render());
        $this->assign('organize_list', $organize_list);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }
    /**
     * 获取分/子公司栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        if(session('office_is_super') !=1 && !in_array(1,$this->action)){
            $menu_array = array(
                array(
                    'name' => 'index',
                    'text' => '分/子（代理）公司管理',
                    'url' => url('office/Company/index')
                )
            );
        }else{
            $menu_array = array(
                array(
                    'name' => 'index',
                    'text' => '分/子（代理）公司管理',
                    'url' => url('office/Company/index')
                ),
                array(
                    'name' => 'add',
                    'text' => '添加',
                    'url' => url('office/Company/add')
                )
            );
        }

        if (request()->action() == 'edit') {
            $oid=$_GET['organize_id'];
            $menu_array[1] = array(
                'name' => 'organize_edit', 'text' => '编辑', 'url' => url('office/Company/edit',array('organize_id'=>$oid))
            );
        }
        return $menu_array;
    }
    /**
     * 子公司新增
    */
    public function add() {
        if(session('office_is_super') !=1 && !in_array(1,$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        if (request()->isPost()) {
            //提交表单
            //保存
            $input = array();
            $input['o_name'] = trim($_POST['o_name']);
            $input['o_role'] = intval($_POST['o_role']);
            $input['o_provinceid'] = intval($_POST['o_provinceid']);
            $input['o_cityid'] = intval($_POST['o_cityid']);
            $input['o_areaid'] = intval($_POST['area_id']);
            $input['o_area'] = trim($_POST['area_info']);
            $input['o_address'] = trim($_POST['o_address']);
            $input['o_phone'] = trim($_POST['o_phone']);
            $input['o_leading'] = trim($_POST['o_leading']);
            $input['o_enddate'] = trim($_POST['activity_end_date']);
            $input['o_createtime']=date('Y-m-d H:i:s',time());
            $input['o_remark'] = trim($_POST['o_remark']);
            $input['o_del']=1;
            //$input['is_child']=intval($_POST['is_child']);
            $activity = Model('company');
            $result = $activity->addOrganize($input);
            if ($result) {
                $this->log(lang('ds_add') . lang('ds_company') . '[' . $_POST['o_name'] . ']', 1);
                $this->success(lang('ds_common_save_succ'),'company/index');
            }
            else {
                $this->error(lang('ds_common_save_fail'));
            }
        } else {
            // 角色
            $gadmin = db('gadmin')->where('gid < 5')->order('sort ASC')->select();
            $this->assign('gadmin',$gadmin);
            //地区信息
            $region_list = db('area')->where('area_parent_id','0')->select();
            $this->assign('region_list', $region_list);
            $this->setAdminCurItem('add');
            return $this->fetch();
        }
    }
    /**
     * 子公司编辑
     */
    public function edit()
    {
        if(session('office_is_super') !=1 && !in_array(3,$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $model_organize = Model('company');
        if (request()->isPost()) {
            $where = array();
            $where['o_id'] = intval($_POST['o_id']);
            $update_array = array();
            $update_array['o_name'] = trim($_POST['o_name']);
            $update_array['o_role'] = intval($_POST['o_role']);
            if($_POST['o_role']==2){
                $update_array['o_cityid']=0;
                $update_array['o_areaid'] = 0;
            }else if($_POST['o_role']==3){
                $update_array['o_cityid'] = intval($_POST['city_id']);
                $update_array['o_areaid'] = 0;
            }else{
                $update_array['o_cityid'] = intval($_POST['city_id']);
                $update_array['o_areaid'] = intval($_POST['area_id']);
            }
            $update_array['o_provinceid'] = intval($_POST['o_provinceid']);
            $update_array['o_area'] = trim($_POST['area_info']);
            $update_array['o_address'] = trim($_POST['o_address']);
            $update_array['o_phone'] = trim($_POST['o_phone']);
            $update_array['o_leading'] = trim($_POST['o_leading']);
            $update_array['o_enddate'] = trim($_POST['activity_end_date']);
            $update_array['o_createtime'] = date('Y-m-d H:i:s', time());
            $update_array['o_remark'] = trim($_POST['o_remark']);
            $result = $model_organize->editOrganize($where, $update_array);
            if ($result) {
                $this->log(lang('ds_edit') . lang('ds_company') . '[' . $_POST['o_name'] . ']', 1);
                $this->success(lang('ds_common_save_succ'), 'company/index');
            } else {
                $this->log(lang('ds_edit').lang('ds_company') . '[' . $_POST['o_name'] . ']', 0);
                $this->error(lang('ds_common_save_fail'));
            }
        } else {
            // 角色
            $gadmin = db('gadmin')->where('gid < 5')->select();
            $this->assign('gadmin',$gadmin);
            $organize_info = $model_organize->getOrganizeInfo(array('o_id' => intval(input('param.organize_id'))));
            //地区信息
            $region_list = db('area')->where('area_parent_id','0')->select();
            $this->assign('region_list', $region_list);
            if (empty($organize_info)) {
                $this->error(lang('param_error'));
            }
            $this->assign('organize_array', $organize_info);
            $this->setAdminCurItem('organize_edit');
            return $this->fetch('edit');
        }
    }
    /**
     * 子公司删除
    */
    public function del(){
        if(session('office_is_super') !=1 && !in_array(2,$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $o_id = input('param.o_id');
        if (empty($o_id)) {
            $this->error(lang('param_error'));
        }
        $admin = db('admin')->where(['admin_company_id'=>$o_id,'admin_del_status'=>1])->limit(1)->find();
        if($admin){
            $this->error('该公司下存在正在使用的人员，不能删除，请将使用的人员移除后进行删除');
        }
        $where = array();
        $where['o_id'] =$o_id;
        $del_array = array();
        $del_array['o_del']=2;
        $model_organize = Model('company');
        $result = $model_organize->editOrganize($where, $del_array);
        if ($result) {
            $this->success(lang('ds_common_del_succ'), 'Company/index');
        } else {
            $this->error('删除失败');
        }
    }
    public function export_step1() {

        if(session('office_is_super') !=1 && !in_array(7,$this->action )){
            $this->error(lang('gadmin_no_perms'));
        }

        $model_organize = Model('company');
        $condition = array();

        $condition['o_del']=1;
        if (!empty($_GET['o_name'])) {
            $o_name=$_GET['o_name'];
            $condition['o_name']=array('like', '%' . trim($o_name) . '%');
            $this->assign('search_organize_name',$o_name);
        }
        if(!empty($_GET['o_provinceid'])){
            $o_provinceid=input('param.o_provinceid');
            $condition['o_provinceid']=$o_provinceid;
            $this->assign('o_provinceid',$o_provinceid);
        }
        if(!empty($_GET['o_cityid'])) {
            $o_cityid = input('param.o_cityid');
            $condition['o_cityid'] =$o_cityid;
            $this->assign('o_cityid', $o_cityid);
        }
        if(!empty($_GET['area_id'])){
            $area_id = input('param.area_id');
            $condition['o_areaid'] =$area_id;
            $this->assign('area_id', $area_id);
        }
        $dataResult = $model_organize->getOrganizeList($condition, "o_id,o_name,o_role,o_area,o_address,o_phone,o_leading,o_enddate,o_createtime,o_remark");
        foreach($dataResult as $key=>$v){
            if($v['o_role']==1){
                $dataResult[$key]['o_role']='分公司';
            }else if($v['o_role']==2){
                $dataResult[$key]['o_role']='省级代理';
            }else if($v['o_role']==3){
                $dataResult[$key]['o_role']='市级代理';
            }else{
                $dataResult[$key]['o_role']='区级代理';
            }
        }
        $this->createExcel($dataResult);


    }
    /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel($data = array()) {
        $excel_obj = new \excel\Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id' => 's_title', 'Font' => array('FontName' => '宋体', 'Size' => '12', 'Bold' => '1')));
        //header
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "序号");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "公司名称");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "公司角色");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "地区");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "详细地址");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "电话");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "负责人");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "合同截止日期");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "创建时间");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "备注");
        //data
        foreach ((array) $data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => $k+1);
            $tmp[] = array('data' => $v['o_name']);
            $tmp[] = array('data' => $v['o_role']);
            $tmp[] = array('data' => $v['o_area']);
            $tmp[] = array('data' => $v['o_address']);
            $tmp[] = array('data' => $v['o_phone']);
            $tmp[] = array('data' => $v['o_leading']);
            $tmp[] = array('data' => $v['o_enddate']);
            $tmp[] = array('data' => $v['o_createtime']);
            $tmp[] = array('data' => $v['o_remark']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset("分/子公司列表", CHARSET));
        $excel_obj->generateXML($excel_obj->charset("分/子公司列表", CHARSET) . $_GET['curpage'] . '-' . date('Y-m-d-H', time()));
    }
    /**
     * 管理员添加
     */
    public function admin_add() {
        if(session('office_is_super') !=1 && !in_array(6,$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $admin_id = $this->admin_info['admin_id'];
            $model_admin = Model('admin');
            $where=array();
            $where['admin_company_id']=$_POST['oid'];
            $where['admin_del_status']=1;
            $result=$model_admin->getAdminList($where);
            if(!empty($result)) {
                echo json_encode(['m' => false, 'ms' =>'已添加过超级管理员:'.$result[0]['admin_name'].'，不能再次添加!']);
            }else {
                $param['admin_name'] = $_POST['admin_name'];
                $param['admin_gid'] = $_POST['gid'];
                $param['admin_password'] = md5($_POST['admin_password']);
                $param['create_uid'] = $admin_id;
                $param['admin_company_id'] = $_POST['oid'];
                $param['admin_status'] = 1;
                $param['admin_del_status'] = 1;
                $rs = $model_admin->addAdmin($param);
                if ($rs) {
                    $this->log(lang('ds_add') . lang('limit_admin') . '[' . $_POST['admin_name'] . ']', 1);
                    echo json_encode(['m' => true, 'ms' => lang('co_organize_succ')]);
                } else {
                    echo json_encode(['m' => true, 'ms' => lang('co_organize_succ')]);
                }
            }

    }
}