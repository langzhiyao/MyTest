<?php

namespace app\school\controller;

use think\Lang;
use think\Validate;
/**
 * 套餐展示
 */
class Schooldesc extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'school/lang/zh-cn/admin.lang.php');
    }

    public function schooldesc_manage(){
        $admininfo = $this->getAdminInfo();
        if($admininfo['admin_gid']!=5){
            $this->error(lang('ds_assign_right'));
        }
        //类型
        $model_schooltype = model('Schooltype');
        $schoolType = $model_schooltype->get_sctype_List(array('sc_enabled'=>1));
        $this->assign('schoolType', $schoolType);
        //地区信息
        $region_list = db('area')->where('area_parent_id','0')->select();
        $desc=model('Schooldesc');
        if (!request()->isPost()) {
            if($admininfo['admin_id']!=1){
                if(!empty($admininfo['admin_school_id'])){
                    $schoolid = $admininfo['admin_school_id'];
                }else{
                    $schoolid = 1;
                }
            }
            $result=$desc->getDescInfo(array('s_sid'=>$schoolid));
            if($result){
                $actions='edit';
             } else {
                $actions='add';
            }
            $model_school = Model('school');
            $condition=array();
            $condition['schoolid'] = $schoolid;
            $school_array = $model_school->getSchoolInfo($condition);
            $school_array['typeid']=explode(',',$school_array['typeid']);
            $img_path = "http://".$_SERVER['HTTP_HOST']."/uploads/";
            $this->assign('result',$result);
            $this->assign('img_path',$img_path);
            $this->assign('school_array',$school_array);
            $this->assign('actions',$actions);
            $this->assign('region_list', $region_list);
            return $this->fetch('desc');
        }else{
            $param['s_sid']   = input('post.s_sid');
            $param['s_type']    = input('post.s_type');
            $param['s_present'] = input('post.s_present');
            $param['s_teacher']    = input('post.s_teacher');
            $param['s_desc']    = input('post.s_desc');
            $param['s_createtime']      = date("Y-m-d H:i:s",time());
            $data=array();
            //print_r($_POST);exit;
            if(!empty($_POST['area_id'])) {
                $city_id = db('area')->where('area_id', input('post.area_id'))->find();
                $data['cityid'] = $city_id['area_parent_id'];
                $province_id = db('area')->where('area_id', $city_id['area_parent_id'])->find();
                $data['provinceid'] = $province_id['area_parent_id'];
                $data['region']=input('post.area_info');
                $data['areaid']=input('post.area_id');
            }
            $data['address']=input('post.school_address');
            $data['createtime']=date("Y-m-d H:i:s",time());
            $data['typeid']=implode(",",$_POST['school_type']);
            $school_id=input('post.s_sid');
            $model_school = Model('school');
            $model_school->editSchool($data,array('schoolid'=>$school_id));
            //上传简介封面图
            if($_FILES['file']['name']){
                $_FILES['file']['name'] = "home/schooldescimg/".date("YmdHis",time())."_".time().".".end(explode('.', $_FILES['file']['name']));
                $param['s_img']=$_FILES['file']['name'];
                $this->upload($_FILES);
            }
            $admininfo = $this->getAdminInfo();
            $param['s_oid']=$admininfo['admin_id'];
            switch (input('actions')) {
                case 'edit'://改
                    $s_id=input('post.s_id');
                    $result=$desc->editDesc(array('s_id'=>$s_id),$param);
                    if ($result) {
                        $this->success('修改成功', 'Schooldesc/schooldesc_manage');
                    } else {
                        $this->error('修改失败');
                    }
                case 'add':
                    $result=$desc->addDesc($param);
                    if ($result) {
                        $this->success('添加成功', 'Schooldesc/schooldesc_manage');
                    } else {
                        $this->error('添加失败');
                    }
            }
        }
    }
    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'schooldesc_manage',
                'text' => lang('schooldesc_manage'),
                'url' => url('School/Course/schooldesc_manage')
            )
        );

        return $menu_array;
    }
    /**
* 上传图片
* */
    public function upload($data){
        //上传路径
        $uploadimg_path = substr(str_replace("\\","/",$_SERVER['SCRIPT_FILENAME']),'0','-9')."uploads/";
        //检查是否有该文件夹，如果没有就创建
        if(!is_dir($uploadimg_path."home/schooldescimg/")){
            mkdir($uploadimg_path."home/schooldescimg/",0777,true);
        }
        //允许上传的文件格式
        $tp = array("image/gif","image/jpeg","image/jpg","image/png");
        //检查上传文件是否在允许上传的类型
        if(!in_array($data["file"]["type"],$tp))
        {
            return '图片上传类型不符合，请重新上传';
        }
        if($data["file"]["size"] < 8*1024*1024) {
            if (!empty($data['file']['name'])) {
                $upload = move_uploaded_file($data["file"]["tmp_name"], $uploadimg_path . $data['file']['name']);
                if ($upload) {
                    return $upload;
                } else {
                    return '上传图片失败';
                }
            }
        }else{
            return '图片上传大小不允许超过8M，请重新上传';
        }
    }

}

?>
