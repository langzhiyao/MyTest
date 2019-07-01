<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Adv extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/adv.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }
    /**
     *
     * 广告管理
     */
    public function adv() {
        if(session('admin_is_super') !=1 && !in_array('4',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $adv = Model('adv');
        $ap_id = intval(input('param.ap_id'));
        if (!request()->isPost()) {
            $condition = array();
            $condition['is_allow'] = '1';
            if ($ap_id) {
                $condition['ap_id'] = $ap_id;
            }
            $condition['is_show'] = 1;
            $adv_info = $adv->getList($condition, 20, '', '');
            $this->assign('adv_info', $adv_info);
            $ap_list = $adv->getApList();
            $this->assign('ap_list', $ap_list);
            if ($ap_id) {
                $ap = db('advposition')->where('ap_id = "'.$ap_id.'" AND is_show=1')->find();
                $this->assign('ap_name', $ap['ap_name']);
            } else {
                $this->assign('ap_name', '');
            }

            $this->assign('page', $adv->page_info->render());
            $this->setAdminCurItem('adv');
            return $this->fetch('adv_index');
        }
    }

    /**
     * 管理员添加广告
     */
    public function adv_add() {
        if(session('admin_is_super') !=1 && !in_array('1',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        if (!request()->isPost()) {
            $adv = Model('adv');
            $ap_list = $adv->getApList();
            $this->assign('ap_list', $ap_list);
            $adv = array(
                'adv_start_date' => time(),
                'adv_end_date' => time() + 24 * 3600 * 365,
            );
            $this->assign('adv', $adv);
            $this->setAdminCurItem('adv_add');
            return $this->fetch('adv_form');
        } else {
            $adv = Model('adv');

            $insert_array['adv_title'] = trim($_POST['adv_name']);
            $insert_array['ap_id'] = intval($_POST['ap_id']);
            $insert_array['adv_start_date'] = $this->getunixtime($_POST['adv_start_date']);
            $insert_array['adv_end_date'] = $this->getunixtime($_POST['adv_end_date']);
            $insert_array['adv_link'] = input('post.adv_link');
            $insert_array['is_allow'] = '1';


            //上传文件保存路径
            $upload_file = BASE_UPLOAD_PATH . DS . ATTACH_ADV;
            if (!empty($_FILES['adv_code']['name'])) {
                $file = request()->file('adv_code');
                $info = $file->rule('uniqid')->validate(['ext' => 'jpg,png,gif'])->move($upload_file);
                if ($info) {
                    $insert_array['adv_code'] = $info->getFilename();
                } else {
                    // 上传失败获取错误信息
                    $this->error($file->getError());
                }
            }

            //验证数据  BEGIN
            $rule = [
                ['adv_title', 'require', lang('adv_can_not_null')],
                ['ap_id', 'require', lang('must_select_ap')],
                ['adv_start_date', 'require', lang('must_select_start_time')],
                ['adv_end_date', 'require', lang('must_select_end_time')],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($insert_array);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END
            //广告信息入库
            $result = $adv->adv_add($insert_array);
            //更新相应广告位所拥有的广告数量
            $ap_list = db('advposition')->where('ap_id', intval(input('post.ap_id')))->find();
            $adv_num = $ap_list['adv_num'];
            $param['ap_id'] = intval(input('post.ap_id'));
            $param['adv_num'] = $adv_num + 1;
            $result2 = $adv->ap_update($param);

            if ($result && $result2) {
                $this->log(lang('adv_add_succ') . '[' . input('post.adv_name') . ']', null);
                $this->success(lang('adv_add_succ'), url('Adv/adv', ['ap_id' => input('post.ap_id')]));
            } else {
                $this->error(lang('adv_add_fail'));
            }
        }
    }

    /**
     *
     * 修改广告
     */
    public function adv_edit() {
        if(session('admin_is_super') !=1 && !in_array('3',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $adv_id = intval(input('param.adv_id'));
        if (!request()->isPost()) {
            $adv_model = Model('adv');
            //获取广告列表
            $ap_list = $adv_model->getApList();
            $this->assign('ap_list', $ap_list);
            //获取指定广告
            $condition['adv_id'] = $adv_id;
            $adv = $adv_model->getOneAdv($condition);
            $this->assign('adv', $adv);
            $this->assign('ref_url', getReferer());
            $this->setAdminCurItem('adv_edit');
            return $this->fetch('adv_form');
        } else {
            $adv_model = Model('adv');


            $param['adv_id'] = $adv_id;
            $param['adv_title'] = trim($_POST['adv_name']);
            $param['adv_start_date'] = $this->getunixtime(trim($_POST['adv_start_date']));
            $param['adv_end_date'] = $this->getunixtime(trim($_POST['adv_end_date']));
            $param['adv_link'] = input('post.adv_link');

            if (!empty($_FILES['adv_code']['name'])) {
                //上传文件保存路径
                $upload_file = BASE_UPLOAD_PATH . DS . ATTACH_ADV;
                $file = request()->file('adv_code');
                $info = $file->rule('uniqid')->validate(['ext' => 'jpg,png,gif'])->move($upload_file);
                if ($info) {
                    //还需删除原来图片
                    $adv_code_ori = input('param.adv_code_ori');
                    if($adv_code_ori){
                        @unlink($upload_file. DS .$adv_code_ori);
                    }
                    $param['adv_code'] = $info->getFilename();
                } else {
                    // 上传失败获取错误信息
                    $this->error($file->getError());
                }
            }

            //验证数据  BEGIN
            $rule = [
                ['adv_title', 'require', lang('adv_can_not_null')],
                ['adv_start_date', 'require', lang('must_select_start_time')],
                ['adv_end_date', 'require', lang('must_select_end_time')],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($param);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END

            $result = $adv_model->adv_update($param);

            if ($result) {
                $this->log(lang('adv_change_succ') . '[' . input('post.ap_name') . ']', null);
                $this->success(lang('adv_change_succ'), input('post.ref_url'));
            } else {
                $this->error(lang('adv_change_fail'));
            }
        }
    }

    /**
     *
     * 删除广告
     */
    public function adv_del() {
        if(session('admin_is_super') !=1 && !in_array('2',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $adv_model = Model('adv');
        /**
         * 删除一个广告
         */
        $adv_id = intval(input('param.adv_id'));
        $result = $adv_model->adv_del($adv_id);

        if (!$result) {
            $this->error(lang('adv_del_fail'));
        } else {
            $this->log(lang('adv_del_succ') . '[' . $adv_id . ']', null);
            $this->success(lang('adv_del_succ'));
        }
    }

    /**
     *
     * 获取UNIX时间戳
     */
    public function getunixtime($time) {
        $array = explode("-", $time);
        $unix_time = mktime(0, 0, 0, $array[1], $array[2], $array[0]);
        return $unix_time;
    }

    /**
     *
     * ajaxOp
     */
    public function ajax() {
        switch ($_GET['branch']) {
            case 'is_use':
                $adv_model = Model('adv');
                $param[trim($_GET['column'])] = intval($_GET['value']);
                $param['ap_id'] = intval($_GET['id']);
                $adv_model->ap_update($param);
                echo 'true';
                exit;
                break;
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'adv',
                'text' => lang('adv_manage'),
                'url' => url('Admin/Adv/adv')
            ),
        );
        if(session('admin_is_super') ==1 || in_array('1',$this->action)){
            $menu_array[] = array(
                'name' => 'adv_add',
                'text' => lang('adv_add'),
                'url' => url('Admin/Adv/adv_add', ['ap_id' => input('param.ap_id')])
            );
        }


        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit',
                'text' => '编辑',
                'url' => url('Admin/Article/edit')
            );
        }
        return $menu_array;
    }

}

?>
