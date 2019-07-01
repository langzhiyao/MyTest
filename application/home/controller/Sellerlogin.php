<?php

namespace app\home\controller;

use think\Lang;

class Sellerlogin extends BaseSeller {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/sellerlogin.lang.php');
    }
    
    
    function login() {
        if (!request()->isPost()) {
            return $this->fetch($this->template_dir.'login');
        } else {

            $model_seller = Model('seller');
            $seller_info = $model_seller->getSellerInfo(array('seller_name' => input('post.seller_name')));
            if ($seller_info) {
                $model_member = Model('member');
                $member_info = $model_member->getMemberInfo(
                        array(
                            'member_id' => $seller_info['member_id'],
                            'member_password' => md5(input('post.member_password'))
                        )
                );
                if ($member_info) {
                    // 更新卖家登陆时间
                    $model_seller->editSeller(array('last_login_time' => TIMESTAMP), array('seller_id' => $seller_info['seller_id']));

                    $model_seller_group = Model('sellergroup');
                    $seller_group_info = $model_seller_group->getSellerGroupInfo(array('group_id' => $seller_info['seller_group_id']));

                    $model_store = Model('store');
                    $store_info = $model_store->getStoreInfoByID($seller_info['store_id']);

                    session('is_login', '1');
                    session('member_id', $member_info['member_id']);
                    session('member_name', $member_info['member_name']);
                    session('member_email', $member_info['member_email']);
                    session('is_buy', $member_info['is_buy']);
                    session('avatar', $member_info['member_avatar']);

                    session('grade_id', $store_info['grade_id']); //店铺等级
                    session('seller_id', $seller_info['seller_id']);
                    session('seller_name', $seller_info['seller_name']);
                    session('seller_is_admin', intval($seller_info['is_admin']));
                    session('store_id', intval($seller_info['store_id']));
                    session('store_name', $store_info['store_name']);
                    session('is_own_shop', (bool) $store_info['is_own_shop']);
                    session('bind_all_gc', (bool) $store_info['bind_all_gc']);
                    session('seller_limits', explode(',', $seller_group_info['limits']));
                    if ($seller_info['is_admin']) {
                        session('seller_group_name', '管理员');
                        session('seller_smt_limits', false);
                    } else {
                        session('seller_group_name', $seller_group_info['group_name']);
                        session('seller_smt_limits', explode(',', $seller_group_info['smt_limits']));
                    }
                    if (!$seller_info['last_login_time']) {
                        $seller_info['last_login_time'] = TIMESTAMP;
                    }
                    session('seller_last_login_time', date('Y-m-d H:i', $seller_info['last_login_time']));

                    /*
                      //快捷菜单
                      $seller_menu = $this->getSellerMenuList($seller_info['is_admin'], explode(',', $seller_group_info['limits']));
                      session('seller_menu',$seller_menu['seller_menu']);
                      session('seller_function_list',$seller_menu['seller_function_list']);
                      if (!empty($seller_info['seller_quicklink'])) {
                      $quicklink_array = explode(',', $seller_info['seller_quicklink']);
                      foreach ($quicklink_array as $value) {
                      session('seller_quicklink.'.$value,$value);
                      }
                      }
                     * 
                     */


                    $this->recordSellerLog('登录成功');
                    $this->redirect('Home/Seller/index');
                } else {
                    $this->error('用户名密码错误');
                }
            } else {
                $this->error('没有管理店铺权限');
            }
        }
    }

    function logout() {
        $this->recordSellerLog('注销成功');
        // 清除店铺消息数量缓存
        cookie('storemsgnewnum' . session('seller_id'), 0, -3600);
        session(null);
        $this->redirect('Home/Sellerlogin/login');
    }

}

?>
