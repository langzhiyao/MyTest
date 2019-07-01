<?php

namespace app\school\controller;

use think\Lang;
use think\Validate;

class Studentinfo extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'school/lang/zh-cn/school.lang.php');
    }

    public function index(){
        $student_id = input('param.student_id');
        if (empty($student_id)) {
            $this->error(lang('param_error'));
        }
        $model_student = Model('Student');
        $student_array = $model_student->getStudentInfo(array("s_id"=>$student_id));
        $this->assign('student_array', $student_array);
        //学校名称
        $schoolname = db('school')->where('schoolid',$student_array['s_schoolid'])->find();
        $this->assign('schoolname', $schoolname['name']);
        //学校类型
        $schooltype = db('schooltype')->where('sc_enabled','1')->select();
        $typeids = explode(',',$schoolname['typeid']);
        foreach ($schooltype as $k=>$v){
            foreach ($typeids as $key=>$item){
                if($item ==$v['sc_id']){
                    $type[$item] = $v['sc_type'];
                }
            }
        }
        $this->assign('schooltype', $type);
        //班级名称
        $classname = db('class')->where('classid',$student_array['s_classid'])->find();
        $this->assign('classname', $classname['classname']);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    public function lists() {
        $model_student = model('Student');
        $condition = array();
        $condition['s_id'] = input('param.student_id');
        $studentInfo = $model_student->getStudentInfo($condition);
        //主账号
        $member = db('member')->where(['member_id'=>$studentInfo['s_ownerAccount']])->select();
        //副账号
        $member_fu = db('member')->where(['is_owner'=>$member[0]['member_id']])->select();
        $member = array_merge($member,$member_fu);
        foreach($member as $k=>$v){
            if($v['member_add_time']!=""){
                $member[$k]['member_add_time'] = date("Y-m-d H:i:s",$v['member_add_time']);
            }
        }
        $this->assign('member', $member);
        $this->setAdminCurItem('lists');
        return $this->fetch();
    }

    //看孩订单
    public function order() {
        $student_id = input('param.student_id');
        $model_packagesorder = model('Packagesorder');

        $condition = array();
//        $admininfo = $this->getAdminInfo();
//        if($admininfo['admin_id']!=1){
//            $admin = db('admin')->where(array('admin_id'=>$admininfo['admin_id']))->find();
//            $condition['a.admin_company_id'] = $admin['admin_company_id'];
//        }
        $condition['s_id'] = $student_id;
        $condition['pkg_type'] = 1;
        $lookOrder = $model_packagesorder->getOrderList($condition,10);
        foreach($lookOrder as $k=>$v){
            if(!empty($v['order_amount'])){
                $lookOrder[$k]['order_amount'] = !empty($v['order_amount'])?sprintf('%.2f', $v['order_amount']):"";
            }
        }
        $this->assign('page', $model_packagesorder->page_info->render());
        $this->assign('lookOrder', $lookOrder);
        $this->setAdminCurItem('order');
        return $this->fetch();
    }

    //重温课堂订单
    public function reviveorder() {
        $student_id = input('param.student_id');
        $model_packagesorder = model('Packagesorder');
        $condition = array();
        $condition['s_id'] = $student_id;
        $condition['pkg_type'] = 2;
        $lookOrder = $model_packagesorder->getOrderList($condition,10);
        foreach($lookOrder as $k=>$v){
            if(!empty($v['order_amount'])){
                $lookOrder[$k]['order_amount'] = !empty($v['order_amount'])?sprintf('%.2f', $v['order_amount']):"";
            }
        }
        $this->assign('page', $model_packagesorder->page_info->render());
        $this->assign('lookOrder', $lookOrder);
        $this->setAdminCurItem('reviveorder');
        return $this->fetch();
    }

    //教孩订单
    public function teachorder() {
        //$student_id = input('param.student_id');
        $model_packagesorder = model('Packagesorderteach');

        $condition = array();
        //$condition['student_id'] = $student_id;
        $lookOrder = $model_packagesorder->getOrderList($condition,10);
        $this->assign('page', $model_packagesorder->page_info->render());
        $this->assign('lookOrder', $lookOrder);
        $this->setAdminCurItem('teachorder');
        return $this->fetch();

    }

    //商城订单
    public function shopping() {
        $student_id = input('param.student_id');
        $model_order = model('Order');
        $condition = array();
        $condition['student_id'] = $student_id;
        $lookOrder = $model_order->getOrderList($condition,10);
        $this->assign('page', $model_order->page_info->render());
        $this->assign('lookOrder', $lookOrder);
        $this->setAdminCurItem('shopping');
        return $this->fetch();
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $student_id = $_GET['student_id'];
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '学生信息',
                'url' => url('School/Studentinfo/index',array('student_id'=>$student_id))
            ),
            array(
                'name' => 'lists',
                'text' => '绑定家长账号',
                'url' => url('School/Studentinfo/lists',array('student_id'=>$student_id))
            ),
            array(
                'name' => 'order',
                'text' => '看孩订单',
                'url' => url('School/Studentinfo/order',array('student_id'=>$student_id))
            ),
//            array(
//                'name' => 'teachorder',
//                'text' => '教孩订单',
//                'url' => url('School/Studentinfo/teachorder',array('student_id'=>$student_id))
//            ),
            array(
                'name' => 'reviveorder',
                'text' => '重温课堂订单',
                'url' => url('School/Studentinfo/reviveorder',array('student_id'=>$student_id))
            ),
//            array(
//                'name' => 'shopping',
//                'text' => '商城订单',
//                'url' => url('School/Studentinfo/shopping',array('student_id'=>$student_id))
//            )
//            array(
//                'name' => 'order',
//                'text' => '已购套餐',
//                'url' => url('School/Studentinfo/order',array('student_id'=>$student_id))
//            ),

        );
        return $menu_array;
    }

}

?>
