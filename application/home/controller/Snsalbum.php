<?php

/**
 * 买家相册
 */

namespace app\home\controller;

use think\Lang;
use think\Model;

class Snsalbum extends BaseSns {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/snsalbum.lang.php');
    }

    function test()
    {
        echo $this->fetch($this->template_dir .'test');
    }
    
    public function index() {
        $this->album_cate();
        exit;
    }

    /**
     * 相册分类列表
     *
     */
    public function album_cate() {
        // 验证是否存在默认相册
        $where = array();
        $where['member_id'] = $this->master_id;
        $where['is_default'] = 1;
        $class_info = db('snsalbumclass')->where($where)->find();
        if (empty($class_info)) {
            $insert = array();
            $insert['ac_name'] = lang('sns_buyershow');
            $insert['member_id'] = $this->master_id;
            $insert['ac_des'] = lang('sns_buyershow_album_des');
            $insert['ac_sort'] = 1;
            $insert['is_default'] = 1;
            $insert['upload_time'] = time();
            db('snsalbumclass')->insert($insert);
        }

        /**
         * 相册分类
         */
        $where = array(); // 条件
        $where['member_id'] = $this->master_id;
        $order = 'ac_sort asc';
        // 相册
        $ac_list = db('snsalbumclass')->where($where)->order($order)->select();
        $count = 0; // 图片总数量
        if (!empty($ac_list)) {
            // 相册中商品数量
            $ap_count = db('snsalbumpic')->field('count(ap_id) as count,ac_id')->where($where)->group('ac_id')->select();
            $ap_count = array_under_reset($ap_count, 'ac_id', 1);
            foreach ($ac_list as $key => $val) {
                if (isset($ap_count[$val['ac_id']])) {
                    $count += intval($ap_count[$val['ac_id']]['count']);
                    $ac_list[$key]['count'] = $ap_count[$val['ac_id']]['count'];
                } else {
                    $ac_list[$key]['count'] = 0;
                }
            }
        }
        $this->assign('count', $count);
        $this->assign('ac_list', $ac_list);

        self::profile_menu('album', 'album');
        echo $this->fetch($this->template_dir .'sns_album_list');
    }

    /**
     * 相册分类添加
     *
     */
    public function album_add() {
        $class_count = db('snsalbumclass')->where(array('member_id' => $this->master_id))->count();
        $this->assign('class_count', $class_count);
        return $this->fetch($this->template_dir .'sns_album_class_add', 'null_layout');
    }

    /**
     * 相册保存
     *
     */
    public function album_add_save() {
        if (chksubmit()) {
            $class_count = db('snsalbumclass')->where(array('member_id' => session('member_id')))->count();
            if ($class_count >= 10) {
                showDialog(lang('album_class_save_max_10'), url('snsalbum/index'), 'error');
            }
            $insert = array();
            $insert['ac_name'] = $_POST['name'];
            $insert['member_id'] = session('member_id');
            $insert['ac_des'] = $_POST['description'];
            $insert['ac_sort'] = $_POST['sort'];
            $insert['upload_time'] = time();

            $return = db('snsalbumclass')->insert($insert);
            if ($return) {
                showDialog(lang('album_class_save_succeed'), url('snsalbum/index'), 'succ', empty($_GET['inajax']) ? '' : 'CUR_DIALOG.close();');
            }
        }
        showDialog(lang('album_class_save_lose'));
    }

    /**
     * 相册分类编辑
     */
    public function album_edit() {
        $id = intval($_GET['id']);
        if ($id <= 0) {
            echo lang('album_parameter_error');
            exit;
        }
        $where = array();
        $where['ac_id'] = $id;
        $where['member_id'] = session('member_id');
        $class_info = db('snsalbumclass')->where($where)->find();
        $this->assign('class_info', $class_info);

        return $this->fetch($this->template_dir .'sns_album_class_edit', 'null_layout');
    }

    /**
     * 相册分类编辑保存
     */
    public function album_edit_save() {
        $update = array();
        $update['ac_id'] = intval($_POST['id']);
        $update['ac_name'] = $_POST['name'];
        $update['ac_des'] = $_POST['description'];
        $update['ac_sort'] = $_POST['sort'];

        // 更新
        $re = db('snsalbumclass')->update($update);
        if ($re) {
            showDialog(lang('album_class_edit_succeed'), url('snsalbum/index'), 'succ', empty($_GET['inajax']) ? '' : 'CUR_DIALOG.close();');
        } else {
            showDialog(lang('album_class_edit_lose'));
        }
    }

    /**
     * 相册删除
     */
    public function album_del() {
        $id = intval($_GET['id']);
        if ($id <= 0) {
            $this->error(lang('album_parameter_error'));
        }

        /**
         * 删除分类
         */
        $return = db('snsalbumclass')->where(array('ac_id' => $id, 'member_id' => session('member_id')))->delete();
        if (!$return) {
            showDialog(lang('album_class_file_del_lose'));
        }
        /**
         * 更新图片分类
         */
        $where = array();
        $where['is_default'] = 1;
        $where['member_id'] = session('member_id');
        $class_info = db('snsalbumclass')->where($where)->find();
        $return = $model->where(array('ac_id' => $id))->update(array('ac_id' => $class_info['ac_id']));
        if ($return) {
            showDialog(lang('album_class_file_del_succeed'), url('snsalbum/index'), 'succ');
        } else {
            showDialog(lang('album_class_file_del_lose'));
        }
    }

    /**
     * 图片列表
     */
    public function album_pic_list() {
        $id = intval($_GET['id']);
        if ($id <= 0) {
            $this->error(lang('album_parameter_error'));
        }

        $where = array();
        $where['ac_id'] = $id;
        $param['member_id'] = $this->master_id;
        $order = 'ap_id desc';
        if ($_GET['sort'] != '') {
            switch ($_GET['sort']) {
                case '0':
                    $order = 'upload_time desc';
                    break;
                case '1':
                    $order = 'upload_time asc';
                    break;
                case '2':
                    $order = 'ap_size desc';
                    break;
                case '3':
                    $order = 'ap_size asc';
                    break;
                case '4':
                    $order = 'ap_name desc';
                    break;
                case '5':
                    $order = 'ap_name asc';
                    break;
            }
        }
        $pic_list = db('snsalbumpic')->where($where)->order($order)->page(36)->paginate();
        $this->assign('pic_list', $pic_list);
        $this->assign('show_page', $pic_list->render());


        /**
         * 相册列表
         */
        $where = array();
        $where['member_id'] = $this->master_id;
        $class_array = db('snsalbumclass')->where($where)->select();
        if (empty($class_array)) {
            $this->error(lang('wrong_argument'));
        }

        // 整理
        $class_array = array_under_reset($class_array, 'ac_id');
        $class_list = $class_info = array();
        foreach ($class_array as $val) {
            if ($val['ac_id'] == $id) {
                $class_info = $val;
            } else {
                $class_list[] = $val;
            }
        }
        $this->assign('class_list', $class_list);
        $this->assign('class_info', $class_info);


        self::profile_menu('album_pic', 'pic_list');
        return $this->fetch($this->template_dir .'sns_album_pic_list');
    }

    /**
     * 修改相册封面
     */
    public function change_album_cover() {
        $id = intval($_GET['id']);
        if ($id <= 0) {
            showDialog(lang('ds_common_op_fail'));
        }
        /**
         * 图片信息
         */
        $where = array();
        $where['ap_id'] = $id;
        $where['member_id'] = session('member_id');
        $pic_info = db('snsalbumpic')->where($where)->find();
        $update = array();
        $update['ac_cover'] = str_ireplace('.', '_240.', $pic_info['ap_cover']);
        $update['ac_id'] = $pic_info['ac_id'];
        $return = db('snsalbumclass')->update($update);
        if ($return) {
            showDialog(lang('ds_common_op_succ'), 'reload', 'succ');
        } else {
            showDialog(lang('ds_common_op_fail'));
        }
    }

    /**
     * 图片详细页
     */
    public function album_pic_info() {
        $class_id = intval($_GET['class_id']);
        $id = intval($_GET['id']);
        if ($class_id <= 0 && $id <= 0) {
            $this->error(lang('album_parameter_error'));
        }

        /**
         * 图片列表
         */
        $where = array();
        $where['ac_id'] = $class_id;
        $where['member_id'] = $this->master_id;
        $each_num = 9;
        $pic_list = db('snsalbumpic')->where($where)->order('ap_id desc')->page($each_num)->select();
        if (empty($pic_list)) {
            $this->error(lang('wrong_argument'));
        }

        $curpage = intval($_GET['curpage']);
        if (empty($curpage))
            $curpage = 1;
        $this->assign('total_page', $model->gettotalpage());
        $this->assign('curpage', $curpage);

        foreach ($pic_list as $key => $val) {
            if ($id == $val['ap_id']) {
                $pic_num = $key;
                $pic_info = $val;
            }
            $val['ap_size'] = sprintf('%.2f', intval($val['ap_size']) / 1024);
            $pic_list[$key] = $val;
        }
        if (!isset($pic_info)) {
            $this->error(lang('wrong_argument'));
        }
        $this->assign('pic_num', $pic_num);
        $this->assign('pic_info', $pic_info);
        $this->assign('pic_list', $pic_list);

        /**
         * 相册信息
         */
        $class_info = db('snsalbumclass')->where($where)->find();
        $this->assign('class_info', $class_info);


        self::profile_menu('album_pic_info', 'pic_info');
        return $this->fetch($this->template_dir .'sns_album_pic_info');
    }

    /**
     * 图片详细页
     */
    public function album_pic_scroll_ajax() {
        $class_id = intval($_GET['class_id']);
        $id = intval($_GET['id']);
        if ($class_id <= 0 && $id <= 0) {
            exit();
        }

        /**
         * 图片列表
         */
        $where = array();
        $where['ac_id'] = $class_id;
        $where['member_id'] = $this->master_id;
        $each_num = 9;
        $pic_list = db('snsalbumpic')->where($where)->order('ap_id desc')->page($each_num)->select();
        if (empty($pic_list)) {
            exit();
        }

        foreach ($pic_list as $key => $val) {
            if ($id == $val['ap_id']) {
                $pic_num = $key;
                $pic_info = $val;
            }
            $val['ap_size'] = sprintf('%.2f', intval($val['ap_size']) / 1024);
            $pic_list[$key] = $val;
        }
        $this->assign('pic_list', $pic_list);

        self::profile_menu('album_pic_info', 'pic_info');
        return $this->fetch($this->template_dir .'sns_album_pic_info_scroll_ajax', 'null_layout');
    }

    /**
     * 图片删除
     */
    public function album_pic_del() {
        $ids = input('param.id');
        if (empty($ids)) {
            showDialog(lang('album_parameter_error'));
        }
        if (!empty($ids) && is_array($ids)) {
            $id = $ids;
        } else {
            $id[] = intval($ids);
        }

        foreach ($id as $v) {
            $v = intval($v);
            if ($v <= 0)
                continue;
            $ap_info = db('snsalbumpic')->where(array('ap_id' => $v, 'member_id' => session('member_id')))->find();
            if (empty($ap_info))
                continue;
            @unlink(BASE_UPLOAD_PATH . DS . ATTACH_MALBUM . DS . session('member_id') . DS . $ap_info['ap_cover']);
//            unlink(BASE_UPLOAD_PATH . DS . session('member_id') . DS . str_ireplace('.', '_240.', $ap_info['ap_cover']));
//            unlink(BASE_UPLOAD_PATH . DS . session('member_id') . DS . str_ireplace('.', '_1024.', $ap_info['ap_cover']));
            db('snsalbumpic')->delete($ap_info['ap_id']);
        }

        showDialog(lang('album_class_pic_del_succeed'), 'reload', 'succ');
    }


    /**
     * ajax返回图片信息
     */
    public function ajax_change_imgmessage() {
        $url = explode('/', $_GET['url']);
        $str = array_pop($url);
        $str = explode('.', $str);
        $where = array();
        $where['member_id'] = $this->master_id;
        $where['ap_cover'] = array('like', '%' . $str['0'] . '%');
        $pic_info = db('snsalbumpic')->where($where)->find();


        /**
         * 小图尺寸
         */
        if (strtoupper(CHARSET) == 'GBK') {
            $pic_info['ap_name'] = Language::getUTF8($pic_info['ap_name']);
        }
        echo json_encode(array(
            'img_name' => $pic_info['ap_name'],
            'default_size' => sprintf('%.2f', intval($pic_info['ap_size']) / 1024),
            'default_spec' => $pic_info['ap_spec'],
            'upload_time' => date('Y-m-d', $pic_info['upload_time'])
        ));
    }

    /**
     * 上传图片
     *
     * @param
     * @return
     */
    public function swfupload() {
        $member_id = session('member_id');
        $class_id = intval(input('param.category_id'));
        if ($member_id <= 0 && $class_id <= 0) {
            echo json_encode(array('state' => 'false', 'message' => lang('sns_upload_pic_fail'), 'origin_file_name' => $_FILES["file"]["name"]));
            exit;
        }

        // 验证图片数量
        $count = db('snsalbumpic')->where(array('member_id' => $member_id))->count();
        if (config('malbum_max_sum') != 0 && $count >= config('malbum_max_sum')) {
            echo json_encode(array('state' => 'false', 'message' => lang('sns_upload_img_max_num_error'), 'origin_file_name' => $_FILES["file"]["name"]));
            exit;
        }

        /**
         * 上传图片
         */
        //上传文件保存路径
        $upload_file = BASE_UPLOAD_PATH . DS . ATTACH_MALBUM . DS . $member_id;
        if (!empty($_FILES['file']['name'])) {
            $file = request()->file('file');
            //设置特殊图片名称
            $file_name = $member_id . '_' . date('YmdHis') . rand(10000, 99999);
            $info = $file->rule('uniqid')->validate(['ext' => 'jpg,png,gif'])->move($upload_file, $file_name);
            if ($info) {
                $img_path = $info->getFilename();
            } else {
                // 目前并不出该提示
                $error = $file->getError();
                $data['state'] = 'false';
                $data['message'] = $error;
                $data['origin_file_name'] = $_FILES['file']['name'];
                echo json_encode($data);exit;
            }
        }else{
            //未上传图片不做后面处理
            exit;
        }

        list($width, $height, $type, $attr) = getimagesize($upload_file . DS . $img_path);
        
        $insert = array();
        $insert['ap_name'] = $img_path;
        $insert['ac_id'] = $class_id;
        $insert['ap_cover'] = $img_path;
        $insert['ap_size'] = intval($_FILES['file']['size']);
        $insert['ap_spec'] = $width . 'x' . $height;
        $insert['upload_time'] = time();
        $insert['member_id'] = $member_id;
        $result = db('snsalbumpic')->insertGetId($insert);
        $data = array();
        $data['file_id'] = $result;
        $data['file_name'] = $img_path;
        $data['origin_file_name'] = $_FILES["file"]["name"];
        $data['file_path'] = $img_path;
        $data['file_url'] = snsThumb($img_path, 240);
        $data['state'] = 'true';
        /**
         * 整理为json格式
         */
        $output = json_encode($data);
        echo $output;
    }

    /**
     * ajax验证名称时候重复
     */
    public function ajax_check_class_name() {
        $ac_name = trim($_GET['ac_name']);
        if ($ac_name == '') {
            echo 'true';
            die;
        }
        $where = array();
        $where['ac_name'] = $ac_name;
        $where['member_id'] = session('member_id');
        ;
        $class_info = db('snsalbumclass')->where($where)->count();
        if (!empty($class_info)) {
            echo 'false';
            die;
        } else {
            echo 'true';
            die;
        }
    }

}

?>
