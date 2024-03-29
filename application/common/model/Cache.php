<?php

namespace app\common\model;

use think\Model;

class Cache extends Model {

    public function call($method) {
        $method = '_' . strtolower($method);
        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            return false;
        }
    }

    /**
     * 基本设置
     *
     * @return array
     */
    private function _config() {
        $result = db('config')->select();
        if (is_array($result)) {
            $list_config = array();
            foreach ($result as $k => $v) {
                $list_config[$v['code']] = $v['value'];
            }
        }
        unset($result);
        return $list_config;
    }

    /**
     * 商品分类SEO
     *
     * @return array
     */
    private function _goods_class_seo() {

        $list = db('goodsclass')->field('gc_id,gc_title,gc_keywords,gc_description')->where(array('gc_keywords' => array('neq', '')))->limit(false)->select();
        if (!is_array($list))
            return null;
        $array = array();
        foreach ($list as $k => $v) {
            if ($v['gc_title'] != '' || $v['gc_keywords'] != '' || $v['gc_description'] != '') {
                if ($v['gc_name'] != '') {
                    $array[$v['gc_id']]['name'] = $v['gc_name'];
                }
                if ($v['gc_title'] != '') {
                    $array[$v['gc_id']]['title'] = $v['gc_title'];
                }
                if ($v['gc_keywords'] != '') {
                    $array[$v['gc_id']]['key'] = $v['gc_keywords'];
                }
                if ($v['gc_description'] != '') {
                    $array[$v['gc_id']]['desc'] = $v['gc_description'];
                }
            }
        }
        return $array;
    }


    /**
     * 商城主要频道SEO
     *
     * @return array
     */
    private function _seo() {
        $list = db('seo')->limit(false)->select();
        if (!is_array($list))
            return null;
        $array = array();
        foreach ($list as $key => $value) {
            $array[$value['type']] = $value;
        }
        return $array;
    }

    /**
     * 快递公司
     *
     * @return array
     */
    private function _express() {
        $fields = 'id,e_name,e_code,e_letter,e_order,e_url,e_zt_state';
        $list = db('express')->field($fields)->order('e_order,e_letter')->where(array('e_state' => 1))->limit(false)->select();
        if (!is_array($list))
            return null;
        $array = array();
        foreach ($list as $k => $v) {
            $array[$v['id']] = $v;
        }
        return $array;
    }

    /**
     * 自定义导航
     *
     * @return array
     */
    private function _nav() {
        $list = db('navigation')->order('nav_sort')->limit(false)->select();
        if (!is_array($list))
            return null;
        return $list;
    }

    /**
     * 抢购价格区间
     *
     * @return array
     */
    private function _groupbuy_price() {
        $price = db('groupbuypricerange')->order('range_start')->select();
        if (!is_array($price)){
            $price = array();
        }else{
            $price = ds_changeArraykey($price, 'range_id');
        }
        return $price;
    }

    /**
     * 商品TAG
     *
     * @return array
     */
    private function _class_tag() {
        $field = 'gc_tag_id,gc_tag_name,gc_tag_value,gc_id,type_id';
        $list = db('goodsclasstag')->field($field)->limit(false)->select();
        if (!is_array($list))
            return null;
        return $list;
    }

    /**
     * 店铺分类
     *
     * @return array
     */
    private function _store_class() {
        $store_class_tmp = db('storeclass')->limit(false)->order('sc_sort asc,sc_id asc')->select();
        $store_class = array();
        if (is_array($store_class_tmp) && !empty($store_class_tmp)) {
            foreach ($store_class_tmp as $k => $v) {
                $store_class[$v['sc_id']] = $v;
            }
        }
        return $store_class;
    }

    /**
     * 店铺等级
     *
     * @return array
     */
    private function _store_grade() {
        $list = db('storegrade')->limit(false)->select();
        $array = array();
        foreach ((array) $list as $v) {
            $array[$v['sg_id']] = $v;
        }
        unset($list);
        return $array;
    }

    /**
     * 店铺等级
     *
     * @return array
     */
    private function _store_msg_tpl() {
        $list = Model('storemsgtpl')->getStoreMsgTplList(array());
        $array = array();
        foreach ((array) $list as $v) {
            $array[$v['smt_code']] = $v;
        }
        unset($list);
        return $array;
    }

    /**
     * 店铺等级
     *
     * @return array
     */
    private function _member_msg_tpl() {
        $list = Model('membermsgtpl')->getMemberMsgTplList(array());
        $array = array();
        foreach ((array) $list as $v) {
            $array[$v['mmt_code']] = $v;
        }
        unset($list);
        return $array;
    }

    /**
     * 咨询类型
     *
     * @return array
     */
    private function _consult_type() {
        $list = Model('consulttype')->getConsultTypeList(array());
        $array = array();
        foreach ((array) $list as $val) {
            $val['ct_introduce'] = html_entity_decode($val['ct_introduce']);
            $array[$val['ct_id']] = $val;
        }
        unset($list);
        return $array;
    }

    /**
     * Circle Member Level
     *
     * @return array
     */
    private function _circle_level() {
        $list = db('circlemldefault')->limit(false)->select();

        if (!is_array($list))
            return null;
        $array = array();
        foreach ($list as $val) {
            $array[$val['mld_id']] = $val;
        }
        return $array;
    }

}
