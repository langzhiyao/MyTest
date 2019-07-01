<?php

namespace app\common\model;

use think\Model;

class Orderinviter extends Model {

    public function giveMoney($order_id) {
        $orderinviter_list = db('orderinviter')->where('orderinviter_order_id', $order_id)->select();
        if ($orderinviter_list) {
            $model_pd = Model('predeposit');
            foreach ($orderinviter_list as $val) {
                try {
                    $model_pd->startTrans();
                    $data = array();
                    $data['member_id'] = $val['orderinviter_member_id'];
                    $data['member_name'] = $val['orderinviter_member_name'];
                    $data['amount'] = $val['orderinviter_money'];
                    $data['order_sn'] = $val['orderinviter_order_sn'];
                    $data['lg_desc'] = $val['orderinviter_remark'];
                    $model_pd->changePd('order_inviter', $data);
                    db('orderinviter')->where('orderinviter_id', $val['orderinviter_id'])->update(['orderinviter_valid' => 1]);
                    $model_pd->commit();
                } catch (Exception $e) {
                    $model_pd->rollback();
                }
            }
        }
    }

}
