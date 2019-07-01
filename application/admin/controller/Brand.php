<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Brand extends AdminControl
{
    const EXPORT_SIZE = 1000;
    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/brand.lang.php');
    }

    /**
     * 品牌列表
     */
    public function index()
    {
        $model_brand = Model('brand');
        if (request()->isPost()) {
            if (!empty($_POST['del_brand_id'])) {
                //删除图片
                if (is_array($_POST['del_brand_id'])) {
                    $brandid_array = array();
                    foreach ($_POST['del_brand_id'] as $k => $v) {
                        $brandid_array[] = intval($v);
                    }
                    $model_brand->delBrand(array('brand_id' => array('in', $brandid_array)));
                }
                $this->log(lang('ds_del') . lang('brand_index_brand') . '[ID:' . implode(',', $_POST['del_brand_id']) . ']', 1);
                $this->success(lang('ds_common_del_succ'));
            }
            else {
                $this->log(lang('ds_del,brand_index_brand') . '[ID:' . implode(',', $_POST['del_brand_id']) . ']', 0);
                $this->error(lang('ds_common_del_fail'));
            }
        }
        /**
         * 检索条件
         */
        if (!empty(input('param.search_brand_name'))) {
            $condition['brand_name'] = array('like', "%" . input('param.search_brand_name') . "%");
        }
        if (!empty(input('param.search_brand_class'))) {
            $condition['brand_class'] = array('like', "%" . input('param.search_brand_class') . "%");
        }
        $condition['brand_apply'] = '1';
        $brand_list = $model_brand->getBrandList($condition, "*", 10);
        $this->assign('page', $model_brand->page_info->render());
        $this->assign('brand_list', $brand_list);
        $this->assign('search_brand_name', trim(input('param.search_brand_name')));
        $this->assign('search_brand_class', trim(input('param.search_brand_class')));
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    /**
     * 增加品牌
     */
    public function brand_add()
    {

        $model_brand = Model('brand');
        if (request()->isPost()) {
            $obj_validate = new Validate();
            $data = [
                'brand_name' => $_POST["brand_name"], 'brand_initial' => $_POST["brand_initial"],
                'brand_sort' => $_POST["brand_sort"]
            ];
            $rule = [
                ['brand_name', 'require', lang('brand_add_name_null')],
                ['brand_initial', 'require', lang('brand_add_initial')],
                ['brand_sort', 'require|number', lang('brand_add_sort_int')]
            ];

            $error = $obj_validate->check($data, $rule);
            if (!$error) {
                $this->error($obj_validate->getError());
            }
            else {
                $insert_array = array();
                $insert_array['brand_name'] = trim($_POST['brand_name']);
                $insert_array['brand_initial'] = strtoupper($_POST['brand_initial']);
                $insert_array['gc_id'] = $_POST['class_id'];
                $insert_array['brand_class'] = trim($_POST['brand_class']);
                $insert_array['brand_pic'] = trim($_POST['brand_pic']);
                $insert_array['brand_recommend'] = trim($_POST['brand_recommend']);
                $insert_array['brand_sort'] = intval($_POST['brand_sort']);
                $insert_array['show_type'] = intval($_POST['show_type']) == 1 ? 1 : 0;
                $result = $model_brand->addBrand($insert_array);
                if ($result) {
                    $this->log(lang('ds_add') . lang('brand_index_brand') . '[' . $_POST['brand_name'] . ']', 1);
                    $this->success(lang('ds_common_save_succ'),'brand/index');
                }
                else {
                    $this->error(lang('ds_common_save_fail'));
                }
            }
        }
        $brand_array=[
            'brand_id'=>'',
            'brand_name'=>'',
            'brand_initial'=>'',
            'gc_id'=>'',
            'brand_class'=>'',
            'brand_pic'=>'',
            'show_type'=>'0',
            'brand_recommend'=>'1',
            'brand_sort'=>'',

        ];
        $this->assign('brand_array',$brand_array);

        // 一级商品分类
        $gc_list = Model('goodsclass')->getGoodsClassListByParentId(0);
        $this->assign('gc_list', $gc_list);
        $this->setAdminCurItem('brand_add');
        return $this->fetch('form');
    }

    /**
     * 品牌编辑
     */
    public function brand_edit()
    {
        $model_brand = Model('brand');

        if (request()->isPost()) {
            $obj_validate = new Validate();
            $data = [
                'brand_name' => $_POST["brand_name"], 'brand_initial' => $_POST["brand_initial"],
                'brand_sort' => $_POST["brand_sort"]
            ];
            $rule = [
                ['brand_name', 'require', lang('brand_add_name_null')],
                ['brand_initial', 'require', lang('brand_add_initial')],
                ['brand_sort', 'require|number', lang('brand_add_sort_int')]
            ];

            $error = $obj_validate->check($data, $rule);
            if (!$error) {
                $this->error($obj_validate->getError());
            }
            else {
                if (!empty($_FILES['_pic']['name'])) {
                    $file_name = date('YmdHis') . rand(10000, 99999);
                    $upload_file = BASE_UPLOAD_PATH . DS . ATTACH_BRAND;
                    $file = request()->file('_pic');
                    $result = $file->validate(['ext' => 'jpg,png,gif'])->move($upload_file,$file_name);
                    if ($result) {
                        $brand_pic = $result->getFilename();
                    }
                }
                $brand_info = $model_brand->getBrandInfo(array('brand_id' => intval($_POST['brand_id'])));
                $where = array();
                $where['brand_id'] = intval($_POST['brand_id']);
                $update_array = array();
                $update_array['brand_name'] = trim($_POST['brand_name']);
                $update_array['brand_initial'] = strtoupper($_POST['brand_initial']);
                $update_array['gc_id'] = $_POST['class_id'];
                $update_array['brand_class'] = trim($_POST['brand_class']);
                if (!empty($brand_pic)) {
                    $update_array['brand_pic'] = $brand_pic;
                }
                $update_array['brand_recommend'] = intval($_POST['brand_recommend']);
                $update_array['brand_sort'] = intval($_POST['brand_sort']);
                $update_array['show_type'] = intval($_POST['show_type']) == 1 ? 1 : 0;
                $result = $model_brand->editBrand($where, $update_array);
                if ($result) {
                    if (!empty($_POST['brand_pic']) && !empty($brand_info['brand_pic'])) {
                        @unlink(BASE_UPLOAD_PATH . DS . ATTACH_BRAND . DS . $brand_info['brand_pic']);
                    }
                    $this->log(lang('ds_edit') . lang('brand_index_brand') . '[' . $_POST['brand_name'] . ']', 1);
                    $this->success(lang('ds_common_save_succ'), 'brand/index');
                }
                else {
                    $this->log(lang('ds_edit').lang('brand_index_brand') . '[' . $_POST['brand_name'] . ']', 0);
                    $this->error(lang('ds_common_save_fail'));
                }
            }
        }

        $brand_info = $model_brand->getBrandInfo(array('brand_id' => intval(input('param.brand_id'))));
        if (empty($brand_info)) {
            $this->error(lang('param_error'));
        }
        $this->assign('brand_array', $brand_info);

        // 一级商品分类
        $gc_list = Model('goodsclass')->getGoodsClassListByParentId(0);
        $this->assign('gc_list', $gc_list);
        $this->setAdminCurItem('brand_edit');
        return $this->fetch('form');
    }

    /**
     * 删除品牌
     */
    public function brand_del()
    {
        if (intval(input('param.brand_id')) > 0) {
            Model('brand')->delBrand(array('brand_id' => intval(input('param.brand_id'))));
            $this->log(lang('ds_del').lang('brand_index_brand') . '[ID:' . intval(input('param.brand_id')) . ']', 1);
            $this->error(lang('ds_common_del_succ'), 'brand/index');
        }
        else {
            $this->log(lang('ds_del').lang('brand_index_brand') . '[ID:' . intval(input('param.brand_id')) . ']', 0);
            $this->error(lang('ds_common_del_fail'), 'brand/index');
        }
    }

    /**
     * 品牌申请
     */
    public function brand_apply()
    {
        $model_brand = Model('brand');
        /**
         * 对申请品牌进行操作 通过，拒绝
         */
        if (request()->isPost()) {
            if (!empty($_POST['del_id'])) {
                switch ($_POST['type']) {
                    case 'pass':
                        //更新品牌 申请状态
                        $brandid_array = array();
                        foreach ($_POST['del_id'] as $v) {
                            $brandid_array[] = intval($v);
                        }
                        $update_array = array();
                        $update_array['brand_apply'] = 1;
                        $model_brand->editBrand(array('brand_id' => array('in', $brandid_array)), $update_array);
                        $this->log(lang('brand_apply_pass') . '[ID:' . implode(',', $brandid_array) . ']', null);
                        $this->error(lang('brand_apply_passed'));
                        break;
                    case 'refuse':
                        //删除该品牌
                        $brandid_array = array();
                        foreach ($_POST['del_id'] as $v) {
                            $brandid_array[] = intval($v);
                        }
                        $model_brand->delBrand(array('brand_id' => array('in', $brandid_array)));
                        $this->log(lang('ds_del,brand_index_brand') . '[ID:' . implode(',', $_POST['del_id']) . ']', 1);
                        $this->error(lang('ds_common_del_succ'));
                        break;
                    default:
                        $this->error(lang('brand_apply_invalid_argument'));
                }
            }
            else {
                $this->log(lang('ds_del,brand_index_brand'), 0);
                $this->error(lang('ds_common_del_fail'));
            }
        }
        /**
         * 检索条件
         */
        $condition = array();
        if (!empty($_POST['search_brand_name'])) {
            $condition['brand_name'] = array('like', '%' . trim($_POST['search_brand_name']) . '%');
        }
        if (!empty($_POST['search_brand_class'])) {
            $condition['brand_class'] = array('like', '%' . trim($_POST['search_brand_class']) . '%');
        }
        $brand_list = $model_brand->getBrandNoPassedList($condition, '*', 10);

        $this->assign('brand_list', $brand_list);
        $this->assign('show_page', $model_brand->page_info->render());
        $this->assign('search_brand_name', trim($_POST['search_brand_name']));
        $this->assign('search_brand_class', trim($_POST['search_brand_class']));
        return $this->fetch('brand_apply');
    }

    /**
     * 审核 申请品牌操作
     */
    public function brand_apply_set()
    {
        $model_brand = Model('brand');

        if (intval(input('param.brand_id')) > 0) {
            switch (input('param.state')) {
                case 'pass':
                    /**
                     * 更新品牌 申请状态
                     */
                    $update_array = array();
                    $update_array['brand_apply'] = 1;
                    $result = $model_brand->editBrand(array('brand_id' => intval(input('param.brand_id'))), $update_array);
                    if ($result) {
                        $this->log(lang('brand_apply_pass') . '[ID:' . intval(input('param.brand_id')) . ']', null);
                        $this->success(lang('brand_apply_pass'));
                    }
                    else {
                        $this->log(lang('brand_apply_fail') . '[ID:' . intval(input('param.brand_id')) . ')', 0);
                        $this->error(lang('brand_apply_fail'));
                    }
                    break;
                case 'refuse':
                    // 删除
                    $model_brand->delBrand(array('brand_id' => intval(input('param.brand_id'))));
                    $this->log(lang('ds_del,brand_index_brand') . '[ID:' . intval(input('param.brand_id')) . ']', 1);
                    $this->error(lang('ds_common_del_succ'));
                    break;
                default:
                    $this->error(lang('brand_apply_paramerror'));
            }
        }
        else {
            $this->log(lang('ds_del,brand_index_brand') . '[ID:' . intval(input('param.brand_id')) . ']', 0);
            $this->error(lang('brand_apply_brandparamerror'));
        }
    }

    /**
     * ajax操作
     */
    public function ajax()
    {
        $model_brand = Model('brand');
        switch (input('param.branch')) {
            /**
             * 品牌名称
             */
            case 'brand_name':
                /**
                 * 判断是否有重复
                 */
                $condition['brand_name'] = trim(input('param.value'));
                $condition['brand_id'] = array('neq', intval(input('param.id')));
                $result = $model_brand->getBrandList($condition);
                if (empty($result)) {
                    $model_brand->editBrand(array('brand_id' => intval(input('param.id'))), array('brand_name' => trim(input('param.value'))));
                    $this->log(lang('ds_edit').lang('brand_index_name') . '[' . input('param.value') . ']', 1);
                    echo 'true';
                    exit;
                }
                else {
                    echo 'false';
                    exit;
                }
                break;
            /**
             * 品牌类别，品牌排序，推荐
             */
            case 'brand_class':
            case 'brand_sort':
            case 'brand_recommend':
                $model_brand->editBrand(array('brand_id' => intval(input('param.id'))), array(input('param.column') => trim(input('param.value'))));
                $detail_log = str_replace(array(
                                              'brand_class', 'brand_sort', 'brand_recommend'
                                          ), array(
                                              lang('brand_index_class'), lang('ds_sort'), lang('ds_recommend')
                                          ), input('param.branch'));
                $this->log(lang('ds_edit') . lang('brand_index_brand') . $detail_log . '[ID:' . intval(input('param.id')) . ')', 1);
                echo 'true';
                exit;
                break;
            /**
             * 验证品牌名称是否有重复
             */
            case 'check_brand_name':
                $condition['brand_name'] = trim(input('param.brand_name'));
                $condition['brand_id'] = array('neq', intval(input('param.id')));
                $result = $model_brand->getBrandList($condition);
                if (empty($result)) {
                    echo 'true';
                    exit;
                }
                else {
                    echo 'false';
                    exit;
                }
                break;
        }
    }

    /**
     * 品牌导出第一步
     */
    public function export_step1()
    {
        $model_brand = Model('brand');
        $condition = array();
        if ((input('param.search_brand_name'))) {
            $condition['brand_name'] = array('like', "%{input('param.search_brand_name')}%");
        }
        if ((input('param.search_brand_class'))) {
            $condition['brand_class'] = array('like', "%{input('param.search_brand_class')}%");
        }
        $condition['brand_apply'] = '1';

        if (!is_numeric(input('param.curpage'))) {
            $count = $model_brand->getBrandCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE) {    //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                $this->assign('list', $array);
                $this->assign('murl', url('brand/index'));
                return $this->fetch('export_excel');
            }
            else {    //如果数量小，直接下载
                $data = $model_brand->getBrandList($condition, '*', 0, 'brand_id desc', self::EXPORT_SIZE);
                $this->createExcel($data);
            }
        }
        else {    //下载
            $limit1 = (input('param.curpage') - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_brand->getBrandList($condition, '*', 0, 'brand_id desc', "{$limit1},{$limit2}");
            $this->createExcel($data);
        }
    }

    /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel($data = array())
    {
        $excel_obj = new \excel\Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array(
                                 'id' => 's_title', 'Font' => array('FontName' => '宋体', 'Size' => '12', 'Bold' => '1')
                             ));
        //header
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_brandid'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_brand'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_brand_cate'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_brand_img'));
        foreach ((array)$data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => $v['brand_id']);
            $tmp[] = array('data' => $v['brand_name']);
            $tmp[] = array('data' => $v['brand_class']);
            $tmp[] = array('data' => $v['brand_pic']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset(lang('exp_brand'), CHARSET));
        $excel_obj->generateXML($excel_obj->charset(lang('exp_brand'), CHARSET) . input('param.curpage') . '-' . date('Y-m-d-H', time()));
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList()
    {
        $menu_array = array(
            array(
                'name' => 'index', 'text' => '管理', 'url' => url('Admin/Brand/index')
            ),
        );
        if (request()->action() == 'brand_add' || request()->action() == 'index') {
            $menu_array[] = array(
                'name' => 'brand_add', 'text' => '新增', 'url' => url('Admin/Brand/brand_add')
            );
        }
        if (request()->action() == 'brand_edit') {
            $menu_array[] = array(
                'name' => 'brand_edit', 'text' => '编辑', 'url' => url('Admin/Brand/brand_edit')
            );
        }
        return $menu_array;
    }

}