<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/18
 * Time: 10:17
 */
namespace app\common\model;

use think\Model;

class Teachhistory extends Model {
    /**
     * 课件观看历史列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param number $page
     * @param string $limit
     * @return array
     */
    public function getTeachhistoryList($condition, $field = '*', $page = 0, $order = 't_id desc', $limit = '') {
        if($limit) {
            return db('teachhistory')->where($condition)->field($field)->order($order)->page($page)->limit($limit)->select();
        }else{
            $res= db('teachhistory')->where($condition)->field($field)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info=$res;
            return $res->items();
        }
    }

    public function getHistory($condition){
        return db('teachhistory')->where($condition)->order('t_maketime desc')->select();
    }
    /**
     * 添加课件观看历史
     * @param array $insert
     * @return boolean
     */
    public function addTeachhistory($insert) {
        return db('teachhistory')->insert($insert);
    }
    /**
     * 取单个课件观看历史
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getTeachhistoryInfo($condition, $field = '*') {
        return db('teachhistory')->field($field)->where($condition)->find();
    }
    /**
     * 编辑课件观看历史
     * @param array $condition
     * @param array $update
     * @return boolean
     */
    public function editTeachhistory($condition, $update) {
        return db('teachhistory')->where($condition)->update($update);
    }

    //单个删除
    public function delTeachhistory($where) {
        //return db('teachhistory')->where($where)->delete();
        return db('teachhistory')->where($condition)->update($update);
    }

}