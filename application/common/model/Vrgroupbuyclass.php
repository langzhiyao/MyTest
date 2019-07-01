<?php

namespace app\common\model;


use think\Model;

class Vrgroupbuyclass extends Model
{
    /**
     * 线下分类信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getVrGroupbuyClassInfo($condition, $field = '*')
    {
        return db('vrgroupbuyclass')->field($field)->where($condition)->find();
    }

    /**
     * 线下分类列表
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     * @param string $limit
     */
    public function getVrGroupbuyClassList($condition = array(), $field = '*', $order = 'class_sort', $limit = '0,1000')
    {
        return db('vrgroupbuyclass')->where($condition)->order($order)->limit($limit)->select();
    }

    /**
     * 添加线下分类
     * @param array $data
     */
    public function addVrGroupbuyClass($data)
    {
        return db('vrgroupbuyclass')->insertGetId($data);
    }

    /**
     * 编辑线下分类
     * @param array $condition
     * @param array $data
     */
    public function editVrGroupbuyClass($condition, $data)
    {
        return db('vrgroupbuyclass')->where($condition)->update($data);
    }

    /**
     * 删除线下分类
     * @param array $condition
     */
    public function delVrGroupbuyClass($condition)
    {
        return db('vrgroupbuyclass')->where($condition)->delete();
    }
}