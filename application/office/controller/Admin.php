<?php

namespace app\office\controller;

use think\Lang;
use think\Validate;

class Admin extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'office/lang/zh-cn/admin.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
//        halt($class_name);
        $this->action = $action = $this->get_role_perms(session('office_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    /**
     * 管理员列表
     */
    public function admin() {
        if(session('office_is_super') !=1 && !in_array(4,$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $admin_id = $this->admin_info['admin_id'];
        $company_id = $this->admin_info['admin_company_id'];

        $where = 'a.admin_company_id='.$company_id.' AND admin_school_id=0  AND a.admin_del_status=1 AND a.admin_id !='.$admin_id.'';

        $account = '';$role = '';
        if (request()->isPost()) {
            if(!empty($_POST['account'])){
                $where .= ' AND (a.admin_name LIKE "%'.$_POST["account"].'%" || a.admin_phone LIKE "%'.$_POST["account"].'%") ';
                $account = trim($_POST['account']);
            }
            if(!empty($_POST['company'])){
                $where .= ' AND a.admin_company_id = '.intval($_POST["company"]);
                $com = intval($_POST['company']);
            }
            if(!empty($_POST['role'])){
                $where .= ' AND a.admin_gid = '.intval($_POST["role"]);
                $role = intval($_POST['role']);
            }
        }

        $admin_list = db('admin')->alias('a')
            ->field('a.*,g.gid,g.gname,o.o_id,o.o_name')
            ->join('__GADMIN__ g', 'g.gid = a.admin_gid', 'LEFT')
            ->join('__COMPANY__ o', 'o.o_id = a.admin_company_id', 'LEFT')
            ->where($where)
            ->order('a.admin_id DESC')
            ->paginate(10,false,['query' => request()->param()]);

//        halt($admin_list);
        $company_role_id=db('company')->where('o_id="'.$company_id.'"')->value('o_role');
        $this->assign('company_role_id',$company_role_id);
        //获取所创建的角色
        $gadmin_list = db('gadmin')->field('gid,create_uid,gname')->where('company_id= '.$company_id.' AND school_id=0 ')->select();
        $this->assign('gadmin_list',$gadmin_list);

        $this->assign('admin_list', $admin_list->items());
        $this->assign('account',$account);
        $this->assign('role',$role);
        $this->assign('com',$com);
        $this->assign('page', $admin_list->render());
        $this->setAdminCurItem('admin');
        return $this->fetch('admin');
    }

    /**
     * 管理员添加
     */
    public function admin_add() {
        if(session('office_is_super') !=1 && !in_array('1',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $admin_company_id = $this->admin_info['admin_company_id'];
        $admin_id = $this->admin_info['admin_id'];

        if (!request()->isPost()) {
            //得到权限组
            $gadmin = db('gadmin')->field('gname,gid')->where("company_id = $admin_company_id AND school_id=0")->select();
            $this->assign('gadmin', $gadmin);
            $admin_company_role = db('company')->where('o_id = "'.$admin_company_id.'"')->value('o_role');
            $this->assign('admin_company_role',$admin_company_role);
            $this->setAdminCurItem('admin_add');
            return $this->fetch('admin_add');
        } else {
            $model_admin = Model('admin');
            $param['admin_name'] = $_POST['admin_name'];
            $param['admin_password'] = md5($_POST['admin_password']);
            $param['create_uid'] = $admin_id;
            $param['admin_company_id'] = $admin_company_id;
            $gid = $_POST['gid'];
            $param['admin_gid'] = $gid;
            $param['admin_phone'] = $_POST['admin_phone'];
            $param['admin_true_name'] = $_POST['admin_truename'];
            $param['admin_department'] = $_POST['admin_department'];
            $param['admin_description'] = $_POST['admin_description'];
            $rs = $model_admin->addAdmin($param);
            if ($rs) {
                $this->log(lang('ds_add').lang('limit_admin') . '[' . $_POST['admin_name'] . ']', 1);
                $this->success(lang('ds_common_save_succ'), url('office/Admin/admin'));
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 设置管理员权限
     */
    public function admin_edit() {
        if(session('office_is_super') !=1 && !in_array('3',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $admin_company_id = $this->admin_info['admin_company_id'];
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
                $this->success(lang('admin_edit_success'), url('office/Admin/admin'));
            } else {
                $this->error(lang('admin_edit_fail'), url('office/Admin/admin'));
            }
        } else {
            //查询用户信息
            $admin_model = Model('admin');
            $admin = $admin_model->getOneAdmin($admin_id);
            if (!is_array($admin) || count($admin) <= 0) {
                $this->error(lang('admin_edit_admin_error'), url('office/Admin/admin'));
            }
            $admin_company_role = db('company')->where('o_id = "'.$admin_company_id.'"')->value('o_role');
            $this->assign('admin_company_role',$admin_company_role);
            $this->assign('admin', $admin);
            //得到权限组
            $gadmin = db('gadmin')->field('gname,gid')->where("company_id = '".$admin_company_id."' AND school_id=0")->select();
            $this->assign('gadmin', $gadmin);
            $this->setAdminCurItem('admin');
            return $this->fetch('admin_edit');
        }
    }

    /**
     * ajax操作
     */
    public function ajax() {
        switch (input('get.branch')) {
            //管理人员名称验证
            case 'check_admin_name':
                $model_admin = Model('admin');
                $condition['admin_name'] = input('get.admin_name');
                $condition['admin_del_status']=1;
                $list = $model_admin->where($condition)->find();
                if (!empty($list)) {
                    exit('false');
                } else {
                    exit('true');
                }
                break;
            case 'company_admin_name':
                $model_admin = Model('company');
                $condition['o_name'] = input('get.o_name');
                $condition['o_del']=1;
                $list = $model_admin->where($condition)->find();
                if (!empty($list)) {
                    exit('false');
                } else {
                    exit('true');
                }
                break;
            case 'find_mood_name':
                $mood_admin = Model('mood');
                $where = array();
                $where['id']=intval(input('get.id'));
                $update_array = array();
                $del=input('get.del');
                $update_array['del']=$del;
                if($del==2){
                    $update_array['deltime']=time();
                }
                $list = $mood_admin->editMood($where,$update_array);
                if (!empty($list)) {
                    exit('false');
                } else {
                    exit('true');
                }
                break;
            case 'check_admin_phone':
                $model_admin = Model('admin');
                $condition['admin_phone'] = input('get.admin_phone');
                $condition['admin_del_status']=1;
                $list = $model_admin->where($condition)->find();
                if (!empty($list)) {
                    exit('false');
                } else {
                    exit('true');
                }
                break;
            case 'check_admin_name_edit':
                $model_admin = Model('admin');
                $condition['admin_name'] = input('get.admin_name');
                $condition['admin_id'] = array('neq', intval(input('get.admin_id')));
                $list = $model_admin->where($condition)->find();
                if (!empty($list)) {
                    exit('false');
                } else {
                    exit('true');
                }
                break;
            case 'check_admin_phone_edit':
                $model_admin = Model('admin');
                $condition['admin_phone'] = input('get.admin_phone');
                $condition['admin_id'] = array('neq', intval(input('get.admin_id')));
                $list = $model_admin->where($condition)->find();
                if (!empty($list)) {
                    exit('false');
                } else {
                    exit('true');
                }
                break;
            case 'change_admin_status':
                $model_admin = Model('admin');
                $condition['admin_name'] = input('get.admin_name');
                $condition['admin_id'] = array('eq', intval(input('get.admin_id')));
                $result = $model_admin->where($condition)->update(array('admin_status'=>intval(input('get.admin_status'))));
                if (!$result) {
                    exit('false');
                } else {
                    exit('true');
                }
                break;
            case 'reset_admin_password':
                $model_admin = Model('admin');
                $condition['admin_name'] = input('get.admin_name');
                $condition['admin_id'] = array('eq', intval(input('get.admin_id')));
                $result = $model_admin->where($condition)->update(array('admin_password'=>md5('147258369')));
                if (!$result) {
                    exit('false');
                } else {
                    exit('true');
                }
                break;
            case 'delete_admin':
                //ID为1的会员不允许删除
                if (@in_array(1, intval(input('get.admin_id')))) {
                    $this->error(lang('admin_index_not_allow_del'));
                }
                $model_admin = db('admin');
                $condition['admin_name'] = input('get.admin_name');
                $condition['admin_id'] = array('eq', intval(input('get.admin_id')));
                $condition['admin_del_status'] = 1;
                $res = $model_admin->where($condition)->find();
                if($res){
                    $result = $model_admin->where($condition)->update(array('admin_del_status'=>-1));
                    if (!$result) {
                        exit('false');
                    } else {
                        exit('true');
                    }
                }else{
                    exit('false');
                }
                break;
            case 'get_gadmin':
                $condition['company_id'] = input('post.id');
                $result = db('gadmin')->field('gname,gid')->where($condition)->order('sort ASC')->select();
                $html = '<option value="" selected>请选择所属角色</option>';
                if(!empty($result)){
                    foreach($result as $key=>$value){
                        $html .= '<option value="'.$value["gid"].'">'.$value["gname"].'</option>';
                    }
                }
                return $html;
                break;
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        if(session('office_is_super') !=1 && !in_array('1',$this->action)){
            $menu_array = array(
                array(
                    'name' => 'admin',
                    'text' => '管理员',
                    'url' => url('office/Admin/admin')
                )
            );
        }else{
            $menu_array = array(
                array(
                    'name' => 'admin',
                    'text' => '管理员',
                    'url' => url('office/Admin/admin')
                ),
                array(
                    'name' => 'admin_add',
                    'text' => '添加管理员',
                    'url' => url('office/Admin/admin_add')
                )
            );
        }

        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit',
                'text' => '编辑',
                'url' => url('office/Admin/edit')
            );
        }
        return $menu_array;
    }

}

?>
