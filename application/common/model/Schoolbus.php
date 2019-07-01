<?php

namespace app\common\model;

use think\Model;

class Schoolbus extends Model {

    public $page_info;

    /**
     * 新增学校类型
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function schoolbus_add($param) {
        return db('schoolbus')->insertGetId($param);
    }


    /**
     * 删除一个学校类型
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function schoolbus_del($bus_id) {
        $bus_id = (int) $bus_id;
        return db('schoolbus')->where('bus_id', $bus_id)->delete();
    }

    /**
     * 更新学校类型记录
     *
     * @param array $param 更新内容
     * @return bool
     */
    public function schoolbus_update($param,$condition='') {
        if ($condition) {
            return db('schoolbus')->where($condition)->update($param);
        }else{
            $bus_id = (int) $param['bus_id'];  
            return db('schoolbus')->where('bus_id', $param['bus_id'])->update($param);  
        }
        
        
    }

    /**
     * 获取学校类型列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function get_schoolbus_List($condition = array(), $page = '', $orderby = 'bus_id desc') {
        if ($page) {
            $result = db('schoolbus')->where($condition)->order($orderby)->paginate($page, false, ['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            return db('schoolbus')->where($condition)->order($orderby)->select();
        }
    }


    /**
     * 根据id查询一条记录
     *
     * @param int $id 学校类型id
     * @return array 一维数组
     */
    public function getOneById($id) {
        return db('schoolbus')->where('bus_id', $id)->find();
    }
    public function getOnePkg($condition = array()) {
        return db('schoolbus')->where($condition)->find();
    }
    /**
     * api获取学校类型列表
     *
     * @param array $condition 查询条件
     * @return array 二维数组
     */
    public function get_schoolbus_Lists($condition = array(),$field, $orderby = 'bus_id asc') {
            return db('schoolbus')->field($field)->where($condition)->order($orderby)->select();
    }






}

?>
