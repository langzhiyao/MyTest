<?php

namespace app\home\controller;

use think\Image;
use think\Lang;

class Sellersetting extends BaseSeller {

    const MAX_MB_SLIDERS = 5;

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/sellersetting.lang.php');
    }

    /*
     * 店铺设置
     */

    public function setting() {
        /**
         * 实例化模型
         */
        $model_class = Model('store');

        $store_id = session('store_id'); //当前店铺ID
        /**
         * 获取店铺信息
         */
        $store_info = $model_class->getStoreInfoByID($store_id);

        /**
         * 保存店铺设置
         */
        if (request()->isPost()) {
            /**
             * 更新入库
             */
            $param = array(
                'store_qq' => $_POST['store_qq'],
                'store_ww' => $_POST['store_ww'],
                'store_phone' => $_POST['store_phone'],
                'store_zy' => $_POST['store_zy'],
                'store_keywords' => $_POST['seo_keywords'],
                'store_description' => $_POST['seo_description']
            );

            $upload_file = BASE_UPLOAD_PATH . DS . ATTACH_STORE . DS . $store_id;
            $file_name = session('store_id') . '_' . date('YmdHis') . rand(10000, 99999);

            /**
             * 上传店铺LOGO图片
             */
            if (!empty($_FILES['store_logo']['name'])) {
                $file = request()->file('store_logo');
                $result = $file->validate(['ext' => 'jpg,png,gif'])->move($upload_file, $file_name . '1');
                if ($result) {
                    $store_logo = $result->getFilename();

                    /* 处理图片 */
                    $image = Image::open($upload_file . DS . $store_logo);
                    $image->thumb(200, 60)->save($upload_file . DS . $store_logo);
                    $param['store_logo'] = $store_logo;
                } else {
                    showDialog($file->getError());
                }
                //删除旧店铺图片
                if (!empty($store_logo) && !empty($store_info['store_logo'])) {
                    @unlink($upload_file . DS . $store_info['store_logo']);
                }
            }

            /**
             * 上传店铺图像
             */
            if (!empty($_FILES['store_banner']['name'])) {
                $file = request()->file('store_banner');
                $result = $file->validate(['ext' => 'jpg,png,gif'])->move($upload_file, $file_name . '2');
                if ($result) {
                    $store_banner = $result->getFilename();
                    $param['store_banner'] = $store_banner;
                } else {
                    showDialog($file->getError());
                }
                //删除旧店铺图片
                if (!empty($store_banner) && !empty($store_info['store_banner'])) {
                    @unlink($upload_file . DS . $store_info['store_banner']);
                }
            }

            /**
             * 上传店铺图像
             */
            if (!empty($_FILES['store_avatar']['name'])) {
                $file = request()->file('store_avatar');
                $result = $file->validate(['ext' => 'jpg,png,gif'])->move($upload_file, $file_name . '3');
                if ($result) {
                    $store_avatar = $result->getFilename();
                    $param['store_avatar'] = $store_avatar;
                } else {
                    showDialog($file->getError());
                }
                //删除旧店铺图片
                if (!empty($store_avatar) && !empty($store_info['store_avatar'])) {
                    @unlink($upload_file . DS . $store_info['store_avatar']);
                }
            }

            if (!empty($_POST['store_name'])) {
                $store = $model_class->getStoreInfo(array('store_name' => input('param.store_name')));
                //店铺名存在,则提示错误
                if (!empty($store) && ($store_id != $store['store_id'])) {
                    showDialog('此店铺名称已被其它人使用，请换其它名称。', 'reload', 'error');
                    return;
                }
                $param['store_name'] = $_POST['store_name'];
            }
            //店铺名称修改处理
            if (input('param.store_name') != $store_info['store_name'] && !empty($_POST['store_name'])) {
                $where = array();
                $where['store_id'] = $store_id;
                $update = array();
                $update['store_name'] = input('param.store_name');
                db('goodscommon')->where($where)->update($update);
                db('goods')->where($where)->update($update);
            }

            //生成店铺二维码 
            import('qrcode.index', EXTEND_PATH);
            $PhpQRCode = new \PhpQRCode();
            $PhpQRCode->set('pngTempDir', BASE_UPLOAD_PATH . DS . ATTACH_STORE . DS . $store_id . DS);
            $PhpQRCode->set('date', WAP_SITE_URL . '/tmpl/store.html?store_id=' . $store_id);
            $PhpQRCode->set('pngTempName', $store_id . '_store.png');
            $PhpQRCode->init();

            $model_class->editStore($param, array('store_id' => $store_id));
            showDialog(lang('ds_common_save_succ'), url('sellersetting/setting'), 'succ');
        }
        /**
         * 实例化店铺等级模型
         */
        // 从基类中读取店铺等级信息
        $store_grade = $this->store_grade;

        //编辑器多媒体功能
        $editor_multimedia = false;
        $sg_fun = @explode('|', $store_grade['sg_function']);
        if (!empty($sg_fun) && is_array($sg_fun)) {
            foreach ($sg_fun as $fun) {
                if ($fun == 'editor_multimedia') {
                    $editor_multimedia = true;
                }
            }
        }
        $this->assign('editor_multimedia', $editor_multimedia);

        /**
         * 输出店铺信息
         */
        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('seller_setting');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem('store_setting');
        $this->assign('store_info', $store_info);
        $this->assign('store_grade', $store_grade);
        /**
         * 页面输出
         */
        return $this->fetch($this->template_dir . 'setting');
    }

    /**
     * 店铺幻灯片
     */
    public function store_slide() {
        /**
         * 模型实例化
         */
        $model_store = Model('store');
        $model_upload = Model('upload');
        /**
         * 保存店铺信息
         */
        if (request()->isPost()) {
            // 更新店铺信息
            $update = array();
            $update['store_slide'] = implode(',', $_POST['image_path']);
            $update['store_slide_url'] = implode(',', $_POST['image_url']);
            $model_store->editStore($update, array('store_id' => session('store_id')));

            // 删除upload表中数据
            $model_upload->delByWhere(array('upload_type' => 3, 'item_id' => session('store_id')));
            showDialog(lang('ds_common_save_succ'), url('sellersetting/store_slide'), 'succ');
        }

        // 删除upload中的无用数据
        $upload_info = $model_upload->getUploadList(array(
            'upload_type' => 3, 'item_id' => session('store_id')
                ), 'file_name');
        if (is_array($upload_info) && !empty($upload_info)) {
            foreach ($upload_info as $val) {
                @unlink(BASE_UPLOAD_PATH . DS . ATTACH_SLIDE . DS . $val['file_name']);
            }
        }
        $model_upload->delByWhere(array('upload_type' => 3, 'item_id' => session('store_id')));

        $store_info = $model_store->getStoreInfoByID(session('store_id'));
        if ($store_info['store_slide'] != '' && $store_info['store_slide'] != ',,,,') {
            $this->assign('store_slide', explode(',', $store_info['store_slide']));
            $this->assign('store_slide_url', explode(',', $store_info['store_slide_url']));
        }
        $this->setSellerCurMenu('seller_setting');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem('store_slide');
        /**
         * 页面输出
         */
        return $this->fetch($this->template_dir . 'slide');
    }

    /**
     * 店铺幻灯片ajax上传
     */
    public function silde_image_upload() {
        $upload_file = BASE_UPLOAD_PATH . DS . ATTACH_SLIDE;
        $file_name = session('store_id') . '_' . date('YmdHis') . rand(10000, 99999);
        $name = $_POST['id'];
        $file = request()->file($name);
        $result = $file->validate(['ext' => 'jpg,png,gif'])->move($upload_file, $file_name);
        if ($result) {
            $img_path = $result->getFilename();
        } else {
            echo json_encode($file->getError());
            die;
        }

        $output = array();
        if (!$result) {
            /**
             * 转码
             */
            $output['error'] = $file->getError();
            echo json_encode($output);
            die;
        }
        /**
         * 模型实例化
         */
        $model_upload = Model('upload');

        if (intval($_POST['file_id']) > 0) {
            $file_info = $model_upload->getOneUpload($_POST['file_id']);
            @unlink(BASE_UPLOAD_PATH . DS . ATTACH_SLIDE . DS . $file_info['file_name']);

            $update_array = array();
            $update_array['upload_id'] = intval($_POST['file_id']);
            $update_array['file_name'] = $img_path;
            $update_array['file_size'] = $_FILES[$_POST['id']]['size'];
            $model_upload->update($update_array);

            $output['file_id'] = intval($_POST['file_id']);
            $output['id'] = $_POST['id'];
            $output['file_name'] = $img_path;
            echo json_encode($output);
            die;
        } else {
            /**
             * 图片数据入库
             */
            $insert_array = array();
            $insert_array['file_name'] = $img_path;
            $insert_array['upload_type'] = '3';
            $insert_array['file_size'] = $_FILES[$_POST['id']]['size'];
            $insert_array['item_id'] = session('store_id');
            $insert_array['upload_time'] = time();

            $result = $model_upload->add($insert_array);

            if (!$result) {
                @unlink(BASE_UPLOAD_PATH . DS . ATTACH_SLIDE . DS . $img_path);
                $output['error'] = lang('store_slide_upload_fail');
                echo json_encode($output);
                die;
            }

            $output['file_id'] = $result;
            $output['id'] = $_POST['id'];
            $output['file_name'] = $img_path;
            echo json_encode($output);
            die;
        }
    }

    /**
     * ajax删除幻灯片图片
     */
    public function dorp_img() {
        /**
         * 模型实例化
         */
        $model_upload = Model('upload');
        $file_info = $model_upload->getOneUpload(intval($_GET['file_id']));
        if (!$file_info) {
            
        } else {
            @unlink(BASE_UPLOAD_PATH . DS . ATTACH_SLIDE . DS . $file_info['file_name']);
            $model_upload->del(intval($_GET['file_id']));
        }
        echo json_encode(array('succeed' => lang('ds_common_save_succ', 'UTF-8')));
        die;
    }

    /**
     * 卖家店铺主题设置
     *
     * @param string
     * @param string
     * @return
     */
    public function theme() {
        /**
         * 店铺信息
         */
        $store_class = Model('store');
        $store_info = $store_class->getStoreInfoByID(session('store_id'));
        /**
         * 主题配置信息
         */
        $style_data = array();
        $style_configurl = ROOT_PATH . 'public/static/store/styles/' . "styleconfig.php";

        if (file_exists($style_configurl)) {
            include_once($style_configurl);
        }
        /**
         * 当前店铺主题
         */
        $curr_store_theme = !empty($store_info['store_theme']) ? $store_info['store_theme'] : 'default';
        /**
         * 当前店铺预览图片
         */
        $curr_image = config('url_domain_root') . 'static/store/styles/' . $curr_store_theme . '/images/preview.jpg';

        $curr_theme = array(
            'curr_name' => $curr_store_theme,
            'curr_truename' => $style_data[$curr_store_theme]['truename'],
            'curr_image' => $curr_image
        );

        // 自营店全部可用
        if (checkPlatformStore()) {
            $themes = array_keys($style_data);
        } else {
            /**
             * 店铺等级
             */
            $grade_class = Model('storegrade');
            $grade = $grade_class->getOneGrade($store_info['grade_id']);

            /**
             * 可用主题
             */
            $themes = explode('|', $grade['sg_template']);
        }
        $theme_list = array();
        /**
         * 可用主题预览图片
         */
        foreach ($style_data as $key => $val) {
            if (in_array($key, $themes)) {
                $theme_list[$key] = array(
                    'name' => $key, 'truename' => $val['truename'],
                    'image' => config('url_domain_root') . 'static/store/styles/' . $key . '/images/preview.jpg'
                );
            }
        }
        /**
         * 页面输出
         */
        $this->setSellerCurMenu('seller_setting');
        $this->setSellerCurItem('store_theme');

        $this->assign('store_info', $store_info);
        $this->assign('curr_theme', $curr_theme);
        $this->assign('theme_list', $theme_list);
        return $this->fetch($this->template_dir . 'theme');
    }

    /**
     * 卖家店铺主题设置
     *
     * @param string
     * @param string
     * @return
     */
    public function set_theme() {
        //读取语言包
        $style = input('param.style_name');
        $style = isset($style) ? trim($style) : null;
        if (!empty($style) && file_exists(ROOT_PATH . 'public/static/store/styles/theme/' . $style . '/images/preview.jpg')) {
            $store_class = Model('store');
            $rs = $store_class->editStore(array('store_theme' => $style), array('store_id' => session('store_id')));
            showDialog(lang('store_theme_congfig_success'), 'reload', 'succ');
        } else {
            showDialog(lang('store_theme_congfig_fail'), '', 'succ');
        }
    }

    //v3-b12
    protected function getStoreMbSliders() {
        $store_info = Model('store')->getStoreInfoByID(session('store_id'));

        $mbSliders = @unserialize($store_info['mb_sliders']);
        if (!$mbSliders) {
            $mbSliders = array_fill(1, self::MAX_MB_SLIDERS, array(
                'img' => '', 'type' => 1, 'link' => '',
            ));
        }

        return $mbSliders;
    }

    protected function setStoreMbSliders(array $mbSliders) {
        return Model('store')->editStore(array(
                    'mb_sliders' => serialize($mbSliders),
                        ), array(
                    'store_id' => session('store_id'),
        ));
    }

    public function store_mb_sliders() {
        try {
            $fileName = (string) $_POST['id'];
            if (!preg_match('/^file_(\d+)$/', $fileName, $fileIndex) || empty($_FILES[$fileName]['name'])) {
                exception('参数错误');
            }

            $fileIndex = (int) $fileIndex[1];
            if ($fileIndex < 1 || $fileIndex > self::MAX_MB_SLIDERS) {
                exception('参数错误2');
            }

            $mbSliders = $this->getStoreMbSliders();
            $upload_file = BASE_UPLOAD_PATH . DS . ATTACH_STORE . DS . 'mobileslide';
            $file_name = session('store_id') . '_' . date('YmdHis') . rand(10000, 99999);
            $file_object = request()->file($fileName);
            $info = $file_object->rule('uniqid')->validate(['ext' => 'jpg,png,gif'])->move($upload_file, $file_name);
            if (!$info) {
                exception($file_object->getError());
            }
            $newImg = $info->getFilename();

            $oldImg = $mbSliders[$fileIndex]['img'];

            $mbSliders[$fileIndex]['img'] = $newImg;

            if (!$this->setStoreMbSliders($mbSliders)) {
                exception('更新失败');
            }

            if ($oldImg && file_exists($oldImg)) {
                unlink($oldImg);
            }

            echo json_encode(array(
                'uploadedUrl' => UPLOAD_SITE_URL . DS . ATTACH_STORE . 'mobileslide' . DS . $newImg,
            ));
        } catch (\Exception $ex) {
            echo json_encode(array(
                'error' => $ex->getMessage(),
            ));
        }
    }

    public function store_mb_sliders_drop() {
        try {
            $id = (int) $_REQUEST['id'];
            if ($id < 1 || $id > self::MAX_MB_SLIDERS) {
                exception('参数错误');
            }

            $mbSliders = $this->getStoreMbSliders();

            $mbSliders[$id]['img'] = '';

            if (!$this->setStoreMbSliders($mbSliders)) {
                exception('更新失败');
            }

            echo json_encode(array(
                'success' => true,
            ));
        } catch (\Exception $ex) {
            echo json_encode(array(
                'success' => false, 'error' => $ex->getMessage(),
            ));
        }
    }

    public function store_mobile() {
        $this->assign('max_mb_sliders', self::MAX_MB_SLIDERS);

        $store_info = Model('store')->getStoreInfoByID(session('store_id'));

        // 页头背景图
        $mb_title_img = $store_info['mb_title_img'] ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_info['mb_title_img'] : '';

        // 轮播
        $mbSliders = $this->getStoreMbSliders();

        if (request()->isPost()) {
            $update_array = array();

            if ($mb_title_img_del = !empty($_POST['mb_title_img_del'])) {
                $update_array['mb_title_img'] = '';
            }
            if (!empty($_FILES['mb_title_img']['name'])) {
                $upload_file = BASE_UPLOAD_PATH . DS . ATTACH_STORE;
                $file_name = session('store_id') . '_' . date('YmdHis') . rand(10000, 99999);
                $file_object = request()->file('mb_title_img');
                $result = $file_object->rule('uniqid')->validate(['ext' => 'jpg,png,gif'])->move($upload_file, $file_name);
                if ($result) {
                    $mb_title_img_del = true;
                    $update_array['mb_title_img'] = $result->getFilename();
                } else {
                    showDialog($file_object->getError());
                }
            }
            if ($mb_title_img_del && $mb_title_img && file_exists($mb_title_img)) {
                unlink($mb_title_img);
            }

            // mb_sliders
            $skuToValid = array();
            foreach ((array) $_POST['mb_sliders_links'] as $k => $v) {
                if ($k < 1 || $k > self::MAX_MB_SLIDERS) {
                    showDialog('参数错误');
                }

                $type = (int) $_POST['mb_sliders_type'][$k];
                switch ($type) {
                    case 1:
                        // 链接URL
                        $v = (string) $v;
                        if (!preg_match('#^https?://#', $v)) {
                            $v = '';
                        }
                        break;

                    case 2:
                        // 商品ID
                        $v = (int) $v;
                        if ($v < 1) {
                            $v = '';
                        } else {
                            $skuToValid[$k] = $v;
                        }
                        break;

                    default:
                        $type = 1;
                        $v = '';
                        break;
                }

                $mbSliders[$k]['type'] = $type;
                $mbSliders[$k]['link'] = $v;
            }

            if ($skuToValid) {
                $validSkus = db('goods')->field('goods_id')->where(array('goods_id' =>array('in', $skuToValid), 'store_id' => session('store_id'),))->key('goods_id')->select();
                if(!empty($validSkus)){
                    $validSkus = ds_changeArraykey($validSkus, 'goods_id');
                }
                foreach ($skuToValid as $k => $v) {
                    if (!isset($validSkus[$v])) {
                        $mbSliders[$k]['link'] = '';
                    }
                }
            }

            // sort
            for ($i = 0; $i < self::MAX_MB_SLIDERS; $i++) {
                $sortedMbSliders[$i + 1] = @$mbSliders[$_POST['mb_sliders_sort'][$i]];
            }

            $update_array['mb_sliders'] = serialize($sortedMbSliders);

            Model('store')->editStore($update_array, array(
                'store_id' => session('store_id'),
            ));

            showDialog('保存成功', url('sellersetting/store_mobile'), 'succ');
        }

        $mbSliderUrls = array();
        foreach ($mbSliders as $v) {
            if ($v['img']) {
                $mbSliderUrls[] = UPLOAD_SITE_URL . DS . ATTACH_STORE . DS . 'mobileslide' . DS . $v['img'];
            }
        }

        $this->assign('mb_title_img', $mb_title_img);
        $this->assign('mbSliders', $mbSliders);
        $this->assign('mbSliderUrls', $mbSliderUrls);
        $this->setSellerCurMenu('seller_setting');
        $this->setSellerCurItem('store_mobile');
        return $this->fetch($this->template_dir . 'store_mobile');
    }

    public function map() {
        $this->setSellerCurMenu('seller_setting');
        $this->setSellerCurItem('store_map');
        /**
         * 实例化模型
         */
        $model_class = Model('store');

        $store_id = session('store_id'); //当前店铺ID
        /**
         * 获取店铺信息
         */
        $store_info = $model_class->getStoreInfoByID($store_id);

        /**
         * 保存店铺设置
         */
        if (request()->isPost()) {
            Model('store')->editStore(array(
                'store_address' => $_POST['company_address_detail'],
                'region_id' => $_POST['district_id'] ? $_POST['district_id'] : ($_POST['city_id'] ? $_POST['city_id'] : ($_POST['province_id'] ? $_POST['province_id'] : 0)),
                'area_info' => $_POST['company_address'],
                'longitude' => $_POST['longitude'],
                'latitude' => $_POST['latitude'],
                    ), array(
                'store_id' => session('store_id'),
            ));

            showDialog('保存成功', url('sellersetting/map'), 'succ');
        }
        $this->assign('store_info', $store_info);
        return $this->fetch($this->template_dir . 'map');
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_type 导航类型
     * @param string $name 当前导航的name
     * @return
     */
    protected function getSellerItemList() {
        $menu_array = array(
            1 => array(
                'name' => 'store_setting', 'text' => lang('ds_member_path_store_config'),
                'url' => url('sellersetting/setting')
            ),
            2 => array(
                'name' => 'store_map', 'text' => lang('ds_member_path_store_map'),
                'url' => url('sellersetting/map')
            ),
            4 => array(
                'name' => 'store_slide', 'text' => lang('ds_member_path_store_slide'),
                'url' => url('sellersetting/store_slide')
            ), 5 => array(
                'name' => 'store_theme', 'text' => '店铺主题', 'url' => url('sellersetting/theme')
            ),
            7 => array(
                'name' => 'store_mobile', 'text' => '手机店铺设置', 'url' => url('sellersetting/store_mobile'),
            ),
        );
        return $menu_array;
    }

}

?>
