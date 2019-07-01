<?php

namespace app\common\model;


use think\Model;

class Informsubjecttype extends Model
{
    public $page_info;
    private function getCondition($condition)
    {
        $condition_str = '1=1';
        if (!empty($condition['inform_type_state'])) {
            $condition_str .= " and  inform_type_state = '{$condition['inform_type_state']}'";
        }
        if (!empty($condition['in_inform_type_id'])) {
            $condition_str .= " and inform_type_id in (" . $condition['in_inform_type_id'] . ')';
        }
        return $condition_str;
    }

    /*
     * 增加
     * @param array $param
     * @return bool
     */
    public function saveInformSubjectType($param)
    {
        return db('informsubjecttype')->insert($param);

    }

    /*
     * 更新
     * @param array $update_array
     * @param array $where_array
     * @return bool
     */
    public function updateInformSubjectType($update_array, $where_array)
    {

        $where = $this->getCondition($where_array);
        return db('informsubjecttype')->where($where)->update($update_array);

    }

    /*
     * 删除
     * @param array $param
     * @return bool
     */
    public function dropInformSubjectType($param)
    {

        $where = $this->getCondition($param);
        return db('informsubjecttype')->where($where)->delete();

    }

    /*
     *  获得举报类型列表
     *  @param array $condition
     *  @param obj $page 	//分页对象
     *  @return array
     */
    public function getInformSubjectType($condition = '', $page = '')
    {

        $where = $this->getCondition($condition);
        $order = isset($condition['order']) ? $condition['order'] : ' inform_type_id desc ';

        $res=db('informsubjecttype')->where($where)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info=$res;
        return $res->items();

    }

    /*
     *  获得有效举报类型列表
     *  @param array $condition
     *  @param obj $page 	//分页对象
     *  @return array
     */
    public function getActiveInformSubjectType($page = '')
    {

        //搜索条件
        $condition = array();
        $condition['order'] = 'inform_type_id asc';
        $condition['inform_type_state'] = 1;
        return $this->getInformSubjectType($condition, $page);

    }
}