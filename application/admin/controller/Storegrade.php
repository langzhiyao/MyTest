<?php

namespace app\admin\controller;

use think\View;
use think\Url;
use think\Lang;
use think\Request;
use think\Db;
use think\Validate;

class Storegrade extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/storegrade.lang.php');
    }

    public function index() {
        $storegrade_list = model('storegrade')->getGradeList();
        // 获取分页显示
        $this->assign('storegrade_list', $storegrade_list);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    public function add() {
        if (!request()->isPost()) {
            $storegrade = array(
                'sg_name' => '',
                'sg_goods_limit' => 0,
                'sg_album_limit' => 0,
                'sg_space_limit' => 0,
                'sg_price' => '',
                'sg_description' => '',
                'sg_sort' => 100,
            );
            $this->assign('storegrade', $storegrade);
            $this->setAdminCurItem('add');
            return $this->fetch('form');
        } else {
            $data = array(
                'sg_name' => input('post.sg_name'),
                'sg_goods_limit' => input('post.sg_goods_limit'),
                'sg_album_limit' => input('post.sg_album_limit'),
                'sg_space_limit' => input('post.sg_space_limit'),
                'sg_price' => input('post.sg_price'),
                'sg_description' => input('post.sg_description'),
                'sg_sort' => input('post.sg_sort'),
            );
            //验证数据  BEGIN
            $rule = [
                ['sg_name', 'require', '店铺等级名称必填'],
                ['sg_sort', 'require|number|between:1,100', '排序为必填|排序必须是数字|等级级别不能大于100']
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($data);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END

            $result = db('storegrade')->insert($data);
            if ($result) {
                $this->success('edit_ok', 'Storegrade/index');
            } else {
                $this->error('添加失败');
            }
        }
    }

    public function edit() {
        //注：pathinfo地址参数不能通过get方法获取，查看“获取PARAM变量”
        $sg_id = input('param.sg_id');
        if (empty($sg_id)) {
            $this->error(lang('param_error'));
        }
        if (!request()->isPost()) {
            $storegrade = db('storegrade')->where('sg_id', $sg_id)->find();
            $this->assign('storegrade', $storegrade);
            $this->setAdminCurItem('edit');
            return $this->fetch('form');
        } else {
            
            $data = array(
                'sg_name' => input('post.sg_name'),
                'sg_goods_limit' => input('post.sg_goods_limit'),
                'sg_album_limit' => input('post.sg_album_limit'),
                'sg_space_limit' => input('post.sg_space_limit'),
                'sg_price' => input('post.sg_price'),
                'sg_description' => input('post.sg_description'),
                'sg_sort' => input('post.sg_sort'),
            );
            //验证数据  BEGIN
            $rule = [
                ['sg_name', 'require', '店铺等级名称必填'],
                ['sg_sort', 'require|number|between:1,100', '排序为必填|排序必须是数字|等级级别不能大于100']
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($data);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END
            
            $result = db('storegrade')->where('sg_id',$sg_id)->update($data);
            if ($result) {
                $this->success('edit_ok', 'Storegrade/index');
            } else {
                $this->error('删除失败');
            }
        }
    }

    public function drop() {
        //注：pathinfo地址参数不能通过get方法获取，查看“获取PARAM变量”
        $sg_id = input('param.sg_id');
        if (empty($sg_id)) {
            $this->error(lang('param_error'));
        }
        if($sg_id =='1'){
            $this->error('默认店铺不能删除');
        }
        $result = db('storegrade')->delete($sg_id);
        if ($result) {
            $this->success(lang('ds_common_del_succ'), 'Storegrade/index');
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
                'url' => url('Admin/Storegrade/index')
            ),
        );

        if (request()->action() == 'add' || request()->action() == 'index') {
            $menu_array[] = array(
                'name' => 'add',
                'text' => '新增',
                'url' => url('Admin/Storegrade/add')
            );
        }
        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit',
                'text' => '编辑',
                'url' => url('Admin/Storegrade/edit')
            );
        }
        return $menu_array;
    }
}

?>
