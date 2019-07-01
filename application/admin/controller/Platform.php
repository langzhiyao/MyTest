<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Platform extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/school.lang.php');
        Lang::load(APP_PATH . 'admin/lang/zh-cn/admin.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    //资金明细
    public function index(){
        if(session('admin_is_super') !=1 && !in_array(4,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $condition = array();
        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_id']!=1){
            $company = db("company")->field("o_role,o_provinceid,o_cityid,o_areaid")->where(array('o_id'=>$admininfo['admin_company_id']))->find();
            if($company['o_role']==1){
                $condition['s.member_provinceid'] = $company['o_provinceid'];
                $condition['s.member_cityid'] = $company['o_cityid'];
                $condition['s.member_areaid'] = $company['o_areaid'];
            }elseif($company['o_role']==3){
                $condition['s.member_provinceid'] = $company['o_provinceid'];
                $condition['s.member_cityid'] = $company['o_cityid'];
            }elseif($company['o_role']==2){
                $condition['s.member_provinceid'] = $company['o_provinceid'];
            }
        }
        $status = input('param.status');//交易类型
        if ($status!="") {
            $condition['lg_type'] = $status==1? array("in",array("share_admin_payment","order_pay")) : "cash_pay";
        }
        $user = input('param.user');//会员账号
        if($user!=""){
            $condition['lg_member_name'] = array('like', "%" . $user . "%");
        }
        $query_start_time = input('param.query_start_time');
        $query_end_time = input('param.query_end_time');
        if ($query_start_time && $query_end_time) {
            $condition['lg_add_time'] = array('between', array(strtotime($query_start_time), strtotime($query_end_time)));
        }elseif($query_start_time){
            $condition['lg_add_time'] = array('>',strtotime($query_start_time));
        }elseif($query_end_time){
            $condition['lg_add_time'] = array('<',strtotime($query_end_time));
        }
        $pdlog = Model("Adminpdlog");
        $result = $pdlog->getAllPdlog($condition,15);
        $sum = $pdlog->getAllCount();
        if($sum){
            $this->assign('sum', $sum);
        }
        $this->assign('pdlog', $result);
        $this->assign('page', $pdlog->page_info->render());
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '资金明细',
                'url' => url('Admin/Platform/index')
            )
        );

        return $menu_array;
    }

}
