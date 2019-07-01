<?php

namespace app\home\controller;

use think\Lang;
use think\Validate;

class Sellerjoininc2c extends BaseMember {

    private $joinin_detail = NULL;

    public function __construct() {
        parent::__construct();
        $this->checkLogin();

        $model_seller = Model('seller');
        $seller_info = $model_seller->getSellerInfo(array('member_id' => session('member_id')));
        if (!empty($seller_info)) {
            header('location: '.url('Home/Seller/index'));
            exit;
        }
        
        if (request()->action() != 'check_seller_name_exist' && request()->action() != 'checkname') {
            $this->check_joinin_state();
        }
        
        $phone_array = explode(',', config('site_phone'));
        $this->assign('phone_array', $phone_array);
        $model_help = Model('help');
        $condition = array();
        $condition['type_id'] = '99'; //默认显示入驻流程;
        $list = $model_help->getShowStoreHelpList($condition);
        $this->assign('list', $list); //左侧帮助类型及帮助
        $this->assign('show_sign', 'joinin');
        $this->assign('html_title', config('site_name') . ' - ' . '商家入驻');
        $this->assign('article_list', ''); //底部不显示文章分类
    }

    private function check_joinin_state() {
        $model_store_joinin = Model('storejoinin');
        $joinin_detail = $model_store_joinin->getOne(array('member_id' => session('member_id')));
        if (!empty($joinin_detail)) {
            $this->joinin_detail = $joinin_detail;
            switch (intval($joinin_detail['joinin_state'])) {
                case STORE_JOIN_STATE_NEW:
                    $this->dostep4();
                    $this->show_join_message('入驻申请已经提交，请等待管理员审核', FALSE, '3');
                    break;
                case STORE_JOIN_STATE_PAY:
                    $this->show_join_message('已经提交，请等待管理员核对后为您开通店铺', FALSE, '4');
                    break;
                case STORE_JOIN_STATE_VERIFY_SUCCESS:
                    if (!in_array(request()->action(), array('pay', 'pay_save'))) {
                        $this->pay();
                    }
                    break;
                case STORE_JOIN_STATE_VERIFY_FAIL:
                    if (!in_array(request()->action(), array('step1', 'step2', 'step3', 'step4'))) {
                        $this->show_join_message('审核失败:' . $joinin_detail['joinin_message'], SHOP_SITE_URL . DS . '/sellerjoininc2c/step0');
                    }
                    break;
                case STORE_JOIN_STATE_PAY_FAIL:
                    if (!in_array(request()->action(), array('pay', 'pay_save'))) {
                        $this->show_join_message('付款审核失败:' . $joinin_detail['joinin_message'], SHOP_SITE_URL . DS . '/sellerjoininc2c/pay');
                    }
                    break;
                case STORE_JOIN_STATE_FINAL:
                    header('location:'.url('Home/Seller/index'));
                    exit;
                    break;
            }
        }
    }

    public function index() {
        $this->step0();
    }

    public function step0() {
        $model_document = Model('document');
        $document_info = $model_document->getOneByCode('open_store');
        $this->assign('agreement', $document_info['doc_content']);
        $this->assign('step', 'step1');
        $this->assign('sub_step', 'step0');
        echo $this->fetch($this->template_dir . 'step0');exit;
    }

    public function step1() {
        $this->assign('step', 'step2');
        $this->assign('sub_step', 'step1');
        return $this->fetch($this->template_dir . 'step1');
    }

    public function step2() {
        if (!empty($_POST)) {
            $param = array();
            $param['member_name'] = session('member_name');
            $param['company_name'] = $_POST['company_name'];
            $param['company_address'] = $_POST['company_address'];
            $param['longitude']=$_POST['longitude'];
            $param['latitude']=$_POST['latitude'];
            $param['company_address_detail'] = $_POST['company_address_detail'];
            $param['company_province_id'] = $_POST['district_id']?$_POST['district_id']:($_POST['city_id']?$_POST['city_id']:($_POST['province_id']?$_POST['province_id']:0));
            $param['contacts_name'] = $_POST['contacts_name'];
            $param['contacts_phone'] = $_POST['contacts_phone'];
            $param['contacts_email'] = $_POST['contacts_email'];
            $param['business_licence_number'] = $_POST['business_licence_number'];
            $param['business_sphere'] = $_POST['business_sphere'];
            $param['business_licence_number_electronic'] = $this->upload_image('business_licence_number_electronic');
            $param['general_taxpayer'] = $this->upload_image('general_taxpayer');

            $this->step2_save_valid($param);

            $model_store_joinin = Model('storejoinin');
            $joinin_info = $model_store_joinin->getOne(array('member_id' => session('member_id')));
            if (empty($joinin_info)) {
                $param['member_id'] = session('member_id');
                $model_store_joinin->save($param);
            } else {
                $model_store_joinin->modify($param, array('member_id' => session('member_id')));
            }
        }
        $this->assign('step', 'step2');
        $this->assign('sub_step', 'step2');
        echo $this->fetch($this->template_dir . 'step2');
        exit;
    }

    private function step2_save_valid($param) {
        
        //验证数据  BEGIN
        $rule = [
            ['company_name', 'require|length:1,50', '公司名称不能为空|公司名称必须小于50个字'],
//            ['company_address', 'require|length:1,50', '公司地址不能为空|公司地址必须小于50个字'],
            ['company_address_detail', 'require|length:1,50', '公司详细地址不能为空|公司详细地址必须小于50个字'],
            ['contacts_name', 'require|length:1,20', '联系人姓名不能为空|联系人姓名必须小于20个字'],
            ['contacts_phone', 'require|length:1,20', '联系人电话不能为空|联系人电话必须小于20个字'],
            ['contacts_email', 'require|email', '电子邮箱不能为空|电子邮箱格式不正确'],
            ['business_licence_number', 'require|length:1,20', '身份证号不能为空|身份证号必须小于20个字'],
            ['business_sphere', 'require|length:1,20', '姓名不能为空|姓名不能小于50个字'],
            ['business_licence_number_electronic', 'require', '身份证扫描件不能为空'],
        ];
        $validate = new Validate();
        $validate_result = $validate->check($param, $rule);
        if (!$validate_result) {
            $this->error($validate->getError());
        }
        //验证数据  END
    }

    public function step3() {
        if (!empty($_POST)) {
            $param = array();

            $param['settlement_bank_account_name'] = $_POST['settlement_bank_account_name'];
            $param['settlement_bank_account_number'] = $_POST['settlement_bank_account_number'];

            $this->step3_save_valid($param);

            $model_store_joinin = Model('storejoinin');
            $model_store_joinin->modify($param, array('member_id' => session('member_id')));
        }

        //商品分类
        $gc = Model('goodsclass');
        $gc_list = $gc->getGoodsClassListByParentId(0);
        $this->assign('gc_list', $gc_list);

        //店铺等级
        $grade_list = rkcache('store_grade', true);
        //附加功能
        if (!empty($grade_list) && is_array($grade_list)) {
            foreach ($grade_list as $key => $grade) {
                $sg_function = explode('|', $grade['sg_function']);
                if (!empty($sg_function[0]) && is_array($sg_function)) {
                    foreach ($sg_function as $key1 => $value) {
                        if ($value == 'editor_multimedia') {
                            $grade_list[$key]['function_str'] .= '富文本编辑器';
                        }
                    }
                } else {
                    $grade_list[$key]['function_str'] = '无';
                }
            }
        }
        $this->assign('grade_list', $grade_list);

        //店铺分类
        $model_store = Model('storeclass');
        $store_class = $model_store->getStoreClassList(array(), '', false);
        $this->assign('store_class', $store_class);

        $this->assign('step', '3');
        $this->assign('sub_step', 'step3');
        echo $this->fetch($this->template_dir . 'step3');
        exit;
    }

    private function step3_save_valid($param) {
        //验证数据  BEGIN
        $rule = [
            ['settlement_bank_account_name', 'require|length:1,50', '支付宝不能为空|支付宝必须小于50个字'],
            ['settlement_bank_account_number', 'require|length:1,20', '支付宝账号不能为空|支付宝账号必须小于20个字'],
        ];
        $validate = new Validate();
        $validate_result = $validate->check($param, $rule);
        if (!$validate_result) {
            $this->error($validate->getError());
        }
        //验证数据  END
        
    }

    public function check_seller_name_exist() {
        $condition = array();
        $condition['seller_name'] = $_GET['seller_name'];

        $model_seller = Model('seller');
        $result = $model_seller->isSellerExist($condition);

        if ($result) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    public function step4() {
        $store_class_ids = array();
        $store_class_names = array();
        if (!empty($_POST['store_class_ids'])) {
            foreach ($_POST['store_class_ids'] as $value) {
                $store_class_ids[] = $value;
            }
        }
        if (!empty($_POST['store_class_names'])) {
            foreach ($_POST['store_class_names'] as $value) {
                $store_class_names[] = $value;
            }
        }
        //取最小级分类最新分佣比例
        $sc_ids = array();
        foreach ($store_class_ids as $v) {
            $v = explode(',', trim($v, ','));
            if (!empty($v) && is_array($v)) {
                $sc_ids[] = end($v);
            }
        }
        $store_class_commis_rates = array();
        if (!empty($sc_ids)) {
            $goods_class_list = Model('goodsclass')->getGoodsClassListByIds($sc_ids);
            if (!empty($goods_class_list) && is_array($goods_class_list)) {
                $sc_ids = array();
                foreach ($goods_class_list as $v) {
                    $store_class_commis_rates[] = $v['commis_rate'];
                }
            }
        }
        $param = array();
        $param['seller_name'] = $_POST['seller_name'];
        $param['store_name'] = $_POST['store_name'];
        $param['store_class_ids'] = serialize($store_class_ids);
        $param['store_class_names'] = serialize($store_class_names);
        $param['joinin_year'] = intval($_POST['joinin_year']);
        $param['joinin_state'] = STORE_JOIN_STATE_NEW;
        $param['store_class_commis_rates'] = implode(',', $store_class_commis_rates);

        //取店铺等级信息
        $grade_list = rkcache('store_grade', true);
        if (!empty($grade_list[$_POST['sg_id']])) {
            $param['sg_id'] = $_POST['sg_id'];
            $param['sg_name'] = $grade_list[$_POST['sg_id']]['sg_name'];
            $param['sg_info'] = serialize(array('sg_price' => $grade_list[$_POST['sg_id']]['sg_price']));
        }

        //取最新店铺分类信息
        $store_class_info = Model('storeclass')->getStoreClassInfo(array('sc_id' => intval($_POST['sc_id'])));
        if ($store_class_info) {
            $param['sc_id'] = $store_class_info['sc_id'];
            $param['sc_name'] = $store_class_info['sc_name'];
            $param['sc_bail'] = $store_class_info['sc_bail'];
        }

        //店铺应付款
        $param['paying_amount'] = floatval($grade_list[$_POST['sg_id']]['sg_price']) * $param['joinin_year'] + floatval($param['sc_bail']);
        $this->step4_save_valid($param);
        
        $model_store_joinin = Model('storejoinin');
        $model_store_joinin->modify($param, array('member_id' => session('member_id')));
        
        header('location:'.url('Home/Sellerjoininc2c/index'));
        exit;
    }

    private function step4_save_valid($param) {
        //验证数据  BEGIN
        $rule = [
            ['store_name', 'require|length:1,50', '店铺名称不能为空|店铺名称必须小于50个字'],
            ['sg_id', 'require', '店铺等级不能为空'],
            ['sc_id', 'require', '店铺分类不能为空'],
        ];
        $validate = new Validate();
        $validate_result = $validate->check($param, $rule);
        if (!$validate_result) {
            $this->error($validate->getError());
        }
        //验证数据  END
    }

    public function pay() {
        if (!empty($this->joinin_detail['sg_info'])) {
            $store_grade_info = Model('storegrade')->getOneGrade($this->joinin_detail['sg_id']);
            $this->joinin_detail['sg_price'] = $store_grade_info['sg_price'];
        } else {
            $this->joinin_detail['sg_info'] = @unserialize($this->joinin_detail['sg_info']);
            if (is_array($this->joinin_detail['sg_info'])) {
                $this->joinin_detail['sg_price'] = $this->joinin_detail['sg_info']['sg_price'];
            }
        }
        $this->assign('joinin_detail', $this->joinin_detail);
        $this->assign('step', '4');
        $this->assign('sub_step', 'pay');
        echo $this->fetch($this->template_dir . 'pay');
        exit;
    }

    public function pay_save() {
        $param = array();
        $param['paying_money_certificate'] = $this->upload_image('paying_money_certificate');
        $param['paying_money_certificate_explain'] = $_POST['paying_money_certificate_explain'];
        $param['joinin_state'] = STORE_JOIN_STATE_PAY;

        if (empty($param['paying_money_certificate'])) {
            $this->error('请上传付款凭证');
        }

        $model_store_joinin = Model('storejoinin');
        $model_store_joinin->modify($param, array('member_id' => session('member_id')));

        header('location:'.url('Home/Sellerjoininc2c/index'));
        exit;
    }

    private function dostep4() {
        $model_store_joinin = Model('storejoinin');
        $joinin_detail = $model_store_joinin->getOne(array('member_id' => session('member_id')));
        $joinin_detail['store_class_ids'] = unserialize($joinin_detail['store_class_ids']);
        $joinin_detail['store_class_names'] = unserialize($joinin_detail['store_class_names']);
        $joinin_detail['store_class_commis_rates'] = isset($joinin_detail['store_class_commis_rates'])?explode(',', $joinin_detail['store_class_commis_rates']):"";
        $joinin_detail['sg_info'] = unserialize($joinin_detail['sg_info']);
        $this->assign('joinin_detail', $joinin_detail);
    }

    private function show_join_message($message, $btn_next = FALSE, $step = 'step2') {
        $this->assign('joinin_message', $message);
        $this->assign('btn_next', $btn_next);
        $this->assign('step', $step);
        $this->assign('sub_step', 'step4');
        echo $this->fetch($this->template_dir . 'step4');
        exit;
    }

    private function upload_image($file) {
        
        $pic_name= '';
        $upload_file = BASE_UPLOAD_PATH . DS . 'store_joinin' . DS;
        if (!empty($_FILES[$file]['name'])) {
            $file_object = request()->file($file);
            //设置特殊图片名称
            $file_name = session('member_id') . '_' . date('YmdHis') . rand(10000, 99999);
            $info = $file_object->rule('uniqid')->validate(['ext' => 'jpg,png,gif'])->move($upload_file, $file_name);
            if ($info) {
                $pic_name = $info->getFilename();
            } else {
                // 目前并不出该提示
                $error = $file_object->getError();
                $data['state'] = 'false';
                $data['message'] = $error;
                $data['origin_file_name'] = $_FILES[$file]["name"];
                echo json_encode($data);
            }
        }
        
        
        return $pic_name;
    }

    /**
     * 检查店铺名称是否存在
     *
     * @param 
     * @return 
     */
    public function checkname() {
        /**
         * 实例化卖家模型
         */
        $model_store = Model('store');
        $store_name = $_GET['store_name'];
        $store_info = $model_store->getStoreInfo(array('store_name' => $store_name));
        if (!empty($store_info['store_name']) && $store_info['member_id'] != session('member_id')) {
            echo 'false';
        } else {
            echo 'true';
        }
    }

}

?>
