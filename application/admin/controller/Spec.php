<?php

/*
 * 规格管理
 */

namespace app\admin\controller;

use think\View;
use think\Url;
use think\Lang;
use think\Request;
use think\Db;
use think\Validate;

class Spec extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/spec.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    public function index() {
        if(session('admin_is_super') !=1 && !in_array('4',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $spec_list = db('spec')->paginate(10,false,['query' => request()->param()]);
        // 获取分页显示
        $page = $spec_list->render();
        $this->assign('spec_list', $spec_list);
        $this->assign('page', $page);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    public function spec_add() {
        if(session('admin_is_super') !=1 && !in_array('1',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        if (!(request()->isPost())) {
            $spec = [
                'sp_name' => '',
                'sp_sort' => 0,
                'gc_id' => 0,
                'gc_name' => '',
            ];
            $this->assign('spec', $spec);
            $gc_list = model('goodsclass')->getGoodsClassListByParentId(0);
            $this->assign('gc_list', $gc_list);
            $this->setAdminCurItem('spec_add');
            return $this->fetch('spec_form');
        } else {
            $data = array(
                'sp_name' => input('post.sp_name'),
                'sp_sort' => input('post.sp_sort'),
                'gc_id' => input('post.gc_id'),
                'gc_name' => input('post.gc_name'),
            );
            //验证数据  BEGIN
            $rule = [
                ['sp_name', 'require', '规格名称为必填'],
                ['sp_sort', 'require|number', '规格排序为必填|规格排序必须为数字'],
                ['gc_id', 'require|number', '分类为必填|分类ID必须为数字'],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($data);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END
            $result = db('spec')->insert($data);
            if ($result) {
                $this->success(lang('ds_common_op_succ'), 'Spec/index');
            } else {
                $this->error(lang('error'));
            }
        }
    }

    public function spec_edit() {
        if(session('admin_is_super') !=1 && !in_array('3',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        //注：pathinfo地址参数不能通过get方法获取，查看“获取PARAM变量”
        $sp_id = input('param.sp_id');
        if (empty($sp_id)) {
            $this->error(lang('param_error'));
        }
        if (!request()->isPost()) {
            $map['sp_id'] = $sp_id;
            $spec = db('spec')->where($map)->find();
            $this->assign('spec', $spec);
            $gc_list = model('goodsclass')->getGoodsClassListByParentId(0);
            $this->assign('gc_list', $gc_list);
            $this->setAdminCurItem('spec_edit');
            return $this->fetch('spec_form');
        } else {
            $data = array(
                'sp_name' => input('post.sp_name'),
                'sp_sort' => input('post.sp_sort'),
                'gc_id' => input('post.gc_id'),
                'gc_name' => input('post.gc_name'),
            );
            //验证数据  BEGIN
            $rule = [
                ['sp_name', 'require', '规格名称为必填'],
                ['sp_sort', 'require|number', '规格排序为必填|规格排序必须为数字'],
                ['gc_id', 'require|number', '分类为必填|分类ID必须为数字'],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($data);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END
            $result = db('spec')->where('sp_id', $sp_id)->update($data);
            if ($result) {
                $this->success('edit_ok', 'Spec/index');
            } else {
                $this->error('编辑失败');
            }
        }
    }

    public function spec_drop() {
        if(session('admin_is_super') !=1 && !in_array('2',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        //注：pathinfo地址参数不能通过get方法获取，查看“获取PARAM变量”
        $sp_id = input('param.sp_id');
        if (empty($sp_id)) {
            $this->error(lang('param_error'));
        }
        $result = db('spec')->where(array('sp_id' => $sp_id))->delete();
        if ($result) {
            $this->success(lang('ds_common_del_succ'), 'Spec/index');
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
                'text' => '管理',
                'url' => url('Admin/Spec/index')
            ),
        );
        if(session('admin_is_super') ==1 || in_array('1',$this->action)){
            if (request()->action() == 'spec_add' || request()->action() == 'index') {
                $menu_array[] = array(
                    'name' => 'spec_add',
                    'text' => '新增规格',
                    'url' => url('Admin/Spec/spec_add')
                );
            }
        }
        if(session('admin_is_super') ==1 || in_array('3',$this->action)){
            if (request()->action() == 'spec_edit') {
                $menu_array[] = array(
                    'name' => 'spec_edit',
                    'text' => '编辑规格',
                    'url' => url('Admin/Spec/spec_edit')
                );
            }
        }


        return $menu_array;
    }
}

?>
