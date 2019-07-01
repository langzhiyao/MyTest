<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Robot extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/member.lang.php');
        Lang::load(APP_PATH . 'admin/lang/zh-cn/look.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
//        halt($class_name);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    public function robot() {
        if(session('admin_is_super') !=1 && !in_array(4,$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $Robot = model('robot');
        $condition = array();
        $input = input();
        $search_field_value = input('search_field_value');
        $search_field_name = input('search_field_name');
        if ($search_field_value != '') {
            $School = model('School');
            switch ($search_field_name) {
                case 'robot_school':
                    $schoolList=$School->getAllAchool(array('name'=>array('like', '%' . trim($search_field_value) . '%')),'schoolid');
                    $left = array_column($schoolList, 'schoolid');
                    $condition['r.schoolid'] = array('in',$left);
                    break;
                case 'robot_name':
                    $condition['r.SNNumber'] = array('like', '%' . trim($search_field_value) . '%');
                    break;
            }
        }
        // p($condition);exit;
        $robot_list = $Robot->getRobotList($condition, '*', 10);
        $this->assign('robot_list', $robot_list);
        $this->assign('page', $Robot->page_info->render());

        $this->setAdminCurItem('robot');
        return $this->fetch();
    }

    public function add() {
        if(session('admin_is_super') !=1 && !in_array(1,$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        if (!request()->isPost()) {
            //地区信息
            $region_list = db('area')->where('area_parent_id','0')->select();
            $this->assign('region_list', $region_list);
            $this->setAdminCurItem('add');
            return $this->fetch();
        } else {
            $Robot = model('robot');
            $input = input();
            $data = array(
                'SNNumber'   => $input['SNNumber'],
                'schoolid'   => $input['order_state'],
                'isdel'      => $input['isdel'],
                'desc'       => $input['desc'],
                'creattime'  => TIMESTAMP,
                'updatetime' => TIMESTAMP,
            );
            $result = $Robot->Robotadd($data);
            if ($result) {
                $this->success('机器人添加成功', 'Robot/robot');
            } else {
                $this->error('机器人添加失败');
            }
        }
    }

    public function edit() {
        if(session('admin_is_super') !=1 && !in_array(3,$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        //注：pathinfo地址参数不能通过get方法获取，查看“获取PARAM变量”
        $id = input('param.id');
        if (empty($id)) {
            $this->error(lang('param_error'));
        }
        $Robot = Model('Robot');
        if (!request()->isPost()) {
            $condition['id'] = $id;
            $robot_array = $Robot->getOne($condition);
            $city = db('school')->field('provinceid,cityid,areaid,region')->where('schoolid',$robot_array['schoolid'])->find();
            $robot_array=array_merge($robot_array,$city);
            //学校名称
            $schoolname = db('school')->field('schoolid,name')->where('areaid',$robot_array['areaid'])->select();
            $this->assign('schoolname', $schoolname);
            //地区信息
            $region_list = db('area')->where('area_parent_id','0')->select();
            $this->assign('region_list', $region_list);
            $this->assign('robot_array', $robot_array);
            $this->setAdminCurItem('edit');
            return $this->fetch();
        } else {
            $input = input();
            $data = array(
                'SNNumber'   => $input['SNNumber'],
                'schoolid'   => isset($input['order_state'])?$input['order_state']:$input['school_name'],
                'isdel'      => $input['isdel'],
                'desc'       => $input['desc'],
                'updatetime' => TIMESTAMP,
            );
            $result = $Robot->Robotupdate(array('id'=>intval($id)),$data);
            if ($result) {
                $this->success('编辑成功', 'Robot/robot');
            } else {
                $this->error('编辑失败');
            }
        }
    }


    /**
     * 重要提示，删除会员 要先确定删除店铺,然后删除会员以及会员相关的数据表信息。这个后期需要完善。
     */
    public function drop() {
        if(session('admin_is_super') !=1 && !in_array(2,$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        //注：pathinfo地址参数不能通过get方法获取，查看“获取PARAM变量”
        $robot_id = input('param.robot_id');
        if (empty($robot_id)) {
            $this->error(lang('param_error'));
        }
        $result = db('robot')->where('id',$robot_id)->setField('isdel',2);;
        if ($result) {
            $this->success('禁用成功', 'Robot/robot');
        } else {
            $this->error('禁用失败');
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        if(session('admin_is_super') !=1 && !in_array(1,$this->action)){
            $menu_array = array(
                array(
                    'name' => 'robot',
                    'text' => '管理',
                    'url' => url('Admin/Robot/robot')
                ),
            );
        }else{
            $menu_array = array(
                array(
                    'name' => 'robot',
                    'text' => '管理',
                    'url' => url('Admin/Robot/robot')
                ),
            );

            if (request()->action() == 'add' || request()->action() == 'robot') {
                $menu_array[] = array(
                    'name' => 'add',
                    'text' => '新增',
                    'url' => url('Admin/Robot/add')
                );
            }
        }

        if (request()->action() == 'edit') {
            $id=trim($_GET['id']);
            $menu_array[] = array(
                'name' => 'edit',
                'text' => '编辑',
                'url' => url('Admin/Robot/edit',array('id'=>$id))
            );
        }
        return $menu_array;
    }

}

?>
