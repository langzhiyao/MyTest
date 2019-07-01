<?php

namespace app\common\model;

use think\Model;

class Friendly extends Model {

    public $page_info;

    /**
     * 新增学校类型
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function friendly_add($param) {
        return db('chatfrendly')->insertGetId($param);
    }


    public function friendly_addAll($param) {
        return db('chatfrendly')->insertAll($param);
    }


    public function friendly_set($id,$key,$velue){
        return db('chatfrendly')->where('id', $id)->setField($key, $velue);
    }


    public function getFriendlyCount($condition){
        return db('chatfrendly')->where($condition)->count();
    }
    /**
     * 删除一个
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function friendly_del($id) {
        $id = (int) $id;
        return db('chatfrendly')->where('id', $id)->delete();
    }

    /**
     * 全部删除
     * @param  [type] $where [description]
     * @return [type]        [description]
     */
    public function friendly_delAll($where) {
        return db('chatfrendly')->where($where)->delete();
    }

    /**
     * 更新学校类型记录
     *
     * @param array $param 更新内容
     * @return bool
     */
    public function friendly_update($param) {
        $id = (int) $param['id'];
        return db('chatfrendly')->where('id', $id)->update($param);
    }

    /**
     * 获取学校类型列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function get_friendly_List($condition = array(), $page = '', $orderby = 'id asc') {
        if ($page) {
            $result = db('chatfrendly')->where($condition)->order($orderby)->paginate($page, false, ['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            return db('chatfrendly')->where($condition)->order($orderby)->select();
        }
    }


    /**
     * 根据id查询一条记录
     *
     * @param int $id 学校类型id
     * @return array 一维数组
     */
    public function getOneById($id) {
        return db('chatfrendly')->where('id', $id)->find();
    }
    public function getOne($condition = array()) {
        return db('chatfrendly')->where($condition)->find();
    }
    /**
     * api获取学校类型列表
     *
     * @param array $condition 查询条件
     * @return array 二维数组
     */
    public function get_friendly_Lists($condition = array(),$field='*', $orderby = 'id desc') {
            return db('chatfrendly')->field($field)->where($condition)->order($orderby)->select();
    }






}

?>
