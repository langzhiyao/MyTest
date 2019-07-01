<?php

namespace app\home\controller;

use think\Lang;
use think\Validate;

class Sellersns extends BaseSeller {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/sellersns.lang.php');
    }

    public function index() {
        $this->add();
    }

    /**
     * 发布动态
     */
    public function add() {
        $model_goods = Model('goods');
        // 热销商品
        // where条件
        $where = array('store_id' => session('store_id'));
        $field = 'goods_id,goods_name,goods_image,goods_price,goods_salenum,store_id';
        $order = 'goods_salenum desc';
        $hotsell_list = $model_goods->getGoodsOnlineList($where, $field, 0, $order, 8);
        $this->assign('hotsell_list', $hotsell_list);

        // 新品
        // where条件
        $where = array('store_id' => session('store_id'));
        $field = 'goods_id,goods_name,goods_image,goods_price,goods_salenum,store_id';
        $order = 'goods_id desc';
        $new_list = $model_goods->getGoodsOnlineList($where, $field, 0, $order, 8);
        $this->assign('new_list', $new_list);
        $this->setSellerCurItem('store_sns_add');
        $this->setSellerCurMenu('sellersns');
        echo $this->fetch($this->template_dir . 'store_sns_add');
        exit;
    }

    /**
     * 上传图片
     */
    public function image_upload() {
        // 判断图片数量是否超限
        $model_album = Model('album');
        $album_limit = $this->store_grade['sg_album_limit'];
        if ($album_limit > 0) {
            $album_count = $model_album->getCount(array('store_id' => session('store_id')));
            if ($album_count >= $album_limit) {
                $error = lang('store_goods_album_climit');
                exit(json_encode(array('error' => $error)));
            }
        }

        $class_info = $model_album->getOne(array('store_id' => session('store_id'), 'is_default' => 1), 'albumclass');
        // 上传图片
        $file_save_path=BASE_UPLOAD_PATH . DS . ATTACH_GOODS . DS .session('store_id');
        $file = request()->file(input('param.id'));
        $result = $file->validate(['size'=>config('image_max_filesize'),'ext'=>'jpg,png,gif,jpeg'])->rule('uniqid')->move($file_save_path);
        if (!$result) {
            $output = array();
            $output['error'] = $file->getError();
            $output = json_encode($output);
            exit($output);
        }
        $img_path =  $result->getFilename();
        // 取得图像大小
        list($width, $height, $type, $attr) = getimagesize($file_save_path . DS . $img_path);

        // 存入相册
        $insert_array = array();
        $insert_array['apic_name'] = $img_path;
        $insert_array['apic_tag'] = '';
        $insert_array['aclass_id'] = $class_info['aclass_id'];
        $insert_array['apic_cover'] = $img_path;
        $insert_array['apic_size'] = intval($_FILES[$_POST['id']]['size']);
        $insert_array['apic_spec'] = $width . 'x' . $height;
        $insert_array['upload_time'] = TIMESTAMP;
        $insert_array['store_id'] = session('store_id');
        $model_album->addPic($insert_array);

        $data = array();
        $data ['image'] = cthumb($img_path, 240, session('store_id'));

        // 整理为json格式
        $output = json_encode($data);
        echo $output;
        exit();
    }

    /**
     * 保存动态
     */
    public function store_sns_save() {
        /**
         * 验证表单
         */
        $obj_validate = new Validate();
        $data=[
            'content'=>input('param.content'),
            'goods_url'=>input('goods_url')
        ];
        $message=[
            ['content','require|length:1,140',lang('store_sns_center_error')],
            ['goods_url','url',lang('store_goods_index_goods_price_null')]
        ];
       $error=$obj_validate->check($data,$message);
        if (!$error) {
            showDialog($obj_validate->getError());
        }

        $goodsdata = '';
        $content = '';
        $type = intval(input('param.type'));
        switch ($type) {
            case '2':
                $sns_image = trim(input('param.sns_image'));
                if ($sns_image != '')
                    $content = '<div class="fd-media">
									<div class="thumb-image"><a href="javascript:void(0);" nc_type="thumb-image"><img src="' . $sns_image . '" /><i></i></a></div>
									<div class="origin-image"><a href="javascript:void(0);" nc_type="origin-image"></a></div>
								</div>';
                break;
            case '9':
                $data = $this->getGoodsByUrl(html_entity_decode(input('param.goods_url')));
                $goodsdata = addslashes(json_encode($data));
                break;
            case '10':
                if (is_array(input('param.goods_id'))) {
                    $goods_id_array = input('param.goods_id');
                } else {
                    showDialog(lang('store_sns_choose_goods'));
                }
                $field = 'goods_id,store_id,goods_name,goods_image,goods_price,goods_freight';
                $where = array('store_id' => session('store_id'), 'goods_id' => array('in', $goods_id_array));
                $goods_array = Model('goods')->getGoodsList($where, $field);
                if (!empty($goods_array) && is_array($goods_array)) {
                    $goodsdata = array();
                    foreach ($goods_array as $val) {
                        $goodsdata[] = addslashes(json_encode($val));
                    }
                }
                break;
            case '3':
                if (is_array(input('param.goods_id'))) {
                    $goods_id_array = input('param.goods_id');
                } else {
                    showDialog(lang('store_sns_choose_goods'));
                }
                $field = 'goods_id,store_id,goods_name,goods_image,goods_price,goods_freight';
                $where = array('store_id' => session('store_id'), 'goods_id' => array('in', $goods_id_array));
                $goods_array = Model('goods')->getGoodsList($where, $field);
                if (!empty($goods_array) && is_array($goods_array)) {
                    $goodsdata = array();
                    foreach ($goods_array as $val) {
                        $goodsdata[] = addslashes(json_encode($val));
                    }
                }
                break;
            default:
                showDialog(lang('para_error'));
        }

        $model_stracelog = Model('storesnstracelog');
        // 插入数据
        $stracelog_array = array();
        $stracelog_array['strace_storeid'] = $this->store_info['store_id'];
        $stracelog_array['strace_storename'] = $this->store_info['store_name'];
        $stracelog_array['strace_storelogo'] = empty($this->store_info['store_avatar']) ? '' : $this->store_info['store_avatar'];
        $stracelog_array['strace_title'] = input('param.content');
        $stracelog_array['strace_content'] = $content;
        $stracelog_array['strace_time'] = time();
        $stracelog_array['strace_type'] = $type;
        if (isset($goodsdata) && is_array($goodsdata)) {
            $stracelog = array();
            foreach ($goodsdata as $val) {
                $stracelog_array['strace_goodsdata'] = $val;
                $stracelog[] = $stracelog_array;
            }
            $rs = $model_stracelog->saveStoreSnsTracelogAll($stracelog);
        } else {
            $stracelog_array['strace_goodsdata'] = $goodsdata;
            $rs = $model_stracelog->saveStoreSnsTracelog($stracelog_array);
        }
        if ($rs) {
            showDialog(lang('ds_common_op_succ'), url('Sellersns/index'), 'succ');
        } else {
            showDialog(lang('ds_common_op_fail'));
        }
    }

    /**
     * 动态设置
     */
    public function setting() {
        // 实例化模型
        $model_storesnssetting = Model('storesnssetting');
        $sauto_info = $model_storesnssetting->getStoreSnsSettingInfo(array('sauto_storeid' => session('store_id')));
        if (request()->isPost()) {
            $update = array();
            $update['sauto_storeid'] = session('store_id');
            $update['sauto_new'] = isset($_POST['new']) ? 1 : 0;
            $update['sauto_newtitle'] = trim($_POST['new_title']);
            $update['sauto_coupon'] = isset($_POST['coupon']) ? 1 : 0;
            $update['sauto_xianshi'] = isset($_POST['xianshi']) ? 1 : 0;
            $update['sauto_xianshititle'] = trim($_POST['xianshi_title']);
            $update['sauto_mansong'] = isset($_POST['mansong']) ? 1 : 0;
            $update['sauto_mansongtitle'] = trim($_POST['mansong_title']);
            $update['sauto_bundling'] = isset($_POST['bundling']) ? 1 : 0;
            $update['sauto_bundlingtitle'] = trim($_POST['bundling_title']);
            $update['sauto_groupbuy'] = isset($_POST['groupbuy']) ? 1 : 0;
            $update['sauto_groupbuytitle'] = trim($_POST['groupbuy_title']);
            $info=!empty($sauto_info) ? true:false;
            $result = $model_storesnssetting->isUpdate($info)->save($update);
            if($result){
            showDialog(lang('ds_common_save_succ'), url('sellersns/setting'), 'succ');
            }else {
                showDialog(lang('ds_common_save_fail'), url('sellersns/setting'), 'error');
            }
        } else {
            $this->assign('sauto_info', $sauto_info);
            $this->setSellerCurItem('store_sns_setting');
            $this->setSellerCurMenu('sellersns');
            return $this->fetch($this->template_dir . 'store_sns_setting');
        }
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string	$menu_type	导航类型
     * @param string 	$menu_key	当前导航的menu_key
     * @return
     */
    protected function getSellerItemList() {
        $menu_array = array(
            array('name' => 'store_sns_add', 'text' => lang('store_sns_add'), 'url' => url('Sellersns/add')),
            array('name' => 'store_sns_setting', 'text' => lang('store_sns_setting'), 'url' => url('Sellersns/setting')),
            array('name' => 'store_sns_brower', 'text' => lang('store_sns_browse'), 'url' => url('Storesnshome/index', ['sid' => session('store_id')])),
        );
        return $menu_array;
    }

    /**
     * 根据url取得商品信息
     */
    private function getGoodsByUrl($url) {
        $array = parse_url($url);
        if (isset($array['query'])) {
            // 未开启伪静态
            parse_str($array['query'], $arr);
            $id = $arr['goods_id'];
        } else {
            // 开启伪静态
            $item = explode('/', $array['path']);
            $item = end($item);
            $id = preg_replace('/item-(\d+)\.html/i', '$1', $item);
        }
        if (intval($id) > 0) {
            // 查询商品信息
            $field = 'goods_id,store_id,goods_name,goods_image,goods_price,goods_freight';
            $result = Model('goods')->getGoodsInfoByID($id, $field);
            if (!empty($result) && is_array($result)) {
                return $result;
            } else {
                showDialog(lang('store_sns_goods_url_error'));
            }
        } else {
            showDialog(lang('store_sns_goods_url_error'));
        }
    }

}

?>
