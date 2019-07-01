<?php

namespace app\wap\controller;

use think\Lang;

class Bluetooth extends MobileMember
{

    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'wap\lang\zh-cn\login.lang.php');
    }

    /**
     * @desc 绑定蓝牙
     * @author langzhiyao
     */
    public function bind_blueTooth(){
        $member_id  = intval(input('post.member_id'));
        $append_id  = trim(input('post.append_id'));
        $name  = trim(input('post.name'));
        $distance  = trim(input('post.distance'));
        $voice  = trim(input('post.voice'));
        $openVibrator  = intval(input('post.openVibrator'));
        $status  = intval(input('post.status'));
        if (empty($member_id) || empty($append_id) || empty($name)) {
            output_error('参数有误');
        }
        //判断是否已绑定
        $model_blueTooth = Model('blueTooth');

        $result = $model_blueTooth->isset_blueTooth(array('userId'=>$member_id,'appendId'=>$append_id));
        if(!$result){
            $data = array(
                'userId'=>$member_id,
                'appendId'=>$append_id,
                'name'=>$name,
                'distance'=>$distance,
                'voice'=>$voice,
                'openVibrator'=>$openVibrator,
                'status'=>$status,
                'add_time'=>time()
            );
            $res = $model_blueTooth->blueTooth_add($data);
            if($res){
                output_data(array('message'=>'连接成功'));
            }else{
                output_error('连接失败，请重新连接');
            }
        }else{
            output_error('已连接');
        }
    }

    /**
     * @desc 修改连接蓝牙
     * @author langzhiyao
     */
    public function editBlueTooth(){
        $member_id  = intval(input('post.member_id'));
        $append_id  = trim(input('post.append_id'));
        $name  = trim(input('post.name'));
        $distance  = trim(input('post.distance'));
        $voice  = trim(input('post.voice'));
        $openVibrator  = intval(input('post.openVibrator'));
        $status  = intval(input('post.status'));
        if (empty($member_id) || empty($append_id) || empty($name)) {
            output_error('参数有误');
        }
        //判断是否已绑定
        $model_blueTooth = Model('blueTooth');

        $result = $model_blueTooth->isset_blueTooth(array('userId'=>$member_id,'appendId'=>$append_id));
        $data = array(
            'name'=>$name,
            'distance'=>$distance,
            'voice'=>$voice,
            'openVibrator'=>$openVibrator,
            'status'=>$status,
            'update_time'=>time()
        );
        if(!$result){
            output_error('该蓝牙已被移除或未连接');
        }else{
            $condition['userId']=$member_id;
            $condition['appendId']=$append_id;
            $res = $model_blueTooth->blueTooth_edit($condition,$data);
            if($res){
                output_data(array('message'=>'设置成功'));
            }else{
                output_error('设置失败');
            }
        }
    }

    /**
     * @desc 获取连接蓝牙
     * @author langzhiyao
     */
    public function getBlueTooth(){
        $member_id  = intval(input('post.member_id'));
        if (empty($member_id)) {
            output_error('参数有误');
        }
        $model_blueTooth = Model('blueTooth');

        $result = $model_blueTooth->getList(array('userId'=>$member_id));

        output_data($result);

    }

    /**
     * @desc 解除连接蓝牙
     * @author langzhiyao
     */
    public function delBlueTooth(){
        $member_id  = intval(input('post.member_id'));
        $append_id  = trim(input('post.append_id'));

        if (empty($member_id) || empty($append_id)) {
            output_error('参数有误');
        }
        //判断是否已绑定
        $model_blueTooth = Model('blueTooth');

        $result = $model_blueTooth->isset_blueTooth(array('userId'=>$member_id,'appendId'=>$append_id));

        if(!$result){
            output_error('该蓝牙已被移除或未连接');
        }else{
            $condition['userId']=$member_id;
            $condition['appendId']=$append_id;
            $res = $model_blueTooth->blueTooth_del($condition);
            if($res){
                output_data(array('message'=>'解除成功'));
            }else{
                output_error('解除失败');
            }
        }
    }

}