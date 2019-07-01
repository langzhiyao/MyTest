<?php

namespace app\common\model;

use think\Model;

class Camera extends Model {

    public $page_info;

    /**
     * 新增学校类型
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function camera_add($param) {
        return db('camera')->insertGetId($param);
    }
    /**
     * 批量添加摄像头
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function cameras_add($param) {
        return db('camera')->insertAll($param);
    }


    /**
     * 删除一个学校类型
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function camera_del($cid) {
        return db('camera')->where('cid', $cid)->delete();
    }

    /**
     * 更新学校类型记录
     *
     * @param array $param 更新内容
     * @return bool
     */
    public function camera_update($param) {
        $sc_id = (int) $param['sc_id'];
        return db('camera')->where('sc_id', $param['sc_id'])->update($param);
    }
    /**
     * 编辑分子公司
     * @param array $condition
     * @param array $update
     * @return boolean
     */
    public function editCamera($condition, $update) {
        return db('camera')->where($condition)->update($update);
    }

    /**
     * 获取学校类型列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function getCameraList($condition, $page = '', $field = '*', $school = 'cid desc', $limit = '', $extend = array(), $master = false) {
        $condition = $this->_Condition($condition);
        $list_paginate = db('camera')->field($field)->where($condition)->order($school)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $list_paginate;
        $list = $list_paginate->items();

        if (empty($list))
            return array();
        return $list;
    }

    public function _Condition($where){
        $condition = [];
        if (isset($where['class_id'])) {
            $res_group_id = db('class')->where('classid',$where['class_id'])->value('res_group_id');
            unset($where);
            $condition['parentid'] = array('in',[$res_group_id]);
        }
        if (isset($where['school_id'])) {
            $res_group_ids = db('class')->field('res_group_id')->where('schoolid',$where['school_id'])->select();
            $condition['parentid'] = array('in',$res_group_ids);
        }
        return $condition;
    }


    /**
     * 根据id查询一条记录
     *
     * @param int $id 学校类型id
     * @return array 一维数组
     */
    public function getOneById($id) {
        return db('camera')->where('sc_id', $id)->find();
    }
    public function getOnePkg($condition = array()) {
        return db('camera')->where($condition)->find();
    }
    public function getCameras($condition,$conditions,$field = '*', $school = 'cid desc') {
        $list = db('camera')->field($field)->where($condition)->whereOr($conditions)->order($school)->select();
        if (empty($list))
            return array();
        return $list;
    }
    /**
     * 重温课堂列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function getCameraLists($where, $field = '*',$page = 0) {
        $list_paginate =db('camera')->alias('c')->field($field)->join('class a','c.parentid=a.res_group_id')->join('school s','a.schoolid=s.schoolid')->where($where)->limit($start,$page_count)->order('cid DESC')->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $list_paginate;
        $list = $list_paginate->items();
        if (empty($list))
            return array();
        return $list;
    }

    /**
     * 数量
     * @param array $condition
     * @return int
     */
    public function getCameraCount($condition)
    {
        return db('camera')->where($condition)->count();
    }

}

?>
