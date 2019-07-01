<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/18
 * Time: 10:17
 */
namespace app\common\model;

use think\Model;

class Membercollect extends Model {
    /**
     * 收藏列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param number $page
     * @param string $limit
     * @return array
     */
    public function getMembercollectList($condition, $field = '*', $page = 0, $order = 'id desc', $limit = '') {
        if($limit) {
            return db('membercollect')->where($condition)->field($field)->order($order)->page($page)->limit($limit)->select();
        }else{
            $res= db('membercollect')->where($condition)->field($field)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info=$res;
            return $res->items();
        }
    }
    /**
     * 添加收藏
     * @param array $insert
     * @return boolean
     */
    public function addMembercollect($insert) {
        return db('membercollect')->insert($insert);
    }
    /**
     * 取单个收藏内容
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getMembercollectInfo($condition, $field = '*') {
        return db('membercollect')->field($field)->where($condition)->find();
    }
    /**
     * 编辑收藏
     * @param array $condition
     * @param array $update
     * @return boolean
     */
    public function editMembercollect($condition, $update) {
        return db('membercollect')->where($condition)->update($update);
    }

    public function getCollect($condition){
        return db('membercollect')->where($condition)->order('time desc')->select();
    }

}