<?php

namespace app\common\model;


use think\Model;

class Rcblog extends Model
{
    public $page_info;

    public function getRechargeCardBalanceLogList($condition, $page, $order)
    {
        $res =db('rcblog')->where($condition)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info=$res;
        return $res->items();
    }
}