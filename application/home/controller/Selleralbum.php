<?php

namespace app\home\controller;

use think\Lang;
use think\Validate;

class Selleralbum extends BaseSeller {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/selleralbum.lang.php');
    }

    /*
     * 测试上传 删除时，也删除模板
     */

    function test() {
        return $this->fetch($this->template_dir . 'test');
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
        $model_album = Model('album');

        /**
         * 验证是否存在默认相册
         */
        $return = $model_album->checkAlbum(array('store_id' => session('store_id'), 'is_default' => '1'));
        if (!$return) {
            $album_arr = array();
            $album_arr['aclass_name'] = lang('album_default_album');
            $album_arr['store_id'] = session('store_id');
            $album_arr['aclass_des'] = '';
            $album_arr['aclass_sort'] = '255';
            $album_arr['aclass_cover'] = '';
            $album_arr['upload_time'] = time();
            $album_arr['is_default'] = '1';
            $model_album->addClass($album_arr);
        }
        /**
         * 相册分类
         */
        $condition['store_id'] = session('store_id');
        $order = 'aclass_sort desc';
        $sort = input('get.sort');
        if ($sort != '') {
            switch ($sort) {
                case '0':
                    $order = 'upload_time desc';
                    break;
                case '1':
                    $order = 'upload_time asc';
                    break;
                case '2':
                    $order = 'aclass_name desc';
                    break;
                case '3':
                    $order = 'aclass_name asc';
                    break;
                case '4':
                    $order = 'aclass_sort desc';
                    break;
                case '5':
                    $order = 'aclass_sort asc';
                    break;
            }
        }
        $aclass_info = $model_album->getClassList($condition, '', $order);
        $this->assign('aclass_info', $aclass_info);
        $this->assign('PHPSESSID', session_id());
        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('selleralbum');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem('album_cate');
        echo $this->fetch($this->template_dir . 'album_cate');
        exit;
    }

    /**
     * 相册分类添加
     *
     */
    public function album_add() {
        /**
         * 实例化相册模型
         */
        $model_album = Model('album');
        $class_count = $model_album->countClass(session('store_id'));
        $this->assign('class_count', $class_count['count']);
        return $this->fetch($this->template_dir . 'album_add');
    }

    /**
     * 相册保存
     *
     */
    public function album_add_save() {
        if (request()->isPost()) {
            /**
             * 实例化相册模型
             */
            $model_album = Model('album');
            $class_count = $model_album->countClass(session('store_id'));
            if ($class_count['count'] >= 20) {
                showDialog(lang('album_class_save_max_20'), url('Home/Selleralbum/index'), 'error', !input('param.inajax') ? '' : 'CUR_DIALOG.close();');
            }
            /**
             * 实例化相册模型
             */
            $param = array();
            $param['aclass_name'] = input('post.name');
            $param['store_id'] = session('store_id');
            $param['aclass_des'] = input('post.description');
            $param['aclass_sort'] = input('post.sort');
            $param['upload_time'] = time();

            //验证数据  BEGIN
            $rule = [
                ['aclass_name', 'require', '相册名称必填'],
                ['aclass_des', 'require', '相册描述必填'],
                ['aclass_sort', 'require', '相册排序必填'],
            ];
            $validate = new Validate();
            $validate_result = $validate->check($param, $rule);
            if (!$validate_result) {
                showDialog($validate->getError());
            }
            //验证数据  END

            $return = $model_album->addClass($param);
            if ($return) {
                showDialog(lang('album_class_save_succeed'), url('Home/Selleralbum/index'), 'succ', empty(input('param.inajax')) ? '' : 'CUR_DIALOG.close();');
            }
        }
        showDialog(lang('album_class_save_lose'));
    }

    /**
     * 相册分类编辑
     */
    public function album_edit() {
        $id = intval(input('param.id'));
        if ($id <= 0) {
            echo lang('album_parameter_error');
            exit;
        }
        /**
         * 实例化相册模型
         */
        $model_album = Model('album');
        $condtion['aclass_id'] = $id;
        $condtion['store_id'] = session('store_id');
        $class_info = $model_album->getOneClass($condtion);
        $this->assign('class_info', $class_info);

        return $this->fetch($this->template_dir . 'album_edit');
    }

    /**
     * 相册分类编辑保存
     */
    public function album_edit_save() {
        $id = intval(input('param.id'));
        if ($id <= 0) {
            showDialog(lang('album_parameter_error'));
            exit;
        }
        $param = array();
        $param['aclass_name'] = input('post.name');
        $param['aclass_des'] = input('post.description');
        $param['aclass_sort'] = input('post.sort');

        //验证数据  BEGIN
        $rule = [
            ['aclass_name', 'require', '相册名称必填'],
            ['aclass_des', 'require', '相册描述必填'],
            ['aclass_sort', 'require', '相册排序必填'],
        ];
        $validate = new Validate();
        $validate_result = $validate->check($param, $rule);
        if (!$validate_result) {
            $this->error($validate->getError());
        }
        //验证数据  END

        /**
         * 实例化相册模型
         */
        $model_album = Model('album');
        /**
         * 验证
         */
        $return = $model_album->checkAlbum(array('store_id' => session('store_id'), 'aclass_id' => $id));
        if ($return) {
            /**
             * 更新
             */
            $re = $model_album->updateClass($param, $id);
            if ($re) {
                showDialog(lang('album_class_edit_succeed'), url('Home/Selleralbum/index'), 'succ', empty(input('get.inajax')) ? '' : 'CUR_DIALOG.close();');
            }
        } else {
            showDialog(lang('album_class_edit_lose'));
        }
    }

    /**
     * 相册删除
     */
    public function album_del() {
        $id = intval(input('param.id'));
        if ($id <= 0) {
            $this->error(lang('album_parameter_error'));
        }
        /**
         * 实例化相册模型
         */
        $model_album = Model('album');

        /**
         * 验证是否为默认相册，
         */
        $return = $model_album->checkAlbum(array('store_id' => session('store_id'), 'aclass_id' => $id, 'is_default' => '0'));
        if (!$return) {
            showDialog(lang('album_class_file_del_lose'));
        }
        /**
         * 删除分类
         */
        $return = $model_album->delClass($id);
        if (!$return) {
            showDialog(lang('album_class_file_del_lose'));
        }
        /**
         * 更新图片分类
         */
        $condition['is_default'] = 1;
        $condition['store_id'] = session('store_id');
        $class_info = $model_album->getOneClass($condition);
        $param = array();
        $param['aclass_id'] = $class_info['aclass_id'];
        $model_album->updatePic($param, array('aclass_id' => $id));
        if ($return) {
            showDialog(lang('album_class_file_del_succeed'), url('Home/Selleralbum/index'), 'succ');
        } else {
            showDialog(lang('album_class_file_del_lose'));
        }
    }

    /**
     * 图片列表
     */
    public function album_pic_list() {
        $id = intval(input('param.id'));
        if ($id <= 0) {
            $this->error(lang('album_parameter_error'));
        }

        /**
         * 实例化相册类
         */
        $model_album = Model('album');

        $param = array();
        $param['aclass_id'] = $id;
        $param['store_id'] = session('store_id');
        $order = input('get.sort');
        switch ($order) {
            case '0':
                $order = 'upload_time desc';
                break;
            case '1':
                $order = 'upload_time asc';
                break;
            case '2':
                $order = 'apic_size desc';
                break;
            case '3':
                $order = 'apic_size asc';
                break;
            case '4':
                $order = 'apic_name desc';
                break;
            case '5':
                $order = 'apic_name asc';
                break;
            default :
                $order = 'upload_time desc';
                break;
        }
        $pic_list = $model_album->getPicList($param, 10, '*', $order);
        $this->assign('pic_list', $pic_list);
        $this->assign('show_page', $model_album->page_info->render());

        /**
         * 相册列表，移动
         */
        $param = array();
        $param['aclass_id'] = array('not in', $id);
        $param['store_id'] = session('store_id');
        $class_list = $model_album->getClassList($param);
        $this->assign('class_list', $class_list);
        /**
         * 相册信息
         */
        $condition['aclass_id'] = $id;
        $condition['store_id'] = session('store_id');
        $class_info = $model_album->getOneClass($condition);
        $this->assign('class_info', $class_info);

        $this->assign('PHPSESSID', session_id());

        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('selleralbum');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem('pic_list');
        return $this->fetch($this->template_dir . 'album_pic_list');
    }

    /**
     * 图片列表，外部调用
     */
    public function pic_list() {
        /**
         * 实例化相册类
         */
        $model_album = Model('album');
        /**
         * 图片列表
         */
        $param = array();
        $param['store_id'] = session('store_id');
        $id = intval(input('param.id'));
        if ($id > 0) {
            $param['aclass_id'] = $id;
            /**
             * 分类列表
             */
            $condition = array();
            $condition['aclass_id'] = $id;
            $condition['store_id'] = session('store_id');
            $cinfo = $model_album->getOneClass($condition);
            $this->assign('class_name', $cinfo['aclass_name']);
        }
        $pic_list = $model_album->getPicList($param, 12);
        
        $this->assign('pic_list', $pic_list);
        $this->assign('show_page', $model_album->page_info->render());
        /**
         * 分类列表
         */
        $condition = array();
        $condition['store_id'] = session('store_id');
        $class_info = $model_album->getClassList($condition);
        $this->assign('class_list', $class_info);

        $item = input('param.item');
        switch ($item) {
            case 'goods':
                return $this->fetch($this->template_dir . 'pic_list_goods');
                break;
            case 'des':
                echo $this->fetch($this->template_dir . 'pic_list_des');
                break;
            case 'groupbuy':
                return $this->fetch($this->template_dir . 'pic_list_groupbuy');
                break;
            case 'store_sns_normal':
                return $this->fetch($this->template_dir . 'pic_list_store_sns_normal');
                break;
            case 'goods_image':
                $this->assign('color_id', input('param.color_id'));
                return $this->fetch($this->template_dir . 'pic_list_goods_image');
                break;
            case 'mobile':
                $this->assign('type', input('param.type'));
                echo $this->fetch($this->template_dir . 'pic_list_mobile');
                break;
        }
    }

    /**
     * 修改相册封面
     */
    public function change_album_cover() {
        $id = intval(input('get.id'));
        if ($id <= 0) {
            showDialog(lang('ds_common_op_fail'));
        }
        /**
         * 实例化相册类
         */
        $model_album = Model('album');
        /**
         * 图片信息
         */
        $condition['apic_id'] = $id;
        $condition['store_id'] = session('store_id');

        $pic_info = $model_album->getOnePicById($condition);
        $return = $model_album->checkAlbum(array('store_id' => session('store_id'), 'aclass_id' => $pic_info['aclass_id']));
        if ($return) {
            $re = $model_album->updateClass(array('aclass_cover' => $pic_info['apic_cover']), $pic_info['aclass_id']);
            showDialog(lang('ds_common_op_succ'), 'reload', 'succ');
        } else {
            showDialog(lang('ds_common_op_fail'));
        }
    }

    /**
     * ajax修改图名称
     */
    public function change_pic_name() {
        if (empty($_POST['id']) && empty($_POST['name'])) {
            echo 'false';
        }
        /**
         * 实例化相册类
         */
        $model_album = Model('album');

        $return = $model_album->updatePic(array('apic_name' => $_POST['name']), array('apic_id' => intval($_POST['id'])));
        if ($return) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    /**
     * 图片详细页
     */
    public function album_pic_info() {
        $class_id = input('param.class_id');
        $id = input('param.id');
        if ($class_id <= 0 || $id <= 0) {
            $this->error(lang('album_parameter_error'));
        }
        /**
         * 实例化相册类
         */
        $model_album = Model('album');


        /**
         * 图片列表
         */
        $param = array();
        $param['aclass_id'] = $class_id;
        $param['store_id'] = session('store_id');
        $pic_list = $model_album->getPicList($param, 9);
        $this->assign('pic_list', $pic_list);

        $curpage = intval(input('param.page'));
        if (empty($curpage)) {
            $curpage = 1;
        }
        $total_page = (ceil(10 / 9));

        $this->assign('total_page', $total_page);
        $this->assign('curpage', $curpage);


        /**
         * 相册信息
         */
        $param = array();
        $param['aclass_id'] = $class_id;
        $param['store_id'] = session('store_id');
        $class_info = $model_album->getOneClass($param);
        $this->assign('class_info', $class_info);

        /**
         * 图片信息
         */
        $param = array();
        $param['apic_id'] = $id;
        $param['store_id'] = session('store_id');
        $pic_info = $model_album->getOnePicById($param);
        $pic_info['apic_size'] = sprintf('%.2f', intval($pic_info['apic_size']) / 1024);
        $this->assign('pic_info', $pic_info);


        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('selleralbum');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem('pic_info');
        return $this->fetch($this->template_dir . 'album_pic_info');
    }

    /**
     * 图片 ajax
     */
    public function album_ad_ajax() {
        if (empty($_GET['class_id']) && empty($_GET['id'])) {
            exit();
        }

        $model_album = Model('album');

        $return = $model_album->checkAlbum(array('album_pic.store_id' => session('store_id'), 'album_pic.apic_id' => intval($_GET['id'])));
        if (!$return) {
            exit();
        }

        /**
         * 图片列表
         */
        $param = array();
        $param['aclass_id'] = intval($_GET['class_id']);
        $param['store_id'] = session('store_id');
        $page = new Page();
        $each_num = 9;
        $page->setEachNum($each_num);
        $pic_list = $model_album->getPicList($param, $page);
        $this->assign('pic_list', $pic_list);

        return $this->fetch('store_album.pic_scroll_ajax', 'null_layout');
    }

    /**
     * 图片删除
     */
    public function album_pic_del() {

        $ids=input('id/a');
        if (empty($ids)) {
            showDialog(lang('album_parameter_error'));
        }
        $model_album = Model('album');
        if(!empty($ids) && is_array($ids)){
            $id = implode(',', $ids);
        }else{
            $id = intval($ids);
        }
        //删除图片
        $return = $model_album->delPic($id, session('store_id'));
        if ($return) {
            showDialog(lang('album_class_pic_del_succeed'), 'reload', 'succ');
        } else {
            showDialog(lang('album_class_pic_del_lose'));
        }
    }

    /**
     * 移动相册
     */
    public function album_pic_move() {
        /**
         * 实例化相册类
         */
        $model_album = Model('album');
        if (request()->isPost()) {
            $id = input('post.id/a');
            $cid = input('post.cid');
            if (empty($id)) {
                showDialog(lang('album_parameter_error'));
            }
            if (!empty($id) && is_array($id)) {
                $id = trim(implode(',',$id), ',');
            }

            $update = array();
            $update['aclass_id'] = $cid;
            $condition['apic_id'] = array('in', $id);
            $condition['store_id'] = session('store_id');

            $return = $model_album->updatePic($update, $condition);
            if ($return) {
                showDialog(lang('album_class_pic_move_succeed'), 'reload', 'succ');
            } else {
                showDialog(lang('album_class_pic_move_lose'));
            }
        } else {
            $id = input('param.id');
            $cid = input('param.cid');

            $condition['store_id'] = session('store_id');
            $condition['aclass_id'] = array('not in', $cid);
            $class_list = $model_album->getClassList($condition);

            if (isset($id) && !empty($id)) {
                $this->assign('id', $id);
            }
            $this->assign('class_list', $class_list);
            return $this->fetch($this->template_dir . 'album_pic_move');
        }
    }

    /**
     * 替换图片
     */
    public function replace_image_upload() {
        $file = input('param.id');
        $tpl_array = explode('_', $file);
        $id = intval(end($tpl_array));
        $model_album = Model('album');
        $condition['apic_id'] = $id;
        $condition['store_id'] = session('store_id');
        $apic_info = $model_album->getOnePicById($condition);
        if (substr(strrchr($apic_info['apic_cover'], "."), 1) != substr(strrchr($_FILES[$file]["name"], "."), 1)) {
            // 后缀名必须相同
            $error = lang('album_replace_same_type');
            echo json_encode(array('state' => 'false', 'message' => $error));
            exit();
        }
        $pic_cover = implode(DS, explode(DS, $apic_info['apic_cover'], -1)); // 文件路径
        $tmpvar = explode(DS, $apic_info['apic_cover']);
        $pic_name = end($tmpvar); // 文件名称

        
        /**
         * 上传图片
         */
        //上传文件保存路径
        $store_id = session('store_id');
        $upload_file = BASE_UPLOAD_PATH . DS . ATTACH_GOODS . DS . $store_id;
        if (!empty($_FILES[$file]['name'])) {
            $file_object = request()->file($file);
            $info = $file_object->rule('uniqid')->validate(['ext' => 'jpg,png,gif'])->move($upload_file, $pic_name);
            if ($info) {
                $pic = $info->getFilename();
            } else {
                // 目前并不出该提示
                $error = $file_object->getError();
                $data['state'] = 'false';
                $data['message'] = $error;
                $data['origin_file_name'] = $_FILES[$file]['name'];
                echo json_encode($data);
            }
        }



        list($width, $height, $type, $attr) = getimagesize($upload_file . DS . $pic);

        $update_array = array();
        $update_array['apic_size'] = intval($_FILES[$file]['size']);
        $update_array['apic_spec'] = $width . 'x' . $height;
        $result = Model('uploadalbum')->updatePic($id, $update_array);

        echo json_encode(array('state' => 'true', 'id' => $id));
        exit();
    }

    /**
     * 添加水印
     */
    public function album_pic_watermark() {
        if (empty($_POST['id']) && !is_array($_POST['id'])) {
            showDialog(lang('album_parameter_error'));
        }

        $id = trim(implode(',', $_POST['id']), ',');

        /**
         * 实例化图片模型
         */
        $model_album = Model('album');
        $param['apic_id'] = array('in',$id);
        $param['store_id'] = session('store_id');
        $wm_list = $model_album->getPicList($param);
        $model_store_wm = Model('storewatermark');
        $store_wm_info = $model_store_wm->getOneStoreWMByStoreId(session('store_id'));
        if ($store_wm_info['wm_image_name'] == '' && $store_wm_info['wm_text'] == '') {
            showDialog(lang('album_class_setting_wm'), url('Home/Selleralbum/store_watermark'), 'error', 'CUR_DIALOG.close();'); //"请先设置水印"
        }

        foreach ($wm_list as $v) {
            //商品的图片路径
            $image_file = BASE_UPLOAD_PATH . DS . ATTACH_GOODS . DS . session('store_id') . DS . $v['apic_cover'];

            //打开图片
            $gd_image=\think\Image::open($image_file);

            //添加图片水印
            if(!empty($store_wm_info['wm_image_name'])) {
                //水印图片的路径
                $w_image=BASE_UPLOAD_PATH . DS .ATTACH_WATERMARK.DS.$store_wm_info['wm_image_name'];
                $gd_image->water($w_image, $store_wm_info['wm_image_pos'], $store_wm_info['wm_image_transition'])->save($image_file);
            }
            //添加文字水印
            if(!empty($store_wm_info['wm_text'])){
                //字体文件路径
                $font=ROOT_PATH .'public'.DS . 'font' . DS .$store_wm_info['wm_text_font'].'.ttf';
                $gd_image->text($store_wm_info['wm_text'],$font,$store_wm_info['wm_text_size'],$store_wm_info['wm_text_color'],$store_wm_info['wm_text_pos'],$store_wm_info['wm_text_angle'])->save($image_file);
            }
        }
        showDialog(lang('album_pic_plus_wm_succeed'), 'reload', 'succ');
    }

    /**
     * 水印管理
     */
    public function store_watermark() {
        /**
         * 读取语言包
         */
        $model_store_wm = Model('storewatermark');
        /**
         * 获取会员水印设置
         */
        $store_wm_info = $model_store_wm->getOneStoreWMByStoreId(session('store_id'));
        /**
         * 保存水印配置信息
         */
        if (!request()->isPost()) {
            /**
             * 获取水印字体
             */
            $fontInfo = array();
            include ROOT_PATH . DS . 'public' . DS . 'font' . DS . 'font.info.php';
            foreach ($fontInfo as $key => $value) {
                if (!file_exists(ROOT_PATH . DS . 'public' . DS . 'font' . DS . $key . '.ttf')) {
                    unset($fontInfo[$key]);
                }
            }
            $this->assign('file_list', $fontInfo);


            if (empty($store_wm_info)) {
                /**
                 * 新建店铺水印设置信息
                 */
                $model_store_wm->addStoreWM(array(
                    'wm_text_font' => 'default',
                    'store_id' => session('store_id')
                ));
                $store_wm_info = $model_store_wm->getOneStoreWMByStoreId(session('store_id'));
            }

            /* 设置卖家当前菜单 */
            $this->setSellerCurMenu('selleralbum');
            /* 设置卖家当前栏目 */
            $this->setSellerCurItem('watermark');
            $this->assign('store_wm_info', $store_wm_info);
            return $this->fetch($this->template_dir . 'store_watermark');
        } else {

            $param = array();
            $param['wm_image_pos'] = input('post.image_pos');
            $param['wm_image_transition'] = input('post.image_transition');
            $param['wm_text'] = input('post.wm_text');
            $param['wm_text_size'] = input('post.wm_text_size');
            $param['wm_text_angle'] = input('post.wm_text_angle');
            $param['wm_text_font'] = input('post.wm_text_font');
            $param['wm_text_pos'] = input('post.wm_text_pos');
            $param['wm_text_color'] = input('post.wm_text_color');
            $param['jpeg_quality'] = input('post.image_quality');

            $upload_file = BASE_UPLOAD_PATH . DS . ATTACH_WATERMARK;
            if (!empty($_FILES['image']['name'])) {
                if (!empty($_FILES['image']['name'])) {
                    $file = request()->file('image');
                    $info = $file->rule('uniqid')->validate(['ext' => 'jpg,png,gif'])->move($upload_file);
                    if ($info) {
                        $param['wm_image_name'] = $info->getFilename();
                        /**
                         * 删除旧水印
                         */
                        if (!empty($store_wm_info['wm_image_name'])) {
                            @unlink($upload_file . DS . $store_wm_info['wm_image_name']);
                        }
                    } else {
                        showDialog($file->getError());
                    }
                }
            } elseif (input('post.is_del_image') == 'ok') {
                /**
                 * 删除水印
                 */
                if (!empty($store_wm_info['wm_image_name'])) {
                    $param['wm_image_name'] = '';
                    @unlink($upload_file . DS . $store_wm_info['wm_image_name']);
                }
            }
            $result = $model_store_wm->updateStoreWM($store_wm_info['wm_id'], $param);
            if ($result) {
                showDialog(lang('store_watermark_congfig_success'), 'reload', 'succ');
            } else {
                showDialog(lang('store_watermark_congfig_fail'));
            }
        }
    }

    /**
     * 上传图片
     *
     */
    public function image_upload() {
        $store_id = session('store_id');

        if (input('param.category_id')) {
            $category_id = intval(input('param.category_id'));
        } else {
            $error = '上传 图片失败';
            $data['state'] = 'false';
            $data['message'] = $error;
            $data['origin_file_name'] = $_FILES["file"]["name"];
            echo json_encode($data);
            exit();
        }
        // 判断图片数量是否超限
        $album_limit = $this->store_grade['sg_album_limit'];
        if ($album_limit > 0) {
            $album_count = Model('album')->getCount(array('store_id' => $store_id));
            if ($album_count >= $album_limit) {
                // 目前并不出该提示，而是提示上传0张图片
                $error = lang('store_goods_album_climit');
                $data['state'] = 'false';
                $data['message'] = $error;
                $data['origin_file_name'] = $_FILES["file"]["name"];
                $data['state'] = 'true';
                echo json_encode($data);
                exit();
            }
        }


        
        /**
         * 上传图片
         */
        //上传文件保存路径
        $upload_file = ATTACH_GOODS . '/' . $store_id;
        $save_name = session('store_id') . '_' . date('YmdHis') . rand(10000, 99999);
        $name ='file';
        $result=upload_goods_image($upload_file,$name,$save_name);
        if($result['code'] == '200'){
            $img_path=$result['result'];
            list($width, $height, $type, $attr) = getimagesize($img_path);
            $pic=substr(strrchr($img_path, "/"), 1);
        }else{
            exit($result['message']);
        }
        $insert_array = array();
        $insert_array['apic_name'] = $pic;
        $insert_array['apic_tag'] = '';
        $insert_array['aclass_id'] = $category_id;
        $insert_array['apic_cover'] = $pic;
        $insert_array['apic_size'] = intval($_FILES['file']['size']);
        $insert_array['apic_spec'] = $width . 'x' . $height;
        $insert_array['upload_time'] = time();
        $insert_array['store_id'] = $store_id;
        $result = Model('uploadalbum')->add($insert_array);

        $data = array();
        $data['file_id'] = $result;
        $data['file_name'] = $pic;
        $data['origin_file_name'] = $_FILES["file"]["name"];
        $data['file_path'] = $pic;
        $data['instance'] = input('get.instance');
        $data['state'] = 'true';
        /**
         * 整理为json格式
         */
        $output = json_encode($data);
        echo $output;
        exit;
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string	$menu_type	导航类型
     * @param string 	$menu_key	当前导航的menu_key
     * @return
     */
    function getSellerItemList() {
        $item_list = array(
            array(
                'name' => 'album',
                'text' => lang('ds_member_path_my_album'),
                'url' => url('Home/Selleralbum/index'),
            ),
            array(
                'name' => 'watermark',
                'text' => lang('ds_member_path_watermark'),
                'url' => url('Home/Selleralbum/store_watermark'),
            ),
        );
        if (request()->action() == 'pic_list') {
            $item_list[] = array(
                'name' => 'pic_list',
                'text' => lang('ds_member_path_album_pic_list'),
                'url' => url('Home/Selleralbum/album_pic_list', ['album_pic_list' => intval(input('param.id'))]),
            );
        }
        if (request()->action() == 'pic_info') {
            $item_list[] = array(
                'name' => 'pic_list',
                'text' => lang('ds_member_path_album_pic_list'),
                'url' => url('Home/Selleralbum/album_pic_list', ['class_id' => intval(input('param.class_id')), 'album_pic_list' => intval(input('param.id'))]),
            );
        }
        return $item_list;
    }

    /**
     * ajax返回图片信息
     */
    public function ajax_change_imgmessage() {
        $str_array = explode('/', $_GET['url']);
        $str = array_pop($str_array);
        $str = explode('.', $str);
        /**
         * 实例化图片模型
         */
        $model_album = Model('album');
        $param = array();
        $search = explode(',', GOODS_IMAGES_EXT);
        $param['like_cover'] = str_ireplace($search, '', $str['0']);
        $pic_info = $model_album->getPicList($param);

        /**
         * 小图尺寸
         */
        list($width, $height, $type, $attr) = getimagesize(BASE_UPLOAD_PATH . DS . ATTACH_GOODS . DS . session('store_id') . DS . $pic_info['0']['apic_cover']);
        if (strtoupper(CHARSET) == 'GBK') {
            $pic_info['0']['apic_name'] = Language::getUTF8($pic_info['0']['apic_name']);
        }
        echo json_encode(array(
            'img_name' => $pic_info['0']['apic_name'],
            'default_size' => sprintf('%.2f', intval($pic_info['0']['apic_size']) / 1024),
            'default_spec' => $pic_info['0']['apic_spec'],
            'upload_time' => date('Y-m-d', $pic_info['0']['upload_time']),
            'small_spec' => $width . 'x' . $height
        ));
    }

    /**
     * ajax验证名称时候重复
     */
    public function ajax_check_class_name() {
        $ac_name = trim(input('get.ac_name'));
        if ($ac_name == '') {
            echo 'true';
            die;
        }
        $model_album = Model('album');
        $condition['store_id'] = session('store_id');
        $condition['aclass_name'] = $ac_name;

        $class_info = $model_album->getOneClass($condition);
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
