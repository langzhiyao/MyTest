<?php
namespace app\home\controller;

use think\Lang;
use think\Validate;

class Selleraccount extends BaseSeller {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/selleraccount.lang.php');
    }
    

    public function account_list() {
        $model_seller = Model('seller');
        $condition = array(
            'store_id' => session('store_id'),
            'seller_group_id' => array('gt', 0)
        );
        
        $page = input('param.page') ? input('param.page') : 0;
        $seller_list = db('seller')->where($condition)->page($page)->limit(10)->select();
        $this->assign('seller_list', $seller_list);
        
        $seller_list_page = db('seller')->where($condition)->paginate(10,false,['query' => request()->param()]);
        $this->assign('page', $seller_list_page->render());
        
//        $seller_list = $model_seller->getSellerList($condition);

        $model_seller_group = Model('sellergroup');
        $seller_group_list = $model_seller_group->getSellerGroupList(array('store_id' => session('store_id')));
        $seller_group_array = array_under_reset($seller_group_list, 'group_id');
        $this->assign('seller_group_array', $seller_group_array);

//        $this->profile_menu('account_list');
        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('selleraccount');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem('account_list');
        return $this->fetch($this->template_dir.'account_list');
    }

    public function account_add() {
        $model_seller_group = Model('sellergroup');
        $seller_group_list = $model_seller_group->getSellerGroupList(array('store_id' => session('store_id')));
        if (empty($seller_group_list)) {
            $this->error('请先建立账号组', url('Home/selleraccountgroup/group_add'));
        }
        $this->assign('seller_group_list', $seller_group_list);
//        $this->profile_menu('account_add');
        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('selleraccount');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem('account_add');
        return $this->fetch($this->template_dir.'account_add');
    }

    public function account_edit() {
        $seller_id = intval(input('param.seller_id'));
        if ($seller_id <= 0) {
            $this->error('参数错误');
        }
        $model_seller = Model('seller');
        $seller_info = $model_seller->getSellerInfo(array('seller_id' => $seller_id));
        if (empty($seller_info) || intval($seller_info['store_id']) !== intval(session('store_id'))) {
            $this->error('账号不存在');
        }
        $this->assign('seller_info', $seller_info);

        $model_seller_group = Model('sellergroup');
        $seller_group_list = $model_seller_group->getSellerGroupList(array('store_id' => session('store_id')));
        if (empty($seller_group_list)) {
            $this->error('请先建立账号组',url('Home/selleraccountgroup/group_add'));
        }
        $this->assign('seller_group_list', $seller_group_list);

//        $this->profile_menu('account_edit');
        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('selleraccount');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem('account_edit');
        return $this->fetch($this->template_dir.'account_edit');
    }

    public function account_add_save() {
        $member_name = input('post.member_name');
        $password = input('post.password');
        $member_info = $this->_check_seller_member($member_name, $password);
        if(!$member_info) {
            showDialog('用户验证失败', 'reload', 'error');
        }

        $seller_name = input('post.seller_name');
        if($this->_is_seller_name_exist($seller_name)) {
            showDialog('卖家账号已存在', 'reload', 'error');
        }

        $group_id = intval(input('post.group_id'));

        $seller_info = array(
            'seller_name' => $seller_name,
            'member_id' => $member_info['member_id'],
            'seller_group_id' => $group_id,
            'store_id' => session('store_id'),
            'is_admin' => 0
        );
        $model_seller = Model('seller');
        $result = $model_seller->addSeller($seller_info);

        if($result) {
            $this->recordSellerLog('添加账号成功，账号编号'.$result);
            showDialog(lang('ds_common_op_succ'), url('/home/selleraccount/account_list'), 'succ');
        } else {
            $this->recordSellerLog('添加账号失败');
            showDialog(lang('ds_common_save_fail'), url('/home/selleraccount/account_list'), 'error');
        }
    }

    public function account_edit_save() {
        $param = array('seller_group_id' => intval(input('post.group_id')));
        $condition = array(
            'seller_id' => intval(input('post.seller_id')),
            'store_id' =>  session('store_id')
        );
        $model_seller = Model('seller');
        $result = $model_seller->editSeller($param, $condition);
        if($result) {
            $this->recordSellerLog('编辑账号成功，账号编号：'.input('post.seller_id'));
            showDialog(lang('ds_common_op_succ'), url('/home/selleraccount/account_list'), 'succ');
        } else {
            $this->recordSellerLog('编辑账号失败，账号编号：'.input('post.seller_id'), 0);
            showDialog(lang('ds_common_save_fail'), url('/home/selleraccount/account_list'), 'error');
        }
    }

    public function account_del() {
        $seller_id = intval(input('post.seller_id'));
        if($seller_id > 0) {
            $condition = array();
            $condition['seller_id'] = $seller_id;
            $condition['store_id'] = session('store_id');
            $model_seller = Model('seller');
            $result = $model_seller->delSeller($condition);
            if($result) {
                $this->recordSellerLog('删除账号成功，账号编号'.$seller_id);
                showDialog(lang('ds_common_op_succ'),'reload','succ');
            } else {
                $this->recordSellerLog('删除账号失败，账号编号'.$seller_id);
                showDialog(lang('ds_common_save_fail'),'reload','error');
            }
        } else {
            showDialog(lang('wrong_argument'),'reload','error');
        }
    }

    public function check_seller_name_exist() {
        $seller_name = input('get.seller_name');
        $result = $this->_is_seller_name_exist($seller_name);
        if($result) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    private function _is_seller_name_exist($seller_name) {
        $condition = array();
        $condition['seller_name'] = $seller_name;
        $model_seller = Model('seller');
        return $model_seller->isSellerExist($condition);
    }

    public function check_seller_member() {
        $member_name = input('get.member_name');
        $password = input('get.password');
        $result = $this->_check_seller_member($member_name, $password);
        if($result) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    private function _check_seller_member($member_name, $password) {
        $member_info = $this->_check_member_password($member_name, $password);
        if($member_info && !$this->_is_seller_member_exist($member_info['member_id'])) {
            return $member_info;
        } else {
            return false;
        }
    }

    private function _check_member_password($member_name, $password) {
        $condition = array();
        $condition['member_name']	= $member_name;
        $condition['member_password']	= md5($password);
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfo($condition);
        return $member_info;
    }

    private function _is_seller_member_exist($member_id) {
        $condition = array();
        $condition['member_id'] = $member_id;
        $model_seller = Model('seller');
        return $model_seller->isSellerExist($condition);
    }

    
    /**
     *    栏目菜单
     */
    function getSellerItemList() {
        $menu_array[] = array(
            'name' => 'account_list',
            'text' => '账号列表',
            'url' => url('Home/selleraccount/account_list'),
        );

        if (request()->action() === 'account_add') {
            $menu_array[] = array(
                'name' => 'account_add',
                'text' => '添加账号',
                'url' => url('Home/selleraccount/account_add'),
            );
        }
        if (request()->action() === 'group_edit') {
            $menu_array[] = array(
                'name' => 'account_edit',
                'text' => '编辑账号',
                'url' => url('Home/selleraccount/account_edit'),
            );
        }
        
        return $menu_array;
    }
    
    
}
