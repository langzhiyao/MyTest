<?php

namespace app\common\model;

use think\Model;

class Mailtemplates extends Model {
    
    /**
     * 取单条信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getTplInfo($condition = array(), $fields = '*') {
        return db('mailmsgtemlates')->where($condition)->field($fields)->find();
    }

    /**
     * 模板列表
     *
     * @param array $condition 检索条件
     * @return array 数组形式的返回结果
     */
    public function getTplList($condition = array()) {
        return db('mailmsgtemlates')->where($condition)->select();
    }

    public function editTpl($data = array(), $condition = array()) {
        return db('mailmsgtemlates')->where($condition)->update($data);
    }

}

?>
