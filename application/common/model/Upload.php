<?php

namespace app\common\model;

use think\Model;

class Upload extends Model {

    public function getUploadList($condition, $field = '*') {
        $condition_str = $this->_condition($condition);

        $result = db('upload')->field($field)->order('upload_id asc')->where($condition_str)->select();
        return $result;
    }

    /**
     * 构造检索条件
     *
     * @param int $id 记录ID
     * @return string 字符串类型的返回结果
     */
    private function _condition($condition) {
        $condition_str = '1=1';

        if (isset($condition['upload_type']) && $condition['upload_type'] != '') {
            $condition_str .= " and upload_type = '" . $condition['upload_type'] . "'";
        }
        if (isset($condition['item_id']) && $condition['item_id'] != '') {
            $condition_str .= " and item_id = '" . $condition['item_id'] . "'";
        }
        if (isset($condition['file_name']) && ($condition['file_name'] != '')) {
            $condition_str .= " and file_name = '" . $condition['file_name'] . "'";
        }
        if (isset($condition['upload_type_in'])) {
            if ($condition['upload_type_in'] == '') {
                $condition_str .= " and upload_type in('')";
            } else {
                $condition_str .= " and upload_type in({$condition['upload_type_in']})";
            }
        }
        if (isset($condition['item_id_in'])) {
            if ($condition['item_id_in'] == '') {
                $condition_str .= " and item_id in('')";
            } else {
                $condition_str .= " and item_id in({$condition['item_id_in']})";
            }
        }
        if (isset($condition['upload_id_in'])) {
            if ($condition['upload_id_in'] == '') {
                $condition_str .= " and upload_id in('')";
            } else {
                $condition_str .= " and upload_id in({$condition['upload_id_in']})";
            }
        }
        if (isset($condition['upload_time_lt']) && $condition['upload_time_lt'] != '') {
            $condition_str .= " and upload_time < '" . $condition['upload_time_lt'] . "'";
        }
        return $condition_str;
    }

    /**
     * 取单个内容
     *
     * @param int $id 分类ID
     * @return array 数组类型的返回结果
     */
    public function getOneUpload($id) {
        if (intval($id) > 0) {
            $data['upload_id'] = intval($id);
            $result = db('upload')->where($data)->find();
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 新增
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function add($param) {
        $result = db('upload')->insertGetId($param);
        return $result;
    }

    /**
     * 更新信息
     *
     * @param array $param 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function update_info($param, $upload_id) {
        $where = " upload_id = '" . $upload_id . "'";
        $result = db('upload')->where($where)->update($param);
        return $result;
    }

    /**
     * 更新信息
     *
     * @param array $param 更新数据
     * @param array $conditionarr 条件数组
     * @return bool 布尔类型的返回结果
     */
    public function updatebywhere($param, $conditionarr) {
        if (empty($param)) {
            return false;
        }
        if (is_array($param)) {
            //条件
            $condition_str = $this->_condition($conditionarr);
            //更新信息
            $tmp = array();
            foreach ($param as $k => $v) {
                $tmp[$k] = $v;
            }
            $result = db('upload')->where($condition_str)->update($tmp);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 删除分类
     *
     * @param int $id 记录ID
     * @return bool 布尔类型的返回结果
     */
    public function del($id) {
        if (intval($id) > 0) {
            $where = " upload_id = '" . intval($id) . "'";
            $result = db('upload')->where($where)->delete();
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 删除上传图片信息
     * @param	mixed $id 删除上传图片记录编号
     */
    public function dropUploadById($id) {
        if (empty($id)) {
            return false;
        }
        $condition_str = ' 1=1 ';
        if (is_array($id) && count($id) > 0) {
            $idStr = implode(',', $id);
            $condition_str .= " and upload_id in({$idStr}) ";
        } else {
            $condition_str .= " and upload_id = '{$id}' ";
        }
        $result = db('upload')->where($condition_str)->delete();
        return $result;
    }

    /**
     * 删除图片信息，根据where
     *
     * @param int	$id 店铺id
     * @param array $conditionarr 条件数组
     * @return bool 布尔类型的返回结果
     */
    public function delByWhere($conditionarr) {
        if (is_array($conditionarr)) {
            $condition_str = $this->_condition($conditionarr);
            $result = db('upload')->where($condition_str)->delete();
            return $result;
        } else {
            return false;
        }
    }

}