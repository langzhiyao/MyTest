<?php

namespace app\admin\controller;

use think\Lang;

class Seo extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/seo.lang.php');
    }

    function index() {
        $model_seo = Model('seo');
        if (!request()->isPost()) {
            //读取SEO信息
            $list = $model_seo->select();
            $seo = array();
            foreach ((array) $list as $value) {
                $seo[$value['type']] = $value;
            }
            $this->assign('seo', $seo);
//            $category = Model('goodsclass')->getGoodsClassForCacheModel();
//            $this->assign('category', $category);
            return $this->fetch('index');
        } else {
            $update = array();
            $seo = $_POST['SEO'];
            foreach ((array) $seo as $key => $value) {
                $model_seo->where(array('type' => $key))->update($value);
            }
            dkcache('seo');
            $this->success(lang('ds_common_save_succ'));
        }
    }

}

?>
