<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/18
 * Time: 10:17
 */
namespace app\common\model;

use think\Model;

class Schooldesc extends Model {
    /**
     * 分子公司列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param number $page
     * @param string $limit
     * @return array
     */
    public function getDescList($condition, $field = '*', $page = 0, $order = 'o_id desc', $limit = '') {
        if($limit) {
            return db('schooldesc')->where($condition)->field($field)->order($order)->page($page)->limit($limit)->select();
        }else{
            $res= db('schooldesc')->where($condition)->field($field)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info=$res;
            return $res->items();
        }
    }
    /**
     * 添加学校简介
     * @param array $insert
     * @return boolean
     */
    public function addDesc($insert) {
        return db('schooldesc')->insert($insert);
    }
    /**
     * 取单个分子公司内容
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getDescInfo($condition, $field = '*') {
        return db('schooldesc')->field($field)->where($condition)->find();
    }
    /**
     * 编辑分子公司
     * @param array $condition
     * @param array $update
     * @return boolean
     */
    public function editDesc($condition, $update) {
        return db('schooldesc')->where($condition)->update($update);
    }

}