<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Link extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/link.lang.php');
    }

    public function index() {
        if (!(request()->isPost())) {
            $link_list = db('link')->order('link_sort')->paginate(10,false,['query' => request()->param()]);
        } else {
            $search_link = input('post.search_link');
            $map['link_title|link_url'] = ['like', "%$search_link%"];
            $link_list = db('link')->where($map)->order('link_sort')->paginate(10,false,['query' => request()->param()]);
        }
        $page = $link_list->render();
        $this->assign('link_list', $link_list);
        $this->assign('page', $page);
        $this->setAdminCurItem('index');
        return $this->fetch('');
    }

    /**
     * 新增友情链接
     * */
    public function add() {
        if (!(request()->isPost())) {
            $link = [
                'link_id'=>'',
                'link_title' => '',
                'link_pic' => '',
                'link_url' => '',
                'link_sort' => 255,
            ];
            $this->assign('link_array', $link);
            $this->setAdminCurItem('add');
            return $this->fetch('form');
        } else {
            //上传图片
            $brand_pic='';
            if ($_FILES['link_pic']['name'] != ''){
                $file = request()->file('link_pic');
                $file_name = date('YmdHis') . rand(10000, 99999);
                $upload_file = BASE_UPLOAD_PATH . DS . DIR_ADMIN . DS .'link';
                $result = $file->validate(['ext' => 'gif,jpg,jpeg,png'])->move($upload_file,$file_name);
                if ($result) {
                    $brand_pic = $result->getFilename();
                }
            }

            $data = array(
                'link_title' => input('post.link_title'),
                'link_pic' => $brand_pic,
                'link_url' => input('post.link_url'),
                'link_sort' => input('post.link_sort'),
            );
            //验证数据  BEGIN
            $rule = [
                ['link_sort', 'number', lang('link_sort_error')],
                ['link_title', 'require', lang('link_title_error')],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($data);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END
            $result = db('link')->insert($data);
            if ($result) {
                $this->success(lang('ds_common_save_succ'), 'Link/index');
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 编辑友情链接
     * */
    public function edit() {
        $link_id = input('param.link_id');
        if (empty($link_id)) {
            $this->error(lang('empty_error'));
        }
        if (!request()->isPost()) {
            $link = db('link')->where('link_id', $link_id)->find();
            $this->assign('link_array', $link);
            $this->setAdminCurItem('edit');
            return $this->fetch('form');
        } else {
            //上传图片
            if ($_FILES['link_pic']['name'] != ''){
                $file = request()->file('link_pic');
                $file_name = date('YmdHis') . rand(10000, 99999);
                $upload_file = BASE_UPLOAD_PATH . DS . DIR_ADMIN . DS .'link';
                $result = $file->validate(['ext' => 'gif,jpg,jpeg,png'])->move($upload_file,$file_name);
                if ($result) {
                    $brand_pic = $result->getFilename();
                }
            }
            $data = array(
                'link_title' => input('post.link_title'),
                'link_sort' => input('post.link_sort'),
                'link_url'  =>input('post.link_url'),
                'link_pic'  =>$brand_pic,
            );
            //定义验证规则
            $rule = [
                ['link_sort', 'number', lang('link_sort_error')],
                ['link_title', 'require', lang('link_title_error')],
            ];
            $validate = new Validate();
            $validate_result = $validate->check($data,$rule);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            $result = db('link')->where('link_id', $link_id)->update($data);
            if ($result) {
                $this->success(lang('ds_common_save_succ'), 'Link/index');
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    public function drop() {
        $link_id = input('param.link_id');
        if (empty($link_id)) {
            $this->error(lang('empty_error'));
        }
        $result = db('link')->delete($link_id);
        if ($result) {
            $this->success(lang('ds_common_op_succ'), 'Link/index');
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
                'url' => url('Admin/Link/index')
            ),
        );
        if (request()->action() == 'add' || request()->action() == 'index') {
            $menu_array[] = array(
                'name' => 'add',
                'text' => '新增',
                'url' => url('Admin/Link/add')
            );
        }
        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit',
                'text' => '编辑',
                'url' => url('Admin/Link/edit')
            );
        }
        return $menu_array;
    }

}
