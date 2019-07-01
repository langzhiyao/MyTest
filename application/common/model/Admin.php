<?php

namespace app\common\model;

use think\Model;

class Admin extends Model {

    /**
     * 管理员列表
     *
     * @param array $condition 检索条件
     * @param obj $obj_page 分页对象
     * @return array 数组类型的返回结果
     */
    public function getAdminList($condition, $obj_page='') {
        $result = db('admin')->where($condition)->select();
        return $result;
    }

    /**
     * 取单个管理员的内容
     *
     * @param int $admin_id 管理员ID
     * @return array 数组类型的返回结果
     */
    public function getOneAdmin($admin_id) {
        if (intval($admin_id) > 0) {
            return db('admin')->where('admin_id',$admin_id)->find();
        } else {
            return false;
        }
    }

    /**
     * 获取管理员信息
     *
     * @param	array $param 管理员条件
     * @param	string $field 显示字段
     * @return	array 数组格式的返回结果
     */
    public function infoAdmin($condition, $field = '*') {
        if (empty($param)) {
            return false;
        }
        $result = db('admin')->where($condition)->select();
        return $admin_info[0];
    }

    /**
     * 新增
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addAdmin($param) {
        if (empty($param)) {
            return false;
        }
        return db('admin')->insert($param);
    }

    /**
     * 更新信息
     *
     * @param array $param 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function updateAdmin($param,$admin_id) {
        if (empty($param)) {
            return false;
        }
        return db('admin')->where('admin_id',$admin_id)->update($param);
    }

    /**
     * 删除
     *
     * @param int $id 记录ID
     * @return array $rs_row 返回数组形式的查询结果
     */
    public function delAdmin($id) {
        if (intval($id) > 0) {
            return db('admin')->delete($id);
        } else {
            return false;
        }
    }
}

?>
