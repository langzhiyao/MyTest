<?php

namespace app\common\model;


use think\Model;

class Storemsgread extends Model
{
    /**
     * 新增店铺纤细阅读
     * @param unknown $insert
     */
    public function addStoreMsgRead($insert)
    {
        $insert['read_time'] = TIMESTAMP;
        return db('storemsgread')->insert($insert);
    }

    /**
     * 查看店铺消息阅读详细
     * @param unknown $condition
     * @param string $field
     */
    public function getStoreMsgReadInfo($condition, $field = '*')
    {
        return db('storemsgread')->field($field)->where($condition)->find();
    }

    /**
     * 店铺消息阅读列表
     * @param unknown $condition
     * @param string $field
     * @param string $order
     */
    public function getStoreMsgReadList($condition, $field = '*', $order = 'read_time desc')
    {
        return db('storemsgread')->field($field)->where($condition)->order($order)->select();
    }

    /**
     * 删除店铺消息阅读记录
     * @param unknown $condition
     */
    public function delStoreMsgRead($condition)
    {
        db('storemsgread')->where($condition)->delete();
    }
}