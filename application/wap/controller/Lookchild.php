<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/16
 * Time: 20:12
 */

namespace app\wap\controller;
use think\Model;
use vomont\Vomont;

class Lookchild extends MobileMall
{
    //看孩
    public function index(){
        $member_id = intval(input('post.member_id'));
        if($member_id){
            $member_info = db("member")->where(array("member_id"=>$member_id))->find();
            $member_id = $member_info['is_owner']!=0 ? $member_info['is_owner'] : $member_id;
        }
        $student = Model("Student");
        if(empty($member_id)){
            //请登录
            $data['status']=1;
            $data = !empty($data)?[$data]:$data;
            output_data($data);
        }else{
            $result = db("student")->alias('s')
                ->join('__SCHOOL__ sc','sc.schoolid=s.s_schoolid','LEFT')
                ->join('__CLASS__ cl','cl.classid=s.s_classid','LEFT')
                ->field('s.s_id,s.s_name,s.s_region,sc.schoolid,sc.name,sc.res_group_id,cl.classid,cl.classname,cl.classCard,cl.res_group_id as clres_group_id')
                ->where(array("s_ownerAccount"=>$member_id,'s_del'=>1))->select();
//            $student=model('student');
//            $result=$student->getAllChilds($member_id);
            if(empty($result)){
                if($member_info['is_owner']==0){
                    //请绑定学生
                    $data['status']=2;
                    $data = !empty($data)?[$data]:$data;
                    output_data($data);
                }
            }else {
                foreach($result as $v){
                    $res[$v['s_id']]=$v['s_name'];
                }
                $data['name']=$res;
                $sid  = input('post.sid');
                $packtime=model('packagetime');
                $condition=array();
                $condition['pkg_type']=1;
                if(empty($sid)) {
                    $condition['s_id']=$result[0]['s_id'];
                    $stu=$packtime->getOnePkg($condition);
                    if(empty($stu)){
                        //请先购买看孩套餐
                        $data['status']=3;
                    }else{
                        if($stu['end_time']<time()){
                            //看孩套餐已到期
                            $data['status']=4;
                        }else{
                            //获取成功
                            $data['status']=0;
                        }
                    }
                    $schoolid = $result[0]['res_group_id'];
                    $classid=$result[0]['clres_group_id'];
                }else{
                    $condition['s_id']=$sid;
                    $stu=$packtime->getOnePkg($condition);
                    if(empty($stu)){
                        //请先购买看孩套餐
                        $data['status']=3;
                    }else{
                        if($stu['end_time']<time()){
                            //看孩套餐已到期
                            $data['status']=4;
                        }else{
                            //获取成功
                            $data['status']=0;
                        }
                    }
                    $str=$student->getChildrenInfoByIdes($sid);
                    $schoolid=$str['res_group_id'];
                    $classid=$str['clres_group_id'];
                }
                $user['ip']='117.78.26.155';
                $user['port']='9001';
                $user['username']='test';
                $user['pwd']='123456';
                $camera_model=Model('camera');
                $condition=array();
                $condition['parentid']=$classid;
                $conditions['parentid']=$schoolid;
                $html=$camera_model->getCameras($condition,$conditions,'ability,channelid,companyid,deviceid,id,name,online,parentid,privilege,type,usernum,is_classroom,status,begintime,endtime');
                $date=date('H:i',time());
                foreach($html as $k=> $v){
                    if($v['online']==0){
                        $html[$k]['status']=2;
                    }else{
                        if($v['status']==1){
                            if(!empty($v['begintime'])&&!empty($v['endtime'])){
                                $begintime=date('H:i',$v['begintime']);
                                $endtime=date('H:i',$v['endtime']);
                                if($date<$begintime||$date>$endtime){
                                 $html[$k]['status']=2;
                                }
                                $html[$k]['begintime']=$begintime;
                                $html[$k]['endtime']=$endtime;
                            }else{
                                $html[$k]['begintime']='';
                                $html[$k]['endtime']='';
                            }
                        }else{
                            $html[$k]['begintime']='';
                            $html[$k]['endtime']='';
                        }
                    }
                }
                $data['camera']=!empty($html)?$html:[];
                $data['logo']=$user;
                $data = !empty($data)?[$data]:$data;
                output_data($data);
                }
        }
    }
//获取支付订单详情
    public function getDetail(){
        $orderSn = trim(input('post.orderSn'));
        $member_id = intval(input('post.member_id'));

        $result = db('packagesorder')->where('pay_sn="'.$orderSn.'" AND buyer_id="'.$member_id.'"')->field('pkg_pirce,s_id')->find();
        output_data($result);
    }



}