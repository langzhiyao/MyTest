<?php

namespace app\common\model;

use think\Model;

class Transport extends Model {

    public $page_info;

    /**
     * 增加售卖区域
     *
     * @param unknown_type $data
     *
     * @return unknown
     */
    public function addTransport($data) {
        return db('transport')->insertGetId($data);
    }

    /**
     * 增加各地区详细运费设置
     *
     * @param unknown_type $data
     *
     * @return unknown
     */
    public function addExtend($data) {
        return db('transportextend')->insertAll($data);
    }

    /**
     * 取得一条售卖区域信息
     *
     * @return unknown
     */
    public function getTransportInfo($condition) {
        return db('transport')->where($condition)->find();
    }

    /**
     * 取得一条售卖区域扩展信息
     *
     * @return unknown
     */
    public function getExtendInfo($condition) {
        return db('transportextend')->where($condition)->select();
    }

    /**
     * 删除售卖区域
     *
     * @param unknown_type $id
     *
     * @return unknown
     */
    public function delTansport($condition) {
        try {
            $this->startTrans();
            $delete = db('transport')->where($condition)->delete();
            if ($delete) {
                $delete = db('transportextend')->where(array('transport_id' => $condition['id']))->delete();
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();

            return false;
        }

        return true;
    }

    /**
     * 删除售卖区域扩展信息
     *
     * @param unknown_type $transport_id
     *
     * @return unknown
     */
    public function delExtend($transport_id) {
        return db('transportextend')->where(array('transport_id' => $transport_id))->delete();
    }

    /**
     * 取得售卖区域列表
     *
     * @param unknown_type $condition
     * @param unknown_type $page
     * @param unknown_type $order
     *
     * @return unknown
     */
    public function getTransportList($condition = array(), $pagesize = '', $order = 'id desc') {
        if($pagesize){
            $res = db('transport')->where($condition)->order($order)->paginate($pagesize,false,['query' => request()->param()]);
            $this->page_info=$res;
            return $res->items();
        }else{
            return db('transport')->where($condition)->order($order)->select();
        }
        
        
    }

    /**
     * 取得扩展信息列表
     *
     * @param unknown_type $condition
     * @param unknown_type $order
     *
     * @return unknown
     */
    public function getExtendList($condition = array(), $order = '') {
        return db('transportextend')->where($condition)->order($order)->select();
    }

    public function transUpdate($data, $condition = array()) {
        return db('transport')->where($condition)->update($data);
    }

    /**
     * 检测售卖区域是否正在被使用
     *
     */
    public function isUsing($id) {
        if (!is_numeric($id)) {
            return false;
        }
        $goods_info = db('goods')->where(array('transport_id' => $id))->field('goods_id')->find();

        return $goods_info ? true : false;
    }

    /**
     * 计算某地区某售卖区域ID下的商品总运费，如果售卖区域不存在或，按免运费处理
     *
     * @param int $transport_id
     * @param int $area_id
     *
     * @return number/boolean
     */
    public function calc_transport($transport_id, $area_id) {
        if (empty($transport_id) || empty($area_id)) {
            return 0;
        }
        $extend_list = $this->getExtendList(array('transport_id' => $transport_id));
        if (empty($extend_list)) {
            return false;
        } else {
            return $this->_calc_unit($area_id, $extend_list);
        }
    }

    /**
     * 计算某个具单元的运费
     *
     * @param 配送地区 $area_id
     * @param 售卖区域内容 $extend
     *
     * @return number/false 总运费
     */
    private function _calc_unit($area_id, $extend) {
        if (!empty($extend) && is_array($extend)) {
            foreach ($extend as $v) {
                if (strpos($v['area_id'], "," . $area_id . ",") !== false) {
                    $calc_total = $v['sprice'];
                }
            }
        }

        return isset($calc_total) ? dsPriceFormat($calc_total) : false;
    }

}