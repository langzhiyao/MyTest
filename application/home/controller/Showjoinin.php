<?php

namespace app\home\controller;

use think\Lang;

class Showjoinin extends BaseMall {
    

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/showjoinin.lang.php');
    }
    /*
     * 入驻相关首页介绍
     */

    public function index() {
        $code_info = config('store_joinin_pic');
        $info['pic'] = array();
        $info['show_txt'] = '';
        if (!empty($code_info)) {
            $info = unserialize($code_info);
        }
        $this->assign('pic_list', $info['pic']); //首页图片
        $this->assign('show_txt', $info['show_txt']); //贴心提示
        $model_help = Model('help');
        $condition['type_id'] = '1'; //入驻指南
        $help_list = $model_help->getHelpList($condition, '', 4); //显示4个
        $this->assign('help_list', $help_list);
        $this->assign('article_list', ''); //底部不显示文章分类
        $this->assign('show_sign', 'joinin');
        $this->assign('html_title', config('site_name') . ' - ' . '商家入驻');
        return $this->fetch($this->template_dir . 'index');
    }

}

?>
