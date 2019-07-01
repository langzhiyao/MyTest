<?php

namespace app\common\model;

use think\Model;

class Goodsattrindex extends Model {
    /**
     * 对应列表
     * 
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodsAttrIndexList($condition, $field = '*') {
        return db('goodsattrindex')->where($condition)->field($field)->select();
    }
    
}

