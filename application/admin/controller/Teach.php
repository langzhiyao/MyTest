<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Teach extends AdminControl {

    /**
     * 每次导出订单数量
     * @var int
     */
    const EXPORT_SIZE = 1000;

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/vrorder.lang.php');
        Lang::load(APP_PATH . 'admin/lang/zh-cn/school.lang.php');
        Lang::load(APP_PATH . 'admin/lang/zh-cn/admin.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    public function index(){
        if(session('admin_is_super') !=1 && !in_array(4,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $order = Model('Packagesorderteach');
        $condition = array();
        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_id']!=1){
            $model_company = Model("Company");
            $condition = $model_company->getCondition($admininfo['admin_company_id']);
        }
        $condition['delete_state'] = 0;
        $buyer_name = input('get.buyer_name');
        if ($buyer_name) {
            $condition['buyer_name'] = array('like', "%" . $buyer_name . "%");
        }
        $order_state = input('get.order_state');
        if ($order_state!="") {
            $condition['order_state'] = intval($order_state);
        }
        $payment_code = input('get.payment_code');
        if (!empty($payment_code)) {
            $condition['payment_code'] = $payment_code;
        }
        $order_list = $order->getOrderList($condition, 15);
        foreach ($order_list as $key=>$item) {
            $studentinfo = db('student')->where(array('s_id' => $item['student_id']))->find();
            $order_list[$key]['student_name'] = $studentinfo['s_name'];
        }
        $payment = db('mbpayment')->select();
        $this->assign('payment', $payment);
        $this->assign('order_list', $order_list);
        $this->assign('page', $order->page_info->render());
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '管理',
                'url' => url('Admin/Teach/index')
            ),
        );
        return $menu_array;
    }

}
