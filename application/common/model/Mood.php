<?php

namespace app\common\model;

use think\Model;

class Mood extends Model {

    /**
     * 查询所有系统文章
     */
    public function getList($condition, $field = '*', $page = 0, $order = 'id desc', $limit = '') {
        if($limit) {
            return db('mood')->where($condition)->field($field)->order($order)->page($page)->limit($limit)->select();
        }else{
            $res= db('mood')->where($condition)->field($field)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info=$res;
            return $res->items();
        }
    }
    /**
     * 根据编号查询一条
     * 
     * @param unknown_type $id
     */
    public function getOneById($id) {
        return db('mood')->where('id',$id)->find();
    }

    /**
     * 添加分子公司
     * @param array $insert
     * @return boolean
     */
    public function addMood($insert) {
        return db('mood')->insert($insert);
    }

    /**
     * 编辑分子公司
     * @param array $condition
     * @param array $update
     * @return boolean
     */
    public function editMood($condition, $update) {
        return db('mood')->where($condition)->update($update);
    }
    //指定日期几天前
    function getnum($time)
    {
        //获取今天凌晨的时间戳
        $day = strtotime(date('Y-m-d',time()));
        //获取昨天凌晨的时间戳
        $pday = strtotime(date('Y-m-d',strtotime('-1 day')));
        //获取现在的时间戳
        $nowtime = time();

        $tc = $nowtime-$time;
        if($time<$pday){
            $str = date('Y-m-d H:i:s',$time);
        }elseif($time<$day && $time>$pday){
            $str = "昨天";
        }elseif($tc>60*60){
            $str = floor($tc/(60*60))."小时前";
        }elseif($tc>60){
            $str = floor($tc/60)."分钟前";
        }else{
            $str = "刚刚";
        }
        return $str;
    }
}

?>
