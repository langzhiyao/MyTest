<?php

namespace app\common\model;
use think\Model;

class Mbfeedback extends Model {
public $page_info;
    /**
     * 列表
     *
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @return array
     */
    public function getMbFeedbackList($condition, $page = null, $order = 'id desc') {
        $list = db('mbfeedback')->where($condition)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info=$list;
        return $list;
    }

    /**
     * 新增
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addMbFeedback($param) {
        return db('mbfeedback')->insert($param);
    }

    /**
     * 删除
     *
     * @param int $id 记录ID
     * @return bool 布尔类型的返回结果
     */
    public function delMbFeedback($id) {
        $condition = array('id' => array('in', $id));
        return db('mbfeedback')->where($condition)->delete();
    }

}

?>
