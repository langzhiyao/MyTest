<?php

namespace app\common\model;


use think\Model;

class Complainsubject extends Model
{
    public $page_info;

    /*
         * 构造条件
         */
    private function getCondition($condition)
    {
        $condition_str = '1=1';
        if (!empty($condition['complain_subject_state'])) {
            $condition_str .= " and complain_subject_state = '{$condition['complain_subject_state']}'";
        }
        if (!empty($condition['in_complain_subject_id'])) {
            $condition_str .= " and complain_subject_id in (" . $condition['in_complain_subject_id'] . ')';
        }
        return $condition_str;
    }

    /*
     * 增加
     * @param array $param
     * @return bool
     */
    public function saveComplainSubject($param)
    {
        return db('complainsubject')->insert($param);

    }

    /*
     * 更新
     * @param array $update_array
     * @param array $where_array
     * @return bool
     */
    public function updateComplainSubject($update_array, $where_array)
    {

        $where = $this->getCondition($where_array);
        return db('complainsubject')->where($where)->update($update_array);

    }

    /*
     * 删除
     * @param array $param
     * @return bool
     */
    public function dropComplainSubject($param)
    {

        $where = $this->getCondition($param);
        return db('complainsubject')->where($where)->delete();

    }

    /*
     *  获得投诉主题列表
     *  @param array $condition
     *  @param obj $page 	//分页对象
     *  @return array
     */
    public function getComplainSubject($condition = '', $page = '')
    {

        $where = $this->getCondition($condition);
        $order= $condition['order'] ? $condition['order'] : ' complain_subject_id desc ';
        $res= db('complainsubject')->where($where)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info=$res;
        return $res->items();

    }

    /*
     *  获得有效投诉主题列表
     *  @param array $condition
     *  @param obj $page 	//分页对象
     *  @return array
     */
    public function getActiveComplainSubject($condition = '', $page = '')
    {

        //搜索条件
        $condition['complain_subject_state'] = 1;
        $where= $this->getCondition($condition);
        $order = isset($condition['order']) ? $condition['order'] : ' complain_subject_id desc ';
        $res=db('complainsubject')->where($where)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info=$res;
        return $res->items();

    }

}