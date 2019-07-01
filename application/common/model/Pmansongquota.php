<?php

/**
 * 满即送套餐模型
 *
 */

namespace app\common\model;

use think\Model;

class Pmansongquota extends Model
{
    public $page_info;

    /**
     * 读取满即送套餐列表
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @return array 满即送套餐列表
     *
     */
    public function getMansongQuotaList($condition, $page = null, $order = '', $field = '*')
    {
        $res = db('pmansongquota')->field($field)->where($condition)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        $result = $res->items();
        return $result;
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getMansongQuotaInfo($condition)
    {
        $result = db('pmansongquota')->where($condition)->find();
        return $result;
    }

    /**
     * 获取当前可用套餐
     * @param int $store_id
     * @return array
     *
     */
    public function getMansongQuotaCurrent($store_id)
    {
        $condition = array();
        $condition['store_id'] = $store_id;
        $condition['end_time'] = array('gt', TIMESTAMP);
        return $this->getMansongQuotaInfo($condition);
    }

    /*
     * 增加 
     * @param array $param
     * @return bool
     *
     */

    public function addMansongQuota($param)
    {
        return db('pmansongquota')->insert($param);
    }

    /*
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     *
     */

    public function editMansongQuota($update, $condition)
    {
        return db('pmansongquota')->where($condition)->update($update);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     *
     */

    public function delMansongQuota($condition)
    {
        return db('pmansongquota')->where($condition)->delete();
    }

}
