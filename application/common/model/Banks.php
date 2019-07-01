<?php

namespace app\common\model;

use think\Model;

class Banks extends Model {

    /**
     * 获取所有银行卡信息
     * @创建时间   2018-12-04T17:40:06+0800
     * @param  [type]                   $condition [description]
     * @return [type]                              [description]
     */
    public function getAllBanks($condition){
        return db('banks')->where($condition)->order('updatetime desc')->select();
    }

    /**
     * 取数量
     * @param unknown $condition
     */
    public function getBanksCount($condition = array()) {
        return db('banks')->where($condition)->count();
    }

    /**
     * 新增银行卡
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addBanks($param) {
        return db('banks')->insertGetId($param);
    }

    /**
     * 取单个银行卡信息
     *
     * @param int $bank_id 银行卡ID
     * @return array 数组类型的返回结果
     */
    public function getOneBanks($id) {
        if (intval($id) > 0) {
            $result = db('banks')->where('bank_id',intval($id))->find();
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 根据卡号获取银行卡信息
     * @创建时间   2018-12-04T18:29:55+0800
     * @param  [type]                   $card [description]
     * @return [type]                         [description]
     */
    public function getOneBanksByCard($condition,$order='bank_id ASC') {
        return db('banks')->where($condition)->order($order)->find();
    }

    /**
     * 更新银行卡信息
     *
     * @param array $param 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function editBanks($update, $condition) {
        return db('banks')->where($condition)->update($update);
    }

    /**
     * 修改单个字段
     * @创建时间  2018-12-04T19:05:19+0800
     * @param [type]                   $member_id [description]
     * @param [type]                   $key       [description]
     * @param [type]                   $value     [description]
     */
    public function SetBanks($condition,$key,$value){
        return db('banks')->where($condition)->setField($key, $value);   
    }


    /**
     * 删除银行卡
     *
     * @param int $id 记录ID
     * @return bool 布尔类型的返回结果
     */
    public function delBanks($condition) {
        return db('banks')->where($condition)->delete();
    }

}

?>
