<?php

namespace app\common\model;

use think\Model;

class Position extends Model {

    public $page_info;

    /**
     * 新增教室位置
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function position_add($param) {
        return db('position')->insertGetId($param);
    }
    /**
     * 批量添加教室
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function positions_add($param) {
        return db('position')->insertAll($param);
    }


    /**
     * 删除一个教室位置
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function position_del($cid) {
        return db('position')->where('id', $cid)->delete();
    }

    /**
     * 更新教室位置记录
     *
     * @param array $param 更新内容
     * @return bool
     */
    public function position_update($param) {
        $sc_id = (int) $param['id'];
        unset($param['id']);
        return db('position')->where('id', $sc_id)->update($param);
    }
    /**
     * 编辑分子公司
     * @param array $condition
     * @param array $update
     * @return boolean
     */
    public function editposition($condition, $update) {
        return db('position')->where($condition)->update($update);
    }

    /**
     * 获取教室位置列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function getpositionList($condition, $page = '', $field = '*', $school = 'position_id desc', $limit = '', $extend = array(), $master = false) {
        $list_paginate = db('position')->field($field)->where($condition)->order($school)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $list_paginate;
        $list = $list_paginate->items();

        if (empty($list))
            return array();
        return $list;
    }

    /*
     * @desc 获取位置信息列表
     * @author langzhiyao
     * @time 20190625
     */
    public function get_position_list($condition, $page = '', $field = '*', $order = 'p.position_id desc', $limit = '', $extend = array(), $master = false) {
        $list_paginate = db('position')
            ->alias('p')
            ->field('p.*,s.name as school_name')
            ->join('school s','s.schoolid=p.school_id',LEFT)
//            ->join('schooltype t','t.sc_id=p.type_id',LEFT)
            ->where($condition)
            ->order($order)
            ->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $list_paginate;
        $list = $list_paginate->items();

        if (empty($list))
            return array();
        return $list;
    }

    public function getpositionClass($condition, $page = '', $field = '*', $order = 'p.id asc', $limit = '', $extend = array(), $master = false) {
        $list_paginate = db('position')
                        ->alias('p')
                        ->join('__CLASS__ c','p.id=c.position_id','LEFT')
                        ->field('p.id,p.position,p.camera_num,c.classname,c.classid')
                        ->where($condition)
                        ->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $list_paginate;
        $list = $list_paginate->items();

        if (empty($list))
            return array();
        return $list;
    }



    /**
     * 根据id查询一条记录
     *
     * @param int $id 教室位置id
     * @return array 一维数组
     */
    public function getOneById($id) {
        return db('position')->where('id', $id)->find();
    }
    public function getOnePkg($condition = array()) {
        return db('position')->where($condition)->find();
    }
    public function getpositions($condition,$conditions,$field = '*', $order = 'id desc') {
        $list = db('position')->field($field)->where($condition)->whereOr($conditions)->order($order)->select();
        if (empty($list))
            return array();
        return $list;
    }


    /**
     * 数量
     * @param array $condition
     * @return int
     */
    public function getpositionCount($condition)
    {
        return db('position')->where($condition)->count();
    }

}

?>
