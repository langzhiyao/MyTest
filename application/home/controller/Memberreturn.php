<?php

/*
 * 买家退货
 */

namespace app\home\controller;

use think\Lang;

class Memberreturn extends BaseMember {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/memberreturn.lang.php');
        //向模板页面输出退款退货状态
        $this->getRefundStateArray();
    }

    /**
     * 退货记录列表页
     *
     */
    public function index() {
        $model_refund = Model('refundreturn');
        $condition = array();
        $condition['buyer_id'] = session('member_id');
        $condition['refund_type'] = '2'; //类型:1为退款,2为退货
        
        $keyword_type = array('order_sn', 'refund_sn', 'goods_name');

        $key = input('get.key');
        $type = input('get.type');
        if (trim($key) != '' && in_array($type, $keyword_type)) {
            $condition[$type] = array('like', '%' . $key . '%');
        }
        $add_time_from = input('get.add_time_from');
        $add_time_to = input('get.add_time_to');
        if (trim($add_time_from) != '' || trim($add_time_to) != '') {
            $add_time_from = strtotime(trim($add_time_from));
            $add_time_to = strtotime(trim($add_time_to));
            if ($add_time_from !== false || $add_time_to !== false) {
                $condition['add_time'] = array('between', array($add_time_from, $add_time_to));
            }
        }

//        $return_list = $model_refund->getReturnList($condition, 10);
        
        //数据
        $page = input('param.page') ? input('param.page') : 0;
        $return_list = db('refundreturn')->where($condition)->page($page)->limit(10)->select();
        $this->assign('return_list', $return_list);
        //分页
        $return_list_page = db('refundreturn')->where($condition)->paginate(10);
        $page = $return_list_page->render();
        $this->assign('page', $page);
        
        
        $store_list = $model_refund->getRefundStoreList($return_list);
        $this->assign('store_list', $store_list);
        
        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('membervrrefund');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('buyer_return');
        return $this->fetch($this->template_dir.'index');
    }

    /**
     * 发货
     *
     */
    public function ship() {
        $model_refund = Model('refundreturn');
        $condition = array();
        $condition['buyer_id'] = session('member_id');
        $condition['refund_id'] = intval(input('param.return_id'));
        $condition['refund_type'] = '2'; //类型:1为退款,2为退货
        $return_list = $model_refund->getReturnList($condition);
        $return = $return_list[0];
        $this->assign('return', $return);
        $express_list = rkcache('express', true);
        $this->assign('express_list', $express_list);
        if ($return['seller_state'] != '2' || $return['goods_state'] != '1') {//检查状态,防止页面刷新不及时造成数据错误
            showDialog(lang('wrong_argument'), 'reload', 'error');
        }
        if (request()->isPost()) {
            $refund_array = array();
            $refund_array['ship_time'] = time();
            $refund_array['delay_time'] = time();
            $refund_array['express_id'] = $_POST['express_id'];
            $refund_array['invoice_no'] = $_POST['invoice_no'];
            $refund_array['goods_state'] = '2';
            $state = $model_refund->editRefundReturn($condition, $refund_array);
            if ($state) {
                showDialog(lang('ds_common_save_succ'), url('memberreturn/index'), 'succ');
            } else {
                showDialog(lang('ds_common_save_fail'), 'reload', 'error');
            }
        }

        $info['buyer'] = array();
        if (!empty($return['pic_info'])) {
            $info[] = unserialize($return['pic_info']);
        }

        $this->assign('pic_list', $info['buyer']);
        $condition = array();
        $condition['order_id'] = $return['order_id'];
        $order = $model_refund->getRightOrderList($condition, $return['order_goods_id']);
        $this->assign('order', $order);
        $this->assign('store', $order['extend_store']);
        $this->assign('order_common', $order['extend_order_common']);
        $this->assign('goods_list', $order['goods_list']);
        
        
        $model_trade = Model('trade');
        $return_delay = $model_trade->getMaxDay('return_delay'); //发货默认5天后才能选择没收到
        $this->assign('return_delay', $return_delay);
        $this->assign('return_confirm', $model_trade->getMaxDay('return_confirm')); //卖家不处理收货时按同意并弃货处理
        $this->assign('ship', 1);
        $this->setMemberCurMenu('member_refund');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('my_address_edit');
        return $this->fetch($this->template_dir.'view');
    }

    /**
     * 延迟时间
     *
     */
    public function delay() {
        $model_refund = Model('refundreturn');
        $condition = array();
        $condition['buyer_id'] = session('member_id');
        $condition['refund_id'] = intval(input('param.return_id'));
        $condition['refund_type'] = '2'; //类型:1为退款,2为退货
        $return_list = $model_refund->getReturnList($condition);
        $return = $return_list[0];
        $this->assign('return', $return);
        if (chksubmit()) {
            if ($return['seller_state'] != '2' || $return['goods_state'] != '3') {//检查状态,防止页面刷新不及时造成数据错误
                showDialog(lang('wrong_argument'), 'reload', 'error', 'CUR_DIALOG.close();');
            }
            $refund_array = array();
            $refund_array['delay_time'] = time();
            $refund_array['goods_state'] = '2';
            $state = $model_refund->editRefundReturn($condition, $refund_array);
            if ($state) {
                showDialog(lang('ds_common_save_succ'), 'reload', 'succ', 'CUR_DIALOG.close();');
            } else {
                showDialog(lang('ds_common_save_fail'), 'reload', 'error', 'CUR_DIALOG.close();');
            }
        }
        $model_trade = Model('trade');
        $return_delay = $model_trade->getMaxDay('return_delay'); //发货默认5天后才能选择没收到
        $this->assign('return_delay', $return_delay);
        $this->assign('return_confirm', $model_trade->getMaxDay('return_confirm')); //卖家不处理收货时按弃货处理
        return $this->fetch($this->template_dir.'delay');
    }

    /**
     * 退货记录查看页
     *
     */
    public function view() {
        $model_refund = Model('refundreturn');
        $condition = array();
        $condition['buyer_id'] = session('member_id');
        $condition['refund_id'] = intval(input('param.return_id'));
        $condition['refund_type'] = '2'; //类型:1为退款,2为退货
        $return_list = $model_refund->getReturnList($condition);
        $return = $return_list[0];
        $this->assign('return', $return);
        $express_list = rkcache('express', true);
        if ($return['express_id'] > 0 && !empty($return['invoice_no'])) {
            $this->assign('return_e_name', $express_list[$return['express_id']]['e_name']);
        }
        $info['buyer'] = array();
        if (!empty($return['pic_info'])) {
            $info = unserialize($return['pic_info']);
        }
//        $this->assign('pic_list', $info['buyer']);
        $this->assign('pic_list', '');
        $condition = array();
        $condition['order_id'] = $return['order_id'];
        $order = $model_refund->getRightOrderList($condition, $return['order_goods_id']);
        //halt($order);
        $this->assign('order', $order);
        $this->assign('ship', 0);
        $this->assign('store', $order['extend_store']);
        $this->assign('order_common', $order['extend_order_common']);
        $this->assign('goods_list', $order['goods_list']);
        
        
        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('member_refund');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('my_address_edit');
        return $this->fetch($this->template_dir.'view');
    }

    
    /**
     *    栏目菜单
     */
    function getMemberItemList() {
        $item_list = array(
            array(
                'name' => 'buyer_refund',
                'text' => lang('ds_member_path_buyer_refund'),
                'url' => url('Home/Memberrefund/index'),
            ),
            array(
                'name' => 'buyer_return',
                'text' => lang('ds_member_path_buyer_return'),
                'url' => url('Home/Memberreturn/index'),
            ),
            array(
                'name' => 'buyer_vr_refund',
                'text' => lang('虚拟兑码退款'),
                'url' => url('Home/Membervrrefund/index'),
            ),
        );
        return $item_list;
    }
    

    function getRefundStateArray($type = 'all') {
        $state_array = array(
            '1' => lang('refund_state_confirm'),
            '2' => lang('refund_state_yes'),
            '3' => lang('refund_state_no')
        ); //卖家处理状态:1为待审核,2为同意,3为不同意
        $this->assign('state_array', $state_array);

        $admin_array = array(
            '1' => '处理中',
            '2' => '待处理',
            '3' => '已完成'
        ); //确认状态:1为买家或卖家处理中,2为待平台管理员处理,3为退款退货已完成
        $this->assign('admin_array', $admin_array);

        $state_data = array(
            'seller' => $state_array,
            'admin' => $admin_array
        );
        if ($type == 'all') {
            return $state_data; //返回所有
        }
        return $state_data[$type];
    }

}
