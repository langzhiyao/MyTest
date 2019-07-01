<?php

namespace app\common\model;

use think\Model;

class Snsgoods extends Model {

    /**
     * 查询SNS商品详细
     * 
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getSNSGoodsInfo($condition, $field = '*') {
        $result = db('snsgoods')->field($field)->where($condition)->find();
        return $result;
    }

    /**
     * 新增SNS商品
     *
     * @param $param 添加信息数组
     * @return 返回结果
     */
    public function goodsAdd($param) {
        return db('snsgoods')->insertGetId($param);
    }

    /**
     * 查询SNS商品详细
     * 
     * @param $condition 查询条件
     * @param $field 查询字段
     */
    public function getGoodsInfo($condition, $field = '*') {
        return db('snsgoods')->where($condition)->field($field)->find();
    }

    /**
     * 更新SNS商品信息
     * @param $param 更新内容
     * @param $condition 更新条件
     */
    public function editGoods($param, $condition) {
        return db('snsgoods')->where($condition)->update($param);
    }


}

?>
