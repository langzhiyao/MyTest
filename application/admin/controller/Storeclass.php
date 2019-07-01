<?php

/**
 * 店铺分类
 */

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Storeclass extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/storeclass.lang.php');
    }

    /**
     * 店铺分类
     */
    public function store_class() {
        $model_class = Model('storeclass');

        if (!request()->isPost()) {
            $store_class_list = $model_class->getStoreClassList(array(), 20);
            $this->assign('class_list', $store_class_list);
            $this->assign('page', $model_class->page_info->render());
            $this->setAdminCurItem('store_class');
            return $this->fetch('store_class_index');
        } else {
            //删除
            if (!empty($_POST['check_sc_id']) && is_array($_POST['check_sc_id'])) {
                $result = $model_class->delStoreClass(array('sc_id' => array('in', $_POST['check_sc_id'])));
                if ($result) {
                    $this->log(lang('ds_del').lang('store_class') . '[ID:' . implode(',', $_POST['check_sc_id']) . ']', 1);
                    $this->success(lang('ds_common_del_succ'));
                }
            }
            $this->error(lang('ds_common_del_fail'));
        }
    }

    /**
     * 商品分类添加
     */
    public function store_class_add() {
        $model_class = model('storeclass');

        if (!request()->isPost()) {
            $this->setAdminCurItem('store_class_add');
            return $this->fetch('store_class_add');
        } else {
            $insert_array = array();
            $insert_array['sc_name'] = input('post.sc_name');
            $insert_array['sc_bail'] = intval(input('post.sc_bail'));
            $insert_array['sc_sort'] = intval(input('post.sc_sort'));


            //验证数据  BEGIN
            $rule = [
                ['sc_name', 'require', '分类名称必填']
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($insert_array);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END


            $result = $model_class->addStoreClass($insert_array);
            if ($result) {
                $this->log(lang('ds_add').lang('store_class') . '[' . input('post.sc_name') . ']', 1);
                $this->success(lang('ds_common_save_succ'), url('Admin/Storeclass/store_class'));
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 编辑
     */
    public function store_class_edit() {
        $model_class = model('storeclass');

        if (!request()->isPost()) {
            $class_array = $model_class->getStoreClassInfo(array('sc_id' => intval(input('param.sc_id'))));
            if (empty($class_array)) {
                $this->error(lang('illegal_parameter'));
            }

            $this->assign('class_array', $class_array);
            $this->setAdminCurItem('store_class_edit');
            return $this->fetch('store_class_edit');
        } else {
            $update_array = array();
            $update_array['sc_name'] = input('post.sc_name');
            $update_array['sc_bail'] = intval(input('post.sc_bail'));
            $update_array['sc_sort'] = intval(input('post.sc_sort'));
            
            //验证数据  BEGIN
            $rule = [
                ['sc_name', 'require', '分类名称必填']
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($update_array);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END
            
            $result = $model_class->editStoreClass($update_array, array('sc_id' => intval(input('param.sc_id'))));
            if ($result) {
                $this->log(lang('ds_edit').lang('store_class') . '[' . input('post.sc_name') . ']', 1);
                $this->success(lang('ds_common_save_succ'), url('Admin/Storeclass/store_class'));
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 删除分类
     */
    public function store_class_del() {
        $model_class = model('storeclass');
        if (intval(input('param.sc_id')) > 0) {
            $array = array(intval(input('param.sc_id')));
            $result = $model_class->delStoreClass(array('sc_id' => intval(input('param.sc_id'))));
            if ($result) {
                $this->log(lang('ds_del').lang('store_class') . '[ID:' . input('param.sc_id') . ']', 1);
                $this->success(lang('ds_common_del_succ'), getReferer());
            }
        }
        $this->error(lang('ds_common_del_fail'), url('Admin/Storeclass/store_class'));
    }

    /**
     * ajax操作
     */
    public function ajax() {
        $model_class = model('storeclass');
        $update_array = array();
        $branch = input('param.branch');
        switch ($branch) {
            //分类：验证是否有重复的名称
            case 'store_class_name':
                $condition = array();
                $condition['sc_name'] = input('get.value');
                $condition['sc_id'] = array('sc_id' => array('neq', intval(input('param.sc_id'))));
                $class_list = $model_class->getStoreClassList($condition);
                if (empty($class_list)) {
                    $update_array['sc_name'] = input('get.value');
                    $update = $model_class->editStoreClass($update_array, array('sc_id' => intval($_GET['id'])));
                    $return = $update ? 'true' : 'false';
                } else {
                    $return = 'false';
                }
                break;
            //分类： 排序 显示 设置
            case 'store_class_sort':
                $update_array['sc_sort'] = intval(input('get.value'));
                $result = $model_class->editStoreClass($update_array, array('sc_id' => intval($_GET['id'])));
                $return = $result ? 'true' : 'false';
                break;
            //分类：添加、修改操作中 检测类别名称是否有重复
            case 'check_class_name':
                $condition['sc_name'] = input('get.sc_name');
                $class_list = $model_class->getStoreClassList($condition);
                $return = empty($class_list) ? 'true' : 'false';
                break;
        }
        exit($return);
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'store_class',
                'text' => '管理',
                'url' => url('Admin/Storeclass/store_class')
            ),
        );

        if (request()->action() == 'store_class_add' || request()->action() == 'store_class') {
            $menu_array[] = array(
                'name' => 'store_class_add',
                'text' => '新增',
                'url' => url('Admin/Storeclass/store_class_add')
            );
        }
        if (request()->action() == 'store_class_edit') {
            $menu_array[] = array(
                'name' => 'store_class_edit',
                'text' => '编辑',
                'url' => url('Admin/Storeclass/store_class_edit')
            );
        }
        return $menu_array;
    }

}

?>
