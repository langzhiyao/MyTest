<?php

namespace app\common\model;

use think\Model;

class Cron extends Model {
    

    /**
     * 取单条任务信息
     * @param array $condition
     */
    public function getCronInfo($condition = array()) {
        return db('cron')->where($condition)->find();
    }
    /**
     * 任务队列列表
     * @param array $condition
     * @param number $limit
     * @return array
     */
    public function getCronList($condition, $limit = 100) {
        return db('cron')->where($condition)->limit($limit)->select();
    }
    
    /**
     * 保存任务队列
     * 
     * @param unknown $insert
     * @return array
     */
    public function addCronAll($insert) {
        return db('cron')->insertAll($insert);
    }
    
    /**
     * 保存任务队列
     * 
     * @param array $insert
     * @return boolean
     */
    public function addCron($insert) {
        return db('cron')->insert($insert);
    }
    
    /**
     * 删除任务队列
     * 
     * @param array $condition
     * @return array
     */
    public function delCron($condition) {
        return db('cron')->where($condition)->delete();
    }
    
}

?>
