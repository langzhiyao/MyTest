<?php


namespace app\common\model;


use think\Model;

class Complaintalk extends Model
{
    private function getCondition($condition)
    {
        $condition_str = '1=1';
        if (!empty($condition['complain_id'])) {
            $condition_str .= " and complain_id = '{$condition['complain_id']}'";
        }
        if (!empty($condition['talk_id'])) {

            $condition_str .= " and talk_id = '{$condition['talk_id']}'";
        }
        return $condition_str;
    }

    /*
     * 增加
     * @param array $param
     * @return bool
     */
    public function saveComplainTalk($param)
    {

        //return Db::insert('complain_talk', $param);
        return db('complaintalk')->insert($param);
    }

    /*
     * 更新
     * @param array $update_array
     * @param array $where_array
     * @return bool
     */
    public function updateComplainTalk($update_array, $where_array)
    {

        $where = $this->getCondition($where_array);
        //return Db::update('complain_talk', $update_array, $where);
        return db('complaintalk')->where($where)->update($update_array);

    }

    /*
     * 删除
     * @param array $param
     * @return bool
     */
    public function dropComplainTalk($param)
    {

        $where = $this->getCondition($param);
        //return Db::delete('complain_talk', $where);
        return db('complaintalk')->where($where)->delete($param);

    }

    /*
     *  获得列表
     *  @param array $condition

     *  @return array
     */
    public function getComplainTalk($condition = '', $field = '*')
    {

         $where=array();
        $where = $this->getCondition($condition);
        $order = isset($condition['order']) ? $condition['order'] : 'talk_id desc ';
        //return Db::select($param, $page);
        return db('complaintalk')->where($where)->field($field)->order($order)->select();

    }
}