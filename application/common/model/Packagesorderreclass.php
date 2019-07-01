<?php

namespace app\common\model;
use think\Model;

class Packagesorderreclass extends Model {
    public $page_info;

    /**
     * 取单条订单信息
     *
     * @param unknown_type $condition
     * @return unknown
     */
    public function getOrderInfo($condition = array(), $extend = array(), $fields = '*', $class = '', $group = '') {
        $class_info = db('packagesorderreclass')->field($fields)->where($condition)->group($group)->order($class)->find();
        if (empty($class_info)) {
            return array();
        }
        return $class_info;
    }

    /**
     * 取得学校列表(所有)
     * @param unknown $condition
     * @param string $page
     * @param string $field
     * @param string $school
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getOrderList($condition, $page = '', $field = '*', $class = 'order_id desc', $limit = '', $extend = array(), $master = false) {
        $list_paginate = db('packagesorderreclass')->field($field)->where($condition)->order($class)->paginate($page,false,['query' => request()->param()]);
        //$sql =  db('school')->getlastsql();
        $this->page_info = $list_paginate;
        $list = $list_paginate->items();
        if (empty($list))
            return array();
        return $list;
    }

    /**
     * 插入班级表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrder($data) {
        $insert = db('packagesorderreclass')->insertGetId($data);
        return $insert;
    }

    /**
     * 更改班级信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editOrder($data, $condition, $limit = '') {
        $update = db('packagesorderreclass')->where($condition)->limit($limit)->update($data);
        return $update;
    }

    /**
     * 生成支付单编号(两位随机 + 从2000-01-01 00:00:00 到现在的秒数+微秒+会员ID%1000)，该值会传给第三方支付接口
     * 长度 =2位 + 10位 + 3位 + 3位  = 18位
     * 1000个会员同一微秒提订单，重复机率为1/100
     * @return string
     */
    public function makePaySn($member_id) {
        return mt_rand(10, 99)
            . sprintf('%010d', time() - 946656000)
            . sprintf('%03d', (float) microtime() * 1000)
            . sprintf('%03d', (int) $member_id % 1000);
    }

    /**
     * 订单编号生成规则，n(n>=1)个订单表对应一个支付表，
     * 生成订单编号(年取1位 + $pay_id取13位 + 第N个子订单取2位)
     * 1000个会员同一微秒提订单，重复机率为1/100
     * @param $pay_id 支付表自增ID
     * @return string
     */
    public function makeOrderSn($pay_id) {
        //记录生成子订单的个数，如果生成多个子订单，该值会累加
        static $num;
        if (empty($num)) {
            $num = 1;
        } else {
            $num++;
        }
        return (date('y', time()) % 9 + 1) . sprintf('%013d', $pay_id) . sprintf('%02d', $num);
    }

    public function getTeachOrder($condition){
        return db('packagesorderreclass')->where($condition)->order('add_time desc')->select();
    }
}