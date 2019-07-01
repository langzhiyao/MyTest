<?php

namespace app\common\model;

use think\Model;

class Robot extends Model {

    public $page_info;

    /**
     * 新增学校类型
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function Robotadd($param) {
        return db('robot')->insertGetId($param);
    }


    public function RobotaddAll($param) {
        return db('robot')->insertAll($param);
    }


    public function Robotset($id,$key,$velue){
        return db('robot')->where('id', $id)->setField($key, $velue);
    }


    public function getRobotCount($condition){
        return db('robot')->where($condition)->count();
    }
    /**
     * 删除一个
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function Robotdel($id) {
        $id = (int) $id;
        return db('robot')->where('id', $id)->delete();
    }

    /**
     * 全部删除
     * @param  [type] $where [description]
     * @return [type]        [description]
     */
    public function RobotdelAll($where) {
        return db('robot')->where($where)->delete();
    }

    /**
     * 更新学校类型记录
     *
     * @param array $param 更新内容
     * @return bool
     */
    public function Robotupdate($where,$param) {
        return db('robot')->where($where)->update($param);
    }

    /**
     * 获取学校类型列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function getRobotList($condition = array(),$field='*', $page = '', $orderby = 'id asc') {
        if ($page) {
            $where = '';
            if (isset($condition)) {
                # code...
            }
            $result = db('robot')
                        ->alias('r')
                        ->where($condition)
                        ->join('__SCHOOL__ s','s.schoolid=r.schoolid','LEFT')
                        ->field('r.*,s.schoolid,s.name,s.region')
                        ->order($orderby)
                        ->paginate($page, false, ['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            return db('robot')->where($condition)->field($field)->order($orderby)->select();
        }
    }


    /**
     * 根据id查询一条记录
     *
     * @param int $id 学校类型id
     * @return array 一维数组
     */
    public function getOneById($id) {
        return db('robot')->where('id', $id)->find();
    }
    public function getOne($condition = array()) {
        return db('robot')->alias('r')->join('__SCHOOL__ s','s.schoolid=r.schoolid','LEFT')->field('r.*,s.name')->where($condition)->find();
    }
    /**
     * api获取学校类型列表
     *
     * @param array $condition 查询条件
     * @return array 二维数组
     */
    public function get_Robot_Lists($condition = array(),$field='*', $orderby = 'id desc') {
            return db('robot')->field($field)->where($condition)->order($orderby)->select();
    }






}

?>
