<?php

/**
 * 地区设置
 */

namespace app\admin\controller;

use think\View;
use think\Url;
use think\Lang;
use think\Request;
use think\Db;
use think\Validate;

class Region extends AdminControl
{

    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/region.lang.php');
        $this->_Region_mod = model('Region');
        define('MAX_LAYER', 4);
    }

    public function index()
    {
        $region_list = $this->_Region_mod->get_list(0);
        /* 先根排序 */
        foreach ($region_list as $key => $val) {
            $region_list[$key]['switchs'] = 0;
            if ($this->_Region_mod->get_list($val['area_id'])) {
                $region_list[$key]['switchs'] = 1;
            }
        }
        $this->assign('region_list', $region_list);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    function ajax_cate()
    {
        $cate_id = input('param.id');
        if (empty($cate_id)) {
            return;
        }

        $cate = $this->_Region_mod->get_list($cate_id);
        foreach ($cate as $key => $val) {
            $child = $this->_Region_mod->get_list($val['area_id']);
//            $lay = $this->_Region_mod->get_layer($val['cate_id']);
//            if ($lay >= MAX_LAYER) {
//                $cate[$key]['add_child'] = 0;
//            } else {
//                $cate[$key]['add_child'] = 1;
//            }
            if (!$child || empty($child)) {
                $cate[$key]['switchs'] = 0;
            } else {
                $cate[$key]['switchs'] = 1;
            }
        }
        echo json_encode(array_values($cate));
        return;
    }

    public function add()
    {
        if (!request()->isPost()) {
            $area = array(
                'area_name' => '',
                'area_region' => '',
                'area_parent_id' => input('param.area_id'),
                'area_sort' => '0',
            );
            $this->assign('area', $area);
            $this->assign('parents', $this->_get_options());
            $this->setAdminCurItem('add');
            return $this->fetch('form');
        } else {
            $data = array(
                'area_name' => input('post.area_name'),
                'area_region' => input('post.area_region'),
                'area_parent_id' => input('param.area_parentid'),
                'area_sort' => input('post.area_sort'),
            );
            //验证数据  BEGIN
            $rule = [
                ['area_name', 'require', lang('area_name_error')],
                ['area_sort', 'between:0,255', lang('area_sort_error')]
            ];
            $validate = new Validate();
            $validate_result = $validate->check($data, $rule);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END

            $result = db('area')->insert($data);
            if ($result) {
                $this->success(lang('ds_common_save_succ'), 'Region/index');
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    public function edit()
    {
        $area_id = input('param.area_id');
        if (empty($area_id)) {
            $this->error(lang('empty_error'));
        }
        if (!request()->isPost()) {
            $area = db('area')->where('area_id', $area_id)->find();
            $this->assign('area', $area);
            $this->assign('parents', $this->_get_options());
            $this->setAdminCurItem('edit');
            return $this->fetch('form');
        } else {
            $data = array(
                'area_name' => input('post.area_name'),
                'area_region' => input('post.area_region'),
                'area_parent_id' => input('param.area_parentid'),
                'area_sort' => input('post.area_sort'),
            );
            //验证数据  BEGIN
            $rule = [
                ['area_name', 'require', lang('area_name_error')],
                ['area_sort', 'between:0,255', lang('area_sort_error')]
            ];
            $validate = new Validate();
            $validate_result = $validate->check($data, $rule);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END

            $result = db('area')->where('area_id', $area_id)->update($data);
            if ($result) {
                $this->success(lang('ds_common_op_succ'), 'Region/index');
            } else {
                $this->error(lang('ds_common_op_fail'));
            }
        }
    }

    public function drop()
    {
        $area_id = input('param.area_id');
        if (empty($area_id)) {
            $this->error(lang('empty_error'));
        }
        //判断此分类下是否有子分类
        $result = db('area')->where('area_parent_id', $area_id)->find();
        if ($result) {
            $this->error(lang('drop_child_error'));
        }
        $result = db('area')->delete($area_id);
        if ($result) {
            $this->success(lang('ds_common_op_succ'), 'Region/index');
        } else {
            $this->error(lang('error'));
        }
    }

    /* 取得可以作为上级的地区分类数据 */

    function _get_options($except = NULL)
    {
        $area = $this->_Region_mod->get_list();
        if (empty($area)) {
            return;
        }
        $tree = new \mall\Tree();
        $tree->setTree($area, 'area_id', 'area_parent_id', 'area_name');
        return $tree->getOptions(MAX_LAYER - 1, 0, $except);
    }
    
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '管理',
                'url' => url('Admin/Region/index')
            ),
        );

        if (request()->action() == 'add' || request()->action() == 'index') {
            $menu_array[] = array(
                'name' => 'add',
                'text' => '新增',
                'url' => url('Admin/Region/add')
            );
        }
        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit',
                'text' => '编辑',
                'url' => url('Admin/Region/edit')
            );
        }
        return $menu_array;
    }

}
