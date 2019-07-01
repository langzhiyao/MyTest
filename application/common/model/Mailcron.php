<?php

namespace app\common\model;


use think\Model;

class Mailcron extends Model
{
    /**
     * 新增商家消息任务计划
     * @param unknown $insert
     */
    public function addMailCron($insert) {
        return db('mailcron')->insert($insert);
    }
    /**
     * 查看商家消息任务计划
     *
     * @param unknown $condition
     * @param number $limit
     */
    public function getMailCronList($condition, $limit = 0, $order = 'mail_id asc') {
        return db('mailcron')->where($condition)->limit($limit)->order($order)->select();
    }

    /**
     * 删除商家消息任务计划
     * @param unknown $condition
     */
    public function delMailCron($condition) {
        return db('mailcron')->where($condition)->delete();
    }
}