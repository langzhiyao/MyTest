<?php

namespace app\home\controller;

use think\Lang;
use think\Validate;

class Memberaddress extends BaseMember {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/memberaddress.lang.php');
    }

    /*
     * 收货地址列表
     */

    public function index() {
        $address_list = db('address')->where('member_id', session('member_id'))->select();
        $this->assign('address_list', $address_list);


        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('member_address');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('my_address');
        return $this->fetch($this->template_dir.'index');
    }

    public function add() {
        if (!request()->isPost()) {
            $region_list = db('area')->where('area_parent_id','0')->select();
            $this->assign('region_list', $region_list);
            $address = array(
                'true_name' => '',
                'area_id' => '',
                'city_id' => '',
                'address' => '',
                'tel_phone' => '',
                'mob_phone' => '',
                'is_default' => '',
                'area_info'=>''
            );
            $this->assign('address', $address);
            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('member_address');
            /* 设置买家当前栏目 */
            $this->setMemberCurItem('my_address_add');
            return $this->fetch($this->template_dir.'form');
        } else {
            $data = array(
                'member_id' => session('member_id'),
                'true_name' => input('post.true_name'),
                'area_id' => input('post.area_id'),
                'city_id' => input('post.city_id'),
                'address' => input('post.address'),
            		'longitude' => input('post.longitude'),
            		'latitude' => input('post.latitude'),
                'tel_phone' => input('post.tel_phone'),
                'mob_phone' => input('post.mob_phone'),
                'is_default' => input('post.is_default') == 1 ? 1 : 0,
                'area_info'=>input('post.area_info'),
            );
            //验证数据  BEGIN
            $rule = [
                ['true_name', 'require', '真实姓名必填'],
            ];
            $validate = new Validate();
            $validate_result = $validate->check($data, $rule);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END
            $result = db('address')->insert($data);
            if ($result) {
                $this->success(lang('ds_common_save_succ'), 'Memberaddress/index');
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    public function edit() {

        $address_id = intval(input('param.address_id'));
        if (0 >= $address_id) {
            $this->error('参数错误');
        }
        $address = db('address')->where(array('member_id' => session('member_id'), 'address_id' => $address_id))->find();
        if (empty($address)) {
            $this->error('地址不存在');
        }
        if (!request()->isPost()) {
            $region_list = db('area')->where('area_parent_id','0')->select();
            $this->assign('region_list', $region_list);
            $this->assign('address', $address);
            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('member_address');
            /* 设置买家当前栏目 */
            $this->setMemberCurItem('my_address_edit');
            return $this->fetch($this->template_dir.'form');
        } else {
            $data = array(
                'true_name' => input('post.true_name'),
                'area_id' => input('post.area_id'),
                'city_id' => input('post.city_id'),
                'address' => input('post.address'),
            		'longitude' => input('post.longitude'),
            		'latitude' => input('post.latitude'),
                'tel_phone' => input('post.tel_phone'),
                'mob_phone' => input('post.mob_phone'),
                'is_default' => input('post.is_default') == 1 ? 1 : 0,
                'area_info'=>input('post.area_info'),
            );
            //验证数据  BEGIN
            $rule = [
                ['true_name', 'require', '真实姓名必填'],
            ];
            $validate = new Validate();
            $validate_result = $validate->check($data, $rule);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END

            $result = db('address')->where(array('member_id' => session('member_id'), 'address_id' => $address_id))->update($data);
            if ($result) {
                $this->success(lang('ds_common_save_succ'), 'Memberaddress/index');
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    public function drop() {
        $address_id = intval(input('param.address_id'));
        if (0 >= $address_id) {
            $this->error(lang('empty_error'));
        }
        $result = db('address')->delete($address_id);
        if ($result) {
            $this->success(lang('ds_common_del_succ'), 'Memberaddress/index');
        } else {
            $this->error(lang('ds_common_del_fail'));
        }
    }

    /**
     *    栏目菜单
     */
    function getMemberItemList() {
        $item_list = array(
            array(
                'name' => 'my_address',
                'text' => '我的地址',
                'url' => url('Home/memberaddress/index'),
            ),
            array(
                'name' => 'my_address_add',
                'text' => '新增地址',
                'url' => url('Home/memberaddress/add'),
            ),
        );
        return $item_list;
    }

}

?>
