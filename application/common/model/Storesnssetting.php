<?php

namespace app\common\model;


use think\Model;

class Storesnssetting extends Model
{
    /**
     * 获取单条动态设置设置信息
     *
     * @param unknown $condition
     * @param string $field
     * @return array
     */
    public function getStoreSnsSettingInfo($condition, $field = '*')
    {
        return db('storesnssetting')->field($field)->where($condition)->find();
    }

    /**
     * 保存店铺动态设置
     *
     * @param unknown $insert
     * @return boolean
     */
    public function saveStoreSnsSetting($insert)
    {
        return db('storesnssetting')->insertGetId($insert);
    }
}