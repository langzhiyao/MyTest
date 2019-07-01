<?php

namespace app\common\model;

use think\Model;

class Pkgs extends Model {

    public $page_info;

    /**
     * 新增套餐
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function pkg_add($param) {
        return db('packages')->insertGetId($param);
    }


    /**
     * 删除一个套餐
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function pkg_del($pkg_id) {
        $pkg_id = (int) $pkg_id;
        return db('packages')->where('pkg_id', $pkg_id)->delete();
    }

    /**
     * 更新套餐记录
     *
     * @param array $param 更新内容
     * @return bool
     */
    public function pkg_update($param) {
        $pkg_id = (int) $param['pkg_id'];
        return db('packages')->where('pkg_id', $param['pkg_id'])->update($param);
    }

    /**
     * 获取套餐列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function getPkgList($condition = array(), $page = '', $orderby = 'pkg_sort asc') {
        if ($page) {
            $result = db('packages')->where($condition)->order($orderby)->paginate($page, false, ['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            return db('packages')->where($condition)->order($orderby)->select();
        }
    }


    /**
     * 根据id查询一条记录
     *
     * @param int $id 套餐id
     * @return array 一维数组
     */
    public function getOneById($id) {
        return db('packages')->where('pkg_id', $id)->find();
    }
    public function getOnePkg($condition = array()) {
        return db('packages')->where($condition)->find();
    }

    /**
     * api获取套餐列表
     *
     * @param array $condition 查询条件
     * @return array 二维数组
     */
    public function getPkgLists($condition = array(), $field, $orderby = 'pkg_sort asc') {
            return db('packages')->field($field)->where($condition)->order($orderby)->select();

    }





}

?>
