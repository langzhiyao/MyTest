<?php

/*
 * 多级选择：地区选择，分类选择
 */

namespace app\home\controller;

use think\Lang;

class Mlselection extends BaseHome {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/mlselection.lang.php');
    }

    function index() {
        $type = input('param.type');
        $pid = intval(input('param.pid'));
        
        in_array($type, array('region', 'goodsclass')) or json_encode('invalid type');

        switch ($type) {
            case 'region':
                $cityLevel = db('area')->field('area_id,area_deep')->where('area_id', $pid)->find();
                $regions = db('area')->where('area_parent_id', $pid)->select();
                // echo json_encode($regions);exit;
                foreach ($regions as $key => $region) {
                    $result[$key]['area_name'] = htmlspecialchars($region['area_name']);
                    $result[$key]['area_id'] = $region['area_id'];
                }
                $data = array(
                    'code' => 10000,
                    'message' => '',
                    'result' => $result,
                    'deep'=>$cityLevel['area_deep'],
                );
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
        }
    }

}

?>
