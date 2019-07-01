<?php

namespace app\common\model;

use think\Model;

//以下是定义结算单状态
//默认
define('BILL_STATE_CREATE', 1);
//店铺已确认
define('BILL_STATE_STORE_COFIRM', 2);
//平台已审核
define('BILL_STATE_SYSTEM_CHECK', 3);
//结算完成
define('BILL_STATE_SUCCESS', 4);

class Bill extends Model {
    public $page_info;
    /**
     * 取得平台月结算单
     * @param unknown $condition
     * @param unknown $fields
     * @param unknown $pagesize
     * @param unknown $order
     * @param unknown $limit
     */
    public function getOrderStatisList($condition = array(), $fields = '*', $pagesize = null, $order = '', $limit = null) {
        if($pagesize){
            $result = db('orderstatis')->where($condition)->field($fields)->order($order)->paginate($pagesize,false,['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        }else{
            return db('orderstatis')->where($condition)->field($fields)->order($order)->select();
        }
    }

    /**
     * 取得平台月结算单条信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getOrderStatisInfo($condition = array(), $fields = '*', $order = null) {
        return db('orderstatis')->where($condition)->field($fields)->order($order)->find();
    }

    /**
     * 取得店铺月结算单列表
     * @param unknown $condition
     * @param string $fields
     * @param string $pagesize
     * @param string $order
     * @param string $limit
     */
    public function getOrderBillList($condition = array(), $fields = '*', $pagesize = null, $order = '', $limit = null) {
//        return db('orderbill')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
        if($pagesize){
            $result = db('orderbill')->where($condition)->field($fields)->order($order)->paginate($pagesize,false,['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        }else{
            return db('orderbill')->where($condition)->field($fields)->order($order)->select();
        }
        
        
    }

    /**
     * 取得店铺月结算单单条
     * @param unknown $condition
     * @param string $fields
     */
    public function getOrderBillInfo($condition = array(), $fields = '*') {
        return db('orderbill')->where($condition)->field($fields)->find();
    }

    /**
     * 取得订单数量
     * @param unknown $condition
     */
    public function getOrderBillCount($condition) {
        return db('orderbill')->where($condition)->count();
    }

    public function addOrderStatis($data) {
        return db('orderstatis')->insert($data);
    }

    public function addOrderBill($data) {
        return db('orderbill')->insert($data);
    }

    public function editOrderBill($data, $condition = array()) {
        return db('orderbill')->where($condition)->update($data);
    }

}

?>
