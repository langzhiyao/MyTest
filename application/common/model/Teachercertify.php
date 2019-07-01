<?php

namespace app\common\model;

use think\Model;

class Teachercertify extends Model {

    public $page_info;

    /**
     * 新增教师申请
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function teacher_add($param) {
        return db('teachercertify')->insertGetId($param);
    }


    /**
     * 删除一个教师申请
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function teacher_del($sc_id) {
        $sc_id = (int) $sc_id;
        return db('teachercertify')->where('sc_id', $sc_id)->delete();
    }

    /**
     * 更新教师申请
     *
     * @param array $param 更新内容
     * @return bool
     */
    public function teacher_update($param,$where) {
        return db('teachercertify')->where($where)->update($param);
    }

    /**
     * 获取教师申请列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function getTeacherList($condition = array(), $page = '', $orderby = 'id desc') {
        if ($page) {
            $result = db('teachercertify')->alias('s')
                    ->join('__MEMBER__ me','me.member_id=s.member_id','LEFT')
                    ->field('s.id,s.username,s.idcard,s.cardimg,s.cardimg_fan,s.certificate,s.createtime,s.status,s.failreason,me.member_add_time')
                    ->where($condition)->order($orderby)->paginate($page, false, ['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            return db('teachercertify')->where($condition)->order($orderby)->select();
        }
    }


    /**
     * 根据id查询一条记录
     *
     * @param int $id 学校类型id
     * @return array 一维数组
     */
    public function getOneById($id) {
        return db('teachercertify')->where('id', $id)->find();
    }

    /**
     * 查询单条记录
     *
     * @param int $id 学校类型id
     * @return array 一维数组
     */
    public function getOneInfo($where) {
        return db('teachercertify')->where($where)->find();
    }

}

?>
