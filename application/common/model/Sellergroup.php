<?php

namespace app\common\model;

use think\Model;

class Sellergroup extends Model {

    /**
     * 读取列表 
     * @param array $condition
     *
     */
    public function getSellerGroupList($condition, $page = '', $order = '', $field = '*') {
        $result = db('sellergroup')->field($field)->where($condition)->page($page)->order($order)->select();
        return $result;
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getSellerGroupInfo($condition) {
        $result = db('sellergroup')->where($condition)->find();
        return $result;
    }

    /*
     *  判断是否存在 
     *  @param array $condition
     *
     */

    public function isSellerGroupExist($condition) {
        $result = db('sellergroup')->getOne($condition);
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

    public function addSellerGroup($param) {
        return db('sellergroup')->insertGetId($param);
    }

    /*
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     */

    public function editSellerGroup($update, $condition) {
        return db('sellergroup')->where($condition)->update($update);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     */

    public function delSellerGroup($condition) {
        return db('sellergroup')->where($condition)->delete();
    }

}
