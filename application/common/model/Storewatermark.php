<?php

namespace app\common\model;

use think\Model;

class Storewatermark extends Model {

    /**
     * 根据店铺id获取水印
     *
     * @param array $param 参数内容
     * @return array $param 水印数组
     */
    public function getOneStoreWMByStoreId($store_id) {
        $wm_arr = db('storewatermark')->where('store_id',$store_id)->find();
        return $wm_arr;
    }

    /**
     * 新增水印
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addStoreWM($param) {
        return db('storewatermark')->insertGetId($param);
    }

    /**
     * 更新水印
     *
     * @param array $param 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function updateStoreWM($wm_id,$param) {
        return db('storewatermark')->where('wm_id',$wm_id)->update($param);
    }

    /**
     * 删除水印
     *
     * @param int $id 记录ID
     * @return bool 布尔类型的返回结果
     */
    public function delStoreWM($id) {
        return db('store_watermark')->where('wm_id',$id)->delete();
    }

}
