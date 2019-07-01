<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Navigation extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/navigation.lang.php');
    }

    public function index() {
        if (!(request()->isPost())) {
            $nav_list = db('navigation')->order('nav_sort')->paginate(10,false,['query' => request()->param()]);
        } else {
            $search_nav = input('post.search_nav');
            $map['nav_title|nav_url'] = ['like', "%$search_nav%"];
            $nav_list = db('navigation')->where($map)->order('nav_sort')->paginate(10,false,['query' => request()->param()]);
        }
        $page = $nav_list->render();
        $this->assign('nav_list', $nav_list);
        $this->assign('page', $page);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    public function add() {
        if (!(request()->isPost())) {
            $nav = [
                'nav_title' => '',
                'nav_location' => 'header',
                'nav_url' => '',
                'nav_new_open' => 0,
                'nav_sort' => 0,
            ];
            $this->assign('nav', $nav);
            $this->setAdminCurItem('add');
            return $this->fetch('form');
        } else {
            $data = input('post.');
            //定义验证规则
            $rule = [
                ['nav_sort', 'number', lang('nav_sort_error')],
                ['nav_title', 'require', lang('nav_title_error')],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($data);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            $result = db('navigation')->insert($data);
            if ($result) {
                $this->success(lang('ds_common_op_succ'), 'Navigation/index');
            } else {
                $this->error(lang('error'));
            }
        }
    }

    public function edit() {
        $nav_id = input('param.nav_id');
        if (empty($nav_id)) {
            $this->error(lang('empty_error'));
        }
        if (!request()->isPost()) {
            $nav = db('navigation')->where('nav_id', $nav_id)->find();
            $this->assign('nav', $nav);
            $this->setAdminCurItem('edit');
            return $this->fetch('form');
        } else {
            $data = input('post.');
            $data['nav_new_open'] = (!isset($data['nav_new_open'])) ? '0' : '1';
            //定义验证规则
            $rule = [
                ['nav_sort', 'number', lang('nav_sort_error')],
                ['nav_title', 'require', lang('nav_title_error')],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($data);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            $result = db('navigation')->where('nav_id', $nav_id)->update($data);
            if ($result) {
                $this->success(lang('ds_common_op_succ'), 'Navigation/index');
            } else {
                $this->error(lang('error'));
            }
        }
    }

    public function drop() {
        $nav_id = input('param.nav_id');
        if (empty($nav_id)) {
            $this->error(lang('empty_error'));
        }
        $result = db('navigation')->delete($nav_id);
        if ($result) {
            $this->success(lang('ds_common_op_succ'), 'Navigation/index');
        } else {
            $this->error(lang('error'));
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
                'url' => url('Admin/Navigation/index')
            ),
        );

        if (request()->action() == 'add' || request()->action() == 'index') {
            $menu_array[] = array(
                'name' => 'add',
                'text' => '新增',
                'url' => url('Admin/Navigation/add')
            );
        }
        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit',
                'text' => '编辑',
                'url' => url('Admin/Navigation/edit')
            );
        }
        return $menu_array;
    }

}