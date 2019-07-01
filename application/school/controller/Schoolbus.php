<?php

namespace app\school\controller;

use think\Lang;
use think\Validate;
/**
 * 套餐展示
 */
class Schoolbus extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'school/lang/zh-cn/admin.lang.php');
        Lang::load(APP_PATH . 'school/lang/zh-cn/schoolbus.lang.php');
    }

    public function index(){
        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_gid']!=5){
            $this->error(lang('ds_assign_right'));
        }
        $schoolid = isset($admininfo['admin_school_id'])?$admininfo['admin_school_id']:0;
        $model_bus = Model("Schoolbus");
        $condtion = array();
        $condtion['is_del'] = 1;
        $condtion['sc_id'] = $schoolid;
        $busList = $model_bus->get_schoolbus_List($condtion);
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

    public function drop() {
        $bus_id = input('param.bus_id');
        if (empty($bus_id)) {
            $this->error(lang('param_error'));
        }
        $model_school = Model('Schoolbus');
        $result = $model_school->schoolbus_update(array('is_del'=>2),array('bus_id'=>$bus_id));
        if ($result) {
            $this->success(lang('ds_common_del_succ'), 'Schoolbus/index');
        } else {
            $this->error('删除失败');
        }
    }

    public function schoolbus_manage1(){
        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_gid']!=5){
            $this->error(lang('ds_assign_right'));
        }
        $Schoolbus = model('Schoolbus');
        $condition = array();        
        $bus_list = $Schoolbus->get_schoolbus_List($condition, '10' ,'bus_id asc');

        foreach ($bus_list as $k => &$v) {
            $v['bus_linea']=json_decode($v['bus_line'],TRUE);
        }
        unset($v);
        // p($bus_list);exit;
        $this->assign('bus_list', $bus_list);
        $this->assign('page', $Schoolbus->page_info->render());
        $this->setAdminCurItem('schoolbus_manage');
        return $this->fetch('bus');
    }

    public function schoolbus_manage(){
        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_gid']!=5){
            $this->error(lang('ds_assign_right'));
        }
        $this->setAdminCurItem('schoolbus_manage');
        return $this->fetch();
    }


    public function schoolbus_edit(){
        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_gid']!=5){
            $this->error(lang('ds_assign_right'));
        }
        $schoolid = isset($admininfo['admin_school_id'])?$admininfo['admin_school_id']:0;
        $bus_id = input('param.bus_id');
        $Schoolbus = Model('Schoolbus');
        if (request()->isPost()) {
            $param =array();
            $input = input();
            $param['bus_card']       = input('post.bus_card');
            $param['bus_line_name']  = input('post.bus_line_name');
            $param['bus_color']      = input('post.bus_color');
            $param['bus_desc']       = input('post.bus_desc');
            $param['bus_start']      = input('post.bus_start');
            $param['bus_end']        = input('post.bus_end');
            $param['bus_start_time'] = input('post.bus_start_time');
            $param['bus_end_time']   = input('post.bus_end_time');            
            $param['up_time']        = time();

            $bus_repeat ='';
            foreach ($input['week'] as $key => $w) {
                $bus_repeat .= $w['week'].',';
            }
            $param['bus_repeat'] = trim($bus_repeat,',');
            $bus_line = array();
            foreach ($input['bus_line'] as $k => $v) {
                $bus_line[$k]['Station'] =$v[0];
                $bus_line[$k]['ArrivalTime'] =$v[1];
            }
            $param['bus_line'] = json_encode($bus_line);

            switch (input('actions')) {
                case 'edit'://改
                    $result = $Schoolbus->schoolbus_update($param,array('bus_id'=>$bus_id));
                    if ($result) {
                        $this->log(lang('bus_edit_succ') . '[' . input('post.bus_card') . ']', null);
                        $this->success("编辑成功", 'Schoolbus/index');
//                        echo json_encode(['m' => true, 'ms' => lang('bus_edit_succ')]);
                    }
                    break; 
                case 'del'://删
                    $del=array(
                        'bus_id' =>intval(input('param.bus_id')),
                        'is_del' =>2
                    );
                    $result = $Schoolbus->schoolbus_update($del);
                    if ($result) {
                        $this->log(lang('bus_del_succ') . '[' . input('post.bus_card') . ']', null);
                        echo json_encode(['m' => true, 'ms' => lang('bus_del_succ')]);
                    }
                    break;               
                default://增
                    $param['sc_id']   = $schoolid;
                    $result = $Schoolbus->schoolbus_add($param);
                    if ($result) {
                        $this->log(lang('bus_add_succ') . '[' . input('post.bus_card') . ']', null);
                        $this->success("新增成功", 'Schoolbus/index');
//                        echo json_encode(['m' => true, 'ms' => lang('bus_add_succ')]);
                    }
                    break;
            }
            exit;

        }else{
            $bus_info = $Schoolbus->getOneById($bus_id);
            if($bus_info['bus_line']!=""){
                $bus_info['bus_line'] = json_decode($bus_info['bus_line'],true);
            }
            if($bus_info['bus_repeat']!=""){
                $bus_info['bus_repeat'] = explode(',',$bus_info['bus_repeat']);
            }
            $week = array(0=>"星期日",1=>"星期一",2=>"星期二",3=>"星期三",4=>"星期四",5=>"星期五",6=>"星期六");
            $this->assign('week', $week);
            $this->assign('bus_info', $bus_info);
            $this->setAdminCurItem('schoolbus_edit');
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
                'url' => url('School/Schoolbus/index')
            ),
        );

        if (request()->action() == 'schoolbus_manage' || request()->action() == 'index') {
            $menu_array[] = array(
                'name' => 'schoolbus_manage',
                'text' => '添加校车',
                'url' => url('School/Schoolbus/schoolbus_manage')
            );
        }
        if (request()->action() == 'schoolbus_edit') {
            $menu_array[] = array(
                'name' => 'schoolbus_edit',
                'text' => '编辑',
                'url' => url('School/Schoolbus/schoolbus_edit')
            );
        }
        return $menu_array;
    }

}

?>
