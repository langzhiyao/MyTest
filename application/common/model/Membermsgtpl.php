<?php

namespace app\common\model;

use think\Model;

class Membermsgtpl extends Model {
    
    /**
     * 用户消息模板列表
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     */
    public function getMemberMsgTplList($condition, $field = '*', $page = 0, $order = 'mmt_code asc') {
        return db('membermsgtpl')->field($field)->where($condition)->order($order)->page($page)->select();
    }
    
    /**
     * 用户消息模板详细信息
     * @param array $condition
     * @param string $field
     */
    public function getMemberMsgTplInfo($condition, $field = '*') {
        return db('membermsgtpl')->field($field)->where($condition)->find();
    }
    
    /**
     * 编辑用户消息模板
     * @param array $condition
     * @param unknown $update
     */
    public function editMemberMsgTpl($condition, $update) {
        return db('membermsgtpl')->where($condition)->update($update);
    }
    
    
}
?>
