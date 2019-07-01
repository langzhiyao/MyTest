<?php

namespace app\common\model;


use think\Model;

class Invoice extends Model
{
    /**
     * 取得买家默认发票
     *
     * @param array $condition
     */
    public function getDefaultInvInfo($condition = array())
    {
        return db('invoice')->where($condition)->order('inv_state asc')->find();
    }

    /**
     * 取得单条发票信息
     *
     * @param array $condition
     */
    public function getInvInfo($condition = array())
    {
        return db('invoice')->where($condition)->find();
    }

    /**
     * 取得发票列表
     *
     * @param unknown_type $condition
     * @return unknown
     */
    public function getInvList($condition, $limit = '', $field = '*')
    {
        return db('invoice')->field($field)->where($condition)->limit($limit)->select();
    }

    /**
     * 删除发票信息
     *
     * @param unknown_type $condition
     * @return unknown
     */
    public function delInv($condition)
    {
        return db('invoice')->where($condition)->delete();
    }

    /**
     * 新增发票信息
     *
     * @param unknown_type $data
     * @return unknown
     */
    public function addInv($data)
    {
        return db('invoice')->insertGetId($data);
    }
}