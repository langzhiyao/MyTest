<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/10
 * Time: 16:57
 */

namespace app\common\model;


use think\Model;

class Goodsfcode extends Model
{
    /**
     * 插入数据
     *
     * @param unknown $insert
     * @return boolean
     */
    public function addGoodsFCodeAll($insert) {
        return $this->insertAll($insert);
    }
    /**
     * 取得F码列表
     *
     * @param array $condition
     * @param string $order
     */
    public function getGoodsFCodeList($condition, $order = 'fc_state asc,fc_id asc') {
        return $this->where($condition)->order($order)->select();
    }

    /**
     * 删除F码
     */
    public function delGoodsFCode($condition) {
        return $this->where($condition)->delete();
    }

    /**
     * 取得F码
     */
    public function getGoodsFCode($condition) {
        return $this->where($condition)->find();
    }

    /**
     * 更新F码
     */
    public function editGoodsFCode($data, $condition) {
        return $this->where($condition)->update($data);
    }
}