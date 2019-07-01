<?php

namespace app\common\model;

use think\Model;

class Mbcategory extends Model {

    /**
     * 列表
     *
     * @param array $condition 检索条件
     * @param obj $page 分页
     * @return array 数组结构的返回结果
     */
    public function getLinkList($condition, $page = '',$order='gc_id') {
        $result = db('mbcategory')->where($condition)->order($order)->select();
        return $result;
    }

    /**
     * 取单个内容
     *
     * @param int $id ID
     * @return array 数组类型的返回结果
     */
    public function getOneLink($id) {
        $result = db('mbcategory')->where('gc_id='.intval($id))->find();
        return $result;
    }

    /**
     * 取单个内容
     *
     * @param int $id ID
     * @return array 数组类型的返回结果
     */
    public function getCount() {
        return db('mbcategory')->count();
    }

    /**
     * 新增
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addinfo($param) {
        return db('mbcategory')->insert($param);
    }

    /**
     * 更新信息
     *
     * @param array $param 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function update_mbcategory($param,$gc_id) {
        return db('mbcategory')->where('gc_id='.$gc_id)->update($param);
    }

    /**
     * 删除
     *
     * @param int $id 记录ID
     * @return bool 布尔类型的返回结果
     */
    public function del($id) {
        return db('mbcategory')->where('gc_id='.$id)->delete();
    }

}

?>
