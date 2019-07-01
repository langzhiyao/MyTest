<?php

namespace app\common\model;

use think\Model;

class Brand extends Model {
    
public $page_info;
    /**
     * 添加品牌
     * @param array $insert
     * @return boolean
     */
    public function addBrand($insert) {
        return db('brand')->insert($insert);
    }
    
    /**
     * 编辑品牌
     * @param array $condition
     * @param array $update
     * @return boolean
     */
    public function editBrand($condition, $update) {
        return db('brand')->where($condition)->update($update);
    }
    
    /**
     * 删除品牌
     * @param unknown $condition
     * @return boolean
     */
    public function delBrand($condition) {
        $brand_array = $this->getBrandList($condition, 'brand_id,brand_pic');
        $brandid_array = array();
        foreach ($brand_array as $value) {
            $brandid_array[] = $value['brand_id'];
            @unlink(BASE_UPLOAD_PATH.DS.ATTACH_BRAND.DS.$value['brand_pic']);
        }
        return db('brand')->where(array('brand_id' => array('in', $brandid_array)))->delete();
    }
    
    /**
     * 查询品牌数量
     * @param array $condition
     * @return array
     */
    public function getBrandCount($condition) {
        return db('brand')->where($condition)->count();
    }
    
    /**
     * 品牌列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param number $page
     * @param string $limit
     * @return array
     */
    public function getBrandList($condition, $field = '*', $page = 0, $order = 'brand_sort asc, brand_id desc', $limit = '') {
        if($limit) {
            return db('brand')->where($condition)->field($field)->order($order)->page($page)->limit($limit)->select();
        }else{
            $res= db('brand')->where($condition)->field($field)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info=$res;
            return $res->items();
        }
    }
    
    /**
     * 通过的品牌列表
     * 
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getBrandPassedList($condition, $field = '*', $page = 0, $order = 'brand_sort asc, brand_id desc', $limit = '') {
        $condition['brand_apply'] = 1;
        return $this->getBrandList($condition, $field, $page, $order, $limit);
    }
    
    /**
     * 未通过的品牌列表
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getBrandNoPassedList($condition, $field = '*', $page = 0) {
        $condition['brand_apply'] = 0;
        return $this->getBrandList($condition, $field, $page);
    }
    
    /**
     * 取单个品牌内容
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getBrandInfo($condition, $field = '*') {
        return db('brand')->field($field)->where($condition)->find();
    }
}
?>
