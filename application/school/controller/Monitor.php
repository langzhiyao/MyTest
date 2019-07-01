<?php

namespace app\school\controller;

use think\Lang;
use think\Validate;

class Monitor extends AdminControl
{
    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'school/lang/zh-cn/look.lang.php');
        //获取省份
        $province = db('area')->field('area_id,area_parent_id,area_name')->where('area_parent_id=0')->select();
        //获取学校
        $school = db('school')->field('schoolid,name')->select();
//获取当前角色对当前子目录的权限
        $class_name = strtolower(end(explode('\\',__CLASS__)));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('school_admin_gid') ,$perm_id);
        $this->assign('action',$action);

        $this->assign('school',$school);
        $this->assign('province',$province);
    }

    /**
     * @desc 网络监控
     * @author 郎志耀
     * @time 20180926
     */
    public function monitor(){
        if(session('school_admin_is_super') !=1 && !in_array('4',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        if($_POST){
            //print_r($_POST);
        }
        $this->setAdminCurItem('monitor');
        return $this->fetch('monitor');
    }
    /**
     * 开启rtmp
     */
    public function addrtmp(){
        $camera_update=Model('camera');
        $where=array();
        $cid=intval(input('post.cid'));
        $where['cid']=$cid;
        $update=array();
        $is_rtmp=intval(input('post.is_rtmp'));
        $update['is_rtmp']=$is_rtmp;
        $vlink = new Vomont();
        $res= $vlink->SetLogin();
        $accountid=$res['accountid'];
        $condition=array();
        $condition['cid']=$cid;
        $ress=$camera_update->getOnePkg($condition);
        if($is_rtmp==2) {
            $datas = $vlink->Livestatus($accountid,$ress['id']);
            $update['liveid']=$datas['liveid'];
            if($ress['rtmpplayurl']=='') {
                time_sleep_until(time() + 3);
                $channels = $ress['deviceid'] . '-' . $ress['channelid'] . ',';
                $rtmp = $vlink->Resources($accountid, $channels);
                $update['rtmpplayurl'] = $rtmp['channels'][0]['rtmpplayurl'];
            }
        }else{
            $datas=$vlink->Liveend($accountid,$ress['liveid']);
            $update['liveid']='';
        }
        $res=$camera_update->editCamera($where,$update);
        //print_r($res);exit;
        return $res;
    }

}