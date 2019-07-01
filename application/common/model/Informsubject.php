<?php

namespace app\common\model;


use think\Model;

class Informsubject extends Model
{
    public $page_info;

    private function getCondition($condition)
    {
        $condition_str = '1=1';
        if (!empty($condition['inform_subject_state'])) {
            $condition_str .= " and inform_subject_state = '{$condition['inform_subject_state']}'";
        }
        if (!empty($condition['inform_subject_type_id'])) {
            $condition_str .= " and inform_subject_type_id = '{$condition['inform_subject_type_id']}'";
        }
        if (!empty($condition['in_inform_subject_id'])) {
            $condition_str .= " and inform_subject_id in (" . $condition['in_inform_subject_id'] . ')';
        }
        if (!empty($condition['in_inform_subject_type_id'])) {
            $condition_str .= " and inform_subject_type_id in (" . $condition['in_inform_subject_type_id'] . ')';
        }
        return $condition_str;
    }

    /*
     * 增加
     * @param array $param
     * @return bool
     */
    public function saveInformSubject($param)
    {
        return db('informsubject')->insert($param);

    }

    /*
     * 更新
     * @param array $update_array
     * @param array $where_array
     * @return bool
     */
    public function updateInformSubject($update_array, $where_array)
    {

        $where = $this->getCondition($where_array);
        return db('informsubject')->where($where)->update($update_array);
    }

    /*
     * 删除
     * @param array $param
     * @return bool
     */
    public function dropInformSubject($param)
    {

        $where = $this->getCondition($param);
        return db('informsubject')->where($where)->delete();
    }

    /*
     *  获得列表
     *  @param array $condition
     *  @param obj $page 	//分页对象
     *  @return array
     */
    public function getInformSubject($condition = '', $page = '', $field = '')
    {
        $where = $this->getCondition($condition);
        $order = isset($condition['order']) ? $condition['order'] : ' inform_subject_id desc ';
        $res = db('informsubject')->field($field)->where($where)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }
}