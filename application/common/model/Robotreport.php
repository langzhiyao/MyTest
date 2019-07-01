<?php

namespace app\common\model;

use think\Model;

class Robotreport extends Model {

    public $page_info;

    /**
     * 新增
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function report_add($param) {
        return db('robotreport')->insertGetId($param);
    }

    //批量插入
    public function reportall_add($param) {
        return db('robotreport')->insertAll($param);
    }

    /**
     * 删除一个
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function report_del($sc_id) {
        $sc_id = (int) $sc_id;
        return db('robotreport')->where('sc_id', $sc_id)->delete();
    }

    /**
     * 更新
     *
     * @param array $param 更新内容
     * @return bool
     */
    public function report_update($param,$where) {
        return db('robotreport')->where($where)->update($param);
    }

    /**
     * 获取教师申请列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function getReportList($condition = array(), $page = '', $orderby = 'id desc') {
        if ($page) {
            $result = db('robotreport')->where($condition)->order($orderby)->paginate($page, false, ['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
             return db('robotreport')->where($condition)->order($orderby)->select();
        }
    }


    /**
     * 根据id查询一条记录
     *
     * @param int $id 学校类型id
     * @return array 一维数组
     */
    public function getOneById($id) {
        return db('robotreport')->where('id', $id)->find();
    }

    /**
     * 查询单条记录
     *
     * @param int $id 学校类型id
     * @return array 一维数组
     */
    public function getOneInfo($where) {
        return db('robotreport')->where($where)->find();
    }

}

?>
