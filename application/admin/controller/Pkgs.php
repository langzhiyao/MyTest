<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;
/**
 * 套餐展示
 */
class Pkgs extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/pkgs.lang.php');
        Lang::load(APP_PATH . 'admin/lang/zh-cn/admin.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        // halt($perm_id);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    /**
     *
     * 管理广告位
     */
    public function pkgs_manage() {
        if(session('admin_is_super') !=1 && !in_array(4,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $pkg = model('pkgs');
        /**
         * 多选删除广告位
         */
        if (!request()->isPost()) {
            $condition = array();
            $orderby = '';
            $search_name = trim(input('get.search_name'));
            if (input('param.pkg_type',1)==1) {
                $condition['pkg_type']= 1;
                $this->setAdminCurItem('witch_manage');
            }else{
                $condition['pkg_type']= 2 ;
                $this->setAdminCurItem('revisit_manage');
            }
            if ($search_name != '') {
                $condition['pkg_name'] = array('like', '%' . trim($search_name) . '%');
            }
            $pkg_list = $pkg->getPkgList($condition, 10,'pkg_sort asc');
            $this->assign('pkg_list', $pkg_list);

            $this->assign('page', $pkg->page_info->render());
            return $this->fetch('pkgs_manage');
        }
    }

    public function courseware(){
        $pkg = model('pkgs');

        $condition = array();
        $orderby = '';
        $condition['pkg_type']= 3 ;
        
        $pkg_list = $pkg->getPkgList($condition, '10' ,'pkg_id desc');
        $this->assign('pkg_list', $pkg_list);
        $this->assign('page', $pkg->page_info->render());
        $this->setAdminCurItem('courseware');
        return $this->fetch('courseware');
    }

    public function course_edit(){
        if (request()->isPost()) {
            $Pkgs = Model('pkgs');
            $price = intval(input('price'));
            
            $param =array();            
            $param['pkg_price']  = trim(input('post.price'));
            $param['pkg_name']   = empty(trim(input('post.pname')))?'EasyTeacher':trim(input('post.pname'));
            switch (input('actions')) {
                case 'edit':
                    $param['pkg_id'] = intval(input('param.pkg_id'));
                    $result = $Pkgs->pkg_update($param);
                    if ($result) {
                        $this->log(lang('cour_edit_succ') . '[' . input('post.pkg_name') . ']', null);
                        echo json_encode(['m'=>true,'ms'=>lang('cour_edit_succ')]); 
                    }
                    break;                
                default:
                    $param['pkg_sort']   = 0;
                    $param['pkg_type']   = 3;
                    $param['pkg_length'] = 1;
                    $param['pkg_axis']   = 'day';
                    $param['pkg_desc']   = lang('easyteacher');
                    $param['pkg_enabled'] = 1;
                    $result = $Pkgs->pkg_add($param);
                    if ($result) {
                        $this->log(lang('cour_add_succ') . '[' . input('post.pkg_name') . ']', null);
                        echo json_encode(['m'=>true,'ms'=>lang('cour_add_succ')]); 
                    }
                    break;
            }
            exit;

        }
        
        return $this->fetch('course_edit');
    }

    /**
     *
     * 
     */
    public function pkgs_edit() {
        if(session('admin_is_super') !=1 && !in_array(3,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $pkg_id = intval(input('param.pkg_id'));
        $Pkgs = Model('pkgs');
        if (request()->isPost()) {            
            $rule = [
                ['pkg_name', 'require', lang('pkg_name_err')],
                ['pkg_price', 'require', lang('pkg_price_err')],
                ['pkg_length', 'require', lang('pkg_length_err')],
            ];
            //验证数据  BEGIN
            $validate = new Validate($rule);
            $param =array();
            $param['pkg_name']   = trim(input('post.pkg_name'));
            $param['pkg_sort']   = intval(trim(input('post.pkg_sort')));
            $param['pkg_cprice']  = trim(input('post.pkg_cprice'));
            $param['pkg_price']  = trim(input('post.pkg_price'));
            $param['pkg_type']   = intval(trim(input('post.pkg_type')));
            $param['pkg_length'] = intval(trim(input('post.pkg_length')));
            $param['pkg_axis']   = trim(input('post.pkg_axis'));
            $param['pkg_desc']   = trim(input('post.pkg_desc'));
            $param['up_time']    = time();
            $validate_result     = $validate->check($param);
            if (input('post.pkg_enabled') != '') {
                $param['pkg_enabled'] = intval(input('post.pkg_enabled'));
            }
            if (!$pkg_id) {//新增                                                
                if (!$validate_result)$this->error($validate->getError());
                //验证数据  END
                $result = $Pkgs->pkg_add($param);
                if ($result) {
                    $this->log(lang('pkg_add_succ') . '[' . input('post.pkg_name') . ']', null);
                    $this->success(lang('pkg_add_succ'), url('Admin/Pkgs/pkgs_manage'),array('pkg_type'=>$param['pkg_type']));
                } else {
                    $this->error(lang('pkg_add_fail'));
                }
            }else{//编辑
                $param['pkg_id'] = $pkg_id;                
                if (!$validate_result)$this->error($validate->getError());
                //验证数据  END
                $result = $Pkgs->pkg_update($param);
                if ($result) {
                    $this->log(lang('pkg_edit_succ') . '[' . input('post.pkg_name') . ']', null);
                    $this->success(lang('pkg_edit_succ'), url('Admin/Pkgs/pkgs_manage'),array('pkg_type'=>$param['pkg_type']));
                } else {
                    $this->error(lang('pkg_edit_fail'));
                }
            }
            
        }
        $condition['pkg_id'] = $pkg_id;
        $pkg_info = $Pkgs->getOneById($pkg_id);
        $this->assign('axis_list', config('pkgs_list'));
        $this->assign('ref_url', getReferer());
        $this->assign('pkg_info', $pkg_info);
        $this->setAdminCurItem('pkgs_edit');
        return $this->fetch('pkgs_form');
    }



    /**
     *
     * 删除套餐
     */
    public function pkgs_del() {
        if(session('admin_is_super') !=1 && !in_array(2,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $Pkgs = Model('pkgs');
        /**
         * 删除套餐
         */
        $pkg_id = intval(input('param.pkg_id'));
        $result = $Pkgs->pkg_del($pkg_id);

        if (!$result) {
            $this->error(lang('pkg_del_fail'));
        } else {
            $this->log(lang('pkg_del_succ') . '[' . $pkg_id . ']', null);
            $this->success(lang('pkg_del_succ'));
        }
    }


    /**
     *
     * 获取UNIX时间戳
     */
    public function getunixtime($time) {
        $array = explode("-", $time);
        $unix_time = mktime(0, 0, 0, $array[1], $array[2], $array[0]);
        return $unix_time;
    }

    /**
     *
     * ajaxOp
     */
    public function ajax() {
        switch ($_GET['branch']) {
            case 'pkg_enabled':
                $Pkgs = Model('pkgs');
                $param[trim($_GET['column'])] = intval($_GET['val']);
                $param['pkg_id'] = intval($_GET['id']);
                $Pkgs->pkg_update($param);
                echo 'true';
                exit;
                break;
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'witch_manage',
                'text' => lang('witch_manage'),
                'url' => url('Admin/Pkgs/pkgs_manage',['pkg_type'=>1])
            )
        );
        /*$menu_array[] = array(
             'name' => 'witch_back_manage',
                'text' => lang('witch_back_manage'),
                'url' => url('Admin/Pkgs/pkgs_manage',['pkg_type'=>2])
        );*/
        $menu_array[] = array(
             'name' => 'revisit_manage',
                'text' => lang('revisit_manage'),
                'url' => url('Admin/Pkgs/pkgs_manage',['pkg_type'=>2])
        );
        $menu_array[] = array(
            'name' => 'courseware',
            'text' => lang('courseware'),
            'url' => url('Admin/Pkgs/courseware',['pkg_type'=>3])
        );
        $menu_array[] = array(
            'name' => 'pkgs_edit',
            'text' => lang('pkgs_edit'),
            'url' => url('Admin/Pkgs/pkgs_edit')
        );
        return $menu_array;
    }

}

?>
