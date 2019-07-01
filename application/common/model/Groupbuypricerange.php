<?php

namespace app\common\model;

use think\Model;

class Groupbuypricerange extends Model
{
    //主键
    const PK = 'range_id';
    public $page_info;

    /**
     * 构造检索条件
     *
     * @param array $condition 检索条件
     * @return string
     */
    private function getCondition($condition)
    {
        $condition_str = '1=1';
        if (!empty($condition['range_id'])) {
            $condition_str .= " AND range_id = '" . $condition['range_id'] . "'";
        }
        if (!empty($condition['in_range_id'])) {
            $condition_str .= " AND range_id in (" . $condition['in_range_id'] . ")";
        }
        return $condition_str;
    }

    /**
     * 读取列表
     *
     */
    public function getList($condition = array(), $page = '')
    {
        $where = $this->getCondition($condition);
        $order = isset($condition['order']) ? $condition['order'] : ' ' . self::PK . ' desc ';
        $res = db('groupbuypricerange')->where($where)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }


    /**
     * 根据编号获取单个内容
     *
     * @param int 主键编号
     * @return array 数组类型的返回结果
     */
    public function getOne($id)
    {
        if (intval($id) > 0) {
            $data['range_id'] = intval($id);
            $result = db('groupbuypricerange')->where($data)->find();
            return $result;
        }
        else {
            return false;
        }
    }

    /*
     *  判断是否存在
     *  @param array $condition
     *  @param obj $page 	//分页对象
     *  @return array
     */
    public function isExist($condition = '')
    {
        $where = $this->getCondition($condition);
        $list = db('groupbuypricerange')->where($where)->select();
        if (empty($list)) {
            return false;
        }
        else {
            return true;
        }
    }

    /*
     * 增加
     * @param array $param
     * @return bool
     */
    public function saveinfo($param)
    {
        return db('groupbuypricerange')->insertGetId($param);

    }

    /*
     * 更新
     * @param array $update_array
     * @param array $where_array
     * @return bool
     */
    public function updateinfo($update_array, $where_array)
    {
        $where = $this->getCondition($where_array);
        return db('groupbuypricerange')->where($where)->update($update_array);

    }

    /*
     * 删除
     * @param array $param
     * @return bool
     */
    public function drop($param)
    {

        $where = $this->getCondition($param);
        return db('groupbuypricerange')->where($where)->delete();
    }

}