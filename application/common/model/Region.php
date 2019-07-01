<?php
namespace app\common\model;

use think\Model;

class Region extends Model
{
    protected static function init()
    {
        //TODO:自定义的初始化
    }
    public function get_list($parent_id=-1){
        $map = array();
        if ($parent_id >= 0) {
            $map['area_parent_id'] = $parent_id;
        }
        return db('area')->where($map)->order('area_sort')->select();
    }
}