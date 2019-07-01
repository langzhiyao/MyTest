<?php


namespace app\common\model;

use think\Model;

class Daddress extends Model
{
    

    /**
     * 新增
     * @param unknown $data
     * @return boolean, number
     */
    public function addAddress($data) {
        return db('daddress')->insert($data);
    }

    /**
     * 删除
     * @param unknown $condition
     */
    public function delAddress($condition) {
        return db('daddress')->where($condition)->delete();
    }

    public function editAddress($data, $condition) {
        return db('daddress')->where($condition)->update($data);
    }

    /**
     * 查询单条
     * @param unknown $condition
     * @param string $fields
     */
    public function getAddressInfo($condition, $fields = '*') {
        return db('daddress')->field($fields)->where($condition)->find();
    }

    /**
     * 查询多条
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getAddressList($condition, $fields = '*', $order = '', $limit = '') {
        return db('daddress')->field($fields)->where($condition)->order($order)->limit($limit)->select();
    }
    
}
