<?php

namespace app\common\model;
use think\Model;

class Order extends Model {
    public $page_info;
    
    /**
     * 取单条订单信息
     *
     * @param unknown_type $condition
     * @param array $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return unknown
     */
    public function getOrderInfo($condition = array(), $extend = array(), $fields = '*', $order = '', $group = '') {
        $order_info = db('order')->field($fields)->where($condition)->group($group)->order($order)->find();
        if (empty($order_info)) {
            return array();
        }
        if (isset($order_info['order_state'])) {
            $order_info['state_desc'] = orderState($order_info);
        }
        if (isset($order_info['payment_code'])) {
            $order_info['payment_name'] = orderPaymentName($order_info['payment_code']);
        }

        //追加返回订单扩展表信息
        if (in_array('order_common', $extend)) {
            $order_info['extend_order_common'] = $this->getOrderCommonInfo(array('order_id' => $order_info['order_id']));
            $order_info['extend_order_common']['reciver_info'] = unserialize($order_info['extend_order_common']['reciver_info']);
            $order_info['extend_order_common']['invoice_info'] = unserialize($order_info['extend_order_common']['invoice_info']);
        }

        //追加返回店铺信息
        if (in_array('store', $extend)) {
            $order_info['extend_store'] = Model('store')->getStoreInfo(array('store_id' => $order_info['store_id']));
        }

        //返回买家信息
        if (in_array('member', $extend)) {
            $order_info['extend_member'] = Model('member')->getMemberInfoByID($order_info['buyer_id']);
        }

        //追加返回商品信息
        if (in_array('order_goods', $extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id' => $order_info['order_id']));
            $order_info['extend_order_goods'] = $order_goods_list;
        }
        return $order_info;
    }

    public function getOrderCommonInfo($condition = array(), $field = '*') {
        return db('ordercommon')->where($condition)->find();
    }

    public function getOrderPayInfo($condition = array(), $master = false) {
        return db('orderpay')->where($condition)->master($master)->find();
    }

    /**
     * 取得支付单列表
     *
     * @param unknown_type $condition
     * @param unknown_type $page
     * @param unknown_type $filed
     * @param unknown_type $order
     * @param string $key 以哪个字段作为下标,这里一般指pay_id
     * @return unknown
     */
    public function getOrderPayList($condition, $page = '', $filed = '*', $order = '', $key = '') {
        $pay_list = db('orderpay')->field($filed)->where($condition)->order($order)->page($page)->select();
        if($key){
            $pay_list = ds_changeArraykey($pay_list, $key);
        }
        
        return $pay_list;
    }

    /**
     * 取得订单列表(未被删除)
     * @param unknown $condition
     * @param string $page
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getNormalOrderList($condition, $page = '', $field = '*', $order = 'order_id desc', $limit = '', $extend = array()) {
        $condition['delete_state'] = 0;
        return $this->getOrderList($condition, $page, $field, $order, $limit, $extend);
    }

    /**
     * 取得订单列表(所有)
     * @param unknown $condition
     * @param string $page
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getOrderList($condition, $page = '', $field = '*', $order = 'order_id desc', $limit = '', $extend = array(), $master = false) {

            $list_paginate = db('order')->field($field)->where($condition)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info = $list_paginate;
            $list = $list_paginate->items();

        if (empty($list))
            return array();


        $order_list = array();
        foreach ($list as $order) {
            if (isset($order['order_state'])) {
                $order['state_desc'] = orderState($order);
            }
            if (isset($order['payment_code'])) {
                $order['payment_name'] = orderPaymentName($order['payment_code']);
            }
            if (!empty($extend))
                $order_list[$order['order_id']] = $order;
        }
        if (empty($order_list))
            $order_list = $list;

        //追加返回订单扩展表信息
        if (in_array('order_common', $extend)) {
            $order_common_list = $this->getOrderCommonList(array('order_id' => array('in', array_keys($order_list))));
            foreach ($order_common_list as $value) {
                $order_list[$value['order_id']]['extend_order_common'] = $value;
                $order_list[$value['order_id']]['extend_order_common']['reciver_info'] = @unserialize($value['reciver_info']);
                $order_list[$value['order_id']]['extend_order_common']['invoice_info'] = @unserialize($value['invoice_info']);
            }
        }
        //追加返回店铺信息
        if (in_array('store', $extend)) {
            $store_id_array = array();
            foreach ($order_list as $value) {
                if (!in_array($value['store_id'], $store_id_array))
                    $store_id_array[] = $value['store_id'];
            }
            $store_list = db('store')->where('store_id', 'in', array_values($store_id_array))->select();
            $store_new_list = array();
            foreach ($store_list as $store) {
                $store_new_list[$store['store_id']] = $store;
            }
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_store'] = isset($store_new_list[$order['store_id']])?$store_new_list[$order['store_id']]:'';
            }
        }

        //追加返回买家信息
        if (in_array('member', $extend)) {
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_member'] = Model('member')->getMemberInfoByID($order['buyer_id']);
            }
        }


        //追加返回商品信息
        if (in_array('order_goods', $extend)) {
            //取商品列表
            $order_goods_list = db('ordergoods')->where('order_id', 'in', array_keys($order_list))->select();

            if (!empty($order_goods_list)) {
                foreach ($order_goods_list as $value) {

                    $order_list[$value['order_id']]['extend_order_goods'][] = $value;
                }
            } else {
                $order_list[$value['order_id']]['extend_order_goods']= array();
            }
        }

        return $order_list;
    }

    /**
     * 取得订单列表(所有)
     * @param unknown $condition
     * @param string $page
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getOrderListForAdmin($condition, $page = '',  $limit = '', $extend = array(), $master = false) {
        $list = db('order')->where($condition)->order('order_id desc')->paginate($limit,false,['var_page'=>'page']);
        $count = $list->total();
        $list = $list->items();
        if (empty($list))
            return array();
        $order_list = array();
        foreach ($list as $order) {
            if (isset($order['order_state'])) {
                $order['state_desc'] = orderState($order);
            }
            if (isset($order['payment_code'])) {
                $order['payment_name'] = orderPaymentName($order['payment_code']);
            }
            if (!empty($extend))
                $order_list[$order['order_id']] = $order;
        }
        if (empty($order_list))
            $order_list = $list;
        //追加返回商品信息
        if (in_array('order_goods', $extend)) {
            //取商品列表
            $order_goods_list = db('ordergoods')->where('order_id', 'in', array_keys($order_list))->select();

            if (!empty($order_goods_list)) {
                foreach ($order_goods_list as $value) {

                    $order_list[$value['order_id']]['extend_order_goods'][] = $value;
                }
            } else {
                $order_list[$value['order_id']]['extend_order_goods']= array();
            }
        }

        $orders= ['list'=>$order_list,'count'=>$count];
        return $orders;
    }

    /**
     * 取得(买/卖家)订单某个数量缓存
     * @param string $type 买/卖家标志，允许传入 buyer、store
     * @param int $id   买家ID、店铺ID
     * @param string $key 允许传入  NewCount、PayCount、SendCount、EvalCount，分别取相应数量缓存，只许传入一个
     * @return array
     */
    public function getOrderCountCache($type, $id, $key) {
        if (!config('cache_open'))
            return array();
        $types = $id.'_ordercount' . $key;
        $order_info = cache::get($types);
        return !is_array($order_info) ? array($key => $order_info) : $order_info;
    }

    /**
     * 设置(买/卖家)订单某个数量缓存
     * @param string $type 买/卖家标志，允许传入 buyer、store
     * @param int $id 买家ID、店铺ID
     * @param array $data
     */
    public function editOrderCountCache($type, $id, $data) {
        if (!config('cache_open') || empty($type) || !intval($id) || !is_array($data))
            return;
        $key=array_keys($data);
        $types = $id.'_ordercount' . $key['0'];
        $type='ordercount'.$type;
        cache::tag($type)->set($types, $data);
    }

    /**
     * 取得买卖家订单数量某个缓存
     * @param string $type $type 买/卖家标志，允许传入 buyer、store
     * @param int $id 买家ID、店铺ID
     * @param string $key 允许传入  NewCount、PayCount、SendCount、EvalCount，分别取相应数量缓存，只许传入一个
     * @return int
     */
    public function getOrderCountByID($type, $id, $key) {
        $cache_info = $this->getOrderCountCache($type, $id, $key);
        if (isset($cache_info[$key])&&is_numeric($cache_info[$key])) {
            //从缓存中取得
            $count = $cache_info[$key];
        } else {
            //从数据库中取得
            $field = $type == 'buyer' ? 'buyer_id' : 'store_id';
            $condition = array($field => $id);
            $func = 'getOrderState' . $key;
            $count = $this->$func($condition);
            $this->editOrderCountCache($type, $id, array($key => $count));
        }
        return $count;
    }

    /**
     * 删除(买/卖家)订单全部数量缓存
     * @param string $type 买/卖家标志，允许传入 buyer、store
     * @param int $id   买家ID、店铺ID
     * @return bool
     */
    public function delOrderCountCache($type, $id) {
        if (!config('cache_open'))
            return true;
        $type = 'ordercount' . $type;
        return cache::clear($type);
    }

    /**
     * 待付款订单数量
     * @param unknown $condition
     */
    public function getOrderStateNewCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_NEW;
        return $this->getOrderCount($condition);
    }

    /**
     * 待发货订单数量
     * @param unknown $condition
     */
    public function getOrderStatePayCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_PAY;
        return $this->getOrderCount($condition);
    }

    /**
     * 待收货订单数量
     * @param unknown $condition
     */
    public function getOrderStateSendCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_SEND;
        return $this->getOrderCount($condition);
    }

    /**
     * 待评价订单数量
     * @param unknown $condition
     */
    public function getOrderStateEvalCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_SUCCESS;
        $condition['evaluation_state'] = 0;
        return $this->getOrderCount($condition);
    }

    /**
     * 交易中的订单数量
     * @param unknown $condition
     */
    public function getOrderStateTradeCount($condition = array()) {
        $condition['order_state'] = array(array('neq', ORDER_STATE_CANCEL), array('neq', ORDER_STATE_SUCCESS), 'and');
        return $this->getOrderCount($condition);
    }

    /**
     * 取得订单数量
     * @param unknown $condition
     */
    public function getOrderCount($condition) {
        return db('order')->where($condition)->count();
    }

    /**
     * 取得订单商品表详细信息
     * @param unknown $condition
     * @param string $fields
     * @param string $order
     */
    public function getOrderGoodsInfo($condition = array(), $fields = '*', $order = '') {
        return db('ordergoods')->where($condition)->field($fields)->order($order)->find();
    }

    /**
     * 取得订单商品表列表
     * @param unknown $condition
     * @param string $fields
     * @param string $limit
     * @param string $page
     * @param string $order
     * @param string $group
     * @param string $key
     */
    public function getOrderGoodsList($condition = array(), $fields = '*', $limit = null, $page = null, $order = 'rec_id desc', $group = null, $key = null) {
        if ($limit) {
            return db('ordergoods')->field($fields)->where($condition)->limit($limit)->order($order)->group($group)->page($page)->select();
        } else {
            return db('ordergoods')->field($fields)->where($condition)->order($order)->group($group)->select();
        }
    }

    /**
     * 取得订单扩展表列表
     * @param unknown $condition
     * @param string $fields
     * @param string $limit
     */
    public function getOrderCommonList($condition = array(), $fields = '*', $order = '', $limit = null) {
        return db('ordercommon')->field($fields)->where($condition)->order($order)->limit($limit)->select();
    }

    /**
     * 插入订单支付表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderPay($data) {
        return db('orderpay')->insertGetId($data);
    }

    /**
     * 插入订单表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrder($data) {
        $insert = db('order')->insertGetId($data);
        if ($insert) {
            //更新缓存
            \mall\queue\QueueClient::push('delOrderCountCache',array('buyer_id'=>$data['buyer_id'],'store_id'=>$data['store_id']));
        }
        return $insert;
    }

    /**
     * 插入订单扩展表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderCommon($data) {
        return db('ordercommon')->insertGetId($data);
    }

    /**
     * 插入订单扩展表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderGoods($data) {
        return db('ordergoods')->insertAll($data);
    }

    /**
     * 添加订单日志
     */
    public function addOrderLog($data) {
        $data['log_role'] = str_replace(array('buyer', 'seller', 'system', 'admin'), array('买家', '商家', '系统', '管理员'), $data['log_role']);
        $data['log_time'] = TIMESTAMP;
        return db('orderlog')->insertGetId($data);
    }

    /**
     * 更改订单信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editOrder($data, $condition, $limit = '') {
        $update = db('order')->where($condition)->limit($limit)->update($data);
        if ($update) {
            //更新缓存
            \mall\queue\QueueClient::push('delOrderCountCache', $condition);
        }
        return $update;
    }

    /**
     * 更改订单信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editOrderCommon($data, $condition) {
        return db('ordercommon')->where($condition)->update($data);
    }

    /**
     * 更改订单信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editOrderGoods($data, $condition) {
        return db('ordergoods')->where($condition)->update($data);
    }

    /**
     * 更改订单支付信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editOrderPay($data, $condition) {
        return db('orderpay')->where($condition)->update($data);
    }

    /**
     * 订单操作历史列表
     * @param unknown $order_id
     * @return Ambigous <multitype:, unknown>
     */
    public function getOrderLogList($condition) {
        return db('orderlog')->where($condition)->select();
    }

    /**
     * 取得单条订单操作记录
     * @param unknown $condition
     * @param string $order
     */
    public function getOrderLogInfo($condition = array(), $order = '') {
        return db('orderlog')->where($condition)->order($order)->find();
    }

    /**
     * 返回是否允许某些操作
     * @param unknown $operate
     * @param unknown $order_info
     */
    public function getOrderOperateState($operate, $order_info) {
        if (!is_array($order_info) || empty($order_info))
            return false;

        switch ($operate) {

            //买家取消订单
            case 'buyer_cancel':
                $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
                        ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
                break;

            //申请退款
            case 'refund_cancel':
                if (isset($order_info['refund'])) {
                    $state = $order_info['refund'] == 1 && !intval($order_info['lock_state']);
                } else {
                    $state = FALSE;
                }
                break;

            //商家取消订单
            case 'store_cancel':
                $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
                        ($order_info['payment_code'] == 'offline' &&
                        in_array($order_info['order_state'], array(ORDER_STATE_PAY, ORDER_STATE_SEND)));
                break;

            //平台取消订单
            case 'system_cancel':
                $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
                        ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
                break;

            //平台收款
            case 'system_receive_pay':
                $state = $order_info['order_state'] == ORDER_STATE_NEW && $order_info['payment_code'] == 'online';
                break;

            //买家投诉
            case 'complain':
                $state = in_array($order_info['order_state'], array(ORDER_STATE_PAY, ORDER_STATE_SEND)) ||
                        intval($order_info['finnshed_time']) > (TIMESTAMP - config('complain_time_limit'));
                break;

            case 'payment':
                $state = $order_info['order_state'] == ORDER_STATE_NEW && $order_info['payment_code'] == 'online';
                break;

            //调整运费
            case 'modify_price':
                $state = ($order_info['order_state'] == ORDER_STATE_NEW) || ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
                $state = floatval($order_info['shipping_fee']) > 0 && $state;
                break;
            //调整商品价格
            case 'spay_price':
                $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
                        ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
                $state = floatval($order_info['goods_amount']) > 0 && $state;
                break;

            //发货
            case 'send':
                $state = !$order_info['lock_state'] && $order_info['order_state'] == ORDER_STATE_PAY;
                break;

            //收货
            case 'receive':
                $state = !$order_info['lock_state'] && $order_info['order_state'] == ORDER_STATE_SEND;
                break;

            //评价
            case 'evaluation':
                $state = !$order_info['lock_state'] && !$order_info['evaluation_state'] && $order_info['order_state'] == ORDER_STATE_SUCCESS;
                break;

            //锁定
            case 'lock':
                $state = intval($order_info['lock_state']) ? true : false;
                break;

            //快递跟踪
            case 'deliver':
                $state = !empty($order_info['shipping_code']) && in_array($order_info['order_state'], array(ORDER_STATE_SEND, ORDER_STATE_SUCCESS));
                break;

            //放入回收站
            case 'delete':
                $state = in_array($order_info['order_state'], array(ORDER_STATE_CANCEL, ORDER_STATE_SUCCESS)) && $order_info['delete_state'] == 0;
                break;

            //永久删除、从回收站还原
            case 'drop':
            case 'restore':
                $state = in_array($order_info['order_state'], array(ORDER_STATE_CANCEL, ORDER_STATE_SUCCESS)) && $order_info['delete_state'] == 1;
                break;

            //分享
            case 'share':
                $state = true;
                break;
        }
        return $state;
    }

    /**
     * 联查订单表订单商品表
     *
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     * @return array
     */
    public function getOrderAndOrderGoodsList($condition, $field = '*', $page = 0, $order = 'rec_id desc') {
        if($page){
            $list = db('ordergoods')->alias('order_goods')->where($condition)->field($field)->join('__ORDER__ order','order_goods.order_id=order.order_id','LEFT')->paginate($page,false,['query' => request()->param()]);
            $this->page_info = $list;
            return $list->items();
        }else{
            $list = db('ordergoods')->alias('order_goods')->where($condition)->field($field)->join('__ORDER__ order','order_goods.order_id=order.order_id','LEFT')->select();
            return $list;
        }
//        return db('order_goods,order')->join('inner')->on('order_goods.order_id=order.order_id')->where($condition)->field($field)->page($page)->order($order)->select();
    }

    /**
     * 订单销售记录 订单状态为20、30、40时
     * @param unknown $condition
     * @param string $field
     * @param number $page
     * @param string $order
     */
    public function getOrderAndOrderGoodsSalesRecordList($condition, $field = "*", $page = 0, $order = 'rec_id desc') {
        $condition['order_state'] = array('in', array(ORDER_STATE_PAY, ORDER_STATE_SEND, ORDER_STATE_SUCCESS));
        return $this->getOrderAndOrderGoodsList($condition, $field, $page, $order);
    }

}