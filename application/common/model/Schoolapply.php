<?php

namespace app\common\model;
use think\Model;

class Schoolapply extends Model {
    public $page_info;
    
    /**
     * 取单条订单信息
     *
     * @param unknown_type $condition
     * @return unknown
     */
    public function getSchoolapplyInfo($condition = array(), $extend = array(), $fields = '*', $school = '', $group = '') {
        $school_info = db('schoolapply')->field($fields)->where($condition)->group($group)->order($school)->find();
        if (empty($school_info)) {
            return array();
        }
        return $school_info;
    }

    public function getSchoolapplyById($id){
        return db('schoolapply')->where('applyid',$id)->find();
    }


    /**
     * 取得学校列表(所有)
     * @param unknown $condition
     * @param string $page
     * @param string $field
     * @param string $school
     * @param string $limit
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getSchoolapplyList($condition, $page = '', $field = '*', $school = 'applyid asc', $limit = '', $extend = array(), $master = false) {
        //$list_paginate = db('schoolapply')->alias('s')->join('__ADMIN__ a',' a.admin_id=s.auditor ','LEFT')->field($field)->where($condition)->order($school)->paginate($page,false,['query' => request()->param()]);
        $list_paginate = db('schoolapply')->field($field)->where($condition)->order($school)->paginate($page,false,['query' => request()->param()]);
        //$sql =  db('school')->getlastsql();
        $this->page_info = $list_paginate;
        $list = $list_paginate->items();

        if (empty($list))
            return array();
        return $list;
    }

    public function getAllAchoolapply(){
        $result = db('schoolapply')->select();
        return $result;
    }


    /**
     * 插入订单表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addSchoolapply($data) {
        $insert = db('schoolapply')->insertGetId($data);
        return $insert;
    }


    /**
     * 更改学校信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editSchoolapply($data, $condition, $limit = '') {
        $update = db('schoolapply')->where($condition)->limit($limit)->update($data);
        return $update;
    }

    /**
     * 取得数量
     * @param unknown $condition
     */
    public function getApplyCount($condition) {
        return db('schoolapply')->where($condition)->count();
    }

}