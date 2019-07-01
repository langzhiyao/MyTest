<?php

namespace app\common\model;

use think\Model;

class Course extends Model {

    public $page_info;

    /**
     * 新增学校类型
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function course_add($param) {
        return db('course')->insertGetId($param);
    }


    /**
     * 删除一个学校类型
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function course_del($co_id) {
        $co_id = (int) $co_id;
        return db('course')->where('co_id', $co_id)->delete();
    }

    /**
     * 更新学校类型记录
     *
     * @param array $param 更新内容
     * @return bool
     */
    public function course_update($param) {
        $co_id = (int) $param['co_id'];
        return db('course')->where('co_id', $param['co_id'])->update($param);
    }

    /**
     * 获取学校类型列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function get_course_List($condition = array(), $page = '', $orderby = 'co_sort asc') {
        if ($page) {
            $result = db('course')->where($condition)->order($orderby)->paginate($page, false, ['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            return db('course')->where($condition)->order($orderby)->select();
        }
    }


    /**
     * 根据id查询一条记录
     *
     * @param int $id 学校类型id
     * @return array 一维数组
     */
    public function getOneById($id) {
        return db('course')->where('co_id', $id)->find();
    }
    public function getOnePkg($condition = array()) {
        return db('course')->where($condition)->find();
    }
    /**
     * api获取学校类型列表
     *
     * @param array $condition 查询条件
     * @return array 二维数组
     */
    public function get_course_Lists($condition = array(),$field, $orderby = 'co_sort asc') {
            return db('course')->field($field)->where($condition)->order($orderby)->select();
    }






}

?>
