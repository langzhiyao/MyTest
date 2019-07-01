<?php

namespace app\common\model;

use think\Model;

class Storeextend extends Model {

    /**
     * 查询店铺扩展信息
     *
     * @param int $store_id 店铺编号
     * @param string $field 查询字段
     * @return array
     */
    public function getStoreExtendInfo($condition, $field = '*') {
        return db('storeextend')->field($field)->where($condition)->find();
    }

    /*
     * 编辑店铺扩展信息
     *
     * @param array $update 更新信息
     * @param array $condition 条件
     * @return bool
     */

    public function editStoreExtend($update, $condition) {
        $extend=new Storeextend();
        $result=$extend->where($condition)->find();
        if($result){
            $res= $extend->save($update,$condition);
        }else{
            $update=array_merge($update,$condition);
            $res= $extend->save($update);
        }
        return $res;

    }

    /*
     * 删除店铺扩展信息
     */

    public function delStoreExtend($condition) {
        return db('storeextend')->where($condition)->delete();
    }
    
    public function getfby_store_id($store_id,$field){
        $data = db('storeextend')->field($field)->where('store_id',$store_id)->find();
        return $data[$field];
    }

}

?>
