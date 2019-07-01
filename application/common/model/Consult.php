<?php

namespace app\common\model;


use think\Model;

class Consult extends Model
{
    public $page_info;
    /**
     * 咨询数量
     *
     * @param array $condition
     * @return int
     */
    public function getConsultCount($condition)
    {
        return db('consult')->where($condition)->count();
    }

    /**
     * 添加咨询
     * @param array $insert
     * @return int
     */
    public function addConsult($insert)
    {
        return db('consult')->insert($insert);
    }

    /**
     * 商品咨询列表
     * @param unknown $condition
     * @param string $field
     * @param number $limit
     * @param number $page
     * @param string $order
     * @return array
     */
    public function getConsultList($condition, $field = '*', $page=10, $order = 'consult_id desc')
    {
         $res=db('consult')->where($condition)->field($field)->order($order)->paginate($page,false,['query' => request()->param()]);
         $this->page_info=$res;
         return $res->items();
        //db('consult')->where($condition)->field($field)->order($order)->limit($limit)->page($page)->select();
    }

    public function getConsultInfo($condition)
    {
        return db('consult')->where($condition)->find();
    }

    /**
     * 删除咨询
     *
     * @param unknown_type $id
     */
    public function delConsult($condition)
    {
        return db('consult')->where($condition)->delete();
    }

    /**
     * 回复咨询
     *
     * @param unknown_type $input
     */
    public function editConsult($condition, $update)
    {
        $update['consult_reply_time'] = TIMESTAMP;
        return db('consult')->where($condition)->update($update);
    }
}