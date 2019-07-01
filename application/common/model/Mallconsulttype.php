<?php

namespace app\common\model;


use think\Model;

class Mallconsulttype extends Model
{
    /**
     * 咨询类型列表
     *
     * @param array $condition
     * @param string $field
     * @param string $key
     * @param string $order
     */
    public function getMallConsultTypeList($condition, $field = '*', $key = '', $order = 'mct_sort asc,mct_id asc')
    {
        $res= db('mallconsulttype')->where($condition)->field($field)->order($order)->select();
        if(!empty($key)) {
            return ds_changeArraykey($res, $key);
        }else{
            return $res;
        }
    }

    /**
     * 单条咨询类型
     *
     * @param unknown $condition
     * @param string $field
     */
    public function getMallConsultTypeInfo($condition, $field = '*')
    {
        return db('mallconsulttype')->where($condition)->field($field)->find();
    }

    /**
     * 添加咨询类型
     * @param array $insert
     * @return int
     */
    public function addMallConsultType($insert)
    {
        return db('mallconsulttype')->insert($insert);
    }

    /**
     * 编辑咨询类型
     * @param array $condition
     * @param array $update
     * @return boolean
     */
    public function editMallConsultType($condition, $update)
    {
        return db('mallconsulttype')->where($condition)->update($update);
    }

    /**
     * 删除咨询类型
     *
     * @param array $condition
     * @return boolean
     */
    public function delMallConsultType($condition)
    {
        return db('mallconsulttype')->where($condition)->delete();
    }
}