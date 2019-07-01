<?php

namespace app\admin\controller;

use think\Lang;

class Config extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/config.lang.php');
        Lang::load(APP_PATH . 'admin/lang/zh-cn/admin.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    /**
     * @desc APP设置
     * @author langzhiyao
     * @time 20181018
     */
    public function index(){

        if(session('admin_is_super') !=1 && !in_array(4,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $model_config = model('config');
        if (!request()->isPost()) {
            $list_config = rkcache('config', true);
            $list_config['video_pay_scale'] = json_decode($list_config['video_pay_scale']);
            $this->assign('list_config', $list_config);
            /* 设置卖家当前栏目 */
            $this->setAdminCurItem('index');
            return $this->fetch();
        } else {
            $p_s_t = floatval(input('post.province_agent'))+floatval(input('post.city_agent'))+floatval(input('post.area_agent'))+floatval(input('post.agent'));
            if($p_s_t >= 100){
                //分配错误
                $this->error('视频支付分成比例已超出100%，请重新分配');
            }
            $video_scale = array(
                'province_agent' =>floatval(input('post.province_agent')),
                'city_agent' =>floatval(input('post.city_agent')),
                'area_agent' =>floatval(input('post.area_agent')),
                'agent' =>floatval(input('post.agent')),
            );
            $update_array['bind_student_num'] = input('post.bind_student_num');
            $update_array['f_account_num'] = input('post.f_account_num');
            $update_array['after_start_password'] = input('post.after_start_password');
            //教孩在线支付分成比例
            $update_array['video_pay_scale'] = json_encode($video_scale);
//            halt($update_array);

            $result = $model_config->updateConfig($update_array);
            if ($result) {
                dkcache('config');
                $this->log(lang('ds_edit').lang('web_set'),1);
                $this->success(lang('ds_common_save_succ'), 'Config/index');
            }else{
                $this->log(lang('ds_edit').lang('web_set'),0);
            }
        }
    }

    /**
     * @desc 渠道标识管理
     * @author langzhiyao
     * @time 20181120
     */
    public function channel(){

        if(session('admin_is_super') !=1 && !in_array(4,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $list_count = db('channel')->count();

        $this->assign('list_count',$list_count);
        /* 设置卖家当前栏目 */
        $this->setAdminCurItem('channel');
        return $this->fetch();

    }

    /**
     * @desc 获取渠道标识列表
     * @author langzhiyao
     * @time 20181121
     */
    public function get_channel_list(){

        $page_count = intval(input('post.page_count')) ? intval(input('post.page_count')) : 10;//每页的条数
        $start = intval(input('post.page')) ? (intval(input('post.page'))-1)*$page_count : 0;//开始页数

//        halt($start);
        //查询未绑定的摄像头
        $list = db('channel')->limit($start,$page_count)->order('time DESC')->select();
        $list_count = db('channel')->count();

        $html = '';
        if(!empty($list)){
            foreach($list as $key=>$value){
                $html .= '<tr class="hover">';
                $html .= '<td class="align-center" >'.($key+1).'</td>';
                $html .= '<td class="align-center">'.$value["channel_name"].'</td>';
                $html .= '<td class="align-center">'.$value["channel"].'</td>';
                $html .= '<td class="align-center">'.date('Y-m-d H:i',$value["time"]).'</td>';
                $html .= '<td class="align-center">'.$value["description"].'</td>';

                $html .= '<td class="w150 align-center">
                        <div class="layui-table-cell laytable-cell-9-8">
                           <a href="javascript:void(0)"  class="layui-btn  layui-btn-sm edit" data-id="'.$value["id"].'" lay-event="reset">修改</a>';
                $html .=  '</div></td>';

                $html .= '</tr>';
            }
        }else{
            $html = '<tr class="no_data">
                    <td colspan="15">没有符合条件的记录</td>
                </tr>';
        }

        exit(json_encode(array('html'=>$html,'count'=>$list_count)));

    }

    /**
     * @desc 添加渠道标识
     * @author langzhiyao
     * @time 20181120
     */
    public function add_channel(){
        if(session('admin_is_super') !=1 && !in_array(1,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        if (!request()->isPost()) {
            $this->setAdminCurItem('edit_channel');
            return $this->fetch();
        }else{
            $data = array(
                'channel_name' => trim(input('post.channel_name')),
                'channel' => trim(input('post.channel')),
                'description'=>trim(input('post.description')),
                'time'=>time()
            );
            $result = db('channel')->insert($data);
            if($result){
                exit(json_encode(array('code'=>200,'msg'=>'添加成功')));
            }else{
                exit(json_encode(array('code'=>0,'msg'=>'添加失败')));
            }
        }
    }

    /**
     * @desc 修改渠道标识
     * @author langzhiyao
     * @time 20181120
     */
    public function edit_channel(){
        if(session('admin_is_super') !=1 && !in_array(3,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $id = intval(input('get.id'));
        if (!request()->isPost()) {
            $channel = db('channel')->where('id="' . $id . '"')->find();
            $this->assign('channel', $channel);
            $this->setAdminCurItem('edit_channel');
            return $this->fetch();
        }else{
            $data = array(
                'channel_name' => trim(input('post.channel_name')),
                'channel' => trim(input('post.channel')),
                'description'=>trim(input('post.description')),
                'time'=>time()
            );
            $result = db('channel')->where('id="'.$id.'"')->update($data);
            if($result){
                exit(json_encode(array('code'=>200,'msg'=>'修改成功')));
            }else{
                exit(json_encode(array('code'=>0,'msg'=>'修改失败')));
            }
        }
    }

    /**
     * @desc 版本管理
     * @author langzhiyao
     * @time 20181120
     */
    public function version(){

        if(session('admin_is_super') !=1 && !in_array(4,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $list_count = db('version_update')->where('type=1')->count();
        $list_count2 = db('version_update')->where('type=2')->count();
        $list_count3 = db('version_update')->where('type=3')->count();
        $list_count4 = db('version_update')->where('type=4')->count();

        $this->assign('list_count',$list_count);
        $this->assign('list_count2',$list_count2);
        $this->assign('list_count3',$list_count3);
        $this->assign('list_count4',$list_count4);
        $this->setAdminCurItem('version');
        return $this->fetch();

    }

    /**
     * @desc 获取分页数据
     * @author langzhiyao
     * @time 20190929
     */
    public function get_version_list(){
        $type = intval(input('get.type'));
        $where = ' type="'.$type.'" ';


        $page_count = intval(input('post.page_count')) ? intval(input('post.page_count')) : 10;//每页的条数
        $start = intval(input('post.page')) ? (intval(input('post.page'))-1)*$page_count : 0;//开始页数

//        halt($start);
        //查询
        $list = db('version_update')->where($where)->limit($start,$page_count)->order('time DESC')->select();
        $list_count = db('version_update')->where($where)->count();

        $html = '';
        if(!empty($list)){
            if($type == 1){
                foreach($list as $key=>$value){
                    $html .= '<tr class="hover">';
                    $html .= '<td class="align-center" >'.($key+1).'</td>';
                    $html .= '<td class="align-center">'.$value["content"].'</td>';
                    if($value['mode'] == 1){
                        $html .= '<td class="align-center">建议更新</td>';
                    }else if($value['mode'] == 2){
                        $html .= '<td class="align-center">强制更新</td>';
                    }
                    $html .= '<td class="align-center">'.$value["version_num"].'</td>';
                    $html .= '<td class="align-center">'.$value["channel"].'</td>';
                    $html .= '<td class="align-center">'.$value["package_name"].'</td>';
                    $html .= '<td class="align-center">'.date('Y-m-d H:i',$value["time"]).'</td>';
                    $html .= '</tr>';
                }
            }else{
                foreach($list as $key=>$value){
                    $html .= '<tr class="hover">';
                    $html .= '<td class="align-center" >'.($key+1).'</td>';
                    $html .= '<td class="align-center">'.$value["content"].'</td>';
                    if($value['mode'] == 1){
                        $html .= '<td class="align-center">建议更新</td>';
                    }else if($value['mode'] == 2){
                        $html .= '<td class="align-center">强制更新</td>';
                    }
                    $html .= '<td class="align-center">'.$value["version_num"].'</td>';
                    $html .= '<td class="align-center">'.$value["url"].'</td>';
                    $html .= '<td class="align-center">'.date('Y-m-d H;i',$value["time"]).'</td>';
                    $html .= '</tr>';
                }
            }

        }else{
            $html .= '<tr class="no_data">
                    <td colspan="15">没有符合条件的记录</td>
                </tr>';
        }

        exit(json_encode(array('html'=>$html,'count'=>$list_count)));

    }


    /**
     * @desc Android版本号添加
     * @time 20181121
     * @author langzhiyao
     */
    public function android_version(){
            $channel = db('channel')->select();
            $this->assign('channel',$channel);
            $this->setAdminCurItem('android_version');
            return $this->fetch();
    }

    /**
     * @desc IOS版本号添加
     * @time 20181121
     * @author langzhiyao
     */
        public function ios_version(){
            $this->setAdminCurItem('ios_version');
            return $this->fetch();
        }

    /**
     * @desc 更新版本
     * @author langzhiyao
     * @time 20181121
     */
    public function updateVersion(){
        $type = intval(input('post.type'));
        $version = trim(input('post.version_num'));
        $res = $this->is_version($version,$type);
        if($res){
            if($type == 1){
                $data = array(
                    'type'=>$type,
                    'version_num' => trim(input('post.version_num')),
                    'mode' => intval(input('post.mode')),
                    'url' => trim(input('post.url')),
                    'channel'=>trim(input('post.channel')),
                    'package_name'=>trim(input('post.package_name')),
                    'content'=>trim(input('post.description')),
                    'time'=> time()
                );
                $result = db('version_update')->insert($data);
                if($result){
                    exit(json_encode(array('code'=>200,'msg'=>'Android版本更新成功')));
                }else{
                    exit(json_encode(array('code'=>-1,'msg'=>'Android版本更新失败')));
                }
            }else{
                $data = array(
                    'type'=>$type,
                    'version_num' => trim(input('post.version_num')),
                    'mode' => intval(input('post.mode')),
                    'url'=>trim(input('post.url')),
                    'content'=>trim(input('post.description')),
                    'time'=>time()
                );
                $result = db('version_update')->insert($data);
                if($result){
                    exit(json_encode(array('code'=>200,'msg'=>'IOS版本更新成功')));
                }else{
                    exit(json_encode(array('code'=>-1,'msg'=>'IOS版本更新失败')));
                }
            }
        }else{
            exit(json_encode(array('code'=>-1,'msg'=>'版本号低于原来版本号，无法进行更新')));
        }


    }


    /**
     * @return mixed
     * @desc 站点设置
     */
    public function base() {
        if(session('admin_is_super') !=1 && !in_array(4,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $model_config = model('config');
        if (!request()->isPost()) {
            $list_config = rkcache('config', true);
            $this->assign('list_config', $list_config);
            /* 设置卖家当前栏目 */
            $this->setAdminCurItem('base');
            return $this->fetch();
        } else {
            //上传文件保存路径
            $upload_file = BASE_UPLOAD_PATH . DS . ATTACH_COMMON;
            if (!empty($_FILES['site_logo']['name'])) {
                $file = request()->file('site_logo');
                $info = $file->validate(['ext'=>'jpg,png,gif'])->move($upload_file, 'site_logo');
                if ($info) {
                    $upload['site_logo'] = $info->getFilename();
                } else {
                    // 上传失败获取错误信息
                    $this->error($file->getError());
                }
            }
            if (!empty($upload['site_logo'])) {
                $update_array['site_logo'] = $upload['site_logo'];
            }
            if (!empty($_FILES['member_logo']['name'])) {
                $file = request()->file('member_logo');
                $info = $file->validate(['ext'=>'jpg,png,gif'])->move($upload_file, 'member_logo');
                if ($info) {
                    $upload['member_logo'] = $info->getFilename();
                } else {
                    // 上传失败获取错误信息
                    $this->error($file->getError());
                }
            }
            if (!empty($upload['member_logo'])) {
                $update_array['member_logo'] = $upload['member_logo'];
            }
            if (!empty($_FILES['seller_center_logo']['name'])) {
                $file = request()->file('seller_center_logo');
                $info = $file->validate(['ext'=>'jpg,png,gif'])->move($upload_file, 'seller_center_logo');
                if ($info) {
                    $upload['seller_center_logo'] = $info->getFilename();
                } else {
                    // 上传失败获取错误信息
                    $this->error($file->getError());
                }
            }
            if (!empty($upload['seller_center_logo'])) {
                $update_array['seller_center_logo'] = $upload['seller_center_logo'];
            }
            if (!empty($_FILES['site_mobile_logo']['name'])) {
                $file = request()->file('site_mobile_logo');
                $info = $file->validate(['ext'=>'jpg,png,gif'])->move($upload_file, 'site_mobile_logo');
                if ($info) {
                    $upload['site_mobile_logo'] = $info->getFilename();
                } else {
                    // 上传失败获取错误信息
                    $this->error($file->getError());
                }
            }
            if (!empty($upload['site_mobile_logo'])) {
                $update_array['site_mobile_logo'] = $upload['site_mobile_logo'];
            }
            if (!empty($_FILES['site_logowx']['name'])) {
                $file = request()->file('site_logowx');
                $info = $file->validate(['ext'=>'jpg,png,gif'])->move($upload_file, 'site_logowx');
                if ($info) {
                    $upload['site_logowx'] = $info->getFilename();
                } else {
                    // 上传失败获取错误信息
                    $this->error($file->getError());
                }
            }
            if (!empty($upload['site_logowx'])) {
                $update_array['site_logowx'] = $upload['site_logowx'];
            }

            $update_array['site_name'] = input('post.site_name');
            $update_array['icp_number'] = input('post.icp_number');
            $update_array['site_phone'] = input('post.site_phone');
            $update_array['site_tel400'] = input('post.site_tel400');
            $update_array['site_email'] = input('post.site_email');
            $update_array['flow_static_code'] = input('post.flow_static_code');
            $update_array['site_state'] = input('post.site_state');
            $update_array['closed_reason'] = input('post.closed_reason');
            $update_array['hot_search'] = input('post.hot_search');
            $update_array['node_site_url'] = input('post.node_site_url');
            $result = $model_config->updateConfig($update_array);
            if ($result) {
                dkcache('config');
                $this->log(lang('ds_edit').lang('web_set'),1);
                $this->success(lang('ds_common_save_succ'), 'Config/base');
            }else{
                $this->log(lang('ds_edit').lang('web_set'),0);
            }
        }
    }

    /**
     * 防灌水设置
     */
    public function dump(){
        $model_config = model('config');
        if (!request()->isPost()) {
            $list_config = $model_config->getListConfig();
            $this->assign('list_config', $list_config);
            /* 设置卖家当前栏目 */
            $this->setAdminCurItem('dump');
            return $this->fetch();
        } else {
            $update_array = array();
            $update_array['guest_comment'] = $_POST['guest_comment'];
            $update_array['captcha_status_login'] = isset($_POST['captcha_status_login'])?'1':'';
            $update_array['captcha_status_register'] = isset($_POST['captcha_status_register'])?'1':'';
            $update_array['captcha_status_goodsqa'] = isset($_POST['captcha_status_goodsqa'])?'1':'';
            $result = $model_config->updateConfig($update_array);
            if ($result === true) {
                $this->log(lang('ds_edit').lang('dis_dump'), 1);
                $this->success(lang('ds_common_save_succ'));
            } else {
                $this->log(lang('ds_edit').lang('dis_dump'), 0);
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }


    /**
     * @desc 判断版本号
     * @author langzhiyao
     * @time 20181121
     */
    public function is_version($version,$type){
        $ver = db('version_update')->where('type="'.$type.'"')->order('id DESC')->find();
        if($type == 1){
            $android_version = explode('.',$ver['version_num']);
            $android_num = $android_version[0]*100+$android_version[1]*10+$android_version[2];
            //得到传过来的版本号
            $new_android_version = explode('.',$version);
            $new_android_num = $new_android_version[0]*100+$new_android_version[1]*10+$new_android_version[2];
            if($android_num >= $new_android_num){
                return false;
            }else{
                return true;
            }
        }else{
            $ios_version = explode('.',$ver['version_num']);
            $ios_num = $ios_version[0]*100+$ios_version[1]*10+$ios_version[2];
            //得到传过来的版本号
            $new_ios_version = explode('.',$version);
            $new_ios_num = $new_ios_version[0]*100+$new_ios_version[1]*10+$new_ios_version[2];
            //判断
            if($ios_num >$new_ios_num){
                return false;
            }else{
                return true;
            }
        }

    }


    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '时长/分成/副账号设置',
                'url' => url('Admin/Config/index')
            ),
            array(
                'name' => 'channel',
                'text' => '渠道标识管理',
                'url' => url('Admin/Config/channel')
            ),
            array(
                'name' => 'version',
                'text' => '版本管理',
                'url' => url('Admin/Config/version')
            ),
//            array(
//                'name' => 'base',
//                'text' => '站点设置',
//                'url' => url('Admin/Config/base')
//            ),
//            array(
//                'name' => 'dump',
//                'text' => '防灌水设置',
//                'url' => url('Admin/Config/dump')
//            ),
        );
        return $menu_array;
    }

}
