<?php

namespace app\school\controller;

use think\Lang;
use think\Validate;

vendor('qiniu.autoload');
use Qiniu\Auth as Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;


class Find extends AdminControl {

    const EXPORT_SIZE = 1000;

//    public function _initialize()
//    {
//        parent::_initialize();
//        Lang::load(APP_PATH . 'school/lang/zh-cn/admin.lang.php');
//        //获取当前角色对当前子目录的权限
//        $class_name = strtolower(end(explode('\\',__CLASS__)));
//        $perm_id = $this->get_permid($class_name);
////        halt($perm_id);
//        $this->action = $action = $this->get_role_perms(session('school_admin_gid') ,$perm_id);
//        $this->assign('action',$action);
//    }
    /**
     *
     * 晒心情管理列表
     */
    public function index()
    {
        $img_path = "http://".$_SERVER['HTTP_HOST']."/uploads/";
        //晒心情列表
        $where = array();
        //判断登陆角色
        //$adminUser = db('admin')->field('admin_company_id')->where('admin_id = "'.session("admin_id").'"')->find();
//        if($adminUser['admin_company_id'] != 1){
//            $condition['o_id'] = $adminUser['admin_company_id'];
//        }
//
        if (!empty($_POST['account'])) {
            $member_name=input('param.account');
            $where['member_name']=array('like', '%' . trim($member_name) . '%');
            $this->assign('member_name',$member_name);
        }
        if(!empty($_POST['del'])){
            $del=input('param.del');
            $where['del']=$del;
            $this->assign('del',$del);
        }
        $stime = input('param.stime')?strtotime(input('param.stime')):0;
        $etime = input('param.etime')?strtotime(input('param.etime')):0;
        if ($stime > 0 && $etime>0){
            $etimes=$etime+86400;
            $where['pubtime'] = array('between',array($stime,$etimes));
            $stime=date('Y-m-d',$stime);
            $etime=date('Y-m-d',$etime);
            $this->assign('stime',$stime);
            $this->assign('etime',$etime);
        }elseif ($stime > 0){
            $where['pubtime'] = array('egt',$stime);
            $stime=date('Y-m-d',$stime);
            $this->assign('stime',$stime);
        }elseif ($etime > 0){
            $etimes=$etime+86400;
            $where['pubtime'] = array('elt',$etimes);
            $etime=date('Y-m-d',$etime);
            $this->assign('etime',$etime);
        }
        $mood_list = db('mood')->alias('m')->join('__MEMBER__ b', 'b.member_id = m.member_id', 'LEFT')->where($where)->order('id desc')->paginate(15,false,['query' => request()->param()]);
        $model_student = model('Student');
        $list=$mood_list->items();
        foreach($list as $k=>$v){
            $student=$model_student->getAllChilds($v['member_id']);
            if($student[0]!='') {
                $list[$k]['student'] = $student[0];
            }else{
                $list[$k]['student'] = '';
            }
            $list[$k]['image']=explode(',',$v['image']);
        }

        $this->assign('path',$img_path);
        $this->assign('page', $mood_list->render());
        $this->assign('mood_list', $list);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }
 
    /**
     * 获取分/子公司栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
            $menu_array = array(
                array(
                    'name' => 'index',
                    'text' => '晒心情管理',
                    'url' => url('School/Find/index')
                )
            );
        return $menu_array;
    }
    //心情详情页
    public function view()
    {
        $where = array();
        $where['id']=$_GET['id'];
        $mood = db('mood')->alias('m')->join('__MEMBER__ b', 'b.member_id = m.member_id', 'LEFT')->where($where)->find();
        $mood['image']=explode(',',$mood['image']);
        $img_path = "http://".$_SERVER['HTTP_HOST']."/uploads/";
        $contient=array();
        $contient['v_mid']=$mood['id'];
        $moodview=db('moodview')->alias('m')->join('__MEMBER__ b', 'b.member_id = m.v_memberid', 'LEFT')->where($contient)->select();
        $this->assign('moodview',$moodview);
        $this->assign('path',$img_path);
        $this->assign('mview', $mood);
        $this->setAdminCurItem('view');
        return $this->fetch();
    }
}