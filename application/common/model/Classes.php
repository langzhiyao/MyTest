<?php

namespace app\common\model;
use think\Model;

class Classes extends Model {
    public $page_info;
    
    /**
     * 取单条订单信息
     *
     * @param unknown_type $condition
     * @return unknown
     */
    public function getClassInfo($condition = array(), $extend = array(), $fields = '*', $class = '', $group = '') {
        $class_info = db('class')
                      ->alias('c')
                      ->join('__POSITION__ po','po.position_id=c.position_id','LEFT')
                      ->field('c.*,po.position')
                      ->where($condition)->group($group)->order($class)->find();
        if (empty($class_info)) {
            return array();
        }
        return $class_info;
    }


    public function getClassInfoBySchool($areaid) {
        $class_info = db('class')
                    ->alias('c')
                    ->join('__SCHOOL__ sc','sc.schoolid=c.schoolid','LEFT')
                    ->field('sc.schoolid,sc.name,sc.res_group_id as sc_res_group_id,c.classid as res_group_id,c.classname as res_group_name')
                    ->where('sc.res_group_id = '.$areaid)
                    ->where('c.res_group_id = 0')
                    ->where('c.isdel = 1')
                    ->whereor('sc.schoolid = '.$areaid)
                    ->select();
        if (empty($class_info)) {
            return array();
        }
        return $class_info;
    }

    /**
     * 修改班级单个字段
     * @param  [type] $classid [description]
     * @param  [type] $key     [键名]
     * @param  [type] $velue   [键值]
     * @return [type]          [description]
     */
    public function class_set($classid,$key,$velue){
        return db('class')->where('classid', $classid)->setField($key, $velue);
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
    public function getClasslList($condition, $page = '', $field = '*', $class = 'c.classid desc', $limit = '', $extend = array(), $master = false) {
        $list_paginate = db('class')
                         ->alias('c')
                         ->join('__POSITION__ po','po.position_id=c.position_id','LEFT')
                         ->join('__SCHOOL__ s','s.schoolid=c.schoolid','LEFT')
                         ->join('__SCHOOLTYPE__ t','t.sc_id=c.typeid','LEFT')
                         ->field('c.*,po.position,s.name as schoolname,t.sc_type as typename')
                         ->where($condition)
                         ->order($class)
                         ->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $list_paginate;
        $list = $list_paginate->items();

        if (empty($list))
            return array();
        return $list;
    }

    public function getAllClasses($condtion,$field="*"){
        $result = db('class')->where($condtion)->field($field)->select();
        return $result;
    }

    /**
     * 插入班级表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addClasses($data) {
        $insert = db('class')->insertGetId($data);
        return $insert;
    }

    /**
     * 批量添加班级
     * @创建时间   2019-05-29
     * @param  [type]     $data [description]
     * @return [type]           [description]
     */
    public function class_add($data) {
        $insert = db('class')->insertAll($data);
        return $insert;
    }

    /**
     * 添加订单日志
     */
    public function addOrderLog($data) {
        $data['log_role'] = str_replace(array('buyer', 'seller', 'system', 'admin'), array('买家', '商家', '系统', '管理员'), $data['log_role']);
        $data['log_time'] = TIMESTAMP;
        return db('orderlog')->insertGetId($data);
    }

    /**
     * 更改班级信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editClass($data, $condition, $limit = '') {

        $update = db('class')->where($condition)->limit($limit)->update($data);
        return $update;
    }

    public function getClassById($id){
        return db('class')->where('classid',$id)->find();
    }

    /**
     * @name 获取某地区编码数
     * @param $str
     * @return null|string
     */
    public function getNumber($str){
        $where = "classCard like '%$str%'";
        $classInfo = db('class')->where($where)->order('classid desc')->limit(1)->find();
        $number = sprintf("%04d", substr($classInfo['classCard'],-4)+1);
        return $number;
    }

}