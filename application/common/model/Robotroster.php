<?php

namespace app\common\model;

use think\Model;

class Robotroster extends Model {

    public $page_info;

    /**
     * 新增
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function rosterAdd($param) {
        return db('robotroster')->insertGetId($param);
    }


    /**
     * 删除一个
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function rosterDel($sc_id) {
        $sc_id = (int) $sc_id;
        return db('robotroster')->where('sc_id', $sc_id)->delete();
    }

    /**
     * 更新
     *
     * @param array $param 更新内容
     * @return bool
     */
    public function rosterUpdate($param,$where) {
        return db('robotroster')->where($where)->update($param);
    }

    /**
     * 获取教师申请列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function getRosterList($condition = array(), $page = '', $orderby = 'id desc') {
        if ($page) {
            $result = db('robotroster')->where($condition)->order($orderby)->paginate($page, false, ['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
             return db('robotroster')->where($condition)->order($orderby)->select();
        }
    }


    /**
     * 根据id查询一条记录
     *
     * @param int $id 学校类型id
     * @return array 一维数组
     */
    public function getOneById($id) {
        return db('robotroster')->where('id', $id)->find();
    }

    /**
     * 查询单条记录
     *
     * @param int $id 学校类型id
     * @return array 一维数组
     */
    public function getOneInfo($where) {
        return db('robotroster')->where($where)->find();
    }

}

?>
