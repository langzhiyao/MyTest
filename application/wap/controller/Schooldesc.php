<?php

namespace app\wap\controller;

use app\mobile\controller\MobileMember;
use think\captcha\Captcha;

class Schooldesc extends MobileMember
{


    /**
     * 学校简介
     * $member 会员id
     */
    public function detailinfo() {

//        $member_id  = intval(input('post.member_id'));
//        if (empty($member_id)) {
//            output_error('参数有误');
//        }
//        $student=model('student');
//        $res=$student->getAllChilds($member_id);
//        $school_id=$res['schoolid'];
        $school_id  = intval(input('post.school_id'));
        if (empty($school_id)) {
            output_error('参数有误');
        }
        $school=model('school');
        $logindata = $school->getSchoolById($school_id);
        //学校类型
        $type = explode(',',$logindata['typeid']);
        $a = '';
        if(!empty($type)){
            foreach($type as $k=>$v){
                if($k == 0){
                    $a .= $this->get_schoolType($v);
                }else{
                    $a .= '&'.$this->get_schoolType($v);
                }
            }
        }

        $desc=model('Schooldesc');
        $where=array();
        $where['s_sid']=$school_id;
        $data=$desc->getDescInfo($where);
        $path = "http://".$_SERVER['HTTP_HOST']."/uploads/";
        if(!empty($data['s_img'])){
            $data['s_img'] = $path.$data['s_img'];
        }else{
            $data['s_present']='';
            $data['s_teacher']='';
        }
        $data['name']=$logindata['name'];
        $data['region']=$logindata['region'];
        $data['address']=$logindata['address'];
        $data['school_type']=$a;
        if($data){
            output_data($data);
        }else{
            output_data(array());
        }


    }

//获取学校类型
function get_schoolType($id){
        $res = db('schooltype')->where('sc_id="'.$id.'"')->field('sc_type')->find();
        return $res['sc_type'];
}

}