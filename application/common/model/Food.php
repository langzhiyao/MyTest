<?php

namespace app\common\model;

use think\Model;

class Food extends Model {

    /**
     * 查询所有系统文章
     */
    public function getList($where) {
        return db('food')->where($where)->order('id desc')->select();
    }



    /**
     * 查询本周、上周、下周 食谱
     */
    public function getnowweek($where,$day='') {
        $thisweek_start=mktime(0,0,0,date('m'),date('d')-date('w'+1),date('Y'));
        $thisweek_end=mktime(23,59,59,date('m'),date('d')-date('w')+7,date('Y'));
        $sql = "SELECT * FROM x_food WHERE $where and ( unix_timestamp(weekday) BETWEEN $thisweek_start and $thisweek_end )";

        if($day && $day=="last"){
            $lastweek_start=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
            $lastweek_end=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
            $sql = "SELECT * FROM x_food WHERE $where and ( unix_timestamp(weekday) BETWEEN $lastweek_start and $lastweek_end )";
        }

        if($day && $day=="next"){
            $lastweek_start=mktime(0,0,0,date('m'),date('d')-date('w')+1+7,date('Y'));
            $lastweek_end=mktime(23,59,59,date('m'),date('d')-date('w')+7+7,date('Y'));
            $sql = "SELECT * FROM x_food WHERE $where and ( unix_timestamp(weekday) BETWEEN $lastweek_start and $lastweek_end )";
        }

        $result = $this->query($sql);
        return $result;
    }
    /**
     * 根据编号查询一条
     * 
     * @param unknown_type $id
     */
    public function getOneById($id) {
        return db('food')->where('doc_id',$id)->find();
    }

    /**
     * 根据标识码查询一条
     * 
     * @param unknown_type $id
     */
    public function getOneByCode($code) {
        return db('food')->where('doc_code',$code)->find();
    }

    /**
     * 更新
     * 
     * @param unknown_type $param
     */
    public function update1($param) {
        return db('food')->where('doc_id',$param['doc_id'])->update($param);
    }
}

?>
