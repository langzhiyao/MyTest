<?php

namespace app\common\model;


use think\Model;

class Mallconsult extends Model
{
    public $page_info;
    /**
     * 咨询列表
     *
     * @param array $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getMallConsultList($condition, $field = '*', $page = 0, $order = 'mc_id desc') {
        $res= db('mallconsult')->where($condition)->field($field)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info=$res;
        return $res->items();
    }

    /**
     * 咨询数量
     *
     * @param array $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getMallConsultCount($condition) {
        return db('mallconsult')->where($condition)->count();
    }

    /**
     * 单条咨询
     *
     * @param unknown $condition
     * @param string $field
     */
    public function getMallConsultInfo($condition, $field = '*') {
        return db('mallconsult')->where($condition)->field($field)->find();
    }

    /**
     * 咨询详细信息
     *
     * @param unknown $mc_id
     * @return boolean|multitype:
     */
    public function getMallConsultDetail($mc_id) {
        $consult_info = $this->getMallConsultInfo(array('mc_id' => $mc_id));
        if (empty($consult_info)) {
            return false;
        }

        $type_info = Model('mallconsulttype')->getMallConsultTypeInfo(array('mct_id' => $consult_info['mct_id']), 'mct_name');
        return array_merge($consult_info, $type_info);
    }

    /**
     * 添加咨询
     * @param array $insert
     * @return int
     */
    public function addMallConsult($insert) {
        $insert['mc_addtime'] = TIMESTAMP;
        return db('mallconsult')->insertGetId($insert);
    }

    /**
     * 编辑咨询
     * @param array $condition
     * @param array $update
     * @return boolean
     */
    public function editMallConsult($condition, $update) {
        return db('mallconsult')->where($condition)->update($update);
    }

    /**
     * 删除咨询
     *
     * @param array $condition
     * @return boolean
     */
    public function delMallConsult($condition) {
        return db('mallconsult')->where($condition)->delete();
    }
}