<?php

namespace app\common\model;

use think\Model;

class Arrangement extends Model {

    /**
     * 查询所有系统文章
     */
    public function getList($where) {
        return db('arrangement')->where($where)->order('id desc')->select();
    }

    /**
     * 新增学校类型
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function arrangement_add($param) {
        return db('arrangement')->insertGetId($param);
    }

    /**
     * 查询本周课程
     */
    public function getnowweek($where,$day='') {
        $thisweek_start=mktime(0,0,0,date('m'),date('d')-date('w'+1),date('Y'));
        $thisweek_end=mktime(23,59,59,date('m'),date('d')-date('w')+7,date('Y'));
        $sql = "SELECT * FROM x_arrangement WHERE unix_timestamp(date) BETWEEN $thisweek_start and $thisweek_end";

        if($day && $day=="last"){
            $lastweek_start=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
            $lastweek_end=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
            $sql = "SELECT * FROM x_arrangement $where and ( WHERE unix_timestamp(date) BETWEEN $lastweek_start and $lastweek_end )";
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
        return db('arrangement')->where('id',$id)->find();
    }

    public function getOneInfo($condition) {
        return db('arrangement')->where($condition)->find();
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
    public function update1($param,$id) {
        return db('arrangement')->where('id',$id)->update($param);
    }
}

?>
