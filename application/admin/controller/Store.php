<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Store extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/store.lang.php');
    }

    /**
     * 店铺
     */
    public function store() {
        $model_store = Model('store');

        $owner_and_name = input('get.owner_and_name');
        if (trim($owner_and_name) != '') {
            $condition['member_name'] = array('like', '%' . $owner_and_name . '%');
        }
        $store_name = input('get.store_name');
        if (trim($store_name) != '') {
            $condition['store_name'] = array('like', '%' . trim($store_name) . '%');
        }
        $grade_id = input('get.grade_id');
        if (intval($grade_id) > 0) {
            $condition['grade_id'] = intval($grade_id);
        }
        $store_type = input('get.store_type');
        switch ($store_type) {
            case 'close':
                $condition['store_state'] = 0;
                break;
            case 'open':
                $condition['store_state'] = 1;
                break;
            case 'expired':
                $condition['store_end_time'] = array('between', array(1, TIMESTAMP));
                $condition['store_state'] = 1;
                break;
            case 'expire':
                $condition['store_end_time'] = array('between', array(TIMESTAMP, TIMESTAMP + 864000));
                $condition['store_state'] = 1;
                break;
        }

        // 默认店铺管理不包含自营店铺
        $condition['is_own_shop'] = 0;

        //店铺列表
        $store_list = $model_store->getStoreList($condition, 10, 'store_id desc');
        $page = $model_store->page_info->render();
        //店铺等级
        $model_grade = Model('storegrade');
        $grade_list = $model_grade->getGradeList();
        $search_grade_list = array();
        if (!empty($grade_list)) {
            $search_grade_list[0] = '未选择等级';
            foreach ($grade_list as $k => $v) {
                $search_grade_list[$v['sg_id']] = $v['sg_name'];
            }
        }
        $this->assign('search_grade_list', $search_grade_list);

        $this->assign('grade_list', $grade_list);
        $this->assign('store_list', $store_list);
        $this->assign('store_type', $this->_get_store_type_array());
        $this->assign('page', $page);
        $this->setAdminCurItem('store');
        return $this->fetch('store');
    }

    private function _get_store_type_array() {
        return array(
            'open' => '开启',
            'close' => '关闭',
            'expire' => '即将到期',
            'expired' => '已到期'
        );
    }

    /**
     * 店铺编辑
     */
    public function store_edit() {
        $store_id = input('param.store_id');
        $model_store = Model('store');
        //保存
        if (!request()->isPost()) {
            //取店铺信息
            $store_array = $model_store->getStoreInfoByID($store_id);
            if (empty($store_array)) {
                $this->error(lang('store_no_exist'));
            }
            //整理店铺内容
            $store_array['store_end_time'] = $store_array['store_end_time'] ? date('Y-m-d', $store_array['store_end_time']) : '';
            //店铺分类
            $model_store_class = Model('storeclass');
            $parent_list = $model_store_class->getStoreClassList(array(), '', false);

            //店铺等级
            $model_grade = Model('storegrade');
            $grade_list = $model_grade->getGradeList();
            $this->assign('grade_list', $grade_list);
            $this->assign('class_list', $parent_list);
            $this->assign('store_array', $store_array);

            $joinin_detail = Model('storejoinin')->getOne(array('member_id' => $store_array['member_id']));
            $this->assign('joinin_detail', $joinin_detail);
            $this->setAdminCurItem('store_edit');
            return $this->fetch('store_edit');
        } else {
            //取店铺等级的审核
            $model_grade = Model('storegrade');
            $grade_array = $model_grade->getOneGrade(intval($_POST['grade_id']));
            if (empty($grade_array)) {
                $this->error(lang('please_input_store_level'));
            }
            //结束时间
            $time = '';
            if (trim($_POST['end_time']) != '') {
                $time = strtotime($_POST['end_time']);
            }
            $update_array = array();
            $update_array['store_name'] = trim($_POST['store_name']);
            $update_array['sc_id'] = intval($_POST['sc_id']);
            $update_array['grade_id'] = intval($_POST['grade_id']);
            $update_array['store_end_time'] = $time;
            $update_array['store_state'] = intval($_POST['store_state']);
            $update_array['store_baozh'] = trim($_POST['store_baozh']); //保障服务开关
            $update_array['store_baozhopen'] = trim($_POST['store_baozhopen']); //保证金显示开关
            $update_array['store_baozhrmb'] = trim($_POST['store_baozhrmb']); //新加保证金-金额
            $update_array['store_qtian'] = trim($_POST['store_qtian']); //保障服务-七天退换
            $update_array['store_zhping'] = trim($_POST['store_zhping']); //保障服务-正品保证
            $update_array['store_erxiaoshi'] = trim($_POST['store_erxiaoshi']); //保障服务-两小时发货
            $update_array['store_tuihuo'] = trim($_POST['store_tuihuo']); //保障服务-退货承诺
            $update_array['store_shiyong'] = trim($_POST['store_shiyong']); //保障服务-试用
            $update_array['store_xiaoxie'] = trim($_POST['store_xiaoxie']); //保障服务-消协
            $update_array['store_huodaofk'] = trim($_POST['store_huodaofk']); //保障服务-货到付款
            $update_array['store_shiti'] = trim($_POST['store_shiti']); //保障服务-实体店铺
            if ($update_array['store_state'] == 0) {
                //根据店铺状态修改该店铺所有商品状态
                $model_goods = Model('goods');
                $model_goods->editProducesOffline(array('store_id' => $store_id));
                $update_array['store_close_info'] = trim($_POST['store_close_info']);
                $update_array['store_recommend'] = 0;
            } else {
                //店铺开启后商品不在自动上架，需要手动操作
                $update_array['store_close_info'] = '';
//                $update_array['store_recommend'] = intval($_POST['store_recommend']);
            }
            $result = $model_store->editStore($update_array, array('store_id' => $store_id));
            if ($result) {
                //店铺名称修改处理 v3-b12
                $store_name = trim($_POST['store_name']);
                $store_info = $model_store->getStoreInfoByID($store_id);
                if (!empty($store_name)) {
                    $where = array();
                    $where['store_id'] = $store_id;
                    $update = array();
                    $update['store_name'] = $store_name;
                    $bllGoods = db('goodscommon')->where($where)->update($update);
                    $bllGoods = db('goods')->where($where)->update($update);
                }

                $this->log(lang('ds_edit').lang('store') . '[' . $_POST['store_name'] . ']', 1);
                $this->success(lang('ds_common_save_succ'), url('Admin/Store/store'));
            } else {
                $this->log(lang('ds_edit').lang('store') . '[' . $_POST['store_name'] . ']', 1);
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 编辑保存注册信息
     */
    public function edit_save_joinin() {
        if (request()->isPost()) {
            $member_id = input('post.member_id');
            if ($member_id <= 0) {
                $this->error(lang('param_error'));
            }
            $param = array();
            $param['company_name'] = input('post.company_name');
            $param['company_province_id'] = intval(input('post.province_id'));
            $param['company_address'] = input('post.company_address');
            $param['company_address_detail'] = input('post.company_address_detail');
            $param['company_phone'] = input('post.company_phone');
            $param['company_employee_count'] = intval(input('post.company_employee_count'));
            $param['company_registered_capital'] = intval(input('post.company_registered_capital'));
            $param['contacts_name'] = input('post.contacts_name');
            $param['contacts_phone'] = input('post.contacts_phone');
            $param['contacts_email'] = input('post.contacts_email');
            $param['business_licence_number'] = input('post.business_licence_number');
            $param['business_licence_address'] = input('post.business_licence_address');
            $param['business_licence_start'] = input('post.business_licence_start');
            $param['business_licence_end'] = input('post.business_licence_end');
            $param['business_sphere'] = input('post.business_sphere');
            if ($_FILES['business_licence_number_electronic']['name'] != '') {
                $param['business_licence_number_electronic'] = $this->upload_image('business_licence_number_electronic');
            }
            $param['organization_code'] = input('post.organization_code');
            if ($_FILES['organization_code_electronic']['name'] != '') {
                $param['organization_code_electronic'] = $this->upload_image('organization_code_electronic');
            }
            if ($_FILES['general_taxpayer']['name'] != '') {
                $param['general_taxpayer'] = $this->upload_image('general_taxpayer');
            }
            $param['bank_account_name'] = input('post.bank_account_name');
            $param['bank_account_number'] = input('post.bank_account_number');
            $param['bank_name'] = input('post.bank_name');
            $param['bank_code'] = input('post.bank_code');
            $param['bank_address'] = input('post.bank_address');
            if ($_FILES['bank_licence_electronic']['name'] != '') {
                $param['bank_licence_electronic'] = $this->upload_image('bank_licence_electronic');
            }
            $param['settlement_bank_account_name'] = input('post.settlement_bank_account_name');
            $param['settlement_bank_account_number'] = input('post.settlement_bank_account_number');
            $param['settlement_bank_name'] = input('post.settlement_bank_name');
            $param['settlement_bank_code'] = input('post.settlement_bank_code');
            $param['settlement_bank_address'] = input('post.settlement_bank_address');
            $param['tax_registration_certificate'] = input('post.tax_registration_certificate');
            $param['taxpayer_id'] = input('post.taxpayer_id');
            if ($_FILES['tax_registration_certificate_electronic']['name'] != '') {
                $param['tax_registration_certificate_electronic'] = $this->upload_image('tax_registration_certificate_electronic');
            }
            $result = Model('storejoinin')->editStoreJoinin(array('member_id' => $member_id), $param);
            if ($result) {
                //更新店铺信息
                $store_update = array();
                $store_update['store_company_name'] = $param['company_name'];
//                $store_update['area_info'] = $param['company_address'];
                $store_update['store_address'] = $param['company_address_detail'];
                $model_store = Model('store');
                $store_info = $model_store->getStoreInfo(array('member_id' => $member_id));
                if (!empty($store_info)) {
                    $r = $model_store->editStore($store_update, array('member_id' => $member_id));
                    $this->log('编辑店铺信息' . '[ID:' . $r . ']', 1);
                }
                $this->success(lang('ds_common_op_succ'), url('Admin/Store/store'));
            } else {
                $this->error(lang('ds_common_op_fail'));
            }
        }
    }

    private function upload_image($file) {
        $pic_name = '';
        $upload = new UploadFile();
        $uploaddir = ATTACH_PATH . DS . 'store_joinin' . DS;
        $upload->set('default_dir', $uploaddir);
        $upload->set('allow_type', array('jpg', 'jpeg', 'gif', 'png'));
        if (!empty($_FILES[$file]['name'])) {
            $result = $upload->upfile($file);
            if ($result) {
                $pic_name = $upload->file_name;
                $upload->file_name = '';
            }
        }
        return $pic_name;
    }

    /**
     * 店铺经营类目管理
     */

    public function store_bind_class() {
        
        $store_id = intval(input('param.store_id'));

        $model_store = Model('store');
        $model_store_bind_class = Model('storebindclass');
        $model_goods_class = Model('goodsclass');

        $gc_list = $model_goods_class->getGoodsClassListByParentId(0);
        $this->assign('gc_list', $gc_list);

        $store_info = $model_store->getStoreInfoByID($store_id);
        if (empty($store_info)) {
            $this->error(lang('param_error'));
        }
        $this->assign('store_info', $store_info);

        $store_bind_class_list = $model_store_bind_class->getStoreBindClassList(array('store_id' => $store_id, 'state' => array('in', array(1, 2))), null);
        $goods_class = Model('goodsclass')->getGoodsClassIndexedListAll();
        for ($i = 0, $j = count($store_bind_class_list); $i < $j; $i++) {
            $store_bind_class_list[$i]['class_1_name'] = $goods_class[$store_bind_class_list[$i]['class_1']]['gc_name'];
            $store_bind_class_list[$i]['class_2_name'] = @$goods_class[$store_bind_class_list[$i]['class_2']]['gc_name'];
            $store_bind_class_list[$i]['class_3_name'] = @$goods_class[$store_bind_class_list[$i]['class_3']]['gc_name'];
        }
        $this->assign('store_bind_class_list', $store_bind_class_list);
        $this->setAdminCurItem('store_bind_class');
        return $this->fetch('store_bind_class');
    }

    /**
     * 添加经营类目
     */
    public function store_bind_class_add() {
        $store_id = intval($_POST['store_id']);
        $commis_rate = intval($_POST['commis_rate']);
        if ($commis_rate < 0 || $commis_rate > 100) {
            $this->error(lang('param_error'));
        }
        @list($class_1, $class_2, $class_3) = explode(',', $_POST['goods_class']);

        $model_store_bind_class = Model('storebindclass');

        $param = array();
        $param['store_id'] = $store_id;
        $param['class_1'] = $class_1;
        $param['state'] = 1;
        if (!empty($class_2)) {
            $param['class_2'] = $class_2;
        }
        if (!empty($class_3)) {
            $param['class_3'] = $class_3;
        }

        // 检查类目是否已经存在
        $store_bind_class_info = $model_store_bind_class->getStoreBindClassInfo($param);
        if (!empty($store_bind_class_info)) {
            $this->error('该类目已经存在');
        }

        $param['commis_rate'] = $commis_rate;
        $result = $model_store_bind_class->addStoreBindClass($param);

        if ($result) {
            $this->log('新增店铺经营类目，类目编号:' . $result . ',店铺编号:' . $store_id);
            $this->success(lang('ds_common_save_succ'));
        } else {
            $this->error(lang('ds_common_save_fail'));
        }
    }

    /**
     * 删除经营类目
     */
    public function store_bind_class_del() {
        $bid = intval($_POST['bid']);

        $data = array();
        $data['result'] = true;

        $model_store_bind_class = Model('storebindclass');
        $model_goods = Model('goods');

        $store_bind_class_info = $model_store_bind_class->getStoreBindClassInfo(array('bid' => $bid));
        if (empty($store_bind_class_info)) {
            $data['result'] = false;
            $data['message'] = '经营类目删除失败';
            echo json_encode($data);
            die;
        }

        // 商品下架
        $condition = array();
        $condition['store_id'] = $store_bind_class_info['store_id'];
        $gc_id = $store_bind_class_info['class_1'] . ',' . $store_bind_class_info['class_2'] . ',' . $store_bind_class_info['class_3'];
        $update = array();
        $update['goods_stateremark'] = '管理员删除经营类目';
        $condition['gc_id'] = array('in', rtrim($gc_id, ','));
        $model_goods->editProducesLockUp($update, $condition);

        $result = $model_store_bind_class->delStoreBindClass(array('bid' => $bid));

        if (!$result) {
            $data['result'] = false;
            $data['message'] = '经营类目删除失败';
        }
        $this->log('删除店铺经营类目，类目编号:' . $bid . ',店铺编号:' . $store_bind_class_info['store_id']);
        echo json_encode($data);
        die;
    }

    public function store_bind_class_update() {
        $bid = intval(input('param.id'));
        if ($bid <= 0) {
            echo json_encode(array('result' => FALSE, 'message' => lang('param_error')));
            die;
        }
        $new_commis_rate = intval(input('param.value'));
        if ($new_commis_rate < 0 || $new_commis_rate >= 100) {
            echo json_encode(array('result' => FALSE, 'message' => lang('param_error')));
            die;
        } else {
            $update = array('commis_rate' => $new_commis_rate);
            $condition = array('bid' => $bid);
            $model_store_bind_class = Model('storebindclass');
            $result = $model_store_bind_class->editStoreBindClass($update, $condition);
            if ($result) {
                $this->log('更新店铺经营类目，类目编号:' . $bid);
                echo json_encode(array('result' => TRUE));
                die;
            } else {
                echo json_encode(array('result' => FALSE, 'message' => lang('ds_common_op_fail')));
                die;
            }
        }
    }

    /**
     * 店铺 待审核列表
     */
    public function store_joinin() {
        //店铺列表
        if (input('param.owner_and_name')) {
            $condition['member_name'] = array('like', '%' . input('param.owner_and_name') . '%');
        }
        if (input('param.store_name')) {
            $condition['store_name'] = array('like', '%' . input('param.store_name') . '%');
        }
        if (input('param.grade_id') && intval(input('param.grade_id')) > 0) {
            $condition['sg_id'] = input('param.grade_id');
        }
        if (input('param.joinin_state') && intval(input('param.joinin_state')) > 0) {
            $condition['joinin_state'] = input('param.joinin_state');
        } else {
            $condition['joinin_state'] = array('gt', 0);
        }
        $model_store_joinin = Model('storejoinin');
        $store_list = $model_store_joinin->getList($condition, 10, 'joinin_state asc');
        $page = $model_store_joinin->page_info->render();
        $this->assign('store_list', $store_list);
        $this->assign('joinin_state_array', $this->get_store_joinin_state());

        //店铺等级
        $model_grade = Model('storegrade');
        $grade_list = $model_grade->getGradeList();
        $this->assign('grade_list', $grade_list);

        $this->assign('page', $page);
        $this->setAdminCurItem('store_joinin');
        return $this->fetch('store_joinin');
    }

    /**
     * 经营类目申请列表
     */

    public function store_bind_class_applay_list() {
        $condition = array();
        // 不显示自营店铺绑定的类目
        $state = input('state');
        if ($state != '') {
            $condition['state'] = intval($state);
            if (!in_array($condition['state'], array('0', '1',)))
                unset($condition['state']);
        } else {
            $condition['state'] = array('in', array('0', '1',));
        }

        $store_id = input('store_id');
        if (intval($store_id)) {
            $condition['store_id'] = intval($store_id);
        }

        $model_store_bind_class = Model('storebindclass');
        $store_bind_class_list = $model_store_bind_class->getStoreBindClassList($condition, 15, 'state asc,bid desc');
        $goods_class = Model('goodsclass')->getGoodsClassIndexedListAll();
        $store_ids = array();

        for ($i = 0; $i < count($store_bind_class_list); $i++) {
            $store_bind_class_list[$i]['class_1_name'] = @$goods_class[$store_bind_class_list[$i]['class_1']]['gc_name'];
            $store_bind_class_list[$i]['class_2_name'] = @$goods_class[$store_bind_class_list[$i]['class_2']]['gc_name'];
            $store_bind_class_list[$i]['class_3_name'] = @$goods_class[$store_bind_class_list[$i]['class_3']]['gc_name'];
            $store_ids[] = $store_bind_class_list[$i]['store_id'];
        }

        //取店铺信息
        $model_store = Model('store');
        $store_list = $model_store->getStoreList(array('store_id' => array('in', $store_ids)), null);
        $bind_store_list = array();
        if (!empty($store_list) && is_array($store_list)) {
            foreach ($store_list as $k => $v) {
                $bind_store_list[$v['store_id']]['store_name'] = $v['store_name'];
                $bind_store_list[$v['store_id']]['seller_name'] = $v['seller_name'];
            }
        }
        $this->assign('bind_list', $store_bind_class_list);
        $this->assign('bind_store_list', $bind_store_list);

        $this->assign('page', $model_store_bind_class->page_info->render());
        $this->setAdminCurItem('store_bind_class_applay_list');
        return $this->fetch('bind_class_applay_list');
    }

    /**
     * 审核经营类目申请
     */
    public function store_bind_class_applay_check() {
        $model_store_bind_class = Model('storebindclass');
        $condition = array();
        $condition['bid'] = intval(input('param.bid'));
        $condition['state'] = 0;
        $update = $model_store_bind_class->editStoreBindClass(array('state' => 1), $condition);
        if ($update) {
            $this->log('审核新经营类目申请，店铺ID：' . input('param.store_id'), 1);
            $this->success('审核成功', getReferer());
        } else {
            $this->error('审核失败', getReferer());
        }
    }

    /**
     * 删除经营类目申请
     */
    public function store_bind_class_applay_del() {
        $model_store_bind_class = Model('storebindclass');
        $condition = array();
        $condition['bid'] = intval(input('param.bid'));
        $del = $model_store_bind_class->delStoreBindClass($condition);
        if ($del) {
            $this->log('删除经营类目，店铺ID：' . input('param.store_id'), 1);
            $this->success('删除成功', getReferer());
        } else {
            $this->error('删除失败', getReferer());
        }
    }

    private function get_store_joinin_state() {
        $joinin_state_array = array(
            STORE_JOIN_STATE_NEW => '新申请',
            STORE_JOIN_STATE_PAY => '已付款',
            STORE_JOIN_STATE_VERIFY_SUCCESS => '待付款',
            STORE_JOIN_STATE_VERIFY_FAIL => '审核失败',
            STORE_JOIN_STATE_PAY_FAIL => '付款审核失败',
            STORE_JOIN_STATE_FINAL => '开店成功',
        );
        return $joinin_state_array;
    }

    /**
     * 店铺续签申请列表
     */
    public function reopen_list() {
        $condition = array();
        $store_id = input('get.store_id');
        if (intval($store_id)) {
            $condition['re_store_id'] = intval($store_id);
        }
        $store_name = input('get.store_name');
        if (!empty($store_name)) {
            $condition['re_store_name'] = $store_name;
        }
        $re_state = input('get.re_state');
        if ($re_state != '') {
            $condition['re_state'] = intval($re_state);
        }
        $model_store_reopen = Model('storereopen');
        $reopen_list = $model_store_reopen->getStoreReopenList($condition, 15);

        $this->assign('reopen_list', $reopen_list);

        $this->assign('page', $model_store_reopen->page_info->render());
        $this->setAdminCurItem('reopen_list');
        return $this->fetch('store_reopen_list');
    }

    /**
     * 审核店铺续签申请
     */
    public function reopen_check() {
        if (intval(input('param.re_id')) <= 0)
            exit();
        $model_store_reopen = Model('store_reopen');
        $condition = array();
        $condition['re_id'] = intval(input('param.re_id'));
        $condition['re_state'] = 1;
        //取当前申请信息
        $reopen_info = $model_store_reopen->getStoreReopenInfo($condition);

        //取目前店铺有效截止日期
        $store_info = Model('store')->getStoreInfoByID($reopen_info['re_store_id']);
        $data = array();
        $data['re_start_time'] = strtotime(date('Y-m-d 0:0:0', $store_info['store_end_time'])) + 24 * 3600;
        $data['re_end_time'] = strtotime(date('Y-m-d 23:59:59', $data['re_start_time']) . " +" . intval($reopen_info['re_year']) . " year");
        $data['re_state'] = 2;
        $update = $model_store_reopen->editStoreReopen($data, $condition);
        if ($update) {
            //更新店铺有效期
            Model('store')->editStore(array('store_end_time' => $data['re_end_time']), array('store_id' => $reopen_info['re_store_id']));
            $msg = '审核通过店铺续签申请，店铺ID：' . $reopen_info['re_store_id'] . '，续签时间段：' . date('Y-m-d', $data['re_start_time']) . ' - ' . date('Y-m-d', $data['re_end_time']);
            $this->log($msg, 1);
            $this->success('续签成功，店铺有效成功延续到了' . date('Y-m-d', $data['re_end_time']) . '日', getReferer());
        } else {
            $this->error('审核失败', getReferer());
        }
    }

    /**
     * 删除店铺续签申请
     */
    public function reopen_del() {
        $model_store_reopen = Model('store_reopen');
        $condition = array();
        $condition['re_id'] = intval(input('param.re_id'));
        $condition['re_state'] = array('in', array(0, 1));

        //取当前申请信息
        $reopen_info = $model_store_reopen->getStoreReopenInfo($condition);
        $cert_file = BASE_UPLOAD_PATH . DS . ATTACH_STORE_JOININ . DS . $reopen_info['re_pay_cert'];
        $del = $model_store_reopen->delStoreReopen($condition);
        if ($del) {
            if (is_file($cert_file)) {
                unlink($cert_file);
            }
            $this->log('删除店铺续签目申请，店铺ID：' . input('param.re_store_id'), 1);
            $this->success('删除成功', getReferer());
        } else {
            $this->error('删除失败', getReferer());
        }
    }

    /**
     * 审核详细页
     */
    public function store_joinin_detail() {
        $model_store_joinin = Model('storejoinin');
        $member_id = input('param.member_id');
        $joinin_detail = $model_store_joinin->getOne(array('member_id' => $member_id));
        $joinin_detail_title = '查看';
        if (in_array(intval($joinin_detail['joinin_state']), array(STORE_JOIN_STATE_NEW, STORE_JOIN_STATE_PAY))) {
            $joinin_detail_title = '审核';
        }
        if (!empty($joinin_detail['sg_info'])) {
            $store_grade_info = Model('storegrade')->getOneGrade($joinin_detail['sg_id']);
            $joinin_detail['sg_price'] = $store_grade_info['sg_price'];
        } else {
            $joinin_detail['sg_info'] = @unserialize($joinin_detail['sg_info']);
            if (is_array($joinin_detail['sg_info'])) {
                $joinin_detail['sg_price'] = $joinin_detail['sg_info']['sg_price'];
            }
        }

        $this->assign('joinin_detail_title', $joinin_detail_title);
        $this->assign('joinin_detail', $joinin_detail);
        $this->setAdminCurItem('store_joinin_detail');
        return $this->fetch('store_joinin_detail');
    }

    /**
     * 审核
     */
    public function store_joinin_verify() {
        $model_store_joinin = Model('storejoinin');
        $joinin_detail = $model_store_joinin->getOne(array('member_id' => input('param.member_id')));

        switch (intval($joinin_detail['joinin_state'])) {
            case STORE_JOIN_STATE_NEW:
                $this->store_joinin_verify_pass($joinin_detail);
                break;
            case STORE_JOIN_STATE_PAY:
                $this->store_joinin_verify_open($joinin_detail);
                break;
            default:
                $this->error(lang('param_error'));
                break;
        }
    }

    private function store_joinin_verify_pass($joinin_detail) {
        $param = array();
        $param['joinin_state'] = $_POST['verify_type'] === 'pass' ? STORE_JOIN_STATE_VERIFY_SUCCESS : STORE_JOIN_STATE_VERIFY_FAIL;
        $param['joinin_message'] = $_POST['joinin_message'];
        $param['paying_amount'] = abs(floatval($_POST['paying_amount']));
        $param['store_class_commis_rates'] = isset($_POST['commis_rate'])?implode(',', $_POST['commis_rate']):'';
        $model_store_joinin = Model('storejoinin');
        $model_store_joinin->modify($param, array('member_id' => $_POST['member_id']));
        if ($param['paying_amount'] > 0) {
            $this->success('店铺入驻申请审核完成', url('Admin/Store/store_joinin'));
        } else {
            //如果开店支付费用为零，则审核通过后直接开通，无需再上传付款凭证
            $this->store_joinin_verify_open($joinin_detail);
        }
    }

    private function store_joinin_verify_open($joinin_detail) {
        $model_store_joinin = Model('storejoinin');
        $model_store = Model('store');
        $model_seller = Model('seller');

        //验证卖家用户名是否已经存在
        if ($model_seller->isSellerExist(array('seller_name' => $joinin_detail['seller_name']))) {
            $this->error('卖家用户名已存在');
        }

        $param = array();
        $param['joinin_state'] = input('post.verify_type') === 'pass' ? STORE_JOIN_STATE_FINAL : STORE_JOIN_STATE_PAY_FAIL;
        $param['joinin_message'] = input('post.joinin_message');

        if (input('post.verify_type') === 'pass') {
            //开店
            $shop_array = array();
            $shop_array['member_id'] = $joinin_detail['member_id'];
            $shop_array['member_name'] = $joinin_detail['member_name'];
            $shop_array['seller_name'] = $joinin_detail['seller_name'];
            $shop_array['grade_id'] = $joinin_detail['sg_id'];
            $shop_array['store_name'] = $joinin_detail['store_name'];
            $shop_array['sc_id'] = $joinin_detail['sc_id'];
            $shop_array['store_company_name'] = $joinin_detail['company_name'];
            $shop_array['region_id'] = $joinin_detail['company_province_id'];
            $shop_array['longitude'] = $joinin_detail['longitude'];
            $shop_array['latitude'] = $joinin_detail['latitude'];
            $shop_array['area_info'] = $joinin_detail['company_address'];

            $shop_array['store_address'] = $joinin_detail['company_address_detail'];
            $shop_array['store_zip'] = '';
            $shop_array['store_zy'] = '';
            $shop_array['store_state'] = 1;
            $shop_array['store_add_time'] = time();
            $shop_array['store_end_time'] = strtotime(date('Y-m-d 23:59:59', strtotime('+1 day')) . " +" . intval($joinin_detail['joinin_year']) . " year");
            $store_id = $model_store->addStore($shop_array);

            if ($store_id) {
                //写入卖家账号
                $seller_array = array();
                $seller_array['seller_name'] = $joinin_detail['seller_name'];
                $seller_array['member_id'] = $joinin_detail['member_id'];
                $seller_array['seller_group_id'] = 0;
                $seller_array['store_id'] = $store_id;
                $seller_array['is_admin'] = 1;
                $state = $model_seller->addSeller($seller_array);
                //改变店铺状态
                $model_store_joinin->modify($param, array('member_id' => input('param.member_id')));
            }

            if ($state) {
                // 添加相册默认
                $album_model = Model('album');
                $album_arr = array();
                $album_arr['aclass_name'] = lang('store_save_defaultalbumclass_name');
                $album_arr['store_id'] = $store_id;
                $album_arr['aclass_des'] = '';
                $album_arr['aclass_sort'] = '255';
                $album_arr['aclass_cover'] = '';
                $album_arr['upload_time'] = time();
                $album_arr['is_default'] = '1';
                $album_model->addClass($album_arr);

                //插入店铺扩展表
                db('storeextend')->insert(array('store_id' => $store_id));
                $msg = lang('store_save_create_success');

                //插入店铺绑定分类表
                $store_bind_class_array = array();
                $store_bind_class = unserialize($joinin_detail['store_class_ids']);
                $store_bind_commis_rates = explode(',', $joinin_detail['store_class_commis_rates']);
                for ($i = 0, $length = count($store_bind_class); $i < $length; $i++) {
                    list($class1, $class2, $class3) = explode(',', $store_bind_class[$i]);
                    $store_bind_class_array[] = array(
                        'store_id' => $store_id,
                        'commis_rate' => $store_bind_commis_rates[$i],
                        'class_1' => $class1,
                        'class_2' => $class2,
                        'class_3' => $class3,
                        'state' => 1
                    );
                }
                $model_store_bind_class = Model('storebindclass');
                $model_store_bind_class->addStoreBindClassAll($store_bind_class_array);
                $this->success('店铺开店成功', url('Admin/Store/store_joinin'));
            } else {
                $this->error('店铺开店失败', url('Admin/Store/store_joinin'));
            }
        } else {
            $this->error('店铺开店拒绝', url('Admin/Store/store_joinin'));
        }
    }

    /**
     * 提醒续费
     */
    public function remind_renewal() {
        $store_id = intval(input('param.store_id'));
        $store_info = Model('store')->getStoreInfoByID($store_id);
        if (!empty($store_info) && $store_info['store_end_time'] < (TIMESTAMP + 864000) && cookie('remindRenewal' . $store_id) == null) {
            // 发送商家消息
            $param = array();
            $param['code'] = 'store_expire';
            $param['store_id'] = intval(input('param.store_id'));
            $param['param'] = array();
            \mall\queue\QueueClient::push('sendStoreMsg', $param);

            cookie('remindRenewal' . $store_id, 1, 86400 * 10);  // 十天
            $this->success('消息发送成功');
        }
        $this->error('消息发送失败');
    }

    public function del() {
        
        $storeId = intval(input('param.id'));
        $storeModel = model('store');

        $storeArray = $storeModel->field('is_own_shop,store_name')->find($storeId);

        if (empty($storeArray)) {
            $this->error('外驻店铺不存在');
        }

        if ($storeArray['is_own_shop']) {
            $this->error('不能在此删除自营店铺');
        }

        $condition = array(
            'store_id' => $storeId,
        );

        if ((int) model('goods')->getGoodsCount($condition) > 0)
            $this->error('已经发布商品的外驻店铺不能被删除');

        // 完全删除店铺
        $storeModel->delStoreEntirely($condition);

        //删除入驻相关 v3-b12
        $member_id = (int) input('param.member_id');
        $store_joinin = model('storejoinin');
        $condition = array(
            'member_id' => $member_id,
        );
        $store_joinin->drop($condition);

        $this->log("删除外驻店铺: {$storeArray['store_name']}");
        $this->success('操作成功', getReferer());
    }

    //删除店铺操作 v3-b12
    public function del_join() {
        $member_id = (int) input('param.member_id');
        $store_joinin = model('storejoinin');
        $condition = array(
            'member_id' => $member_id,
        );
        $mm = $store_joinin->getOne($condition);
        if (empty($mm)) {
            $this->error('操作失败', getReferer());
        }
        if ($mm['joinin_state'] == '20') {
            
        }
        $store_name = $mm['store_name'];
        $storeModel = model('store');
        $scount = $storeModel->getStoreCount($condition);
        if ($scount > 0) {
            $this->error('操作失败已有店铺在运营', getReferer());
        }
        // 完全删除店铺入驻
        $store_joinin->drop($condition);
        $this->log("删除店铺入驻:" . $store_name);
        $this->success('操作成功', getReferer());
    }

    public function newshop_add() {
        if (!request()->isPost()) {
            $this->setAdminCurItem('newshop_add');
            return $this->fetch('store_newshop_add');
        } else {

            $memberName = input('post.member_name');
            $memberPasswd = (string)input('post.member_password');
            $seller_name = input('post.seller_name');
            $store_name = input('post.store_name');

            if (strlen($memberName) < 3 || strlen($memberName) > 15 || strlen($seller_name) < 3 || strlen($seller_name) > 15)
                $this->error('账号名称必须是3~15位');

            if (strlen($memberPasswd) < 6)
                $this->error('登录密码不能短于6位');

            if (!$this->checkMemberName($memberName))
                $this->error('店主账号已被占用');

            if (!$this->checkSellerName($seller_name))
                $this->error('店主卖家账号名称已被其它店铺占用');

            try {
                $memberId = model('member')->addMember(array(
                    'member_name' => $memberName,
                    'member_password' => $memberPasswd,
                    'member_email' => '',
                ));
            } catch (Exception $ex) {
                $this->error('店主账号新增失败');
            }

            $storeModel = model('store');

            $saveArray = array();
            $saveArray['store_name'] = $store_name;
            $saveArray['member_id'] = $memberId;
            $saveArray['member_name'] = $memberName;
            $saveArray['seller_name'] = $seller_name;
            $saveArray['bind_all_gc'] = 1;
            $saveArray['store_state'] = 1;
            $saveArray['store_add_time'] = time();
            $saveArray['is_own_shop'] = 0;

            $storeId = $storeModel->addStore($saveArray);

            model('seller')->addSeller(array(
                'seller_name' => $seller_name,
                'member_id' => $memberId,
                'store_id' => $storeId,
                'seller_group_id' => 0,
                'is_admin' => 1,
            ));
            model('storejoinin')->save(array(
                'seller_name' => $seller_name,
                'store_name' => $store_name,
                'member_name' => $memberName,
                'member_id' => $memberId,
                'joinin_state' => 40,
                'company_province_id' => 0,
                'sc_bail' => 0,
                'joinin_year' => 1,
            ));

            // 添加相册默认
            $album_model = Model('album');
            $album_arr = array();
            $album_arr['aclass_name'] = '默认相册';
            $album_arr['store_id'] = $storeId;
            $album_arr['aclass_des'] = '';
            $album_arr['aclass_sort'] = '255';
            $album_arr['aclass_cover'] = '';
            $album_arr['upload_time'] = time();
            $album_arr['is_default'] = '1';
            $album_model->addClass($album_arr);

            //插入店铺扩展表
            db('storeextend')->insert(array('store_id' => $storeId));

            // 删除自营店id缓存
            Model('store')->dropCachedOwnShopIds();

            $this->log("新增外驻店铺: {$saveArray['store_name']}");
            $this->success('操作成功', url('Admin/Store/store'));
            return;
        }
    }

    public function check_seller_name() {
        echo json_encode($this->checkSellerName(input('param.seller_name'), input('param.id')));
        exit;
    }

    private function checkSellerName($sellerName, $storeId = 0) {
        // 判断store_joinin是否存在记录
        $count = (int) Model('storejoinin')->getStoreJoininCount(array(
                    'seller_name' => $sellerName,
        ));
        if ($count > 0)
            return false;

        $seller = Model('seller')->getSellerInfo(array(
            'seller_name' => $sellerName,
        ));

        if (empty($seller))
            return true;

        if (!$storeId)
            return false;

        if ($storeId == $seller['store_id'] && $seller['seller_group_id'] == 0 && $seller['is_admin'] == 1)
            return true;

        return false;
    }

    public function check_member_name() {
        echo json_encode($this->checkMemberName(input('param.member_name')));
        exit;
    }

    private function checkMemberName($memberName) {
        // 判断store_joinin是否存在记录
        $count = (int) Model('storejoinin')->getStoreJoininCount(array(
                    'member_name' => $memberName,
        ));
        if ($count > 0)
            return false;

        return !Model('member')->getMemberCount(array(
                    'member_name' => $memberName,
        ));
    }

    /**
     * 验证店铺名称是否存在
     */
    public function ckeck_store_name() {
        /**
         * 实例化卖家模型
         */
        $where = array();
        $where['store_name'] = input('param.store_name');
        $where['store_id'] = array('neq', input('param.store_id'));
        $store_info = Model('store')->getStoreInfo($where);
        if (!empty($store_info['store_name'])) {
            echo 'false';
        } else {
            echo 'true';
        }
    }

    /**
     * 验证店铺名称是否存在
     */
    private function ckeckStoreName($store_name) {
        /**
         * 实例化卖家模型
         */
        $where = array();
        $where['store_name'] = $store_name;
        $store_info = Model('store')->getStoreInfo($where);
        if (!empty($store_info['store_name'])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'store',
                'text' => '管理',
                'url' => url('Admin/Store/store')
            ), array(
                'name' => 'store_joinin',
                'text' => '待审核',
                'url' => url('Admin/Store/store_joinin')
            ), array(
                'name' => 'reopen_list',
                'text' => '续签申请',
                'url' => url('Admin/Store/reopen_list')
            ), array(
                'name' => 'store_bind_class_applay_list',
                'text' => '经营类目申请',
                'url' => url('Admin/Store/store_bind_class_applay_list')
            ), array(
                'name' => 'newshop_add',
                'text' => '新增店铺',
                'url' => url('Admin/Store/newshop_add')
            )
        );
        if(request()->action()=='store_bind_class'){
            $menu_array[]=[
                'name'=>'store_bind_class','text'=>'编辑经营类目','url'=>'#'
            ];
        }
        return $menu_array;
    }

}

?>
