<?php

namespace app\common\model;

use think\Model;

class Predeposit extends Model {

    public $page_info;

    /**
     * 生成充值编号
     * @return string
     */
    public function makeSn() {
        return mt_rand(10, 99)
                . sprintf('%010d', time() - 946656000)
                . sprintf('%03d', (float) microtime() * 1000)
                . sprintf('%03d', (int) session('member_id') % 1000);
    }

    public function addRechargeCard($sn,$session) {
        $memberId = $session['member_id'];
        $memberName = $session['member_name'];

        if ($memberId < 1 || !$memberName) {
            return array('message'=>'当前登录状态为未登录，不能使用充值卡');
        }

        $rechargecard_model = Model('rechargecard');

        $card = $rechargecard_model->getRechargeCardBySN($sn);

        if (empty($card) || $card['state'] != 0 || $card['member_id'] != 0) {
            return array('message'=>'充值卡不存在或已被使用');
        }

        $card['member_id'] = $memberId;
        $card['member_name'] = $memberName;

        try {
            $this->startTrans();

            $rechargecard_model->setRechargeCardUsedById($card['id'], $memberId, $memberName);

            $card['amount'] = $card['denomination'];
            $this->changeRcb('recharge', $card);

            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * 取得充值列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdRechargeList($condition = array(), $pagesize = '', $fields = '*', $order = '') {
        if ($pagesize) {
            $result = db('pdrecharge')->where($condition)->field($fields)->order($order)->paginate($pagesize,false,['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            return db('pdrecharge')->where($condition)->field($fields)->order($order)->select();
        }
    }

    /**
     * 添加充值记录
     * @param array $data
     */
    public function addPdRecharge($data) {
        return db('pdrecharge')->insert($data);
    }

    /**
     * 编辑
     * @param unknown $data
     * @param unknown $condition
     */
    public function editPdRecharge($data, $condition = array()) {
        return db('pdrecharge')->where($condition)->update($data);
    }

    /**
     * 取得单条充值信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getPdRechargeInfo($condition = array(), $fields = '*') {
        return db('pdrecharge')->where($condition)->field($fields)->find();
    }

    /**
     * 取充值信息总数
     * @param unknown $condition
     */
    public function getPdRechargeCount($condition = array()) {
        return db('pdrecharge')->where($condition)->count();
    }

    /**
     * 取提现单信息总数
     * @param unknown $condition
     */
    public function getPdCashCount($condition = array()) {
        return db('pdcash')->where($condition)->count();
    }

    /**
     * 取日志总数
     * @param unknown $condition
     */
    public function getPdLogCount($condition = array()) {
        return db('pdlog')->where($condition)->count();
    }

    /**
     * 取得预存款变更日志列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdLogList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
//        return db('pdlog')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
        if ($pagesize) {
            $pdlog_list_paginate = db('pdlog')->where($condition)->field($fields)->order($order)->paginate($pagesize,false,['query' => request()->param()]);
            $this->page_info = $pdlog_list_paginate;
            return $pdlog_list_paginate->items();
        }else{
            $pdlog_list_paginate = db('pdlog')->where($condition)->field($fields)->order($order)->select();
            return $pdlog_list_paginate;
        }

    }

    /**
     * 变更充值卡余额
     *
     * @param string $type
     * @param array  $data
     *
     * @return mixed
     * @throws Exception
     */
    public function changeRcb($type, $data = array()) {
        $amount = (float) $data['amount'];
        if ($amount < .01) {
            exception('参数错误');
        }

        $available = $freeze = 0;
        $desc = null;

        switch ($type) {
            case 'order_pay':
                $available = -$amount;
                $desc = '下单，使用充值卡余额，订单号: ' . $data['order_sn'];
                break;

            case 'order_freeze':
                $available = -$amount;
                $freeze = $amount;
                $desc = '下单，冻结充值卡余额，订单号: ' . $data['order_sn'];
                break;

            case 'order_cancel':
                $available = $amount;
                $freeze = -$amount;
                $desc = '取消订单，解冻充值卡余额，订单号: ' . $data['order_sn'];
                break;

            case 'order_comb_pay':
                $freeze = -$amount;
                $desc = '下单，扣除被冻结的充值卡余额，订单号: ' . $data['order_sn'];
                break;

            case 'recharge':
                $available = $amount;
                $desc = '平台充值卡充值，充值卡号: ' . $data['sn'];
                break;

            case 'refund':
                $available = $amount;
                $desc = '确认退款，订单号: ' . $data['order_sn'];
                break;

            case 'vr_refund':
                $available = $amount;
                $desc = '虚拟兑码退款成功，订单号: ' . $data['order_sn'];
                break;

            default:
                exception('参数错误');
        }

        $update = array();
        if ($available) {
            $update['available_rc_balance'] = array('exp', "available_rc_balance + ({$available})");
        }
        if ($freeze) {
            $update['freeze_rc_balance'] = array('exp', "freeze_rc_balance + ({$freeze})");
        }

        if (!$update) {
            exception('参数错误');
        }

        // 更新会员
        $updateSuccess = Model('member')->editMember(array(
            'member_id' => $data['member_id'],
                ), $update);

        if (!$updateSuccess) {
            exception('操作失败');
        }

        // 添加日志
        $log = array(
            'member_id' => $data['member_id'],
            'member_name' => $data['member_name'],
            'type' => $type,
            'add_time' => TIMESTAMP,
            'available_amount' => $available,
            'freeze_amount' => $freeze,
            'description' => $desc,
        );

        $insertSuccess = db('rcblog')->insert($log);
        if (!$insertSuccess) {
            exception('操作失败');
        }

        $msg = array(
            'code' => 'recharge_card_balance_change',
            'member_id' => $data['member_id'],
            'param' => array(
                'time' => date('Y-m-d H:i:s', TIMESTAMP),
                'url' => url('Home/Predeposit/rcb_log_list'),
                'available_amount' => dsPriceFormat($available),
                'freeze_amount' => dsPriceFormat($freeze),
                'description' => $desc,
            ),
        );

        // 发送买家消息
        \mall\queue\QueueClient::push('sendMemberMsg', $msg);
        return $insertSuccess;
    }

    /**
     * 变更预存款
     * @param unknown $change_type
     * @param unknown $data
     * @throws Exception
     * @return unknown
     */
    public function changePd($change_type, $data = array()) {
        $data_log = array();
        $data_pd = array();
        $data_msg = array();

        $data_log['lg_member_id'] = $data['member_id'];
        $data_log['lg_member_name'] = $data['member_name'];
        $data_log['lg_add_time'] = TIMESTAMP;
        $data_log['lg_type'] = $change_type;

        $data_msg['time'] = date('Y-m-d H:i:s');
        $data_msg['pd_url'] = url('home/predeposit/pd_log_list');
        switch ($change_type) {
            case 'order_pay':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '下单，支付预存款，订单号: ' . $data['order_sn'];
                $data_pd['available_predeposit'] = array('exp', 'available_predeposit-' . $data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'order_freeze':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_freeze_amount'] = $data['amount'];
                $data_log['lg_desc'] = '下单，冻结预存款，订单号: ' . $data['order_sn'];
                $data_pd['freeze_predeposit'] = array('exp', 'freeze_predeposit+' . $data['amount']);
                $data_pd['available_predeposit'] = array('exp', 'available_predeposit-' . $data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = $data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'order_cancel':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '取消订单，解冻预存款，订单号: ' . $data['order_sn'];
                $data_pd['freeze_predeposit'] = array('exp', 'freeze_predeposit-' . $data['amount']);
                $data_pd['available_predeposit'] = array('exp', 'available_predeposit+' . $data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'order_comb_pay':
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '下单，支付被冻结的预存款，订单号: ' . $data['order_sn'];
                $data_pd['freeze_predeposit'] = array('exp', 'freeze_predeposit-' . $data['amount']);

                $data_msg['av_amount'] = 0;
                $data_msg['freeze_amount'] = $data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'recharge':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '充值，充值单号: ' . $data['pdr_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = array('exp', 'available_predeposit+' . $data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;

            case 'refund':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '确认退款，订单号: ' . $data['order_sn'];
                $data_pd['available_predeposit'] = array('exp', 'available_predeposit+' . $data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'vr_refund':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '虚拟兑码退款成功，订单号: ' . $data['order_sn'];
                $data_pd['available_predeposit'] = array('exp', 'available_predeposit+' . $data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'cash_apply':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_freeze_amount'] = $data['amount'];
                $data_log['lg_desc'] = '申请提现，冻结预存款，提现单号: ' . $data['order_sn'];
                $data_pd['available_predeposit'] = array('exp', 'available_predeposit-' . $data['amount']);
                $data_pd['freeze_predeposit'] = array('exp', 'freeze_predeposit+' . $data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = $data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'cash_pay':
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '提现成功，提现单号: ' . $data['order_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['freeze_predeposit'] = array('exp', 'freeze_predeposit-' . $data['amount']);

                $data_msg['av_amount'] = 0;
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'cash_del':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '取消提现申请，解冻预存款，提现单号: ' . $data['order_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = array('exp', 'available_predeposit+' . $data['amount']);
                $data_pd['freeze_predeposit'] = array('exp', 'freeze_predeposit-' . $data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            //v 3- b 1 3
            case 'sys_add_money':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '管理员调节预存款【增加】，充值单号: ' . $data['pdr_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = array('exp', 'available_predeposit+' . $data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'sys_del_money':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '管理员调节预存款【减少】，充值单号: ' . $data['pdr_sn'];
                $data_pd['available_predeposit'] = array('exp', 'available_predeposit-' . $data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'sys_freeze_money':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_freeze_amount'] = $data['amount'];
                $data_log['lg_desc'] = '管理员调节预存款【冻结】，充值单号: ' . $data['pdr_sn'];
                $data_pd['available_predeposit'] = array('exp', 'available_predeposit-' . $data['amount']);
                $data_pd['freeze_predeposit'] = array('exp', 'freeze_predeposit+' . $data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = $data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'sys_unfreeze_money':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '管理员调节预存款【解冻】，充值单号: ' . $data['pdr_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = array('exp', 'available_predeposit+' . $data['amount']);
                $data_pd['freeze_predeposit'] = array('exp', 'freeze_predeposit-' . $data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'order_inviter':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = $data['lg_desc'];
                $data_pd['available_predeposit'] = array('exp', 'available_predeposit+' . $data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;

            //end

            default:
                exception('参数错误');
                break;
        }

        $update = Model('member')->editMember(array('member_id' => $data['member_id']), $data_pd);

        if (!$update) {
            exception('操作失败');
        }
        $insert = db('pdlog')->insert($data_log);
        if (!$insert) {
            exception('操作失败');
        }

        // 支付成功发送买家消息
        $param = array();
        $param['code'] = 'predeposit_change';
        $param['member_id'] = $data['member_id'];
        $data_msg['av_amount'] = dsPriceFormat($data_msg['av_amount']);
        $data_msg['freeze_amount'] = dsPriceFormat($data_msg['freeze_amount']);
        $param['param'] = $data_msg;
        \mall\queue\QueueClient::push('sendMemberMsg', $param);
        return $insert;
    }

    /**
     * 删除充值记录
     * @param unknown $condition
     */
    public function delPdRecharge($condition) {
        return db('pdrecharge')->where($condition)->delete();
    }

    /**
     * 取得提现列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdCashList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
//        db('pdcash')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
        $pdcash_list_paginate = db('pdcash')->where($condition)->field($fields)->order($order)->paginate($pagesize,false,['query' => request()->param()]);
        $this->page_info = $pdcash_list_paginate;
        return $pdcash_list_paginate->items();
    }

    /**
     * 添加提现记录
     * @param array $data
     */
    public function addPdCash($data) {
        return db('pdcash')->insert($data);
    }

    /**
     * 编辑提现记录
     * @param unknown $data
     * @param unknown $condition
     */
    public function editPdCash($data, $condition = array()) {
        return db('pdcash')->where($condition)->update($data);
    }

    /**
     * 取得单条提现信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getPdCashInfo($condition = array(), $fields = '*') {
        return db('pdcash')->where($condition)->field($fields)->find();
    }

    /**
     * 删除提现记录
     * @param unknown $condition
     */
    public function delPdCash($condition) {
        return db('pdcash')->where($condition)->delete();
    }

    //提现总额
    public function getAllCount(){
        $sql = "select sum(pdc_amount) as num from x_pdcash";
        $result = $this->query($sql);
        return $result;
    }

}
