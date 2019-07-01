<?php

namespace app\common\model;


use think\Model;

class Link extends Model
{
    public $page_info;
    /**
     * 列表
     *
     * @param array $condition 检索条件
     * @param obj $page 分页
     * @return array 数组结构的返回结果
     */
    public function getLinkList($condition = '', $page = '')
    {
        $condition_str = $this->_condition($condition);
        $where = $condition_str;
        $order = isset($condition['order']) ? $condition['order'] : 'link_id ';
        $result = db('link')->where($where)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info=$result;
        return $result->items();
    }

    /**
     * 构造检索条件
     *
     * @param int $id 记录ID
     * @return string 字符串类型的返回结果
     */
    private function _condition($condition)
    {
        $condition_str = '';

        if (isset($condition['like_link_title']) && $condition['like_link_title'] != '') {
            $condition_str .= " and link_title like '%" . $condition['like_link_title'] . "%'";
        }
        if (isset($condition['link_pic']) && $condition['link_pic'] == 'yes') {
            $condition_str .= " and link_pic != ''";
        }
        if (isset($condition['link_pic']) && $condition['link_pic'] == 'no') {
            $condition_str .= " and LENGTH(link_pic)=0";
        }
        return $condition_str;
    }

    /**
     * 取单个内容
     *
     * @param int $id ID
     * @return array 数组类型的返回结果
     */
    public function getOneLink($id)
    {
        return db('link')->where('link_id='.intval($id))->find();
    }

    /**
     * 新增
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function add($param)
    {
        return db('link')->insertGetId($param);
    }

    /**
     * 更新信息
     *
     * @param array $param 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function updateinfo($param,$link_id)
    {
        
        return db('link')->where(" link_id = '" . $param['link_id'] . "'")->update($param);
    }

    /**
     * 删除
     *
     * @param int $id 记录ID
     * @return bool 布尔类型的返回结果
     */
    public function del($id)
    {
        return db('link')->where('id',intval($id))->delete();
    }
}