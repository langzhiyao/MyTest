<?php

namespace app\common\model;

use think\Model;

class Snsfriend extends Model {

    public $page_info;

    /**
     * 好友添加
     *
     * @param	array $param 添加信息数组
     */
    public function addFriend($param) {
        $result = db('snsfriend')->insertGetId($param);
        return $result;
    }

    /**
     * 好友列表
     *
     * @param	array $condition	条件数组
     * @param	string $field 	显示字段
     * @param	obj $obj_page 	分页
     * @param	string $type 	查询类型
     */
    public function listFriend($condition, $field = '*', $page = '', $type = 'simple') {
        //得到条件语句
        $order = isset($condition['order']) ? $condition['order'] : 'friend_id desc';
        $group = isset($condition['group']) ? $condition['group'] : '';
        switch ($type) {
            case 'simple':
                $data = db('snsfriend')->where($condition)->order($order)->field($field)->group($group)->paginate($page,false,['query' => request()->param()]);
                $this->page_info = $data;
                $friend_list = $data->items();
                break;
            case 'detail':
                $data = db('snsfriend')->alias('snsfriend')->where($condition)->order($order)->group($group)->field($field)->join('__MEMBER__ member', 'snsfriend.friend_tomid=member.member_id')->paginate($page,false,['query' => request()->param()]);
                $this->page_info = $data;
                $friend_list = $data->items();
                break;
            case 'fromdetail':
                $data = db('snsfriend')->alias('snsfriend')->where($condition)->order($order)->group($group)->field($field)->join('__MEMBER__ member', 'snsfriend.friend_frommid=member.member_id')->paginate($page,false,['query' => request()->param()]);
                $this->page_info = $data;
                $friend_list = $data->items();
                break;
        }
        return $friend_list;
    }

    /**
     * 获取好友详细
     *
     * @param $condition 查询条件
     * @param $field 查询字段
     */
    public function getFriendRow($condition, $field = '*') {
        return db('snsfriend')->where($condition)->field($field)->select();
    }

    /**
     * 好友总数
     */
    public function countFriend($condition) {
        //得到条件语句
        $count = db('snsfriend')->where($condition)->count();
        return $count;
    }

    /**
     * 更新好友信息
     * @param $param 更新内容
     * @param $condition 更新条件
     */
    public function editFriend($param, $condition) {
        if (empty($param)) {
            return false;
        }
        //得到条件语句
        $condition_str = $this->getCondition($condition);
        $result = db('snsfriend')->where($condition_str)->update($param);
        return $result;
    }

    /**
     * 删除关注
     */
    public function delFriend($condition) {
        if (empty($condition)) {
            return false;
        }
        $condition_str = '1=1';
        if ($condition['friend_frommid'] != '') {
            $condition_str .= " and friend_frommid='{$condition['friend_frommid']}' ";
        }
        if ($condition['friend_tomid'] != '') {
            $condition_str .= " and friend_tomid='{$condition['friend_tomid']}' ";
        }
        return db('snsfriend')->where($condition_str)->delete();
    }

    /**
     * 将条件数组组合为SQL语句的条件部分
     *
     * @param	array $conditon_array
     * @return	string
     */
    private function getCondition($conditon_array) {
        $condition_sql = '1=1';
        //自增编号
        if (isset($conditon_array['friend_id']) && $conditon_array['friend_id'] != '') {
            $condition_sql .= " and sns_friend.friend_id= '{$conditon_array['friend_id']}'";
        }
        //会员编号
        if (isset($conditon_array['friend_frommid']) && $conditon_array['friend_frommid'] != '') {
            $condition_sql .= " and sns_friend.friend_frommid= '{$conditon_array['friend_frommid']}'";
        }
        //朋友编号
        if (isset($conditon_array['friend_tomid']) && $conditon_array['friend_tomid'] != '') {
            $condition_sql .= " and sns_friend.friend_tomid = '{$conditon_array['friend_tomid']}'";
        }
        //关注状态
        if (isset($conditon_array['friend_followstate']) && $conditon_array['friend_followstate'] != '') {
            $condition_sql .= " and sns_friend.friend_followstate = '{$conditon_array['friend_followstate']}'";
        }
        return $condition_sql;
    }

}