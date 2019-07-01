<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;
vendor('qiniu.autoload');
use Qiniu\Auth as Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

class Teachvideo extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/teacher.lang.php');
        Lang::load(APP_PATH . 'admin/lang/zh-cn/admin.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    public function index() {
        if(session('admin_is_super') !=1 && !in_array(4,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $model_teach = model('Teachchild');
        $condition = array();
        $admininfo = $this->getAdminInfo();
       /* if($admininfo['admin_id']!=1){
            $condition['admin_company_id'] = $admininfo['admin_company_id'];
        }*/
        $user = input('param.user');//会员账户
        if ($user) {
            $condition['member_mobile'] = array('like', "%" . $user . "%");
        }
        $teacher_status = input('param.teacher_status');//状态
        if ($teacher_status) {
            $condition['t_audit'] = $teacher_status;
        }
        $query_start_time = input('param.query_start_time');
        $query_end_time = input('param.query_end_time');
        if ($query_start_time && $query_end_time) {
            $condition['t_maketime'] = array('between', array(strtotime($query_start_time), strtotime($query_end_time)));
        }elseif($query_start_time){
            $condition['t_maketime'] = array('>',strtotime($query_start_time));
        }elseif($query_end_time){
            $condition['t_maketime'] = array('<',strtotime($query_end_time));
        }
        $type4 = input('param.type4');
        $type3 = input('param.type3');
        $type2 = input('param.type2');
        $type1 = input('param.type1');

        if($type4){
            $condition['t_type4'] = $type4;
            $teachtype4 = db('teachtype')->where(array('gc_parent_id'=>$type3))->select();
            $this->assign('teachtype4', $teachtype4);
            $teachtype3 = db('teachtype')->where(array('gc_parent_id'=>$type2))->select();
            $this->assign('teachtype3', $teachtype3);
            $teachtype2 = db('teachtype')->where(array('gc_parent_id'=>$type1))->select();
            $this->assign('teachtype2', $teachtype2);
        }elseif($type3){
            $condition['t_type3'] = $type3;
            $teachtype4 = db('teachtype')->where(array('gc_parent_id'=>$type3))->select();
            $this->assign('teachtype4', $teachtype4);
            $teachtype3 = db('teachtype')->where(array('gc_parent_id'=>$type2))->select();
            $this->assign('teachtype3', $teachtype3);
            $teachtype2 = db('teachtype')->where(array('gc_parent_id'=>$type1))->select();
            $this->assign('teachtype2', $teachtype2);
        }elseif($type2){
            $condition['t_type2'] = $type2;
            $teachtype2 = db('teachtype')->where(array('gc_parent_id'=>$type1))->select();
            $this->assign('teachtype2', $teachtype2);
            $teachtype3 = db('teachtype')->where(array('gc_parent_id'=>$type2))->select();
            $this->assign('teachtype3', $teachtype3);
        }elseif($type1){
            $condition['t_type'] = $type1;
            $teachtype2 = db('teachtype')->where(array('gc_parent_id'=>$type1))->select();
            $this->assign('teachtype2', $teachtype2);
        }
        $condition['t_del'] = 1;
        $teacher_list = $model_teach->getTeachchildList($condition, 15);
        foreach($teacher_list as $k=>$v){
            if($v['t_typename'] && $v['t_type2name'] && $v['t_type3name'] && $v['t_type4name']){
                $type = $v['t_typename'].'-'.$v['t_type2name'].'-'.$v['t_type3name'].'-'.$v['t_type4name'];
            }elseif($v['t_typename'] && $v['t_type2name'] && $v['t_type3name']){
                $type = $v['t_typename'].'-'.$v['t_type2name'].'-'.$v['t_type3name'];
            }elseif($v['t_typename'] && $v['t_type2name']){
                $type = $v['t_typename'].'-'.$v['t_type2name'];
            }elseif($v['t_typename']){
                $type = $v['t_typename'];
            }
            $teacher_list[$k]['type'] = $type;
        }
        $teachtype = db('teachtype')->where(array('gc_parent_id'=>0))->select();
        $this->assign('teachtype', $teachtype);
        $img_path = "http://".$_SERVER['HTTP_HOST']."/uploads/";
        $this->assign('img_path', $img_path);
        $this->assign('page', $model_teach->page_info->render());
        $this->assign('teach_list', $teacher_list);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    //视频分类搜索
    public function fand_type(){
        $gc_id = intval(input('post.gc_id'));
        if($gc_id){
            $teachtype = db('teachtype')->where(array('gc_parent_id'=>$gc_id))->select();
            echo json_encode($teachtype);
        }
    }

    //审核页面
    public function pass() {
        if(session('admin_is_super') !=1 && !in_array(15,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $teacher_id = input('param.t_id');
        if (empty($teacher_id)) {
            $this->error(lang('param_error'));
        }
        $model_teacher = model('Teachchild');
        $teachinfo = $model_teacher->getTeachchildInfo(array('t_id'=>$teacher_id));
        if($teachinfo['t_typename'] && $teachinfo['t_type2name'] && $teachinfo['t_type3name'] && $teachinfo['t_type4name']){
            $teachinfo['type'] = $teachinfo['t_typename'].'-'.$teachinfo['t_type2name'].'-'.$teachinfo['t_type3name'].'-'.$teachinfo['t_type4name'];
        }elseif($teachinfo['t_typename'] && $teachinfo['t_type2name'] && $teachinfo['t_type3name']){
            $teachinfo['type'] = $teachinfo['t_typename'].'-'.$teachinfo['t_type2name'].'-'.$teachinfo['t_type3name'];
        }elseif($teachinfo['t_typename'] && $teachinfo['t_type2name']){
            $teachinfo['type'] = $teachinfo['t_typename'].'-'.$teachinfo['t_type2name'];
        }elseif($teachinfo['t_typename']){
            $teachinfo['type'] = $teachinfo['t_typename'];
        }
        $path = "http://".$_SERVER['HTTP_HOST']."/uploads/";
        $this->assign('path', $path);
        $this->assign('teachinfo', $teachinfo);
        $this->setAdminCurItem('pass');
        return $this->fetch();
    }

    public function edit() {
        if(session('admin_is_super') !=1 && !in_array(3,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $video_id = input('param.video_id');
        if (empty($video_id)) {
            $this->error(lang('param_error'));
        }
        $model_teacher = model('Teachchild');
        if (!request()->isPost()) {
            $condition['t_id'] = $video_id;
            $video_info = $model_teacher->getTeachchildInfo($condition);
            $this->assign('video_info', $video_info);
            //分类
            $teachtype = db('teachtype')->where(array('gc_parent_id'=>0))->select();
            $this->assign('teachtype', $teachtype);
            if($video_info['t_type2']!=""){
                $teachtype2 = db('teachtype')->where(array('gc_parent_id'=>$video_info['t_type']))->select();
                $this->assign('teachtype2', $teachtype2);
            }
            if($video_info['t_type3']!=""){
                $teachtype3 = db('teachtype')->where(array('gc_parent_id'=>$video_info['t_type2']))->select();
                $this->assign('teachtype3', $teachtype3);
            }
            if($video_info['t_type4']!=""){
                $teachtype4 = db('teachtype')->where(array('gc_parent_id'=>$video_info['t_type3']))->select();
                $this->assign('teachtype4', $teachtype4);
            }
            $img_path = "http://".$_SERVER['HTTP_HOST']."/uploads/";
            $this->assign('path', $img_path);
            $this->setAdminCurItem('edit');
            return $this->fetch();
        } else {
            $data = array(
                't_title' => input('post.video_name'),
                't_desc' => input('post.video_desc'),
                't_profile' => input('post.t_profile'),
                't_price' => input('post.video_price'),
                't_author' => input('post.video_author'),
                't_type' => input('post.type1'),
                't_type2' => input('post.type2'),
                't_type3' => input('post.type3'),
                't_type4' => input('post.type4'),
                't_audit' => 1
            );
            if($_FILES['video_filename']['name']){
                $data['t_picture'] = "home/videoimg/".date("YmdHis",time())."_".time().".png";
                $this->image($data['t_picture']);
            }
            //验证数据  END
            $model_teacher = model('Teachchild');
            $result = $model_teacher->editTeachchild(array('t_id'=>$video_id),$data);
            if ($result) {
                $this->success('编辑成功', 'Teachvideo/index');
            } else {
                $this->error('编辑失败');
            }
        }
    }

    //审核
    public function drop() {
        if(session('admin_is_super') !=1 && !in_array(2,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $admininfo = $this->getAdminInfo();
        $teacher_id = input('param.t_id');
        $status = input('param.t_audit');
        $phone = input('param.phone');
        $reason = input('param.reason')? input('param.reason') : "";
        if (empty($teacher_id)) {
            $this->error(lang('param_error'));
        }
        $model_teacher = model('Teachchild');
        $result = $model_teacher->editTeachchild(array('t_id'=>$teacher_id),array('t_audit'=>$status,'t_failreason'=>$reason,'option_id'=>$admininfo['admin_id'],'option_time'=>time()));
        if ($result) {
            if($status==2){
                //审核结果给用户发送短信提醒
                if(preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/i', $phone)){
                    if(empty($reason)){
                        $content = '您的教孩视频审核未通过。请登录想见孩app重新上传!';
                    }else{
                        $content = '您的教孩视频审核未通过，失败原因是：'.$reason."。请登录想见孩app重新上传!";
                    }
                    $sms = new \sendmsg\sdk\SmsApi();
                    $send = $sms->sendSMS($phone,$content);
                    if(!$send){
                        $this->error('给用户发送短信失败 ');
                    }
                }
                $this->success(lang('teacher_index_noapass'), 'Teachvideo/index');
            }else{
                //审核结果给用户发送短信提醒
                if(preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/i', $phone)){
                    $content = '您的教孩视频已审核通过，感谢您的支持!';
                    $sms = new \sendmsg\sdk\SmsApi();
                    $send = $sms->sendSMS($phone,$content);
                    if(!$send){
                        $this->error('给用户发送短信失败 ');
                    }
                }
                $this->success(lang('teacher_index_apass'), 'Teachvideo/index');
            }
        } else {
            $this->error('审核失败');
        }
    }

    /*
     * 设置推荐视频
     * */
    public function recom() {
        if(session('admin_is_super') !=1 && !in_array(2,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $t_id = input('param.t_id');
        $recommend = input('param.t_recommend');
        if (empty($t_id)) {
            $this->error(lang('param_error'));
        }
        $model_teachchild = Model('Teachchild');
        $result = $model_teachchild->editTeachchild(array('t_id'=>$t_id),array('t_recommend'=>$recommend));
        if ($result) {
            $this->success("推荐设置成功", 'Teachvideo/index');
        } else {
            $this->error('推荐设置失败');
        }
    }

    /*
     * 上传视频
     * */
    public function add() {
        if(session('admin_is_super') !=1 && !in_array(1,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $admininfo = $this->getAdminInfo();
        $model_class = model('Teachchild');
        if (!request()->isPost()) {
            $teachtype = db('teachtype')->where(array('gc_parent_id'=>0))->select();
            $this->assign('teachtype', $teachtype);
            $this->setAdminCurItem('add');
            return $this->fetch();
        } else {
            $insert_array = array();
            $insert_array['t_title'] = input('post.video_name');
            $insert_array['t_desc'] = input('post.video_desc');
            $insert_array['t_profile'] = input('post.t_profile');
            $insert_array['t_price'] = input('post.video_price');
            $insert_array['t_author'] = input('post.video_author');
            $insert_array['t_type'] = input('post.type1');
            $insert_array['t_type2'] = input('post.type2');
            $insert_array['t_type3'] = input('post.type3');
            $insert_array['t_type4'] = input('post.type4');
            $insert_array['t_audit'] = 1;
            $insert_array['member_mobile'] = "后台";
            $insert_array['t_maketime'] = time();
            $insert_array['admin_id'] = $admininfo['admin_id'];
            $insert_array['admin_company_id'] = $admininfo['admin_company_id'];
            //上传视频封面图
            if($_FILES['video_filename']['name']){
                $insert_array['t_picture'] = "home/videoimg/".date("YmdHis",time())."_".time().".png";
                $this->image($insert_array['t_picture']);
            }
            //上传视频
            $insert_array['t_url'] = input('post.path')?input('post.path'):"";//视频路径
            $insert_array['t_videoimg'] = input('post.pic')?input('post.pic'):"";;//默认封面图
            $insert_array['t_timelength'] = input('post.time')?input('post.time'):"";//视频时长

            //验证数据  END
            if($insert_array['t_url']!="" && $insert_array['t_videoimg']!=""){
                $result = $model_class->addTeachchild($insert_array);
            }
            if ($result) {
                $this->success(lang('ds_common_save_succ'), url('Admin/Teachvideo/index'));
            } else {
                $this->error(lang('ds_common_save_fail'), url('Admin/Teachvideo/index'));
            }

        }
    }

    /*
     * 上传图片
     * */
    public function image($picture){
        //上传路径
        $uploadimg_path = substr(str_replace("\\","/",$_SERVER['SCRIPT_FILENAME']),'0','-9')."uploads/";
        //检查是否有该文件夹，如果没有就创建
        if(!is_dir($uploadimg_path."home/videoimg/")){
            mkdir($uploadimg_path."home/videoimg/",0777,true);
        }
        //允许上传的文件格式
        $tp = array("image/gif","image/jpeg","image/jpg","image/png","image/*");
        //检查上传文件是否在允许上传的类型
        if(!in_array($_FILES["video_filename"]["type"],$tp))
        {
            $this->error(lang("格式不正确"), url('Admin/Teachvideo/index'));
        }
        if (!empty($_FILES['video_filename']['name'])) {
            $upload = move_uploaded_file($_FILES["video_filename"]["tmp_name"], $uploadimg_path . $picture);
            if($upload){
                return $upload;
            }else{
                $this->error(lang("上传视频封面图失败"), url('Admin/Teachvideo/index'));
            }
        }
    }

    //上传视频到七牛
    public function video_data(){
        $fileTmpLoc = $_FILES["file1"]["tmp_name"]; // File in the PHP tmp folder
        if (!$fileTmpLoc) { // if file not chosen
            echo "ERROR: Please browse for a file before clicking the upload button.";
            exit();
        }
        //获取文件的名字//
        $key = "admin_video_".date("YmdHis",time())."_".time().".mp4";
        //获取token值
        $accessKey = 'V0Su976FmQMUBKKf9TLZIYao34G-l6RN_7zxhfFV';
        $secretKey = 'xvVkqpveV8myyeHYP4c_tghcPRUKUmvc2EqbOumG';
        $WAILIAN='avatar.xiangjianhai.com';
        // 初始化签权对象
        $auth = new Auth($accessKey, $secretKey);
        $bucket = 'avatar';
        // 生成上传Token
        $token = $auth->uploadToken($bucket);
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($token, $key, $fileTmpLoc);
        // 获取视频的时长
        // 第一步先获取到到的是关于视频所有信息的json字符串
        $shichang = file_get_contents('http://'.$WAILIAN.'/'.$key.'?avinfo');
        // 第二部转化为对象
        $shi =json_decode($shichang);
        // 第三部从中取出视频的时长
        $chang = $shi->format->duration;
        // 获取封面
        $vpic = 'http://'.$WAILIAN.'/'.$key.'?vframe/jpg/offset/1';
        $path ='http://'.$WAILIAN.'/'.$ret['key'];
        $data = [
            'path' => $path,
            'pic' =>$vpic,
            'time'=>$chang,
        ];
        echo json_encode($data);
        exit;
    }

    //删除
    public function del() {
        if(session('admin_is_super') !=1 && !in_array(2,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $video_id = input('param.video_id');
        if (empty($video_id)) {
            $this->error(lang('param_error'));
        }
        $model_video = Model('Teachchild');
        $result = $model_video->editTeachchild(array('t_id'=>$video_id),array('t_del'=>2));
        if ($result) {
            $this->success(lang('ds_common_del_succ'), 'Teachvideo/index');
        } else {
            $this->error('删除失败');
        }
    }

    //图片大图
    public function view()
    {
        $video_id = input('param.id');
        if (empty($video_id)) {
            $this->error(lang('param_error'));
        }
        $video_info = db("teachchild")->field("t_title,t_picture,t_videoimg,t_type")->where(array('t_id'=>$video_id))->find();
        $path = "http://".$_SERVER['HTTP_HOST']."/uploads/";
        $this->assign('path',$path);
        $this->assign('video_info', $video_info);
        $this->setAdminCurItem('view');
        return $this->fetch();
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '管理',
                'url' => url('Admin/Teachvideo/index')
            ),
        );
        if(session('admin_is_super') ==1 || in_array(1,$this->action )){
            if (request()->action() == 'add' || request()->action() == 'index') {
                $menu_array[] = array(
                    'name' => 'add',
                    'text' => '上传视频',
                    'url' => url('Admin/Teachvideo/add')
                );
            }
        }
        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit',
                'text' => '编辑',
                'url' => url('Admin/Teachvideo/edit')
            );
        }
        if (request()->action() == 'pass') {
            $menu_array[] = array(
                'name' => 'pass',
                'text' => '审核',
                'url' => url('Admin/Teachvideo/pass')
            );
        }
        return $menu_array;
    }

}

?>
