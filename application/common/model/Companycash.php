<?php

namespace app\common\model;

use think\Model;

class Companycash extends Model {

    public $page_info;

    /**
     * 取得提现列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getCpdCashList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        $pdcash_list_paginate = db('companycash')->alias('s')
                                ->join('__ADMIN__ ad','ad.admin_id=s.pdc_member_id','LEFT')
                                ->join('__COMPANY__ co','ad.admin_company_id=co.o_id','LEFT')
                                ->where($condition)
                                ->field('s.*,co.o_name,o_area')
                                ->order($order)
                                ->paginate($pagesize,false,['query' => request()->param()]);
        $this->page_info = $pdcash_list_paginate;
        return $pdcash_list_paginate->items();
    }

    /**
     * 添加提现记录
     * @param array $data
     */
    public function addCpdCash($data) {
        return db('companycash')->insert($data);
    }

    /**
     * 编辑提现记录
     * @param unknown $data
     * @param unknown $condition
     */
    public function editCpdCash($data, $condition = array()) {
        return db('companycash')->where($condition)->update($data);
    }

    /**
     * 取得单条提现信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getCpdCashInfo($condition = array(), $fields = '*') {
        return db('companycash')->where($condition)->field($fields)->find();
    }

    /**
     * 删除提现记录
     * @param unknown $condition
     */
    public function delCpdCash($condition) {
        return db('companycash')->where($condition)->delete();
    }

    //提现总额
    public function getAllCount(){
        $sql = "select sum(pdc_amount) as num from x_companycash";
        $result = $this->query($sql);
        return $result;
    }

}
