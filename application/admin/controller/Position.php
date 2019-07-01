<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Position extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/school.lang.php');
        Lang::load(APP_PATH . 'admin/lang/zh-cn/admin.lang.php');
        Lang::load(APP_PATH . 'admin/lang/zh-cn/look.lang.php');
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
        $model_position = model('Position');
        $condition = array();
        $position = input('param.position');//学校名称
        if ($position) {
            $condition['p.position'] = array('like', "%" . $position . "%");
        }
        //地区
        if(!empty($_GET['province'])){
            $province_id=input('param.province');
            $condition['p.province_id']=$province_id;
        }
        if(!empty($_GET['city'])) {
            $city_id = input('param.city');
            $condition['p.city_id'] =$city_id;
        }
        if(!empty($_GET['area'])){
            $area_id = input('param.area');
            $condition['p.area_id'] =$area_id;
        }
        if(!empty($_GET['school'])){
            $school_id = input('param.school');
            $condition['p.school_id'] =$school_id;
        }

        /*$school_type = input('param.grade');//学校类型
        if ($school_type) {
            $condition['p.type_id'] = $school_type;
        }*/
//        if(!empty($_GET['position'])){
//            $position_id = input('param.position');
//            $condition['p.position_id'] =$position_id;
//        }

        $position_list = $model_position->get_position_list($condition,15);
        //地区信息
        $region_list = db('area')->where('area_parent_id','0')->select();
        //学校
        $condition_school['isdel'] = 1;
        $model_school = model('School');
        $school_list = $model_school->getAllAchool($condition_school,'schoolid,name');
        $this->assign('schoolList', $school_list);
        $this->assign('region_list', $region_list);
        $this->assign('page', $model_position->page_info->render());
        $this->assign('position_list', $position_list);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    public function add() {
        if(session('admin_is_super') !=1 && !in_array(1,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        if (!request()->isPost()) {
            //地区信息
            $region_list = db('area')->where('area_parent_id','0')->select();
            $this->assign('region_list', $region_list);
            $this->setAdminCurItem('add');
            return $this->fetch();
        } else {
            $model_school = model('School');
            $model_position = model('Position');
            $data = array(
                'school_id' => input('post.school'),
//                'type_id' => input('post.grade'),
                'position' => input('post.school_position_name'),
                'desc' => input('post.position_desc'),
            );
            $schoolinfo = $model_school->find(array("schoolid"=>input('post.school')));
            $data['province_id'] = $schoolinfo['provinceid'];
            $data['city_id'] = $schoolinfo['cityid'];
            $data['area_id'] = $schoolinfo['areaid'];
            $data['region'] = $schoolinfo['region'];
            //验证数据  END
            $result = $model_position->insertGetId($data);
            if ($result) {
                $this->success(lang('school_position_add_succ'), 'Position/index');
            } else {
                $this->error(lang('school_position_add_fail'));
            }
        }
    }

    public function edit() {
        if(session('admin_is_super') !=1 && !in_array(3,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $position_id = input('param.position_id');
        if (empty($position_id)) {
            $this->error(lang('param_error'));
        }
        $model_position = Model('position');
        if (!request()->isPost()) {
            $condition['position_id'] = $position_id;
            $position_array = $model_position->where($condition)->find();
            //地区信息
            $region_list = db('area')->where('area_parent_id','0')->select();
            $this->assign('region_list', $region_list);
            $this->assign('position_array', $position_array);
            $this->setAdminCurItem('edit');
            return $this->fetch();
        } else {
            $model_school = model('School');
            $model_position = model('Position');
            $data = array(
                'school_id' => input('post.school'),
//                'type_id' => input('post.grade'),
                'position' => input('post.school_position_name'),
                'desc' => input('post.position_desc'),
            );
            $schoolinfo = $model_school->find(array("schoolid"=>input('post.school')));
            $data['province_id'] = $schoolinfo['provinceid'];
            $data['city_id'] = $schoolinfo['cityid'];
            $data['area_id'] = $schoolinfo['areaid'];
            $data['region'] = $schoolinfo['region'];
            //验证数据  END
            $result = $model_position->where(array('position_id'=>$position_id))->update($data);
            if ($result) {
                $this->success('编辑成功', 'Position/index');
            } else {
                $this->error('编辑失败');
            }
        }
    }

    /**
     * ajax操作
     */
    public function ajax() {
        $branch = input('param.branch');

        switch ($branch) {
            /**
             * 验证房间位置名是否重复
             */
            case 'check_position_name':
                $class_member = Model('position');
                if(input('param.position_id')){
                    $condition['position_id'] = array('neq',input('param.position_id'));
                }

                $condition['position'] = input('param.position');
                $condition['school_id'] = input('param.school_id');
//                $condition['type_id'] = input('param.type_id');
                $list = $class_member->where($condition)->find();
                if (empty($list)) {
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
        }
    }

    /**
     * 重要提示，删除会员 要先确定删除店铺,然后删除会员以及会员相关的数据表信息。这个后期需要完善。
     */
    public function drop() {
        if(session('admin_is_super') !=1 && !in_array(2,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $position_id = input('param.position_id');
        if (empty($position_id)) {
            $this->error(lang('param_error'));
        }
        $position = db('position')->where(['position_id'=>$position_id,'is_bind'=>2])->limit(1)->find();
        if($position){
            $this->error('该位置已被绑定，无法删除');
        }
        $model_position = Model('position');
        $result = $model_position->where(array('position_id'=>$position_id))->delete();
        if ($result) {
            $this->success(lang('ds_common_del_succ'), 'Position/index');
        } else {
            $this->error('删除失败');
        }
    }


    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '房间列表',
                'url' => url('Admin/Position/index')
            ),
        );
        if(session('admin_is_super') ==1 || in_array(1,$this->action )){
            if (request()->action() == 'add' || request()->action() == 'index') {
                $menu_array[] = array(
                    'name' => 'add',
                    'text' => '添加房间位置',
                    'url' => url('Admin/Position/add')
                );
            }
        }
        if (request()->action() == 'edit') {
            $position_id= trim($_GET['position_id']);
            $menu_array[] = array(
                'name' => 'edit',
                'text' => '编辑房间位置',
                'url' => url('Admin/Position/edit',array('position_id'=>$position_id))
            );
        }
        /*if (request()->action() == 'addclass') {
            $position_id= trim($_GET['position_id']);
            $menu_array[] = array(
                'name' => 'addclass',
                'text' => '添加班级',
                'url' => url('Admin/Position/addclass',array('position_id'=>$position_id))
            );
        }*/

        return $menu_array;
    }

}

?>
