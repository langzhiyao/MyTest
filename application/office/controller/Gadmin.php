<?php

namespace app\office\controller;

use think\Lang;
use think\Validate;

class Gadmin extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'office/lang/zh-cn/admin.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('office_gid') ,$perm_id);
        $this->assign('action',$action);
    }
    /**
     * ajax操作
     */
    public function ajax() {
        switch (input('get.branch')) {
            //权限组名称验证
            case 'check_gadmin_name':
                $condition = array();
                if (is_numeric(input('param.gid'))) {
                    $condition['gid'] = array('neq', intval(input('param.gid')));
                }
                $condition['gname'] = input('get.gname');
                $condition['create_uid'] = $this->admin_info['admin_id'];
                $info = db('gadmin')->where($condition)->find();
                if (!empty($info)) {
                    exit('false');
                } else {
                    exit('true');
                }
                break;
                case 'delete_role_admin':
                $condition = array();
                $condition['admin_name'] = input('get.admin_name');
                $condition['admin_id'] = intval(input('get.admin_id'));
                $info = db('admin')->where($condition)->update(array('admin_gid'=>null));
                if (!empty($info)) {
                    exit('false');
                } else {
                    exit('true');
                }
                break;
        }
    }

    /**
     * 取得所有权限项
     *
     * @return array
     */
    private function permission() {
        $limit = $this->limitList();
        if (is_array($limit)) {
            foreach ($limit as $k => $v) {
                if (is_array($v['child'])) {
                    $tmp = array();
                    foreach ($v['child'] as $key => $value) {
                        $act = (!empty($value['act'])) ? $value['act'] : $v['act'];
                        if (strpos($act, '|') == false) {//act参数不带|
                            $limit[$k]['child'][$key]['op'] = rtrim($act . '.' . str_replace('|', '|' . $act . '.', $value['op']), '.');
                        } else {//act参数带|
                            $tmp_str = '';
                            if (empty($value['op'])) {
                                $limit[$k]['child'][$key]['op'] = $act;
                            } elseif (strpos($value['op'], '|') == false) {//op参数不带|
                                foreach (explode('|', $act) as $v1) {
                                    $tmp_str .= "$v1.{$value['op']}|";
                                }
                                $limit[$k]['child'][$key]['op'] = rtrim($tmp_str, '|');
                            } elseif (strpos($value['op'], '|') != false && strpos($act, '|') != false) {//op,act都带|，交差权限
                                foreach (explode('|', $act) as $v1) {
                                    foreach (explode('|', $value['op']) as $v2) {
                                        $tmp_str .= "$v1.$v2|";
                                    }
                                }
                                $limit[$k]['child'][$key]['op'] = rtrim($tmp_str, '|');
                            }
                        }
                    }
                }
            }
            return $limit;
        } else {
            return array();
        }
    }

    /**
     * 权限组
     */
    public function gadmin() {
        if(session('office_is_super') !=1 && !in_array(4,$this->action )){
            $this->error(lang('gadmin_no_perms'));
        }
        $admin_company_id = $this->admin_info['admin_company_id'];
        $list = db('gadmin')->where('company_id="'.$admin_company_id.'" AND school_id=0')->order('sort ASC')->paginate(10);
        $this->assign('list', $list->items());
        $this->assign('page', $list->render());
        $this->setAdminCurItem('gadmin');
        return $this->fetch('gadmin');
    }

    /**
     * 添加权限组
     */
    public function gadmin_add() {
        if(session('office_is_super') !=1 && !in_array(1,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $admin_company_id = $this->admin_info['admin_company_id'];
        //查询用户信息
        $admin_model = Model('admin');
        $admin = $admin_model->getOneAdmin(session('office_id'));
        if (!request()->isPost()) {
            if($this->admin_info['admin_is_super'] != 1){
                $ginfo = db('gadmin')->where('company_id='.$admin_company_id.' AND school_id=0')->find();
                if (empty($ginfo)) {
                    $this->error(lang('admin_set_admin_not_exists'));
                }
                //获取操作权限
                $gaction = db('roleperms')->where('roleid',$gid)->select();
                $gaction = array_column($gaction,null,'permsid');
                if(!empty($gaction)){
                    foreach ($gaction as $k=>$v){
                        $gaction[$k]['action'] = explode(',',$v['action']);
                    }
                }
                $this->assign('gaction',$gaction);
                //解析已有权限
                $hlimit = decrypt($ginfo['limits'], MD5_KEY . md5($ginfo['gname']));
                $ginfo['limits'] = explode('|', $hlimit);
                $this->assign('ginfo', $ginfo);
            }
            $this->assign('limit', $this->permission());
            $this->setAdminCurItem('gadmin_add');
            return $this->fetch('gadmin_add');
        } else {
            $limit_str = '';
            if (is_array($_POST['permission'])) {
                foreach ($_POST['permission'] as $k=>$v){
                    $arr[] = explode('@',$v);
                }
                $_POST['permission'] = array_column($arr,0);
                $nav = array_values(array_unique(array_column($arr,1)));

                $limit_str = implode('|', $_POST['permission']);
                $nav_str = implode('|',$nav);
            }
//            halt($_POST['nav']);
            //加密
            $data['limits'] = encrypt($limit_str, MD5_KEY . md5($_POST['gname']));
            $data['nav'] = encrypt($nav_str, MD5_KEY . md5($_POST['gname']));
            $data['gname'] = $_POST['gname'];
            $data['create_uid'] = session('office_id');
            $data['company_id'] = $admin['admin_company_id'];
            $data['time'] = time();
            $result = db('gadmin')->insertGetId($data);
            if ($result >0) {
                if(!empty($_POST['action'])){
                    foreach($_POST['action'] as $k=>$v){
                        $permsid = $k;
                        if(!empty($v)){
                            $action = implode(',',$v);
                        }else{
                            $action = '';
                        }
                        $roleid = $result;
                        db('roleperms')->insert(array('permsid'=>$permsid,'roleid'=>$roleid,'action'=>$action));
                    }
                }
                $this->log(lang('ds_add').lang('limit_gadmin') . '[' . $_POST['gname'] . ']', 1);
                $this->success(lang('ds_common_save_succ'), url('office/Gadmin/gadmin'));
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 设置权限组权限
     */
    public function gadmin_set() {
        if(session('office_is_super') !=1 && !in_array(3,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $gid = intval(input('param.gid'));
        $ginfo = db('gadmin')->where('gid', $gid)->find();

        if (empty($ginfo)) {
            $this->error(lang('admin_set_admin_not_exists'));
        }
        if (!request()->isPost()) {
            //解析已有权限
            $hlimit = decrypt($ginfo['limits'], MD5_KEY . md5($ginfo['gname']));
            $ginfo['limits'] = explode('|', $hlimit);
            //获取操作权限
            $gaction = db('roleperms')->where('roleid',$gid)->select();
            $gaction = array_column($gaction,null,'permsid');
            if(!empty($gaction)){
                foreach ($gaction as $k=>$v){
                    $gaction[$k]['action'] = explode(',',$v['action']);
                }
            }
            $this->assign('gaction',$gaction);
            $this->assign('ginfo', $ginfo);
            $this->assign('limit', $this->permission());
            $this->setAdminCurItem('gadmin');
            return $this->fetch('gadmin_set');
        } else {
            $limit_str = '';
            if (is_array($_POST['permission'])) {
                foreach ($_POST['permission'] as $k=>$v){
                    $arr[] = explode('@',$v);
                }
                $_POST['permission'] = array_column($arr,0);
                $nav = array_values(array_unique(array_column($arr,1)));
                $limit_str = implode('|', $_POST['permission']);
                $nav_str = implode('|',$nav);
            }
//            halt($_POST['permission']);
            $limit_str = encrypt($limit_str, MD5_KEY . md5($_POST['gname']));
            $nav_str = encrypt($nav_str, MD5_KEY . md5($_POST['gname']));
            $data['limits'] = $limit_str;
            $data['nav'] = $nav_str;
            $data['gname'] = $_POST['gname'];
            $data['create_uid'] = session('office_id');
            $data['time'] = time();
            $update = db('gadmin')->where(array('gid' => $gid))->update($data);
            if ($update) {
                db('roleperms')->where(array('roleid'=>$gid))->delete();
                if(!empty($_POST['action'])){
                    foreach($_POST['action'] as $k=>$v){
                        $permsid = $k;
                        if(!empty($v)){
                            $action = implode(',',$v);
                        }else{
                            $action = '';
                        }
                        $roleid = $gid;
                        db('roleperms')->insert(array('permsid'=>$permsid,'roleid'=>$roleid,'action'=>$action));
                    }
                }
                $this->log(lang('ds_edit').lang('limit_gadmin') . '[' . $_POST['gname'] . ']', 1);
                $this->success(lang('ds_common_save_succ'), url('office/Gadmin/gadmin'));
            } else {
                $this->error(lang('ds_common_save_succ'));
            }
        }
    }

    /**
     * 组删除
     */
    public function gadmin_del() {
        if(session('office_is_super') !=1 && !in_array(2,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        if (is_numeric(input('param.gid'))) {
            if(!in_array(intval(input('param.gid')),array(1,2,3,4,8))){
                //删除权限组需要判断权限组下是否有成员
                $result = db('admin')->where(array('admin_gid'=>intval(input('param.gid'))))->find();
                if(!$result){
                    db('gadmin')->where(array('gid' => intval(input('param.gid'))))->delete();
                    db('roleperms')->where(array('roleid' => intval(input('param.gid'))))->delete();
                    $this->log(lang('ds_del').lang('limit_gadmin') . '[ID' . intval(input('param.gid')) . ']', 1);
                    $msg['status'] = 1;
                }else{
                    $msg['status'] = -2;
                }
            }else{
                $msg['status'] = -3;
            }
        } else {
            $msg['status'] = -4;
        }
        return json_encode($msg);
    }

    /**
     * @desc  查看所属人员
     * @author langzhiyao
     * @time 2018/09/19
     */
    public function gadmin_member(){
        if(session('office_is_super') !=1 && !in_array(5,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $gid = intval(input('param.gid'));

        $member = db('admin')->where('admin_gid = '.$gid.' AND admin_del_status = 1')->select();

        $this->assign('member',$member);
        $this->setAdminCurItem('gadmin');
        return $this->fetch('gadmin_member');

    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        if(session('office_is_super') ==1 || in_array('1',$this->action)) {
            $menu_array = array(
                array(
                    'name' => 'gadmin',
                    'text' => '权限组',
                    'url' => url('office/Gadmin/gadmin')
                ),
                array(
                    'name' => 'gadmin_add',
                    'text' => '添加权限组',
                    'url' => url('office/Gadmin/gadmin_add')
                ),
            );
        }else{
            $menu_array = array(
                array(
                    'name' => 'gadmin',
                    'text' => '权限组',
                    'url' => url('office/Gadmin/gadmin')
                ),
            );
        }
        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit',
                'text' => '编辑',
                'url' => url('office/Gadmin/edit')
            );
        }
        return $menu_array;
    }



}

?>
