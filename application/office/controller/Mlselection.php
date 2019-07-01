<?php

/*
 * 多级选择：地区选择，分类选择
 */

namespace app\office\controller;

use think\Lang;

class Mlselection extends AdminControl {

    public function _initialize() {
        parent::_initialize('Mlselection');
        Lang::load(APP_PATH . 'office/lang/zh-cn/mlselection.lang.php');
    }

    function index() {
        $type = input('param.type');
        $pid = intval(input('param.pid'));
        
        in_array($type, array('region', 'goodsclass')) or json_encode('invalid type');
        switch ($type) {
            case 'region':
                $cityLevel = db('area')->field('area_id,area_deep')->where('area_id', $pid)->find();
                $regions = db('area')->where('area_parent_id', $pid)->select();
                foreach ($regions as $key => $region) {
                    $result[$key]['area_name'] = htmlspecialchars($region['area_name']);
                    $result[$key]['area_id'] = $region['area_id'];
                }
                $data = array(
                    'code' => 10000,
                    'message' => '',
                    'result' => $result,
                );
                switch ($cityLevel['area_deep']){
                    case 1:
                        $seach_value = 'provinceid';
                        break;
                    case 2:
                        $seach_value = 'cityid';
                        break;
                    case 3:
                        $seach_value = 'areaid';
                        break;
                }

                $condition[$seach_value] = $pid;
                $condition['isdel'] = 1;
                $admininfo = $this->getAdminInfo();
                if($admininfo['admin_id']!=1){
                      if(!empty($admininfo['admin_school_id'])){
                          $condition['schoolid'] = $admininfo['admin_school_id'];
                      }else{
                          $model_company = Model("Company");
                          $condition = $model_company->getCondition($admininfo['admin_company_id']);
                      }
                }
                $school_list = db('school')->field('schoolid,name')->where($condition)->select();
                $data['school_list'] =$school_list;
                echo json_encode($data);
                break;
            case 'goodsclass':
                $model_class = Model('goodsclass');
                $goods_class = $model_class->getGoodsClassListByParentId($pid);
                $array = array();
                if (is_array($goods_class) and count($goods_class) > 0) {
                    foreach ($goods_class as $val) {
                        $array[$val['gc_id']] = array('gc_id' => $val['gc_id'], 'gc_name' => htmlspecialchars($val['gc_name']), 'gc_parent_id' => $val['gc_parent_id'], 'commis_rate' => $val['commis_rate'], 'gc_sort' => $val['gc_sort']);
                    }
                }
                $data = array(
                    'code' => 10000,
                    'message' => '',
                    'result' => array_values($array),
                );
                echo json_encode($data);
                break;
            case 'schoolname':
                $cityLevel = db('area')->field('area_id,area_deep')->where('area_id', $pid)->find();
                $regions = db('area')->where('area_parent_id', $pid)->select();
                foreach ($regions as $key => $region) {
                    $result[$key]['area_name'] = htmlspecialchars($region['area_name']);
                    $result[$key]['area_id'] = $region['area_id'];
                }
                $data = array(
                    'code' => 10000,
                    'message' => '',
                    'result' => $result,
                );
                switch ($cityLevel['area_deep']){
                    case 1:
                        $seach_value = 'provinceid';
                        break;
                    case 2:
                        $seach_value = 'cityid';
                        break;
                    case 3:
                        $seach_value = 'areaid';
                        break;
                }
                $school_list = db('schoolapply')->field('applyid,schoolname')->where(array($seach_value=>$pid))->select();
                $data['school_list'] =$school_list;
                echo json_encode($data);
                break;
        }
    }

}

?>
