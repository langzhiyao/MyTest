<?php

namespace app\common\model;

use think\Model;

class Packagetime extends Model {

    public $page_info;

    /**
     * 新增套餐
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function pkg_add($param) {
        return db('packagetime')->insertGetId($param);
    }


    /**
     * 删除一个套餐
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function pkg_del($id) {
        $id = (int) $id;
        return db('packagetime')->where('id', $id)->delete();
    }

    /**
     * 更新套餐记录
     *
     * @param array $param 更新内容
     * @return bool
     */
    public function pkg_update($param) {
        return db('packagetime')->where('id', $param['id'])->update($param);
    }

    /**
     * 获取套餐列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function getPkgList($condition = array(), $page = '', $orderby = '') {
        if ($page) {
            $result = db('packagetime')->where($condition)->order($orderby)->paginate($page, false, ['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            return db('packagetime')->where($condition)->order($orderby)->select();
        }
    }


    /**
     * 根据id查询一条记录
     *
     * @param int $id 套餐id
     * @return array 一维数组
     */
    public function getOneById($id) {
        return db('packagetime')->where('id', $id)->find();
    }
    public function getOnePkg($condition = array()) {
        return db('packagetime')->where($condition)->find();
    }

    /**
     * api获取套餐列表
     *
     * @param array $condition 查询条件
     * @return array 二维数组
     */
    public function getPkgLists($condition = array(), $field, $orderby = '') {
            return db('packagetime')->field($field)->where($condition)->order($orderby)->select();

    }





}

?>
