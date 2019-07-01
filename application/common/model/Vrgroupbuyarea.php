<?php

namespace app\common\model;


use think\Model;

class Vrgroupbuyarea extends Model
{
    public $page_info;

    /**
     * 线下抢购信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getVrGroupbuyAreaInfo($condition, $field = '*')
    {
        return db('vrgroupbuyarea')->field($field)->where($condition)->find();
    }

    /**
     * 线下抢购列表
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     * @param string $limit
     */
    public function getVrGroupbuyAreaList($condition = array(), $field = '*', $page = '15', $order = 'hot_city desc, area_id')
    {
        $res = db('vrgroupbuyarea')->where($condition)->order($order)->field($field)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

    /**
     * 添加线下抢购
     * @param array $data
     */
    public function addVrGroupbuyArea($data)
    {
        return db('vrgroupbuyarea')->insert($data);
    }

    /**
     * 编辑线下抢购
     * @param array $condition
     * @param array $data
     */
    public function editVrGroupbuyArea($condition, $data)
    {
        return db('vrgroupbuyarea')->where($condition)->update($data);
    }

    /**
     * 删除线下分类
     * @param array $condition
     */
    public function delVrGroupbuyArea($condition)
    {
        return db('vrgroupbuyarea')->where($condition)->delete();
    }
}