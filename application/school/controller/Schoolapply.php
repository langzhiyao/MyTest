<?php

namespace app\school\controller;

use think\Lang;
use think\Validate;

class Schoolapply extends AdminControl {

    const EXPORT_SIZE = 1000;

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
        $model_schoolapply = model('Schoolapply');
        $condition = array();

        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_id']!=1){
            $model_company = Model("Company");
            $condition = $model_company->getCondition($admininfo['admin_company_id']);
        }

        $schoolname = input('param.schoolname');//学校名称
        if ($schoolname) {
            $condition['schoolname'] = array('like', "%" . $schoolname . "%");
        }
        $school_name = input('param.school_name');//学校名称
        if ($school_name) {
            $condition['applyid'] = $school_name;
        }
        $school_type = input('param.school_type');//学校类型
        if ($school_type) {
            $condition['sc_type'] = $school_type;
        }
        $area_id = input('param.area_id');//地区
        if($area_id){
            $region_info = db('area')->where('area_id',$area_id)->find();
            if($region_info['area_deep']==1){
                $condition['provinceid'] = $area_id;
            }elseif($region_info['area_deep']==2){
                $condition['cityid'] = $area_id;
            }else{
                $condition['areaid'] = $area_id;
            }
        }
        $school_status = input('param.school_status');//状态
        if ($school_status) {
            $condition['status'] = $school_status;
        }
        $schoolapply_list = $model_schoolapply->getSchoolapplyList($condition, 15);
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
        $this->assign('show_page', $model_schoolapply->page_info->render());
        $allschoolapply = $model_schoolapply->getAllAchoolapply();
        $this->assign('allschoolapply', $allschoolapply);
        $model_schooltype = model('Schooltype');
        $schooltype = $model_schooltype->get_sctype_List(array('sc_enabled'=>1));
        $this->assign('schooltype', $schooltype);
        $this->assign('schoolapply_list', $schoolapply_list);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }


    /**
     * ajax操作
     */
    public function ajax() {
        $branch = input('param.branch');

        switch ($branch) {
            /**
             * 验证学校名是否重复
             */
            case 'check_user_name':
                $school_member = Model('school');
                $condition['name'] = input('param.school_name');
                $condition['schoolid'] = array('neq', intval(input('get.school_id')));
                $list = $school_member->getSchoolInfo($condition);
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
     * 处理
     */
    public function deal() {
        if(session('school_admin_is_super') !=1 && !in_array(3,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $admininfo = $this->getAdminInfo();
        $applyid = input('param.applyid');
        if (empty($applyid)) {
            $this->error(lang('param_error'));
        }
        $model_schoolapply = Model('schoolapply');
        $data = array('status'=>2,'auditor'=>$admininfo['admin_id'],'auditortime'=>date("Y-m-d H:i:s",time()),'admin_company_id'=>$admininfo['admin_company_id']);
        $result = $model_schoolapply->editSchoolapply($data,array('applyid'=>$applyid));
        if ($result) {
            $this->success(lang('处理成功'), 'Schoolapply/index');
        } else {
            $this->error('处理失败');
        }
    }

    public function fand_schooltype(){
        $sc_id = intval(input('post.sc_id'));
        $Schooltype = model('Schooltype');
        $model_schoolapply = model('Schoolapply');
        $schoolapplyInfo = $model_schoolapply->getSchoolapplyById($sc_id);
        if($schoolapplyInfo){
            $Schooltype_list = $Schooltype->get_sctype_List(array('sc_id'=>array('in',$schoolapplyInfo['sc_type'])));
            echo json_encode($Schooltype_list);
        }
    }

    public function fand_classname(){
        $ty_id = intval(input('post.ty_id'));
        $sc_id = intval(input('post.sc_id'));
        $where = ['applyid'=>$sc_id,'sc_type'=>$ty_id];
        if($ty_id && $sc_id){
            $classList = db('schoolapply')->where($where)->select();
            echo json_encode($classList);
        }
    }


    /**
     * 导出
     *
     */
    public function excel() {

        if(session('school_admin_is_super') !=1 && !in_array(7,$this->action )){
            $this->error(lang('gadmin_no_perms'));
        }

        $model_apply = Model('schoolapply');
        $condition = array();

        $schoolname = input('param.schoolname');//学校名称
        if ($schoolname) {
            $condition['schoolname'] = array('like', "%" . $schoolname . "%");
        }
        $school_name = input('param.school_name');//学校名称
        if ($school_name) {
            $condition['applyid'] = $school_name;
        }
        $school_type = input('param.school_type');//学校类型
        if ($school_type) {
            $condition['sc_type'] = $school_type;
        }
        $area_id = input('param.area_id');//地区
        if($area_id){
            $region_info = db('area')->where('area_id',$area_id)->find();
            if($region_info['area_deep']==1){
                $condition['provinceid'] = $area_id;
            }elseif($region_info['area_deep']==2){
                $condition['cityid'] = $area_id;
            }else{
                $condition['areaid'] = $area_id;
            }
        }
        $school_status = input('param.school_status');//状态
        if ($school_status) {
            $condition['status'] = $school_status;
        }
        if (!is_numeric($_GET['show_page'])) {

            $count = $model_apply->getApplyCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE) { //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                $this->assign('list', $array);
                $this->assign('murl', url('order/index'));
                return $this->fetch('export.excel');
            } else { //如果数量小，直接下载
                $data = $model_apply->getSchoolapplyList($condition, '', '*', 'applyid asc', self::EXPORT_SIZE);
                $this->createExcel($data);
            }
        } else { //下载
            $limit1 = ($_GET['show_page'] - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_apply->getSchoolapplyList($condition, '', '*', 'applyid asc', "{$limit1},{$limit2}");
            $this->createExcel($data);
        }
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
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "学校名称");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "所在地区");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "负责/联系人");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "电话");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "留言内容");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "添加/修改时间");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "状态");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "审核人");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "审核时间");
        //data
        foreach ((array) $data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => 'DS' . $v['applyid']);
            $tmp[] = array('data' => $v['schoolname']);
            $tmp[] = array('data' => $v['region']);
            $tmp[] = array('data' => $v['username']);
            $tmp[] = array('data' => $v['phone']);
            $tmp[] = array('data' => $v['message']);
            $tmp[] = array('data' => $v['createtime']);
            $tmp[] = array('data' => $v['status']==1?"处理中":"已处理");
            $memberinfo = db('admin')->where(array('admin_id'=>$v['auditor']))->find();
            $tmp[] = array('data' => $memberinfo['admin_name']);
            $tmp[] = array('data' => $v['auditortime']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset("学校申请审核", CHARSET));
        $excel_obj->generateXML($excel_obj->charset("学校申请审核", CHARSET) . $_GET['curpage'] . '-' . date('Y-m-d-H', time()));
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '管理',
                'url' => url('School/Schoolapply/index')
            ),
        );
        return $menu_array;
    }

}

?>
