<?php

namespace app\common\model;


use think\Model;

class Offpayarea extends Model
{
    /**
     * 增加某店铺设置
     *
     * @param unknown_type $data
     * @return unknown
     */
    public function addArea($data)
    {
        return db('offpayarea')->insert($data);
    }

    /**
     * 取得某店铺设置
     *
     * @param unknown_type $condition
     * @return unknown
     */
    public function getAreaInfo($condition)
    {
        return db('offpayarea')->where($condition)->find();
    }

    /**
     * 更新某店铺设置
     *
     * @param unknown_type $condition
     * @param unknown_type $data
     * @return unknown
     */
    public function updateArea($condition, $data)
    {
        return db('offpayarea')->where($condition)->update($data);
    }

    /**
     * 某县级地区是否支持货到付款
     *
     * @param unknown_type $area_id
     * @param int $store_id 店铺ID（目前只会传平台店铺）
     * @return unknown
     */
    public function checkSupportOffpay($area_id, $store_id)
    {
        if (empty($area_id)) return false;
        $area = $this->getAreaInfo(array('store_id' => $store_id));
        if (!empty($area['area_id'])) {
            $area_id_array = unserialize($area['area_id']);
        } else {
            $area_id_array = array();
        }
        if (empty($area_id_array)) {
            $area_id_array = array();
        }
        return in_array($area_id, $area_id_array) ? true : false;
    }

    /**
     * 某县级地区是否支持货到付款（多个店铺）
     *
     * @param int $area_id
     * @param array $store_ids 店铺IDs
     * @return array
     */
    public function checkSupportOffpayBatch($area_id, array $store_ids)
    {
        if (empty($area_id))
            return array_fill($store_ids, false);

        $area = db('offpayarea')->where(array(
            'store_id' => array('in', $store_ids),
        ))->select();
        $area = ds_changeArraykey($area, 'store_id');
        $ret = array();
        foreach ($store_ids as $sid) {
            $ret[$sid] = false;
            if (empty($area[$sid]['area_id']))
                continue;

            $area_id_array = unserialize($area[$sid]['area_id']);
            if (!is_array($area_id_array))
                continue;

            if (!in_array($area_id, $area_id_array))
                continue;

            $ret[$sid] = true;
        }

        return $ret;
    }
}