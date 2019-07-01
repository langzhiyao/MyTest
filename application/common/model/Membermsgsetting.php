<?php

namespace app\common\model;


use think\Model;

class Membermsgsetting extends Model
{
    public $page_info;
    /**
     * 用户消息模板列表
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     */
    public function getMemberMsgSettingList($condition, $field = '*', $page = 0, $order = 'mmt_code asc') {
       $result= db('membermsgsetting')->field($field)->where($condition)->order($order)->paginate($page,false,['query' => request()->param()]);
       $this->page_info=$result;
       return $result->items();
    }

    /**
     * 用户消息模板详细信息
     * @param array $condition
     * @param string $field
     */
    public function getMemberMsgSettingInfo($condition, $field = '*') {
        return db('membermsgsetting')->field($field)->where($condition)->find();
    }

    /**
     * 编辑用户消息模板
     * @param array $condition
     * @param unknown $update
     */
    public function addMemberMsgSettingAll($insert) {
        return db('membermsgsetting')->insertAll($insert, array(), true);
    }
}