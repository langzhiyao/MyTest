<?php

namespace app\common\model;

use think\Model;

class Payment extends Model {
    /**
     * 开启状态标识
     * @var unknown
     */
    const STATE_OPEN = 1;
    
    
    /**
     * 读取单行信息
     *
     * @param
     * @return array 数组格式的返回结果
     */
    public function getPaymentInfo($condition = array()) {
        return db('payment')->where($condition)->find();
    }

    /**
     * 读开启中的取单行信息
     *
     * @param
     * @return array 数组格式的返回结果
     */
    public function getPaymentOpenInfo($condition = array()) {
        $condition['payment_state'] = self::STATE_OPEN;
        return db('payment')->where($condition)->find();
    }

    /**
     * 读取多行
     *
     * @param 
     * @return array 数组格式的返回结果
     */
    public function getPaymentList($condition = array()) {
        return db('payment')->where($condition)->select();
    }

    /**
     * 读取开启中的支付方式
     *
     * @param
     * @return array 数组格式的返回结果
     */
    public function getPaymentOpenList($condition = array()) {
        $condition['payment_state'] = self::STATE_OPEN;
        return db('payment')->where($condition)->select();
    }

    /**
     * 更新信息
     *
     * @param array $param 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function editPayment($data, $condition) {
        return db('payment')->where($condition)->update($data);
    }

    /**
     * 读取支付方式信息by Condition
     *
     * @param
     * @return array 数组格式的返回结果
     */
    public function getRowByCondition($conditionfield, $conditionvalue) {
        $param = array();
        $param['table'] = 'payment';
        $param['field'] = $conditionfield;
        $param['value'] = $conditionvalue;
        $result = Db::getRow($param);
        return $result;
    }

}

?>
