<?php

namespace app\common\model;
use think\Model;

class Groupbuyquota extends Model
{
    public $page_info;
    /**
     * 读取抢购套餐列表
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @return array 抢购套餐列表
     *
     */
    public function getGroupbuyQuotaList($condition, $page = null, $order = '', $field = '*')
    {
        $result = db('groupbuyquota')->field($field)->where($condition)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info=$result;
        $result=$result->items();
        return $result;
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getGroupbuyQuotaInfo($condition)
    {
        $result = db('groupbuyquota')->where($condition)->find();
        return $result;
    }

    /**
     * 获取当前可用套餐
     * @param int $store_id
     * @return array
     *
     */
    public function getGroupbuyQuotaCurrent($store_id)
    {
        $condition = array();
        $condition['store_id'] = $store_id;
        $condition['end_time'] = array('gt', TIMESTAMP);
        return $this->getGroupbuyQuotaInfo($condition);
    }

    /*
     * 增加
     * @param array $param
     * @return bool
     *
     */
    public function addGroupbuyQuota($param)
    {
        return db('groupbuyquota')->insert($param);
    }

    /*
	 * 更新
	 * @param array $update
	 * @param array $condition
	 * @return bool
     *
	 */
    public function editGroupbuyQuota($update, $condition)
    {
        return db('groupbuyquota')->where($condition)->update($update);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     *
     */
    public function delGroupbuyQuota($condition)
    {
        return db('groupbuyquota')->where($condition)->delete();
    }
}