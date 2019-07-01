<?php

namespace app\common\model;

use think\Model;

class Storecost extends Model {
    public  $page_info;
    /**
     * 读取列表 
     * @param array $condition
     *
     */
    public function getStoreCostList($condition, $page = '', $order = '', $field = '*') {
        if($page){
            $result = db('storecost')->field($field)->where($condition)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        }else{
            $result = db('storecost')->field($field)->where($condition)->order($order)->select();
            return $result;
        }
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getStoreCostInfo($condition, $fields = '*') {
        $result = db('storecost')->where($condition)->field($fields)->find();
        return $result;
    }

    /*
     * 增加 
     * @param array $param
     * @return bool
     */

    public function addStoreCost($param) {
        return db('storecost')->insert($param);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     */

    public function delStoreCost($condition) {
        return db('storecost')->where($condition)->delete();
    }

    /**
     * 更新
     * @param array $data
     * @param array $condition
     */
    public function editStoreCost($data, $condition) {
        return db('storecost')->where($condition)->update($data);
    }

}

?>
