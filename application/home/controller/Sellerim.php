<?php

namespace app\home\controller;


class Sellerim extends BaseSeller
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $add_time_to = date("Y-m-d");
        $time_from = array();
        $time_from['7'] = strtotime($add_time_to) - 60 * 60 * 24 * 7;
        $time_from['60'] = strtotime($add_time_to) - 60 * 60 * 24 * 60;
        $add_time_from = date("Y-m-d", $time_from['60']);
        $this->assign('minDate', $add_time_from);//只能查看2个月内数据
        $this->assign('maxDate', $add_time_to);
        $timefrom = input('param.add_time_from');
        if (empty($timefrom) || $timefrom < $add_time_from) {
            $timefrom = date("Y-m-d", $time_from['7']);
        }
        $timeto =input('param.add_time_to');
        if (empty($timeto) || $timeto > $add_time_to) {
            $timeto = $add_time_to;
        }
    }

    /**
     * 查询页
     *
     */
    public function index()
    {
        $model_seller = Model('seller');
        $condition = array();
        $condition['store_id'] = session('store_id');
        $seller_list = $model_seller->getSellerList($condition, '', 'seller_id asc');//账号列表
        $this->assign('seller_list', $seller_list);

        $seller_id = session('seller_id');
        //halt($seller_id);
        $this->assign('seller_id', $seller_id);
        $this->setSellerCurMenu('Sellerim');
        $this->setSellerCurItem('index');
       
       return $this->fetch($this->template_dir.'index');
    }

    /**
     * 聊天记录查看页
     *
     */
    public function get_chat_log()
    {
        $model_seller = Model('seller');
        $condition = array();
        $condition['store_id'] = session('store_id');
        $condition['seller_id'] = input('param.seller_id');
        $seller = $model_seller->getSellerInfo($condition);//账号
        $this->assign('seller', $seller);
        if ($seller['member_id'] > 0) {//验证商家账号
            $model_chat = Model('webchat');
            $condition['add_time_from'] = trim(input('param.add_time_from'));
            $condition['add_time_to'] = trim(input('param.add_time_to'));
            $condition['f_id'] = intval($seller['member_id']);
            $condition['t_id'] = intval(input('param.t_id'));
            $condition['t_msg'] = trim(input('param.msg_key'));
            $list = $model_chat->getLogFromList($condition, 15);
            $list = array_reverse($list);
            $this->assign('list', $list);
            $this->assign('show_page', $model_chat->page_info->render());
        }
       echo $this->fetch($this->template_dir.'chat_log');
    }

    /**
     * 最近联系人
     *
     */
    public function get_user_list()
    {
        $model_seller = Model('seller');
        $condition = array();
        $condition['store_id'] = session('store_id');
        $condition['seller_id'] = input('param.seller_id');
        $seller = $model_seller->getSellerInfo($condition);//账号
        $member_list = array();
        if ($seller['member_id'] > 0) {//验证商家账号
            $model_chat = Model('webchat');
            $add_time_to = time();
            $add_time_from = $add_time_to - 60 * 60 * 24 * 60;
            $condition = array();
            $condition['add_time'] = array('between', array($add_time_from, $add_time_to));
            $condition['f_id'] = $seller['member_id'];
            $member_list = $model_chat->getRecentList($condition, 100, $member_list);
            $condition = array();
            $condition['add_time'] = array('between', array($add_time_from, $add_time_to));
            $condition['t_id'] = $seller['member_id'];
            $member_list = $model_chat->getRecentFromList($condition, 100, $member_list);
            $this->assign('list', $member_list);
        }
       echo $this->fetch($this->template_dir.'chat_user');exit;
    }

    /**
     * 小导航
     *
     * @param string $menu_type 导航类型
     * @param string $menu_key 当前导航的menu_key
     * @return
     */
    protected function getSellerItemList()
    {
        $menu_array = array(
            array(
                'name' => 'index', 'text' => '聊天记录查询', 'url' => url('sellerim/index'),
            ),
        );
        return $menu_array;
    }
}