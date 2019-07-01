<?php

namespace app\common\model;
use think\Model;

class Packagesorder extends Model {
    public $page_info;
    
    /**
     * 取单条订单信息
     *
     * @param unknown_type $condition
     * @return unknown
     */
    public function getOrderInfo($condition = array(), $extend = array(), $fields = '*', $class = '', $group = '') {
        $class_info = db('packagesorder')->field($fields)->where($condition)->group($group)->order($class)->find();
        if (empty($class_info)) {
            return array();
        }
        return $class_info;
    }

    /**
     * 取得套餐订单列表(所有)
     * @param unknown $condition
     * @param string $page
     * @param string $field
     * @param string $school
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getOrderList($condition, $page = '', $field = '*', $class = 'order_id desc', $limit = '', $extend = array(), $master = false) {
        //$list_paginate = db('packagesorder')->alias('s')->join('__ADMIN__ a',' a.admin_id=s.option_id ','LEFT')->field($field)->where($condition)->order($class)->paginate($page,false,['query' => request()->param()]);
        $list_paginate = db('packagesorder')->field($field)->where($condition)->order($class)->paginate($page,false,['query' => request()->param()]);
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
        $insert = db('packagesorder')->insertGetId($data);
        return $insert;
    }

    /**
     * 更改班级信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editOrder($data, $condition) {
        $update = db('packagesorder')->where($condition)->update($data);
        return $update;
    }


    /**
     * 添加订单日志
     */
    public function addOrderLog($data) {
        $data['log_role'] = str_replace(array('buyer', 'seller', 'system', 'admin'), array('买家', '商家', '系统', '管理员'), $data['log_role']);
        $data['log_time'] = TIMESTAMP;
        return db('orderlog')->insertGetId($data);
    }

}