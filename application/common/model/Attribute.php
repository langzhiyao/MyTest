<?php

namespace app\common\model;

use think\Model;

class Attribute extends Model {

    const SHOW0 = 0;    // 不显示
    const SHOW1 = 1;    // 显示

    /**
     * 属性列表
     * 
     * @param array $condition
     * @param string $field
     * @return array
     */

    public function getAttributeList($condition, $field = '*') {
        return db('attribute')->where($condition)->field($field)->order('attr_sort asc')->select();
    }

    /**
     * 属性列表
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getAttributeShowList($condition, $field = '*') {
        $condition['attr_show'] = self::SHOW1;
        return $this->getAttributeList($condition, $field);
    }

    /**
     * 属性值列表
     * 
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getAttributeValueList($condition, $field = '*') {
        return db('attributevalue')->where($condition)->field($field)->order('attr_value_sort asc,attr_value_id asc')->select();
    }

    /**
     * 保存属性值
     * @param array $insert
     * @return boolean
     */
    public function addAttributeValueAll($insert) {
        return db('attributevalue')->insertAll($insert);
    }

    /**
     * 保存属性值
     * @param array $insert
     * @return boolean
     */
    public function addAttributeValue($insert) {
        return db('attributevalue')->insert($insert);
    }

    /**
     * 编辑属性值
     * @param array $update
     * @param array $condition
     * @return boolean
     */
    public function editAttributeValue($update, $condition) {
        return db('attributevalue')->where($condition)->update($update);
    }

}

?>
