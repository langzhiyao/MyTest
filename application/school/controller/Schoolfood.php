<?php

namespace app\school\controller;

use think\Lang;
use think\Validate;
/**
 * 套餐展示
 */
class Schoolfood extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'school/lang/zh-cn/admin.lang.php');
        Lang::load(APP_PATH . 'school/lang/zh-cn/schoolfood.lang.php');
    }

    public function index(){
        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_gid']!=5){
            $this->error(lang('ds_assign_right'));
        }
        $schoolid = isset($admininfo['admin_school_id'])?$admininfo['admin_school_id']:0;
        $model_bus = Model("Schoolfood");
        $condtion = array();
        $condtion['is_del'] = 1;
        $condtion['sc_id'] = $schoolid;
        $busList = $model_bus->get_schoolfood_List($condtion);
        if(!empty($busList)){
            $week = array(0=>"日",1=>"一",2=>"二",3=>"三",4=>"四",5=>"五",6=>"六");
            foreach($busList as $k=>$v){
                $day = explode(',',$v['bus_repeat']);
                $wes = [];
                foreach($day as $ke=>$item){
                    $wes[] = $week[$item];
                }
                $busList[$k]['week'] = implode(',',$wes);
                $line = "";
                $busline = json_decode($v['bus_line'],true);
                foreach($busline as $key=>$val){
                    $line .= $val['Station']."/".$val['ArrivalTime'].'--';
                    $busList[$k]['line'] = rtrim($line, '--');
                }

            }
        }
        $this->assign('busList', $busList);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    public function schoolfood_manage1(){
        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_gid']!=5){
            $this->error(lang('ds_assign_right'));
        }
        $Schoolfood = model('Schoolfood');
        $condition = array();        
        $food_list = $Schoolfood->get_schoolfood_List($condition, '10' ,'food_id asc');

        foreach ($food_list as $k => &$v) {
            $v['food_linea']=json_decode($v['food_line'],TRUE);
        }
        unset($v);
        p($food_list);exit;
        $this->assign('food_list', $food_list);
        $this->assign('page', $Schoolfood->page_info->render());
        $this->setAdminCurItem('schoolfood_manage');
        return $this->fetch('schoolfood');
    }

    public function schoolfood_manage(){
        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_gid']!=5){
            $this->error(lang('ds_assign_right'));
        }
        $this->setAdminCurItem('schoolfood_manage');
        return $this->fetch();
    }


    public function schoolfood_edit(){
        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_gid']!=5){
            $this->error(lang('ds_assign_right'));
        }
        $schoolid = isset($admininfo['admin_school_id'])?$admininfo['admin_school_id']:0;
        $Schoolfood = Model('Schoolfood');
        $food_id = input('param.food_id');
        if (request()->isPost() || input('param.actions')) {
            $param =array();
            $input = input();
            $food_class      = input('post.food_class');
            $foodContent = $input['food_content'];            
            if($food_id){
                $param['food_class']   = input('post.food_class');
                $param['food_name']    = input('post.food_name');
                $param['food_content'] = input('post.food_content');
                $param['food_desc']    = input('post.food_desc');
                $param['up_time']      = time();
            }else{
                foreach ($foodContent as $k => $v) {
                    $param[$k] = array(
                        'food_class'   => $food_class,
                        'food_name'    => $v[0],
                        'food_content' => $v[1],
                        'food_desc'    => $v[2],
                        'up_time'      => time(),
                        'sc_id'        => $schoolid
                    );
                }                                           
            }
            switch (input('actions')) {
                case 'edit'://改
                    $result = $Schoolfood->schoolfood_update($param,array('food_id'=>$food_id));
                    if ($result) {
                        $this->log(lang('food_edit_succ') . '[' . input('post.food_card') . ']', null);
                        $this->success("编辑成功", 'Schoolfood/index');
//                        echo json_encode(['m' => true, 'ms' => lang('food_edit_succ')]);
                    }
                    break; 
                case 'del'://删
                    $result = $Schoolfood->schoolfood_update(array('is_del'=>2),array('food_id'=>$food_id));
                    if ($result) {
                        $this->log(lang('food_del_succ') . '[' . input('post.food_card') . ']', null);
                        $this->success("删除成功", 'Schoolfood/index');
                        //echo json_encode(['m' => true, 'ms' => lang('food_del_succ')]);
                    }
                    break;               
                default://增
                    $result = $Schoolfood->schoolfood_add($param);
                    if ($result) {
                        $this->log(lang('food_add_succ') . '[' . input('post.food_card') . ']', null);
                        $this->success("添加成功", 'Schoolfood/index');
                        //echo json_encode(['m' => true, 'ms' => lang('food_add_succ')]);
                    }
                    break;
            }
            exit;

        }else{
            $food_id = $Schoolfood->getOneById($food_id);
            $this->assign('food_info', $food_id);
            $this->setAdminCurItem('schoolfood_edit');
            return $this->fetch();
        }
    }



    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '管理',
                'url' => url('School/Schoolfood/index')
            ),
        );

        if (request()->action() == 'schoolfood_manage' || request()->action() == 'index') {
            $menu_array[] = array(
                'name' => 'schoolfood_manage',
                'text' => '添加食谱',
                'url' => url('School/Schoolfood/schoolfood_manage')
            );
        }
        if (request()->action() == 'schoolfood_edit') {
            $menu_array[] = array(
                'name' => 'schoolfood_edit',
                'text' => '编辑',
                'url' => url('School/Schoolfood/schoolfood_edit')
            );
        }
        return $menu_array;
    }

}

?>
