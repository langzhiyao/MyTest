<?php

namespace app\common\model;

use think\Model;

class Goodsclassnav extends Model {

    /**
     * 根据商品分类id取得数据
     * @param num $gc_id
     * @return array
     */
    public function getGoodsClassNavInfoByGcId($gc_id) {
        return db('goodsclassnav')->where(array('gc_id' => $gc_id))->find();
    }

    /**
     * 保存分类导航设置
     *
     * @param array $insert
     * @param boolean $replace
     * @return boolean
     */
    public function addGoodsClassNav($insert) {
        return db('goodsclassnav')->insert($insert);
    }
    /**
     * 编辑存分类导航设置
     *
     * @param unknown $update
     * @param unknown $gc_id
     * @return boolean
     */
    public function editGoodsClassNav($update, $gc_id) {
        return db('goodsclassnav')->where(array('gc_id' => $gc_id))->update($update);
    }
}
?>
