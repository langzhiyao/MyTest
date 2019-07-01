<?php

namespace app\common\model;


use think\Model;

class Deliverypoint extends Model
{
    const STATE1 = 1;   // 开启
    const STATE0 = 0;   // 关闭
    const STATE10 = 10; // 等待审核
    const STATE20 = 20; // 等待失败
    private $state = array(
        self::STATE0 => '关闭', self::STATE1 => '开启', self::STATE10 => '等待审核', self::STATE20 => '审核失败'
    );
    public $page_info;

    /**
     * 插入数据
     *
     * @param unknown $insert
     * @return boolean
     */
    public function addDeliveryPoint($insert)
    {
        return db('deliverypoint')->insert($insert);
    }

    /**
     * 查询物流自提服务站列表
     * @param array $condition
     * @param int $page
     * @param string $order
     */
    public function getDeliveryPointList($condition, $page = 0, $order = 'dlyp_id desc')
    {
        $res = db('deliverypoint')->where($condition)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info=$res;
        return $res->items();
    }

    /**
     * 等待审核的物流自提服务站列表
     * @param unknown $condition
     * @param number $page
     * @param string $order
     */
    public function getDeliveryPointWaitVerifyList($condition, $page = 0, $order = 'dlyp_id desc')
    {
        $condition['dlyp_state'] = self::STATE10;
        return $this->getDeliveryPointList($condition, $page, $order);
    }

    /**
     * 等待审核的物流自提服务站列表
     * @param unknown $condition
     * @param number $page
     * @param string $order
     */
    public function getDeliveryPointWaitVerifyCount($condition)
    {
        $condition['dlyp_state'] = self::STATE10;
        return db('deliverypoint')->where($condition)->count();
    }

    /**
     * 开启中物流自提服务列表
     * @param unknown $condition
     * @param number $page
     * @param string $order
     */
    public function getDeliveryPointOpenList($condition, $page = 0, $order = 'dlyp_id desc')
    {
        $condition['dlyp_state'] = self::STATE1;
        return $this->getDeliveryPointList($condition, $page, $order);
    }

    /**
     * 取得物流自提服务站详细信息
     * @param unknown $condition
     * @param string $field
     */
    public function getDeliveryPointInfo($condition, $field = '*')
    {
        return db('deliverypoint')->where($condition)->field($field)->find();
    }

    /**
     * 取得开启中物流自提服务信息
     * @param unknown $condition
     * @param string $field
     */
    public function getDeliveryPointOpenInfo($condition, $field = '*')
    {
        $condition['dlyp_state'] = self::STATE1;
        return db('deliverypoint')->where($condition)->field($field)->find();
    }

    /**
     * 取得开启中物流自提服务信息
     * @param unknown $condition
     * @param string $field
     */
    public function getDeliveryPointFailInfo($condition, $field = '*')
    {
        $condition['dlyp_state'] = self::STATE20;
        return db('deliverypoint')->where($condition)->field($field)->find();
    }

    /**
     * 物流自提服务站信息
     * @param array $update
     * @param array $condition
     */
    public function editDeliveryPoint($update, $condition)
    {
        return db('deliverypoint')->where($condition)->update($update);
    }

    /**
     * 返回状态数组
     * @return multitype:string
     */
    public function getDeliveryState()
    {
        return $this->state;
    }
}