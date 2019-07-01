<?php

namespace app\common\model;

use think\Model;

class Uploadalbum extends Model {

    /**
     * 列表
     *
     * @param array $condition 检索条件
     * @return array 数组结构的返回结果
     */
    public function getUploadList($condition) {

        $result = db('albumpic')->where($condition)->select();
        return $result;
    }

    /**
     * 取单个内容
     *
     * @param int $id 分类ID
     * @return array 数组类型的返回结果
     */
    public function getOneUpload($id) {
        return db('albumpic')->where('apic_id',$id)->find();
    }

    /**
     * 新增
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function add($param) {
        return db('albumpic')->insertGetId($param);
    }

    /**
     * 更新信息
     *
     * @param array $param 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function updatePic($apic_id,$param) {
        return db('albumpic')->where('apic_id',$apic_id)->update($param);
    }

    /**
     * 更新信息
     *
     * @param array $param 更新数据
     * @param array $conditionarr 条件数组 
     * @return bool 布尔类型的返回结果
     */
    public function updatebywhere($param, $conditionarr) {
        return db('albumpic')->where($conditionarr)->update($param);
    }

    /**
     * 删除分类
     *
     * @param int $id 记录ID
     * @return bool 布尔类型的返回结果
     */
    public function del($id) {
        return db('albumpic')->where('apic_id',$id)->delete();
    }

    /**
     * 删除上传图片信息
     * @param	mixed $id 删除上传图片记录编号
     */
    public function dropUploadById($id) {
        if (is_array($id) && count($id) > 0) {
            $idStr = implode(',', $id);
            $map['apic_id'] = array('in',$idStr);
        } else {
            $map['apic_id'] = $id;
        }
        return db('albumpic')->where($map)->delete();
    }

}
