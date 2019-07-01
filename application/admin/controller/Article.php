<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;
use think\Db;

class Article extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/article.lang.php');
    }

    public function index() {

        /**
         * 检索条件
         */
        $condition = array();
        $search_ac_id = intval(input('param.search_ac_id'));
        if ($search_ac_id) {
            $condition['ac_id'] = $search_ac_id;
        }
        $search_title = trim(input('param.search_title'));
        if ($search_title) {
            $condition['like_title'] = $search_title;
        }
        $model_article = Model('article');
        $article_list = $model_article->getArticleList($condition, 10);

        $model_class = Model('articleclass');
        /**
         * 整理列表内容
         */
        if (is_array($article_list)) {
            /**
             * 取文章分类
             */
            
            $class_list = $model_class->getClassList($condition);
            $tmp_class_name = array();
            if (is_array($class_list)) {
                foreach ($class_list as $k => $v) {
                    $tmp_class_name[$v['ac_id']] = $v['ac_name'];
                }
            }
            foreach ($article_list as $k => $v) {
                /**
                 * 发布时间
                 */
                $article_list[$k]['article_time'] = date('Y-m-d H:i:s', $v['article_time']);
                /**
                 * 所属分类
                 */
                if (@array_key_exists($v['ac_id'], $tmp_class_name)) {
                    $article_list[$k]['ac_name'] = $tmp_class_name[$v['ac_id']];
                }
            }
        }

        /**
         * 分类列表
         */
        $parent_list = $model_class->getTreeClassList(2);
        if (is_array($parent_list)) {
            $unset_sign = false;
            foreach ($parent_list as $k => $v) {
                $parent_list[$k]['ac_name'] = str_repeat("&nbsp;", $v['deep'] * 2) . $v['ac_name'];
            }
        }

        $this->assign('article_list', $article_list);
        $this->assign('page', $model_article->page_info->render());
        $this->assign('search_title', $search_title);
        $this->assign('search_ac_id', $search_ac_id);
        $this->assign('parent_list', $parent_list);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    public function add() {
        if (!(request()->isPost())) {
            $article = [
                'article_title' => '',
                'ac_id' => input('param.ac_id'),
                'article_url' => '',
                'article_show' => 0,
                'article_sort' => 0,
                'article_content' => '',
            ];
            $mode_calss = model('articleclass');
            $cate_list=$mode_calss->getTreeClassList(2);
            $this->assign('ac_list', $cate_list);
            $this->assign('article', $article);
            $this->setAdminCurItem('add');
            return $this->fetch('form');
        } else {
            $data = array(
                'article_title' => input('post.article_title'),
                'ac_id' => input('post.ac_id'),
                'article_url' => input('post.article_url'),
                'article_sort' => input('post.article_sort'),
                'article_content' => input('post.article_content'),
                'article_time' => TIMESTAMP,
            );
            $article_show = input('post.article_show');
            $data['article_show'] = isset($article_show) ? '1' : '0';
            //验证数据  BEGIN
            $rule = [
                ['article_sort', 'number', lang('article_sort_error')],
                ['article_title', 'require', lang('article_title_error')],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($data);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END
            $result = db('article')->insert($data);
            if ($result) {
                $this->success(lang('ds_common_save_succ'), 'Article/index');
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    public function edit() {
        $art_id = input('param.article_id');
        if (empty($art_id)) {
            $this->error(lang('empty_error'));
        }
        if (!request()->isPost()) {
            $article = db('article')->where('article_id', $art_id)->find();
            $this->assign('article', $article);
            $mode_calss = model('articleclass');
            $cate_list=$mode_calss->getTreeClassList(2);
            $this->assign('ac_list', $cate_list);
            $this->setAdminCurItem('edit');
            return $this->fetch('form');
        } else {
            $data = array(
                'article_title' => input('post.article_title'),
                'ac_id' => input('post.ac_id'),
                'article_url' => input('post.article_url'),
                'article_sort' => input('post.article_sort'),
                'article_content' => input('post.article_content'),
                'article_time' => TIMESTAMP,
            );
            $article_show = input('post.article_show');
            $data['article_show'] = isset($article_show) ? '1' : '0';
            //验证数据  BEGIN
            $rule = [
                ['article_sort', 'number', lang('article_sort_error')],
                ['article_title', 'require', lang('article_title_error')],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($data);
            if (!$validate_result) {
                $this->error($validate->getError());
            }

            //验证数据  END
            $result = db('article')->where('article_id', $art_id)->update($data);
            if ($result) {
                $this->success(lang('ds_common_save_succ'), 'Article/index');
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    public function drop() {
        $article_id = input('param.article_id');
        if (empty($article_id)) {
            $this->error(lang('empty_error'));
        }
        $result = db('article')->delete($article_id);
        if ($result) {
            $this->success(lang('ds_common_op_succ'), 'Article/index');
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
                'url' => url('Admin/Article/index')
            ),
        );

        if (request()->action() == 'add' || request()->action() == 'index') {
            $menu_array[] = array(
                'name' => 'add',
                'text' => '新增',
                'url' => url('Admin/Article/add')
            );
        }
        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit',
                'text' => '编辑',
                'url' => url('Admin/Article/edit')
            );
        }
        return $menu_array;
    }

}