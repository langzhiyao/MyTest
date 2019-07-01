<?php

namespace app\common\model;

use think\Model;

class Storereopen extends Model {
    public $page_info;
    /**
     * 取得列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $order
     */
    public function getStoreReopenList($condition = array(), $pagesize = '', $order = 're_id desc') {
        if($pagesize){
            $result =  db('storereopen')->where($condition)->order($order)->paginate($pagesize,false,['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        }else{
            return db('storereopen')->where($condition)->order($order)->page($pagesize)->select();
        }
        
    }

    /**
     * 增加新记录
     * @param unknown $data
     * @return
     */
    public function addStoreReopen($data) {
        return db('storereopen')->insert($data);
    }

    /**
     * 取单条信息
     * @param unknown $condition
     */
    public function getStoreReopenInfo($condition) {
        return db('storereopen')->where($condition)->find();
    }

    /**
     * 更新记录
     * @param unknown $condition
     * @param unknown $data
     */
    public function editStoreReopen($data, $condition) {
        return db('storereopen')->where($condition)->update($data);
    }

    /**
     * 取得数量
     * @param unknown $condition
     */
    public function getStoreReopenCount($condition) {
        return db('storereopen')->where($condition)->count();
    }

    /**
     * 删除记录
     * @param unknown $condition
     */
    public function delStoreReopen($condition) {
        return db('storereopen')->where($condition)->delete();
    }

}

?>
