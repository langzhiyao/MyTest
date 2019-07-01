<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/16
 * Time: 20:12
 */

namespace app\wap\controller;


use app\mobile\controller\MobileMall;

class Teachertype extends MobileMall
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    //教孩搜索栏菜单
    public function index(){
        //所有分类
        $model_type = model('Teachtype');
        $tmp_list = $model_type->getTreeTypeList(4);
        //一级分类
        $parentType = db('teachtype')->where(array('gc_parent_id'=>0))->order('gc_sort ASC')->select();
        //二级分类
        foreach($parentType as $key=>$item){
            foreach($tmp_list as $k=>$v){
                $newData[$key]['code'] = $item['gc_id'];
                $newData[$key]['name'] = $item['gc_name'];
                if($v['gc_parent_id']==$item['gc_id'] && $v['deep']==2){
                    $newData[$key]['sub'][$k]['code'] = $v['gc_id'];
                    $newData[$key]['sub'][$k]['name'] = $v['gc_name'];
                }
            }
            if($newData[$key]['sub']){
                $newData[$key]['sub'] = array_values($newData[$key]['sub']);
            }
        }

        //三级分类
        foreach($newData as $key=>$item){
            foreach($item['sub'] as $k2=>$v2){
                foreach($tmp_list as $k=>$v){
                    if($v['gc_parent_id']==$v2['code']){
                        $item['sub'][$k2]['sub'][$k]['code'] = $v['gc_id'];
                        $item['sub'][$k2]['sub'][$k]['name'] = $v['gc_name'];
                    }
                }
                if($item['sub'][$k2]['sub']){
                    $item['sub'][$k2]['sub'] = array_values($item['sub'][$k2]['sub']);
                }
            }
            $newData[$key]['sub'] =  $item['sub'];
        }
        //四级分类
        foreach($newData as $key=>$item){
            foreach($item['sub'] as $k2=>$v2){
                foreach($v2['sub'] as $k3=>$v3){
                    foreach($tmp_list as $k=>$v){
                        if($v['gc_parent_id']==$v3['code'] && $v['deep']==4){
                            $v2['sub'][$k3]['sub'][$k]['code'] = $v['gc_id'];
                            $v2['sub'][$k3]['sub'][$k]['name'] = $v['gc_name'];
                        }
                    }
                    if($v2['sub'][$k3]['sub']){
                        $v2['sub'][$k3]['sub'] = array_values($v2['sub'][$k3]['sub']);
                    }
                }
                if($v2['sub']){
                    $item['sub'][$k2]['sub'] = $v2['sub'];
                }
            }
            $newData[$key] =  $item;
        }
        output_data($newData);
    }

}