<?php

namespace app\home\controller;

use think\Lang;
use think\Validate;

class Selleraccountgroup extends BaseSeller {

    public function _initialize() {
        parent::_initialize();
    }

    public function group_list() {
        $model_seller_group = Model('sellergroup');
        $seller_group_list = $model_seller_group->getSellerGroupList(array('store_id' => session('store_id')));
        $this->assign('seller_group_list', $seller_group_list);
//        $this->profile_menu('group_list');
        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('selleraccountgroup');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem('group_list');
        return $this->fetch($this->template_dir.'group_list');
    }

    public function group_add() {
        $seller_group_info = array(
            'group_id' => 0,
            'group_name' => '',
            'limits' => '',
            'smt_limits' => ''
        );
        $this->assign('group_info', $seller_group_info);
        $this->assign('group_limits', explode(',', $seller_group_info['limits']));
        $this->assign('smt_limits', explode(',', $seller_group_info['smt_limits']));

        // 店铺消息模板列表
        $smt_list = Model('storemsgtpl')->getStoreMsgTplList(array(), 'smt_code,smt_name');
        $this->assign('smt_list', $smt_list);

//        $this->profile_menu('group_add');
        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('selleraccountgroup');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem('group_add');
        return $this->fetch($this->template_dir.'group_add');
    }

    public function group_edit() {
        $group_id = intval(input('param.group_id'));
        if ($group_id <= 0) {
            $this->error('参数错误');
        }
        $model_seller_group = Model('sellergroup');
        $seller_group_info = $model_seller_group->getSellerGroupInfo(array('group_id' => $group_id, 'store_id' => session('store_id')));
        if (empty($seller_group_info)) {
            $this->error('组不存在');
        }
        $this->assign('group_info', $seller_group_info);
        $this->assign('group_limits', explode(',', $seller_group_info['limits']));
        $this->assign('smt_limits', explode(',', $seller_group_info['smt_limits']));

        // 店铺消息模板列表
        $smt_list = Model('storemsgtpl')->getStoreMsgTplList(array(), 'smt_code,smt_name');
        $this->assign('smt_list', $smt_list);


//        $this->profile_menu('group_edit');
        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('selleraccountgroup');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem('group_edit');
        return $this->fetch($this->template_dir.'group_add');
    }

    public function group_save() {
        $seller_info = array();
        $group_id = intval(input('param.group_id'));

        $seller_info['group_name'] = input('post.seller_group_name');
        $seller_info['limits'] = implode(',', $_POST['limits']);
        $seller_info['smt_limits'] = empty($_POST['smt_limits']) ? '' : implode(',', $_POST['smt_limits']);
        $seller_info['store_id'] = session('store_id');
        
        
        $model_seller_group = Model('sellergroup');

        if (empty($group_id)) {
            $result = $model_seller_group->addSellerGroup($seller_info);
            $this->recordSellerLog('添加组成功，组编号' . $result);
            if($result){
                showDialog('添加成功', url('Home/selleraccountgroup/group_list'), 'succ');
            }else{
                showDialog('添加失败', url('Home/selleraccountgroup/group_list'), 'error');
            }
            
        } else {
            $condition = array();
            $condition['group_id'] = $group_id;
            $condition['store_id'] = session('store_id');
            $result = $model_seller_group->editSellerGroup($seller_info, $condition);
            $this->recordSellerLog('编辑组成功，组编号' . $group_id);
            if($result){
                showDialog('编辑成功', url('Home/selleraccountgroup/group_list'), 'succ');
            }else{
                showDialog('编辑失败', url('Home/selleraccountgroup/group_list'), 'error');
            }
            
        }
    }

    public function group_del() {
        $group_id = intval(input('param.group_id'));
        if ($group_id > 0) {
            $condition = array();
            $condition['group_id'] = $group_id;
            $condition['store_id'] = session('store_id');
            $model_seller_group = Model('sellergroup');
            $result = $model_seller_group->delSellerGroup($condition);
            if ($result) {
                $this->recordSellerLog('删除组成功，组编号' . $group_id);
                showDialog(lang('ds_common_op_succ'), 'reload', 'succ');
            } else {
                $this->recordSellerLog('删除组失败，组编号' . $group_id);
                showDialog(lang('ds_common_save_fail'), 'reload', 'error');
            }
        } else {
            showDialog(lang('wrong_argument'), 'reload', 'error');
        }
    }

    /**
     *    栏目菜单
     */
    function getSellerItemList() {
        $menu_array[] = array(
            'name' => 'group_list',
            'text' => '组列表',
            'url' => url('Home/selleraccountgroup/group_list'),
        );

        if (request()->action() === 'group_add') {
            $menu_array[] = array(
                'name' => 'group_add',
                'text' => '添加组',
                'url' => url('Home/selleraccountgroup/group_add'),
            );
        }
        if (request()->action() === 'group_edit') {
            $menu_array[] = array(
                'name' => 'group_edit',
                'text' => '编辑组',
                'url' => url('Home/selleraccountgroup/group_edit'),
            );
        }
        
        return $menu_array;
    }


}
