<?php

namespace app\common\model;

use think\Model;

class Sellerlog extends Model {

    public $page_info;
    /**
     * 读取列表 
     * @param array $condition
     *
     */
    public function getSellerLogList($condition, $page = '', $order = '', $field = '*') {
        if($page){
            $result = db('sellerlog')->field($field)->where($condition)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        }else{
            $result = db('sellerlog')->field($field)->where($condition)->order($order)->select();
            return $result;
        }
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getSellerLogInfo($condition) {
        $result = db('sellerlog')->where($condition)->find();
        return $result;
    }

    /*
     * 增加 
     * @param array $param
     * @return bool
     */

    public function addSellerLog($param) {
        return db('sellerlog')->insert($param);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     */

    public function delSellerLog($condition) {
        return db('sellerlog')->where($condition)->delete();
    }

}
