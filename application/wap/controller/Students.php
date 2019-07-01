<?php

namespace app\wap\controller;

use think\captcha\Captcha;

class Students extends MobileMember{

    /**
     * 学校简介
     * $member 会员id
     */
    public function get_student_info() {

        $member_info = db('member')->where(array("member_id"=>$this->member_info['member_id']))->find();
        $member = $member_info['is_owner']==0 ? $this->member_info['member_id'] : $member_info['is_owner'];
        $childs = db('student')->alias('s')
            ->join('__SCHOOL__ sc','sc.schoolid=s.s_schoolid','LEFT')
            ->join('__CLASS__ cl','cl.classid=s.s_classid','LEFT')
            ->field("s.s_id,s.s_name,s.s_schoolid,sc.name,s.s_classid,cl.classname,cl.classCard")
            ->where(array('s_del'=>1,'s_ownerAccount'=>$member))->select();
        output_data($childs);
    }

    /**
     * 获取学校 校车信息
     */
    public function GetSchoolBusInfo(){
        $childId = input('post.child_id');
        $Student = model('Student');
        $childInfo = $Student->getChildrenInfoById($childId);
        $Bus = model('Schoolbus');
        $condition = array(
            'sc_id' => $childInfo['schoolid'],
            'is_del' => 1
        );
        $businfo = $Bus->get_schoolbus_List($condition);
        foreach ($businfo as $k => &$v) {
            $v['bus_line']=json_decode($v['bus_line']);
        }
        unset($v);
        output_data($businfo);
    }

    /**
     * 获取学校 食谱信息
     */
    public function GetSchoolFoodInfo(){
        $childId = input('post.child_id');
        $Student = model('Student');
        $childInfo = $Student->getChildrenInfoById($childId);
        $Food = model('Schoolfood');
        $condition = array(
            'sc_id' => $childInfo['schoolid'],
            'is_del' => 1
        );
        $businfo = $Food->get_schoolfood_List($condition);
        
        output_data($businfo);
    }


}