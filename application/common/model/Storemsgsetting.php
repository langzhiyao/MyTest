<?php

namespace app\common\model;


use think\Model;

class Storemsgsetting extends Model
{
    public $page_info;
    /**
     * 店铺消息接收设置列表
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     */
    public function getStoreMsgSettingList($condition, $field = '*', $key = '', $page = 0, $order = 'smt_code asc') {
        $res=db('storemsgsetting')->field($field)->where($condition)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info=$res;
        $result= $res->items();
        return ds_changeArraykey($result,$key);

    }

    /**
     * 店铺消息接收设置详细
     * @param array $condition
     * @param string $field
     */
    public function getStoreMsgSettingInfo($condition, $field = '*') {
        return db('storemsgsetting')->field($field)->where($condition)->find();
    }

    /**
     * 编辑店铺模板接收设置
     * @param array $insert
     */
    public function addStoreMsgSetting($insert) {
        return db('storemsgsetting')->insert($insert, true);
    }
}