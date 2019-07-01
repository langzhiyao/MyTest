<?php

/*
 * 发货设置
 */

namespace app\home\controller;

use think\Lang;
use think\Validate;

class Sellerdeliverset extends BaseSeller
{

    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/sellerdeliver.lang.php');
    }

    /**
     * 发货地址列表
     */
    public function index()
    {
        $model_daddress = Model('daddress');
        $condition = array();
        $condition['store_id'] = session('store_id');
        $address_list = $model_daddress->getAddressList($condition, '*', '', 20);
        $this->assign('address_list', $address_list);
        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('sellerdeliverset');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem('daddress');
        return $this->fetch($this->template_dir . 'index');
    }

    /**
     * 新增/编辑发货地址
     */
    public function daddress_add()
    {
        $address_id = intval(input('param.address_id'));
        if ($address_id > 0) {
            //编辑
            if (!request()->isPost()) {
                $address_info = db('daddress')->where('address_id', $address_id)->where('store_id', session('store_id'))->find();
                $this->assign('address_info', $address_info);
                return $this->fetch($this->template_dir . 'daddress_add');
            }
            else {
                $data = array(
                    'seller_name' => input('post.seller_name'), 'area_id' => input('post.area_id'),
                    'city_id' => input('post.city_id'), 'area_info' => input('post.region'),
                    'address' => input('post.address'), 'telphone' => input('post.telphone'),
                    'company' => input('post.company'),
                );
                //验证数据  BEGIN
                $rule = [
                    ['seller_name', 'require', '联系人必填'], ['address', 'require', '地址必填'],
                    ['telphone', 'require', '电话必填'],
                ];
                $validate = new Validate();
                $validate_result = $validate->check($data, $rule);
                if (!$validate_result) {
                    showDialog($validate->getError(), '', 'error');
                }
                //验证数据  END
                $result = db('daddress')->where('address_id', $address_id)->where('store_id', session('store_id'))->update($data);
                if ($result) {
                    showDialog(lang('ds_common_op_succ'), 'reload', 'succ', 'CUR_DIALOG.close()');
                }
                else {
                    showDialog(lang('store_daddress_modify_fail'), '', 'error');
                }
            }
        }
        else {
            //新增
            if (!request()->isPost()) {
                $address_info = array(
                    'address_id' => '', 'city_id' => '1', 'area_id' => '1', 'seller_name' => '',
                    'area_info' => '', 'address' => '', 'telphone' => '', 'company' => '',
                );
                $this->assign('address_info', $address_info);
                return $this->fetch($this->template_dir . 'daddress_add');
            }
            else {
                $data = array(
                    'store_id' => session('store_id'), 'seller_name' => input('post.seller_name'),
                    'area_id' => input('post.area_id'), 'city_id' => input('post.city_id'),
                    'area_info' => input('post.region'), 'address' => input('post.address'),
                    'telphone' => input('post.telphone'), 'company' => input('post.company'),
                );
                //验证数据  BEGIN
                $rule = [
                    ['seller_name', 'require', '联系人必填'], ['address', 'require', '地址必填'],
                    ['telphone', 'require', '电话必填'],
                ];
                $validate = new Validate();
                $validate_result = $validate->check($data, $rule);
                if (!$validate_result) {
                    showDialog($validate->getError(), '', 'error');
                }
                //验证数据  END
                $result = db('daddress')->insertGetId($data);
                if ($result) {
                    showDialog(lang('ds_common_op_succ'), 'reload', 'succ', 'CUR_DIALOG.close()');
                }
                else {
                    showDialog(lang('store_daddress_add_fail'), '', 'error');
                }
            }
        }
    }

    /**
     * 删除发货地址
     */
    public function daddress_del()
    {
        $address_id = intval(input('param.address_id'));
        if ($address_id <= 0) {
            showDialog(lang('store_daddress_del_fail'), '', 'error');
        }
        $condition = array();
        $condition['address_id'] = $address_id;
        $condition['store_id'] = session('store_id');
        $delete = Model('daddress')->delAddress($condition);
        if ($delete) {
            showDialog(lang('store_daddress_del_succ'), url('sellerdeliverset/index'), 'succ');
        }
        else {
            showDialog(lang('store_daddress_del_fail'), '', 'error');
        }
    }

    /**
     * 设置默认发货地址
     */
    public function daddress_default_set()
    {
        $address_id = intval($_GET['address_id']);
        if ($address_id <= 0)
            return false;
        $condition = array();
        $condition['store_id'] = session('store_id');
        $update = Model('daddress')->editAddress(array('is_default' => 0), $condition);
        $condition['address_id'] = $address_id;
        $update = Model('daddress')->editAddress(array('is_default' => 1), $condition);
    }

    public function express()
    {
        $model = Model('storeextend');
        if (!request()->isPost()) {
            $express_list = rkcache('express', true);

            //取得店铺启用的快递公司ID
            $express_select = $model->getfby_store_id(session('store_id'), 'express');

            if (!is_null($express_select)) {
                $express_select = explode(',', $express_select);
            }
            else {
                $express_select = array();
            }
            $this->assign('express_select', $express_select);
            //页面输出
            $this->assign('express_list', $express_list);

            /* 设置卖家当前菜单 */
            $this->setSellerCurMenu('sellerdeliverset');
            /* 设置卖家当前栏目 */
            $this->setSellerCurItem('express');
            return $this->fetch($this->template_dir . 'express');
        }
        else {
            $data['store_id'] = session('store_id');
            if (is_array($_POST['cexpress']) && !empty($_POST['cexpress'])) {
                $data['express'] = implode(',', $_POST['cexpress']);
            }
            else {
                $data['express'] = '';
            }
            if (!$model->getby_store_id(session('store_id'))) {
                $result = $model->insert($data);
            }
            else {
                $result = $model->where(array('store_id' => session('store_id')))->update($data);
            }
            if ($result) {
                $this->success(lang('ds_common_save_succ'));
            }
            else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 免运费额度设置
     */
    public function free_freight()
    {
        if (!request()->isPost()) {
            $this->assign('store_free_price', $this->store_info['store_free_price']);
            $this->assign('store_free_time', $this->store_info['store_free_time']);

            /* 设置卖家当前菜单 */
            $this->setSellerCurMenu('sellerdeliverset');
            /* 设置卖家当前栏目 */
            $this->setSellerCurItem('free_freight');
            return $this->fetch($this->template_dir . 'free_freight');
        }
        else {
            $model_store = Model('store');
            $store_free_price = floatval(abs($_POST['store_free_price']));
            $store_free_time = $_POST['store_free_time'];
            $model_store->editStore(array(
                                        'store_free_price' => $store_free_price, 'store_free_time' => $store_free_time
                                    ), array('store_id' => session('store_id')));
            showDialog(lang('ds_common_save_succ'), '', 'succ');
        }
    }

    /**
     * 默认配送区域设置
     */
    public function deliver_region()
    {
        if (!request()->isPost()) {
            $deliver_region = array(
                '', ''
            );
            if (strpos($this->store_info['deliver_region'], '|')) {
                $deliver_region = explode('|', $this->store_info['deliver_region']);
            }
            $this->assign('deliver_region', $deliver_region);
            /* 设置卖家当前菜单 */
            $this->setSellerCurMenu('sellerdeliverset');
            /* 设置卖家当前栏目 */
            $this->setSellerCurItem('deliver_region');
            return $this->fetch($this->template_dir . 'deliver_region');
        }
        else {
            Model('store')->editStore(array('deliver_region' => $_POST['area_ids'] . '|' . $_POST['region']), array('store_id' => session('store_id')));
            showDialog(lang('ds_common_save_succ'), '', 'succ');
        }
    }

    /**
     * 发货单打印设置
     */
    public function print_set()
    {
        $store_info = $this->store_info;

        if (!request()->isPost()) {
            $this->assign('store_info', $store_info);
            /* 设置卖家当前菜单 */
            $this->setSellerCurMenu('sellerdeliverset');
            /* 设置卖家当前栏目 */
            $this->setSellerCurItem('print_set');
            return $this->fetch($this->template_dir . 'print_set');
        }
        else {
            $obj_validate = new Validate();
            $data = array(
                'store_printdesc' => input('store_printdesc')
            );
            $rule = array(
                array('store_printdesc', 'require|length:1,200')
            );

            $error = $obj_validate->check($data, $rule);
            if (!$error) {
                showDialog($obj_validate->getError());
            }
            $update_arr = array();
            //上传认证文件
            if ($_FILES['store_stamp']['name'] != '') {
                $default_dir = BASE_UPLOAD_PATH . DS . ATTACH_STORE;
                $file_name = session('store_id') . '_' . date('YmdHis') . rand(10000, 99999);
                $upload = request()->file('store_stamp');
                $result = $upload->move($default_dir, $file_name);
                if ($result) {
                    $update_arr['store_stamp'] = $result->getFilename();
                    //删除旧认证图片
                    if (!empty($store_info['store_stamp'])) {
                        @unlink(BASE_UPLOAD_PATH . DS . ATTACH_STORE . DS . $store_info['store_stamp']);
                    }
                }
            }
        $update_arr['store_printdesc'] = $_POST['store_printdesc'];
        $rs = db('store')->where(array('store_id' => session('store_id')))->update($update_arr);
        if ($rs) {
            showDialog(lang('ds_common_save_succ'), '', 'succ');
        }
        else {
            showDialog(lang('ds_common_save_fail'), '', 'error');
        }
      }
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_type 导航类型
     * @param string $menu_key 当前导航的menu_key
     * @return
     */
    function getSellerItemList() {
        $menu_array = array(
            array(
                'name' => 'daddress',
                'text' => '地址库',
                'url' => url('home/Sellerdeliverset/index')
            ),
            array(
                'name' => 'express',
                'text' => '默认物流公司',
                'url' => url('home/Sellerdeliverset/express')
            ),
            array(
                'name' => 'free_freight',
                'text' => '免运费额度',
                'url' => url('home/Sellerdeliverset/free_freight')
            ),
            array(
                'name' => 'deliver_region',
                'text' => '默认配送地区',
                'url' => url('home/Sellerdeliverset/deliver_region')
            ),
            array(
                'name' => 'print_set',
                'text' => '发货单打印设置',
                'url' => url('home/Sellerdeliverset/print_set')
            )
        );
        return $menu_array;
    }

}

?>
