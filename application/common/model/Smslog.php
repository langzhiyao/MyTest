<?php

namespace app\common\model;

use think\Model;

class Smslog extends Model {

    public $page_info;

    /**
     * 增加短信记录
     *
     * @param
     * @return int
     */
    public function addSms($log_array) {
        $log_id = db('smslog')->insert($log_array);
        return $log_id;
    }

    /**
     * 查询单条记录
     *
     * @param
     * @return array
     */
    public function getSmsInfo($condition) {
        if (empty($condition)) {
            return false;
        }
        $result = db('smslog')->where($condition)->order('log_id desc')->find();
        return $result;
    }

    /**
     * 查询记录
     *
     * @param
     * @return array
     */
    public function getSmsList($condition = array(), $page = '', $limit = '', $order = 'log_id desc') {
        if ($page) {
            $result = db('smslog')->where($condition)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info = $result;
            $result = $result->items();
        } else {
            $result = db('smslog')->where($condition)->limit($limit)->order($order)->select();
        }

        return $result;
    }

    /*
     * 获取数据条数
     */

    public function getSmsCount($condition) {
        return db('smslog')->where($condition)->count();
    }
}

?>
