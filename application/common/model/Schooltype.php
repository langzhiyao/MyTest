<?php

namespace app\common\model;

use think\Model;

class Schooltype extends Model {

    public $page_info;

    /**
     * 新增学校类型
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function sctype_add($param) {
        return db('schooltype')->insertGetId($param);
    }


    /**
     * 删除一个学校类型
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function sctype_del($sc_id) {
        $sc_id = (int) $sc_id;
        return db('schooltype')->where('sc_id', $sc_id)->delete();
    }

    /**
     * 更新学校类型记录
     *
     * @param array $param 更新内容
     * @return bool
     */
    public function sctype_update($param) {
        $sc_id = (int) $param['sc_id'];
        return db('schooltype')->where('sc_id', $param['sc_id'])->update($param);
    }

    /**
     * 获取学校类型列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function get_sctype_List($condition = array(), $page = '', $orderby = 'sc_sort asc') {
        if ($page) {
            $result = db('schooltype')->where($condition)->order($orderby)->paginate($page, false, ['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            return db('schooltype')->where($condition)->order($orderby)->select();
        }
    }


    /**
     * 根据id查询一条记录
     *
     * @param int $id 学校类型id
     * @return array 一维数组
     */
    public function getOneById($id) {
        return db('schooltype')->where('sc_id', $id)->find();
    }
    public function getOnePkg($condition = array()) {
        return db('schooltype')->where($condition)->find();
    }
    /**
     * api获取学校类型列表
     *
     * @param array $condition 查询条件
     * @return array 二维数组
     */
    public function get_sctype_Lists($condition = array(), $field, $orderby = 'sc_sort asc') {
            return db('schooltype')->field($field)->where($condition)->order($orderby)->select();
    }






}

?>
