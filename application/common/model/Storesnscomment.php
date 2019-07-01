<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 12:01
 */

namespace app\common\model;


use think\Model;

class Storesnscomment extends Model
{
    public $page_info;
    /**
     * 店铺动态评论列表
     *
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getStoreSnsCommentList($condition, $field = '*', $order = 'scomm_id desc', $limit = 0, $page = 0) {
        $res= $this->where($condition)->field($field)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info=$res;
        return $res->items();
    }

    /**
     * 店铺评论数量
     * @param array $condition
     * @return array
     */
    public function getStoreSnsCommentCount($condition) {
        return $this->where($condition)->count();
    }

    /**
     * 获取单条评论
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getStoreSnsCommentInfo($condition, $field = '*') {
        return $this->where($condition)->field($field)->find();
    }

    /**
     * 保存店铺评论
     *
     * @param array $insert
     * @return boolean
     */
    public function saveStoreSnsComment($insert) {
        return $this->insertGetId($insert);
    }

    public function editStoreSnsComment($update, $condition) {
        return $this->where($condition)->update($update);
    }

    /**
     * 删除店铺动态评论
     *
     * @param array $condition
     * @return boolean
     */
    public function delStoreSnsComment($condition) {
        return $this->where($condition)->delete();
    }
}