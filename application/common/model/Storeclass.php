<?php


namespace app\common\model;

use think\Model;

class Storeclass extends Model {
    
    public $page_info;
    /**
     * 取店铺类别列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $order
     */
    public function getStoreClassList($condition = array(), $pagesize = '', $limit = '', $order = 'sc_sort asc,sc_id asc') {
        
        if($pagesize){
            $list = db('storeclass')->where($condition)->order($order)->paginate($pagesize,false,['query' => request()->param()]);
            $this->page_info = $list;
            return $list->items();
        }else{
            return db('storeclass')->where($condition)->order($order)->limit($limit)->select();
        }
        
    }

    /**
     * 取得单条信息
     * @param unknown $condition
     */
    public function getStoreClassInfo($condition = array()) {
        return db('storeclass')->where($condition)->find();
    }

    /**
     * 删除类别
     * @param unknown $condition
     */
    public function delStoreClass($condition = array()) {
        return db('storeclass')->where($condition)->delete();
    }

    /**
     * 增加店铺分类
     * @param unknown $data
     * @return boolean
     */
    public function addStoreClass($data) {
        return db('storeclass')->insert($data);
    }

    /**
     * 更新分类
     * @param unknown $data
     * @param unknown $condition
     */
    public function editStoreClass($data = array(),$condition = array()) {
        return db('storeclass')->where($condition)->update($data);
    }
    
}
?>
