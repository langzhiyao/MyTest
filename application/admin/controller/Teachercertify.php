<?php

namespace app\admin\controller;

use think\Lang;
use think\Model;
use think\Validate;

class Teachercertify extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/teacher.lang.php');
        Lang::load(APP_PATH . 'admin/lang/zh-cn/admin.lang.php');
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
        $model_teacher = model('Teachercertify');
        $condition = array();
        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_id']!=1){
            $model_company = Model("Company");
            $condition = $model_company->getCondition($admininfo['admin_company_id']);
        }
        $user = input('param.user');//会员账户
        if ($user) {
            $condition['idcard'] = array('like', "%" . $user . "%");
        }
        $teacher_status = input('param.teacher_status');//状态
        if ($teacher_status) {
            $condition['status'] = $teacher_status;
        }
        $username = input('param.username');//姓名
        if ($username) {
            $condition['username'] = array('like', "%" . $username . "%");
        }
        $query_start_time = input('param.query_start_time');
        $query_end_time = input('param.query_end_time');
        if ($query_start_time && $query_end_time) {
            $condition['createtime'] = array('between', array(strtotime($query_start_time), strtotime($query_end_time)));
        }elseif($query_start_time){
            $condition['createtime'] = array('>',strtotime($query_start_time));
        }elseif($query_end_time){
            $condition['createtime'] = array('<',strtotime($query_end_time));
        }
        $teacher_list = $model_teacher->getTeacherList($condition, 15);
        $path = "http://".$_SERVER['HTTP_HOST']."/uploads/";
        $this->assign('path', $path);
        $this->assign('page', $model_teacher->page_info->render());
        $this->assign('teacher_list', $teacher_list);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    public function pass() {
        if(session('admin_is_super') !=1 && !in_array(15,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $teacher_id = input('param.teacher_id');
        if (empty($teacher_id)) {
            $this->error(lang('param_error'));
        }
        $model_teacher = model('Teachercertify');
        $teachinfo = $model_teacher->getOneById($teacher_id);
        $memberinfo = db('member')->where(array('member_id'=>$teachinfo['member_id']))->find();
        $teachinfo['member_add_time'] = $memberinfo['member_add_time'];
        $path = "http://".$_SERVER['HTTP_HOST']."/uploads/";
        $this->assign('path', $path);
        $this->assign('teachinfo', $teachinfo);
        $this->setAdminCurItem('pass');
        return $this->fetch();
    }

    /**
     * 重要提示，删除会员 要先确定删除店铺,然后删除会员以及会员相关的数据表信息。这个后期需要完善。
     */
    public function drop() {
        if(session('admin_is_super') !=1 && !in_array(15,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $admininfo = $this->getAdminInfo();
        $teacher_id = input('param.teacher_id');
        $member_id = input('param.member_id');
        $status = input('param.status');
        $phone = input('param.phone');
        $reason = input('param.reason')? input('param.reason') : "";
        if (empty($teacher_id)) {
            $this->error(lang('param_error'));
        }
        $model_teacher = model('Teachercertify');
        $result = $model_teacher->teacher_update(array('status'=>$status,'failreason'=>$reason,'option_id'=>$admininfo['admin_id'],'option_time'=>time()),array('id'=>$teacher_id));
        if ($result) {
            if($status==2){
                $member_model = Model("Member");
                $member_model->editMember(array('member_id'=>$member_id),array("member_identity"=>2));
                //审核结果给用户发送短信提醒
                if(preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/i', $phone)){
                    $content = '您的教师认证申请已通过，登录想见孩APP继续操作。';
                    $sms = new \sendmsg\sdk\SmsApi();
                    $send = $sms->sendSMS($phone,$content);
                    if(!$send){
                        $this->error('给用户发送短信失败 ');
                    }
                }
                $this->success(lang('teacher_index_apass'), 'Teachercertify/index');
            }else{
                //审核结果给用户发送短信提醒
                if(preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/i', $phone)){
                    if(empty($reason)){
                        $content = '您的教师认证申请未通过。请登录想见孩app重新认证!';
                    }else{
                        $content = '您的教师认证申请未通过，失败原因是：'.$reason."。请登录想见孩app重新认证!";
                    }

                    $sms = new \sendmsg\sdk\SmsApi();
                    $send = $sms->sendSMS($phone,$content);
                    if(!$send){
                        $this->error('给用户发送短信失败 ');
                    }
                }
                $this->success(lang('teacher_index_noapass'), 'Teachercertify/index');
            }
        } else {
            $this->error('审核失败');
        }
    }

    //图片大图
    public function view()
    {
        $teacher_id = input('param.id');
        $type = input('param.type');
        if (empty($teacher_id)) {
            $this->error(lang('param_error'));
        }
        $model_teacher = model('Teachercertify');
        $teachinfo = $model_teacher->getOneById($teacher_id);
        $path = "http://".$_SERVER['HTTP_HOST']."/uploads/";
        $this->assign('path',$path);
        $this->assign('type',$type);
        $this->assign('teachinfo', $teachinfo);
        $this->setAdminCurItem('view');
        return $this->fetch();
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '管理',
                'url' => url('Admin/Teachercertify/index')
            ),
        );
        if (request()->action() == 'pass') {
            $menu_array[] = array(
                'name' => 'pass',
                'text' => '审核',
                'url' => url('Admin/Teachercertify/pass')
            );
        }
        return $menu_array;
    }

}

?>
