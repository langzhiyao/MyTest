<?php

namespace app\common\model;

use think\Model;

class Activity extends Model
{
    public $page_info;

    /**
     * 活动列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function getList($condition, $page = '')
    {
        $where = $this->getCondition($condition);
        $order = $condition['order'] ? $condition['order'] : 'activity_id';
        $res = db('activity')->alias('a')->where($where)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

    /**
     * 添加活动
     *
     * @param array $input
     * @return bool
     */
    public function add($input)
    {
        return db('activity')->insertGetId($input);
    }

    /**
     * 更新活动
     *
     * @param array $input
     * @param int $id
     * @return bool
     */
    public function updates($input, $id)
    {
        return db('activity')->where("activity_id='$id' ")->update($input);
    }

    /**
     * 删除活动
     *
     * @param string $id
     * @return bool
     */
    public function del($id)
    {
        return db('activity')->where('activity_id in(' . $id . ')')->delete();
    }

    /**
     * 根据id查询一条活动
     *
     * @param int $id 活动id
     * @return array 一维数组
     */
    public function getOneById($id)
    {
        $data['activity_id'] = $id;
        return db('activity')->where($data)->find();
    }

    /**
     * 根据条件
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function getJoinList($condition, $page = '')
    {

        $join_type = empty($condition['join_type']) ? 'right join' : $condition['join_type'];
        $where = $this->getCondition($condition);
        $order = $condition['order'];
        $res= db('activity')->alias('a')->join('__ACTIVITYDETAIL__ d', 'a.activity_id=d.activity_id', $join_type)->where($where)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info=$res;
        return $res->items();
    }

    /**
     * 构造查询条件
     *
     * @param array $condition 条件数组
     * @return string
     */
    private function getCondition($condition)
    {
        $conditionStr = '1=1';
        if (isset($condition['activity_id'])&& $condition['activity_id'] != '') {
            $conditionStr .= " and a.activity_id='{$condition['activity_id']}' ";
        }
        if (isset($condition['activity_type'])&& $condition['activity_type'] != '') {
            $conditionStr .= " and a.activity_type='{$condition['activity_type']}' ";
        }
        if (isset($condition['activity_state'])&& $condition['activity_state'] != '') {
            $conditionStr .= " and a.activity_state = '{$condition['activity_state']}' ";
        }
        //活动删除in
        if (isset($condition['activity_id_in'])) {
            if ($condition['activity_id_in'] == '') {
                $conditionStr .= " and activity_id in('')";
            }
            else {
                $conditionStr .= " and activity_id in({$condition['activity_id_in']}) ";
            }
        }
        if (isset($condition['activity_title'])&& $condition['activity_title'] != '') {
            $conditionStr .= " and a.activity_title like '%{$condition['activity_title']}%' ";
        }
        //当前时间大于结束时间（过期）
        if (isset($condition['activity_enddate_greater'])&&$condition['activity_enddate_greater'] != '') {
            $conditionStr .= " and a.activity_end_date < '{$condition['activity_enddate_greater']}'";
        }
        //可删除的活动记录
        if (isset($condition['activity_enddate_greater_or'])&&$condition['activity_enddate_greater_or'] != '') {
            $conditionStr .= " or a.activity_end_date < '{$condition['activity_enddate_greater_or']}'";
        }
        //某时间段内正在进行的活动
        if (isset($condition['activity_daterange'])&&$condition['activity_daterange'] != '') {
            $conditionStr .= " and (a.activity_end_date >= '{$condition['activity_daterange']['startdate']}' and a.activity_start_date <= '{$condition['activity_daterange']['enddate']}')";
        }
        if (isset($condition['opening'])) {//在有效期内、活动状态为开启
            $conditionStr .= " and (a.activity_start_date <=" . time() . " and a.activity_end_date >= " . time() . " and a.activity_state =1)";
        }
        return $conditionStr;
    }
}