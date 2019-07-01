<?php

namespace app\home\controller;


class Sellerinfo extends BaseSeller
{
    /**
     * 店铺信息
     */
    public function index()
    {
        $model_store = Model('store');
        $model_store_bind_class = Model('storebindclass');
        $model_store_class = Model('storeclass');
        $model_store_grade = Model('storegrade');

        // 店铺信息
        $store_info = $model_store->getStoreInfoByID(session('store_id'));
        $this->assign('store_info', $store_info);

        // 店铺分类信息
        $store_class_info = $model_store_class->getStoreClassInfo(array('sc_id' => $store_info['sc_id']));
        $this->assign('store_class_name', $store_class_info['sc_name']);

        // 店铺等级信息
        $store_grade_info = $model_store_grade->getOneGrade($store_info['grade_id']);
        $this->assign('store_grade_name', $store_grade_info['sg_name']);

        $model_store_joinin = Model('storejoinin');
        $joinin_detail = $model_store_joinin->getOne(array('member_id' => $store_info['member_id']));
        $this->assign('joinin_detail', $joinin_detail);

        $store_bind_class_list = $model_store_bind_class->getStoreBindClassList(array(
                                                                                    'store_id' => session('store_id'),
                                                                                    'state' => array('in', array(1, 2))
                                                                                ), null);
        $goods_class = Model('goods_class')->getGoodsClassIndexedListAll();
           for ($i = 0, $j = count($store_bind_class_list); $i < $j; $i++) {
               $store_bind_class_list[$i]['class_1_name'] = @$goods_class[$store_bind_class_list[$i]['class_1']]['gc_name'];
               $store_bind_class_list[$i]['class_2_name'] = @$goods_class[$store_bind_class_list[$i]['class_2']]['gc_name'];
               $store_bind_class_list[$i]['class_3_name'] = @$goods_class[$store_bind_class_list[$i]['class_3']]['gc_name'];
           }
        $this->assign('store_bind_class_list', $store_bind_class_list);

        $this->setSellerCurMenu('sellerinfo');
        $this->setSellerCurItem('index');

        return $this->fetch($this->template_dir.'index');
    }

    /**
     * 经营类目列表
     */
    public function bind_class()
    {

        $model_store_bind_class = Model('storebindclass');

        $store_bind_class_list = $model_store_bind_class->getStoreBindClassList(array('store_id' => session('store_id')), null);
        $goods_class = Model('goodsclass')->getGoodsClassIndexedListAll();
        for ($i = 0, $j = count($store_bind_class_list); $i < $j; $i++) {
            $store_bind_class_list[$i]['class_1_name'] = @$goods_class[$store_bind_class_list[$i]['class_1']]['gc_name'];
            $store_bind_class_list[$i]['class_2_name'] = @$goods_class[$store_bind_class_list[$i]['class_2']]['gc_name'];
            $store_bind_class_list[$i]['class_3_name'] = @$goods_class[$store_bind_class_list[$i]['class_3']]['gc_name'];
        }
        $this->assign('bind_list', $store_bind_class_list);

        $this->setSellerCurMenu('sellerinfo');
        $this->setSellerCurItem('bind_class');
        return $this->fetch($this->template_dir.'bind_class_index');
    }

    /**
     * 申请新的经营类目
     */
    public function bind_class_add()
    {
        $model_goods_class = Model('goodsclass');
        $gc_list = $model_goods_class->getGoodsClassListByParentId(0);
        $this->assign('gc_list', $gc_list);

        $this->setSellerCurMenu('sellerinfo');
        $this->setSellerCurItem('bind_class');
        return $this->fetch($this->template_dir.'bind_class_add');
    }

    /**
     * 申请新经营类目保存
     */
    public function bind_class_save()
    {
        if (!request()->isPost())
            exit();
        if (preg_match('/^[\d,]+$/', $_POST['goods_class'])) {
            list($class_1, $class_2, $class_3) = explode(',', trim($_POST['goods_class'], ','));
        }
        else {
            showDialog(lang('ds_common_save_fail'));
        }

        $model_store_bind_class = Model('storebindclass');

        $param = array();
        $param['store_id'] = session('store_id');
        $param['state'] = 0;
        $param['class_1'] = $class_1;
        $last_gc_id = $class_1;
        if (!empty($class_2)) {
            $param['class_2'] = $class_2;
            $last_gc_id = $class_2;
        }
        if (!empty($class_3)) {
            $param['class_3'] = $class_3;
            $last_gc_id = $class_3;
        }

        // 检查类目是否已经存在
        $store_bind_class_info = $model_store_bind_class->getStoreBindClassInfo($param);
        if (!empty($store_bind_class_info)) {
            showDialog('该类目已经存在');
        }

        //取分佣比例
        $goods_class_info = Model('goodsclass')->getGoodsClassInfoById($last_gc_id);
        $param['commis_rate'] = $goods_class_info['commis_rate'];
        $result = $model_store_bind_class->addStoreBindClass($param);

        if ($result) {
            showDialog('申请成功，请等待系统审核', url('Sellerinfo/bind_class'), 'succ', input('param.inajax') ? '' : 'CUR_DIALOG.close();');
        }
        else {
            showDialog(url('ds_common_save_fail'));
        }
    }

    /**
     * 删除申请的经营类目
     */
    public function bind_class_del()
    {
        $model_brand = Model('storebindclass');
        $condition = array();
        $condition['bid'] = intval(input('param.bid'));
        $condition['store_id'] = session('store_id');
        $condition['state'] = 0;
        $del = Model('storebindclass')->delStoreBindClass($condition);
        if ($del) {
            showDialog(lang('ds_common_del_succ'), 'reload', 'succ');
        }
        else {
            showDialog(lang('ds_common_del_fail'));
        }
    }

    /**
     * 店铺续签
     */
    public function reopen()
    {
        $model_store_reopen = Model('storereopen');
        $reopen_list = $model_store_reopen->getStoreReopenList(array('re_store_id' => session('store_id')));
        $this->assign('reopen_list', $reopen_list);

        $store_info = $this->store_info;
        if (intval($store_info['store_end_time']) > 0) {
            $store_info['store_end_time_text'] = date('Y-m-d', $store_info['store_end_time']);
            $reopen_time = $store_info['store_end_time'] - 3600 * 24 + 1 - TIMESTAMP;
            if (!checkPlatformStore() && $store_info['store_end_time'] - TIMESTAMP >= 0 && $reopen_time < 2592000) {
                //(<30天)
                $store_info['reopen'] = true;
            }
            $store_info['allow_applay_date'] = $store_info['store_end_time'] - 2592000;
        }

        if (!empty($reopen_list)) {
            $last = reset($reopen_list);
            $re_end_time = $last['re_end_time'];
            if (!checkPlatformStore() && $re_end_time - TIMESTAMP < 2592000 && $re_end_time - TIMESTAMP >= 0) {
                //(<30天)
                $store_info['reopen'] = true;
            }
            else {
                $store_info['reopen'] = false;
            }
        }
        $this->assign('store_info', $store_info);

        //店铺等级
        $grade_list = rkcache('storegrade', true);

        $this->assign('grade_list', $grade_list);

        //默认选中当前级别
        $this->assign('current_grade_id', session('grade_id'));

        //如果存在有未上传凭证或审核中的信息，则不能再申请续签
        $condition = array();
        $condition['re_state'] = array('in', array(0, 1));
        $condition['re_store_id'] = session('store_id');
        $reopen_info = $model_store_reopen->getStoreReopenInfo($condition);
        if ($reopen_info) {
            if ($reopen_info['re_state'] == '0') {
                $this->assign('upload_cert', true);
                $this->assign('reopen_info', $reopen_info);
            }
        }
        else {
            $this->assign('applay_reopen', isset($store_info['reopen']) ? true : false);
        }

        $this->setSellerCurMenu('sellerinfo');
        $this->setSellerCurItem('reopen');

        return $this->fetch($this->template_dir.'reopen_index');
    }

    /**
     * 申请续签
     */
    public function reopen_add()
    {
        if (!request()->isPost())
            exit();
        if (intval($_POST['re_grade_id']) <= 0 || intval($_POST['re_year']) <= 0)
            exit();

        // 店铺信息
        $model_store = Model('store');
        $store_info = $this->store_info;
        if (empty($store_info['store_end_time'])) {
            showDialog('您的店铺使用期限无限制，无须续签');
        }

        $model_store_reopen = Model('storereopen');

        //如果存在有未上传凭证或审核中的信息，则不能再申请续签
        $condition = array();
        $condition['re_state'] = array('in', array(0, 1));
        $condition['re_store_id'] = session('store_id');
        if ($model_store_reopen->getStoreReopenCount($condition)) {
            showDialog('目前尚存在申请中的续签信息，不能重复申请');
        }

        $data = array();
        //取店铺等级信息
        $grade_list = rkcache('storegrade', true);
        if (empty($grade_list[$_POST['re_grade_id']]))
            exit();

        //取得店铺信息

        $data['re_grade_id'] = $_POST['re_grade_id'];
        $data['re_grade_name'] = $grade_list[$_POST['re_grade_id']]['sg_name'];
        $data['re_grade_price'] = $grade_list[$_POST['re_grade_id']]['sg_price'];

        $data['re_store_id'] = session('store_id');
        $data['re_store_name'] = session('store_name');
        $data['re_year'] = intval($_POST['re_year']);
        $data['re_pay_amount'] = $data['re_grade_price'] * $data['re_year'];
        $data['re_create_time'] = TIMESTAMP;
        if ($data['re_pay_amount'] == 0) {
            //             $data['re_start_time'] = strtotime(date('Y-m-d 0:0:0',$store_info['store_end_time']))+24*3600;
            //             $data['re_end_time'] = strtotime(date('Y-m-d 23:59:59', $data['re_start_time'])." +".intval($data['re_year'])." year");
            $data['re_state'] = 1;
        }
        $insert = $model_store_reopen->addStoreReopen($data);
        if ($insert) {
            if ($data['re_pay_amount'] == 0) {
                // 	            $model_store->editStore(array('store_end_time'=>$data['re_end_time']),array('store_id'=>session('store_id')));
                showDialog('您的申请已经提交，请等待管理员审核', 'reload', 'succ', '', 5);
            }
            else {
                showDialog(lang('ds_common_save_succ') . '，需付款金额' . dsPriceFormat($data['re_pay_amount']) . '元，请尽快完成付款，付款完成后请上传付款凭证', 'reload', 'succ', '', 5);
            }
        }
        else {
            showDialog(lang('ds_common_del_fail'));
        }
    }

    //上传付款凭证
    public function reopen_upload()
    {
        $uploaddir = BASE_UPLOAD_PATH.DS.ATTACH_PATH . DS . 'store_joinin' . DS;
        if (!empty($_FILES['re_pay_cert']['tmp_name'])) {
            $file_object = request()->file('re_pay_cert');
            $info = $file_object->rule('uniqid')->validate(['ext' => 'jpg,png,gif'])->move($uploaddir);
            if ($info) {
                $pic_name = $info->getFilename();;
            }
        }
        $data = array();
        $data['re_pay_cert'] = $pic_name;
        $data['re_pay_cert_explain'] = $_POST['re_pay_cert_explain'];
        $data['re_state'] = 1;
        $model_store_reopen = Model('storereopen');
        $update = $model_store_reopen->editStoreReopen($data, array('re_id' => $_POST['re_id'], 're_state' => 0));
        if ($update) {
            showDialog('上传成功，请等待系统审核', 'reload', 'succ');
        }
        else {
            showDialog(lang('ds_common_del_fail'));
        }
    }

    /**
     * 删除未上传付款凭证的续签信息
     */
    public function reopen_del()
    {
        $model_store_reopen = Model('storereopen');
        $condition = array();
        $condition['re_id'] = intval(input('param.re_id'));
        $condition['re_state'] = 0;
        $condition['re_store_id'] = session('store_id');
        $del = $model_store_reopen->delStoreReopen($condition);
        if ($del) {
            showDialog(lang('ds_common_del_succ'), 'reload', 'succ');
        }
        else {
            showDialog(lang('ds_common_del_fail'));
        }
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_type 导航类型
     * @param string $name 当前导航的name
     * @param array $array 附加菜单
     * @return
     */
    protected function getSellerItemList()
    {
        $menu_array = array();
        switch (request()->action()) {
            case 'index':
                $menu_array []= array(
                    'name' => 'bind_class', 'text' => lang('ds_member_path_bind_class'),
                    'url' => url('Sellerinfo/bind_class')
                );
                $menu_array[] = array(
                    'name' => 'index', 'text' => lang('ds_member_path_store_info'),
                    'url' => url('Sellerinfo/index')
                );
                $menu_array[] = array(
                    'name' => 'reopen', 'text' => lang('ds_member_path_store_reopen'),
                    'url' => url('Sellerinfo/reopen')
                );
                break;
            case 'bind_class':
                $menu_array []= array(
                        'name' => 'bind_class', 'text' => lang('ds_member_path_bind_class'),
                        'url' => url('Sellerinfo/bind_class')
                );
                if (!checkPlatformStore()) {
                    $menu_array[] = array(
                        'name' => 'index', 'text' => lang('ds_member_path_store_info'),
                        'url' => url('Sellerinfo/index')
                    );
                    $menu_array[] = array(
                        'name' => 'reopen', 'text' => lang('ds_member_path_store_reopen'),
                        'url' => url('Sellerinfo/reopen')
                    );
                }
                break;
            case 'reopen':
            $menu_array = array(
                array(
                    'name' => 'index', 'text' => lang('ds_member_path_bind_class'),
                    'url' => url('storebindclass/index')
                ), array(
                    'name' => 'index', 'text' => lang('ds_member_path_store_info'),
                    'url' => url('Sellerinfo/index')
                ),array(
                    'name' => 'reopen', 'text' => lang('ds_member_path_store_reopen'),
                    'url' => url('storebindclass/reopen')
                )
            );
                break;
        }
        if (!empty($array)) {
            $menu_array[] = $array;
        }
       return $menu_array;
    }
}