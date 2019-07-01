<?php

namespace app\common\model;

use think\Model;

class Playback extends Model {

    public $page_info;

    /**
     * 新增学校类型
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function playback_add($param) {
        return db('playback')->insertGetId($param);
    }


    /**
     * 删除一个学校类型
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function playback_del($pid) {
        $pid = (int) $pid;
        return db('playback')->where('pid', $pid)->delete();
    }

    /**
     * 更新学校类型记录
     *
     * @param array $param 更新内容
     * @return bool
     */
    public function playback_update($param) {
        $pid = (int) $param['pid'];
        return db('playback')->where('pid', $param['pid'])->update($param);
    }

    /**
     * 获取学校类型列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function get_playback_List($condition = array(), $page = '', $orderby = 'up_time asc') {
        if ($page) {
            $result = db('playback')->where($condition)->order($orderby)->paginate($page, false, ['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            return db('playback')->where($condition)->order($orderby)->select();
        }
    }


    /**
     * 根据id查询一条记录
     *
     * @param int $id 学校类型id
     * @return array 一维数组
     */
    public function getOneById($id) {
        return db('playback')->where('pid', $id)->find();
    }
    public function getOnePkg($condition = array()) {
        return db('playback')->where($condition)->find();
    }

    public function makeDefaut($id,$type){
        $condition=[
            'pid' =>array('neq',$id),
            'type' => $type,
        ];

        return db('playback')->where($condition)->setField('pl_enabled', 0);
    }









}

?>
