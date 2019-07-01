<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Membercash extends AdminControl {

    //会员提现
    public function index(){
        $condition = array();
        $status = input('param.status');//状态
        if ($status!="") {
            $condition['status'] = $status;
        }
        $user = input('param.user');//会员账号
        if($user!=""){
            $condition['pdc_member_name'] = array('like', "%" . $user . "%");
        }
        $query_start_time = input('param.query_start_time');
        $query_end_time = input('param.query_end_time');
        if ($query_start_time && $query_end_time) {
            $condition['pdc_add_time'] = array('between', array(strtotime($query_start_time), strtotime($query_end_time)));
        }elseif($query_start_time){
            $condition['pdc_add_time'] = array('>',strtotime($query_start_time));
        }elseif($query_end_time){
            $condition['pdc_add_time'] = array('<',strtotime($query_end_time));
        }
        $member_cash = Model("Predeposit");
        $result = $member_cash->getPdCashList($condition,15);
        $sum = $member_cash->getAllCount();
        if($sum[0]['num']){
            $this->assign('sum', $sum[0]['num']);
        }
        $this->assign('result', $result);
        $this->assign('page', $member_cash->page_info->render());
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    //会员提现，后台标识
    public function member_option(){
        $pdc_id = input('param.pdc_id');
        $status = input('param.status');
        $id = input('param.id');
        $name = input('param.name');
        $price = input('param.price');
        $member_cash = Model("Predeposit");
        $result = $member_cash->editPdCash(array('status'=>$status),array('pdc_id'=>$pdc_id));
        if($result&&$status==2){
            $log_model = Model("Adminpdlog");
            $member_data = [
                "lg_member_id" => $id,
                "lg_member_name" => $name,
                "lg_type" => "cash_pay",
                "lg_av_amount" => $price,
                "lg_add_time" => time(),
                "lg_desc" => "会员提现。"
            ];
            $log_model->addLog($member_data);
            $this->success("提现成功", 'Membercash/index');
        }else{
            $this->success("提现失败", 'Membercash/index');
        }
    }

    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '管理',
                'url' => url('Admin/Membercash/index')
            )
        );

        return $menu_array;
    }

}
