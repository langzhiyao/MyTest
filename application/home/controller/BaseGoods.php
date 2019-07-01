<?php

/*
 * 商品的类
 */

namespace app\home\controller;

use think\Controller;

class BaseGoods extends BaseStore {

    protected $store_info;
    protected $store_decoration_only = false;

    public function _initialize() {
        parent::_initialize();
        //输出会员信息
        $this->getMemberAndGradeInfo(false);
    }
    
    protected function getStoreInfo($store_id, $goods_info = null) {
        $model_store = Model('store');
        $store_info = $model_store->getStoreOnlineInfoByID($store_id);
        if (empty($store_info)) {
            $this->error(lang('ds_store_close'));
        }
        if (cookie('dregion')) {
            $store_info['deliver_region'] = cookie('dregion');
        }
        if (strpos($store_info['deliver_region'], '|')) {
            $store_info['deliver_region'] = explode('|', $store_info['deliver_region']);
            $store_info['deliver_region_ids'] = explode(' ', $store_info['deliver_region'][0]);
            $store_info['deliver_region_names'] = explode(' ', $store_info['deliver_region'][1]);
        }
        $this->outputStoreInfo($store_info, $goods_info);
    }
}

?>
