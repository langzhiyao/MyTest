<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/16
 * Time: 20:12
 */

namespace app\wap\controller;


class Teacherchild extends MobileMember
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    //我的上传
    public function myUpload(){
        $member_id = input('post.member_id');
        if(empty($member_id)){
            output_error('member_id参数有误');
        }
        $teachchild = model('Teachchild');
        $condition = array();
        $condition['t_userid'] = $member_id;
        if(input('post.status')){
            if(input('post.status')==3){
                $condition['t_audit'] = 3;
            }elseif(input('post.status')==1){
                $condition['t_audit'] = array('between',array(1,2));
            }
        }else{
            $condition['t_audit'] = array('between',array(1,2));
        }
        $page = !empty(input('post.page'))?input('post.page'):1;
        $list = $teachchild->getTeachchildList($condition,'',$page,'t_id desc',10);
        foreach($list as $k=>$v){
            if($v['t_del']==2){
                $list[$k]['t_audit'] = 4;
            }
        }
        output_data($list);
    }

    //我的视频
    public function myVideos(){
        $member_id = input('post.member_id');
        $last_id = input('post.page');
        if($last_id){
            $last_info = db('packagesorderteach')->field("order_id,add_time")->where("add_time <".$last_id." and buyer_id=".$member_id." and order_state=20 and delete_state=0")->order('add_time desc')->find();
            if(!empty($last_info)){
                $strtime = strtotime(date("Y-m-d",$last_info['add_time'])." 00:00:00");
                $endtime = $strtime+24*3600;
                $teach_model = Model('Packagesorderteach');
                $result = $teach_model->getTeachOrder("buyer_id=".$member_id." and order_state=20 and delete_state=0 and add_time<".$endtime." and add_time>=".$strtime);
            }else{
                $result = [];
            }
        }else{
            $cond['delete_state'] = 0;
            $cond['buyer_id'] = $member_id;
            $cond['order_state'] = 20;
            $result = db('packagesorderteach')->field("order_id,order_sn,order_name,order_tid,order_dieline,add_time,payment_time,order_amount")->where($cond)->order('add_time',desc)->limit(0,10)->select();
            if(count($result)==10){
                foreach($result as $kk=>$vv){
                    $time = $vv['add_time'];
                }
                $strtime = strtotime(date("Y-m-d",$time)." 00:00:00");
                $endtime = $strtime+24*3600;
                $where = "delete_state=0 and buyer_id=".$member_id." and order_state=20 and add_time<".$endtime." and add_time>=".$strtime;
                $day = db('packagesorderteach')->field("order_id,order_sn,order_name,order_tid,order_dieline,add_time,payment_time,order_amount")->where($where)->order('add_time',desc)->select();
                $result=array_merge($result,$day);
                $result=array_unique($result, SORT_REGULAR);
            }
        }
        foreach($result as $k=>$v){
            $result[$k]['date'] = date("Y-m-d",$v['add_time']);
            if(date("Y-m-d",time()) == date("Y-m-d",$v['add_time'])){
                $result[$k]['date'] = "今天";
            }
            $videoinfo = db('teachchild')->where(array('t_id'=>$v['order_tid']))->find();
            $result[$k]['title'] = $videoinfo['t_title'];
            $result[$k]['videoimg'] = $videoinfo['t_videoimg'];
            $result[$k]['t_picture'] = $videoinfo['t_picture'];
            $result[$k]['videourl'] = $videoinfo['t_url'];
            $result[$k]['author'] = $videoinfo['t_author'];
            $result[$k]['order_amount'] = sprintf('%.2f', $videoinfo['order_amount']);
            $result[$k]['order_dieline'] = date("Y-m-d H:i:s",$v['order_dieline']);
            $result[$k]['payment_time'] = date("Y-m-d H:i:s",$v['payment_time']);
            if($v['order_dieline']>time()){
                $result[$k]['is_click'] = 1;
            }else{
                $result[$k]['is_click'] = $videoinfo['t_del']==2 ? 0 : 1;
            }
        }
        foreach($result as $key=>$item){
            $data[$item['date']][] = $item;
            $last_id = $item['add_time'];
        }
        $data = isset($data)?[$data]:[];
        if(!empty($data[0])){
            $data[1]['id'] = !empty($last_id)?$last_id:"";
        }
        output_data($data);
    }

    public function delOrder(){
        $member_id = input('post.member_id');
        $order_id = input('post.order_id');
        if(empty($order_id)){
            output_error('订单id不能为空');
        }
        if(empty($member_id)){
            output_error('会员id不能为空');
        }
        $model_order = Model("Packagesorderteach");
        $orderInfo = $model_order->getOrderInfo(array('buyer_id'=>$member_id,'order_id'=>$order_id));
        if(empty($orderInfo)){
            output_error('会员id和订单id不匹配');
        }
        $result = $model_order->editOrder(array('delete_state'=>1),array('order_id'=>$order_id));
        if($result){
            output_data("删除成功");
        }else{
            output_error('删除失败');
        }
    }

}