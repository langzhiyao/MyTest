<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Companybanks extends AdminControl {

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

    //管理
    public function index(){
        if(session('admin_is_super') !=1 && !in_array(4,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $condition = array();
        $true_name = input('param.true_name');//持卡人姓名
        if($true_name!=""){
            $condition['true_name'] = array('like', "%" . $true_name . "%");
        }
        $default_mobile = input('param.default_mobile');//提现编号
        if($default_mobile!=""){
            $condition['default_mobile'] = array('like', "%" . $default_mobile . "%");
        }
        $company_banks = Model("Companybanks");
        $result = $company_banks->getAllBanks($condition,15);
        $this->assign('result', $result);
        $this->assign('page', $company_banks->page_info->render());
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    //添加银行卡信息
    public function bind(){
        if(session('admin_is_super') ==1 || !in_array(1,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $admininfo = $this->getAdminInfo();
        $company_banks = model('Companybanks');
        if (!request()->isPost()) {
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
            $condition['company_id'] = $admininfo['admin_company_id'];
            $banks_array = $company_banks->getOneBanksByCard($condition);
            $this->assign('banks_array', $banks_array);
            $this->setAdminCurItem('bind');
            return $this->fetch();
        } else {
            $bank_id = input('post.bank_id');
            $data = array(
                'area' => input('post.area_id'),
                'region' => input('post.area_info'),
                'bank_info' => input('post.bank_info'),
                'bank_name' => input('post.bank_name'),
                'bank_card' => input('post.bank_card'),
                'true_name' => input('post.true_name'),
                'default_mobile' => input('post.default_mobile'),
                'option_id' => $admininfo['admin_id'],
                'company_id' => $admininfo['admin_company_id'],
                'creattime' => time()
            );
            $city_id = db('area')->where('area_id',input('post.area_id'))->find();
            $data['city'] = $city_id['area_parent_id'];
            $province_id = db('area')->where('area_id',$city_id['area_parent_id'])->find();
            $data['province'] = $province_id['area_parent_id'];
            //验证数据  END
            if(!empty($bank_id)){
                $result = $company_banks->editBanks($data,array("bank_id"=>$bank_id));
            }else{
                $result = $company_banks->addBanks($data);
            }
            if ($result) {
                $this->success("绑定银行卡成功", 'Companybanks/index');
            } else {
                $this->error("绑定银行卡失败");
            }
        }
    }

    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '管理',
                'url' => url('Admin/Companybanks/index')
            )
        );
        if(session('admin_is_super') !=1 && in_array(1,$this->action )){
            $menu_array[] = array(
                'name' => 'bind',
                'text' => '绑定',
                'url' => url('Admin/Companybanks/bind')
            );
        }

        return $menu_array;
    }

}
