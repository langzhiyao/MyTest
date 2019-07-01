<?php

/**
 * 店铺入住模型
 */

namespace app\common\model;

use think\Model;

class Storejoinin extends Model {
    public $page_info;

    /**
     * 读取列表 
     * @param array $condition
     *
     */
    public function getList($condition, $page = '', $order = '', $field = '*') {
        if($page){
            $result = db('storejoinin')->field($field)->where($condition)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        }else{
            $result = db('storejoinin')->field($field)->where($condition)->order($order)->select();
            return $result;
        }
    }

    /**
     * 店铺入住数量
     * @param unknown $condition
     */
    public function getStoreJoininCount($condition) {
        return db('storejoinin')->where($condition)->count();
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getOne($condition) {
        $result = db('storejoinin')->where($condition)->find();
        return $result;
    }

    /*
     *  判断是否存在 
     *  @param array $condition
     *
     */

    public function isExist($condition) {
        $result = db('storejoinin')->getOne($condition);
        if (empty($result)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /*
     * 增加 
     * @param array $param
     * @return bool
     */

    public function save1($param) {
        return db('storejoinin')->insert($param);
    }

    /*
     * 增加 
     * @param array $param
     * @return bool
     */

    public function saveAll1($param) {
        return db('storejoinin')->insertAll($param);
    }

    /*
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     */

    public function modify($update, $condition) {
        return db('storejoinin')->where($condition)->update($update);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     */

    public function drop($condition) {
        return db('storejoinin')->where($condition)->delete();
    }

    /**
     * 编辑
     * @param array $condition
     * @param array $update
     * @return bool
     */
    public function editStoreJoinin($condition, $update) {
        return db('storejoinin')->where($condition)->update($update);
    }

}
