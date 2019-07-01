<?php

/*
 * 店铺的类
 */

namespace app\home\controller;
use think\Lang;

class BaseStore extends BaseHome {
    protected $store_info;
    protected $store_decoration_only = false;
    
    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/baseseller.lang.php');
        //店铺模板路径
        $this->template_dir = 'store/default/' . strtolower(request()->controller()) . '/';
        $this->assign('store_theme', 'default');
        //当方法为 store 进行执行
        if (request()->controller() == 'Store') {

            //输出会员信息
            $this->getMemberAndGradeInfo(false);

            $store_id = intval(input('param.store_id'));
            if ($store_id <= 0) {
                $this->error(lang('ds_store_close'));
            }

            $model_store = Model('store');
            $store_info = $model_store->getStoreOnlineInfoByID($store_id);
            if (empty($store_info)) {
                $this->error(lang('ds_store_close'));
            } else {
                $this->store_info = $store_info;
            }
            if ($store_info['store_decoration_switch'] > 0 & $store_info['store_decoration_only'] == 1) {
                $this->store_decoration_only = true;
            }

            //店铺装修
            $this->outputStoreDecoration($store_info['store_decoration_switch'], $store_id);

            $this->outputStoreInfo($this->store_info);
            $this->getStoreNavigation($store_id);
            $this->outputSeoInfo($this->store_info);
        }
    }
    
    
    /**
     * 输出店铺装修
     */
    protected function outputStoreDecoration($decoration_id, $store_id) {
        if ($decoration_id > 0) {
            $model_store_decoration = Model('storedecoration');
            $decoration_info = $model_store_decoration->getStoreDecorationInfoDetail($decoration_id, $store_id);
            if ($decoration_info) {
                $decoration_background_style = $model_store_decoration->getDecorationBackgroundStyle($decoration_info['decoration_setting']);
                $this->assign('decoration_background_style', $decoration_background_style);
                $this->assign('decoration_nav', $decoration_info['decoration_nav']);
                $this->assign('decoration_banner', $decoration_info['decoration_banner']);

                $html_file = BASE_UPLOAD_PATH . DS . ATTACH_STORE . DS . 'decoration' . DS . 'html' . DS . md5($store_id) . '.html';
                if (is_file($html_file)) {
                    $this->assign('decoration_file', $html_file);
                }
            }
            $this->assign('store_theme', 'default');
        } else {
            if(!file_exists(ROOT_PATH.'/static/store/styles/'.$this->store_info['store_theme'].'/screenshot.jpg')) {
                $this->assign('store_theme', 'default');
            }else{
                $this->assign('store_theme', $this->store_info['store_theme']);
            }
        }
    }

    /**
     * 检查店铺开启状态
     *
     * @param int $store_id 店铺编号
     * @param string $msg 警告信息
     */
    protected function outputStoreInfo($store_info) {
        if (!$this->store_decoration_only) {
            $model_store = Model('store');
            $model_seller = Model('seller');

            //店铺分类
            $goodsclass_model = Model('storegoodsclass');
            $goods_class_list = $goodsclass_model->getShowTreeList($store_info['store_id']);
            $this->assign('goods_class_list', $goods_class_list);

            //热销排行
            $hot_sales = $model_store->getHotSalesList($store_info['store_id'], 5);
            $this->assign('hot_sales', $hot_sales);

            //收藏排行
            $hot_collect = $model_store->getHotCollectList($store_info['store_id'], 5);
            $this->assign('hot_collect', $hot_collect);
        }

        $this->assign('store_info', $store_info);
        $this->assign('page_title', $store_info['store_name']);
    }

    protected function getStoreNavigation($store_id) {
        $model_store_navigation = Model('storenavigation');
        $store_navigation_list = $model_store_navigation->getStoreNavigationList(array('sn_store_id' => $store_id));
        $this->assign('store_navigation_list', $store_navigation_list);
    }

    protected function outputSeoInfo($store_info) {
        $seo_param = array();
        $seo_param['shopname'] = $store_info['store_name'];
        $seo_param['key'] = $store_info['store_keywords'];
        $seo_param['description'] = $store_info['store_description'];
        //SEO 设置
        $this->_assign_seo(Model('seo')->type('shop')->param($seo_param)->show());
    }
    
    

}

?>
