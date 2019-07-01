<?php

namespace app\common\model;
use think\Model;

class Student extends Model {
    public $page_info;
    
    /**
     * 取单条订单信息
     *
     * @param unknown_type $condition
     * @return unknown
     */
    public function getStudentInfo($condition = array(), $extend = array(), $fields = '*', $class = '', $group = '') {
        $class_info = db('student')->field($fields)->where($condition)->group($group)->order($class)->find();
        if (empty($class_info)) {
            return array();
        }
        return $class_info;
    }

    /**
     * 根据孩子id 查找学校班级信息 单条
     * @param  [type] $chind_id [description]
     * @return [type]           [description]
     */
    public function getChildrenInfoById($chind_id){
        $list_paginate = db('student')->alias('s')
        ->join('__SCHOOL__ sc','sc.schoolid=s.s_schoolid','LEFT')
        ->join('__CLASS__ cl','cl.classid=s.s_classid','LEFT')
        ->field('s.s_id,s.s_name,sc.schoolid,sc.name,sc.option_id,sc.admin_company_id,cl.classid,cl.classname,sc.provinceid,sc.cityid,sc.areaid')
        ->where('s_id',$chind_id)
        ->find();
        return $list_paginate;
    }
    //单个查询学生
    public function getChildrenInfoByIdes($chind_id){
        $list_paginate = db('student')->alias('s')
            ->join('__SCHOOL__ sc','sc.schoolid=s.s_schoolid','LEFT')
            ->join('__CLASS__ cl','cl.classid=s.s_classid','LEFT')
            ->field('s.s_id,s.s_name,s.s_region,sc.schoolid,sc.name,sc.res_group_id,cl.classid,cl.classname,cl.classCard,cl.res_group_id as clres_group_id,sc.provinceid,sc.cityid,sc.areaid')
            ->where('s_id',$chind_id)
            ->find();
        return $list_paginate;
    }

    /**
     * 根据用户id查找名下所有学生  多个学生
     * @param  [type] $member_id [description]
     * @return [type]            [description]
     */
    public function getAllChilds($member_id){

        $result = db('student')->alias('s')
        ->join('__SCHOOL__ sc','sc.schoolid=s.s_schoolid','LEFT')
        ->join('__CLASS__ cl','cl.classid=s.s_classid','LEFT')
        ->field('s.s_id,s.s_name,s.s_region,sc.schoolid,sc.name,sc.res_group_id,cl.classid,cl.classname,cl.classCard,cl.res_group_id as clres_group_id')
        ->where('s_ownerAccount',$member_id)
        ->whereor('FIND_IN_SET('.$member_id.',s_viceAccount)')
        ->select();
        
        return $result;
    }

    /**
     * 查看当前用户是否是孩子的家长
     * @param  [type] $member_id [description]
     * @return [type]            [description]
     */
    public function checkParentRelation($member_id,$s_id){

        $res = db('member')->where('member_id="'.$member_id.'"')->field('is_owner')->find();
        if($res['is_owner'] != 0){
            $member_id = $res['is_owner'];
        }
        $result = db('student')
        ->field('s_id,s_name')
        ->where('s_id',$s_id)
        ->whereor('s_ownerAccount',$member_id)
        ->find();
        if($result){
            return 'true';
        }else{
            return 'false';
        }
    }

    public function getOrderCommonInfo($condition = array(), $field = '*') {
        return db('ordercommon')->where($condition)->find();
    }

    public function getOrderPayInfo($condition = array(), $master = false) {
        return db('orderpay')->where($condition)->master($master)->find();
    }

    /**
     * 取得支付单列表
     *
     * @param unknown_type $condition
     * @param unknown_type $page
     * @param unknown_type $filed
     * @param unknown_type $order
     * @param string $key 以哪个字段作为下标,这里一般指pay_id
     * @return unknown
     */
    public function getOrderPayList($condition, $page = '', $filed = '*', $order = '', $key = '') {
        $pay_list = db('orderpay')->field($filed)->where($condition)->order($order)->page($page)->select();
        if($key){
            $pay_list = ds_changeArraykey($pay_list, $key);
        }
        
        return $pay_list;
    }

    /**
     * 取得订单列表(未被删除)
     * @param unknown $condition
     * @param string $page
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getNormalOrderList($condition, $page = '', $field = '*', $order = 'order_id desc', $limit = '', $extend = array()) {
        $condition['delete_state'] = 0;
        return $this->getOrderList($condition, $page, $field, $order, $limit, $extend);
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
    public function getStudentList($condition, $page = '', $field = '*', $class = 's.s_id desc', $limit = '', $extend = array(), $master = false) {

        $list_paginate = db('student')->alias('s')
            ->field('s.*,c.name as schoolname,t.sc_type as typename,l.classname')
            ->join('school c','c.schoolid = s.s_schoolid')
            ->join('schooltype t','t.sc_id = s.s_sctype')
            ->join('class l','l.classid = s.s_classid')
            ->where($condition)->order($class)->paginate($page,false,['query' => request()->param()]);
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
    public function addStudent($data) {
        $insert = db('student')->insertGetId($data);
        return $insert;
    }


    /**
     * 更改班级信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editStudent($data, $condition, $limit = '') {

        $update = db('student')->where($condition)->limit($limit)->update($data);
        return $update;
    }

    public function getAllStudent($condition){
        return db('student')
            ->join('__CLASS__ cl','cl.classid=s_classid','LEFT')
            ->field("s_id,s_name,s_sex,s_classid,s_card,classname,s_del,s_ownerAccount")->where($condition)->select();
    }

}