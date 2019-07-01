<?php

namespace app\common\model;

use think\Model;

class BlueTooth extends Model {
    public $page_info;

    public function getBlueToothCount($condition){
        /*$str = db('blueTooth')->where($condition)->group('appid')->having($this->count('appid')>1);
        $condition['appid'] = array('in',$str);*/
        return db('bluetooth')->where($condition)->count();
    }

    public function isset_blueTooth($condition){
        $result = db('bluetooth')->where($condition)->find();
        if($result){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 新增
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function blueTooth_add($param) {
        return db('bluetooth')->insertGetId($param);
    }

    /**
     * @param $condition
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function blueTooth_edit($condition,$param){
        $result = db('bluetooth')->where($condition)->update($param);
        if($result){
            return true;
        }else{
            return false;
        }
    }


    public function getList($condition){
        return db('bluetooth')->where($condition)->select();
    }

    /**
     * @desc 解除连接蓝牙
     * @author langzhiyao
     */
    public function blueTooth_del($condition){

        return db('bluetooth')->where($condition)->delete();


    }

}