<?php

namespace app\common\model;

use think\Model;

class Adminpdlog extends Model {

    public $page_info;
    /**
     * 增加
     *
     * @param
     * @return int
     */
    public function addLog($type_array) {
        $log_id = db('adminpdlog')->insertGetId($type_array);
        return $log_id;
    }

    /**
     * 获取所有
     */
    public function getAllPdlog($condition = array(), $page = '', $orderby = 'lg_id desc'){
        if ($page) {
            $result = db('adminpdlog')->alias('s')
                ->join('__MEMBER__ me','me.member_id=s.lg_member_id','LEFT')
                ->field('s.*,me.member_identity')
                ->where($condition)->order($orderby)->paginate($page, false, ['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            return db('adminpdlog')->where($condition)->order($orderby)->select();
        }
    }

    //分成总额 入账总额 出账总额
    public function getAllCount(){
        //出账总额
        $sql = "select sum(lg_av_amount) as num from x_adminpdlog where lg_type='cash_pay'";
        $c_result = $this->query($sql);
        //入账
        $f_sql = "select sum(lg_av_amount) as num from x_adminpdlog where lg_type='share_admin_payment'";//分成总额
        $q_sql = "select sum(lg_av_amount) as num from x_adminpdlog where lg_type='order_pay'";//用户支付总额
        $f_result = $this->query($f_sql);
        $q_result = $this->query($q_sql);
        if($c_result[0]['num']){
            $result['outCount'] = $c_result[0]['num'];
        }
        if($f_result[0]['num']){
            $result['fenCount'] = $f_result[0]['num'];
            $result['totalCount'] = $f_result[0]['num'];
        }
        if($q_result[0]['num']){
            $result['payCount'] = $q_result[0]['num'];
            $result['totalCount'] = $q_result[0]['num'];
        }
        if($f_result[0]['num'] && $q_result[0]['num']){
            $result['totalCount'] = $f_result[0]['num'] + $q_result[0]['num'];
        }
        return $result;
    }

}