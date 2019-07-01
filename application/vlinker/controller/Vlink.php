<?php

namespace app\vlinker\controller;

use think\Lang;
use vomont\Vomont;//调用物盟SDK

class Vlink extends BaseController {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'wumeng/lang/zh-cn/index.lang.php');
    }
    
    public function abcde(){
    	$vlink = new Vomont();
    	$Login= $vlink->SetLogin();
    	p($vlink);

    }
    /**
     * 添加学校--获取学校列表
     */
    public function addSchool(){
    	$input = input();
    	$School = model('School');
    	$areaid = $input['areaid'];
    	$condition = array(
    		'areaid' => $areaid,
            'isdel'  => 1,
    		'res_group_id'  => 0,
    	);
	    $field = 'schoolid as res_group_id,name as res_group_name,areaid';
    	$schoolList = $School->getAllAchool($condition,$field);
    	output_data($schoolList);
    }

    /**
     * 编辑学校--获取学校列表
     * @return [type] [description]
     */
    public function editSchool(){
    	$input = input();
    	$School = model('School');
    	$areaid = $input['areaid'];
    	$field = 'schoolid as res_group_id,name as res_group_name,areaid';
    	$schoolInfo = $School->getSchoolInfo(array('isdel'=>1,'res_group_id'=>$areaid),$field);
    	$condition = array(
    		'areaid' => empty($schoolInfo)?$areaid:$schoolInfo['areaid'],
    		'isdel'  => 1,
    	);
    	$schoolList = $School->getAllAchool($condition,$field);
    	output_data($schoolList);
    }

    /**
     * 添加班级--获取班级列表
     */
    public function addClass(){
    	$input = input();
    	$Class = model('Classes');
    	$areaid = $input['areaid'];
		$classList = $Class->getClassInfoBySchool($areaid);
		output_data($classList);
    }

    /**
     * 编辑班级--获取班级列表
     * @return [type] [description]
     */
    public function editClass(){
    	$input = input();
    	$Class = model('Classes');
    	$areaid = $input['areaid'];
    	$field = 'schoolid,classid,classname';
    	$classInfo = $Class->getClassInfo(array('isdel'=>1,'res_group_id'=>$areaid),array(),$field);
    	$areaid = empty($classInfo)?$areaid:$classInfo['schoolid'];
		$classList = $Class->getClassInfoBySchool($areaid);
		output_data($classList);
    }

    /**
     * 学校资源替换 -- 对应资源id替换
     * @return [type] [description]
     */
    public function editSchoolNotify(){
    	$input = input();
    	$resid = $input['resid'];//流媒体资源id
    	//已替换的班级id
    	$schoolid = $input['changeid'];
    	$School = model('School');
    	$areaid = $input['areaid'];
    	$field = '*';
    	$classInfo = $School->getSchoolInfo(array('res_group_id'=>$resid),$field);
    	$delresid = $School->school_set($classInfo['schoolid'],'res_group_id',0);
    	$addresid = $School->school_set($schoolid,'res_group_id',$resid);
    	if ($delresid && $addresid) {
    		output_data(array('state'=>'true'));
    	}else{
    		output_error('false');
    	}

    }

    /**
     * 学校资源增加 对应
     */
    public function addSchoolNotify(){
    	$input = input();
    	$schoolid = $input['changeid'];
    	$groupres = $input['resid']['groupres'];
    	$res_group_id = $groupres['id'];
    	$School = model('School');
    	$addres = $School->school_set($schoolid,'res_group_id',$res_group_id);
		if ($addres) {
			output_data($addres);
		}else{
			output_error('增加学校资源失败');
		}

    }

    /**
     * 班级资源增加 对应
     */
    public function addClassNotify(){
    	$input = input();
		$classid = $input['changeid'];
    	$groupres = $input['resid']['groupres'];
    	$res_group_id = $groupres['id'];
    	$Class = model('Classes');
    	$addres = $Class->class_set($classid,'res_group_id',$res_group_id);
		if ($addres) {
			output_data($addres);
		}else{
			output_error('增加班级资源失败');
		}

    }

    /**
     * 班级资源替换--对应id替换
     * @return [type] [description]
     */
    public function editClassNotify(){
    	$input = input();
    	$resid = $input['resid'];//流媒体资源id
    	//已替换的班级id
    	$classid = $input['changeid'];
    	$Class = model('Classes');
    	$areaid = $input['areaid'];
    	$field = 'schoolid,classid,classname';
    	$classInfo = $Class->getClassInfo(array('res_group_id'=>$resid),array(),$field);
    	$delresid = $Class->class_set($classInfo['classid'],'res_group_id',0);
    	$addresid = $Class->class_set($classid,'res_group_id',$resid);
    	if ($delresid && $addresid) {
    		output_data(array('state'=>'true'));
    	}else{
    		output_error('false');
    	}
    }
}
