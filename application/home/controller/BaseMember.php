<?php

/**
 * 买家
 */

namespace app\home\controller;
use think\Lang;

class BaseMember extends BaseHome {

    protected $member_info = array();   // 会员信息

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/basemember.lang.php');
        /* 不需要登录就能访问的方法 */
        if (!in_array(request()->controller() ,array('cart','pointshop','pointvoucher')) && !in_array(request()->action(), array('ajax_load', 'add', 'del')) && !session('member_id')) {
            $this->error('您需要先登录',url('Login/login'));
        }
        //会员中心模板路径
        $this->template_dir = 'default/member/' . strtolower(request()->controller()) . '/';
        $this->member_info = $this->getMemberAndGradeInfo(true);
        $this->assign('member_info', $this->member_info);
    }

    /**
     *    当前选中的栏目
     */
    protected function setMemberCurItem($curitem = '') {
        $this->assign('member_item', $this->getMemberItemList());
        $this->assign('curitem', $curitem);
    }

    /**
     *    当前选中的子菜单
     */
    protected function setMemberCurMenu($cursubmenu = '') {
        $member_menu = $this->getMemberMenuList();
        $this->assign('member_menu', $member_menu);
        $curmenu = '';
        foreach ($member_menu as $key => $menu) {
            foreach ($menu['submenu'] as $subkey => $submenu) {
                if ($submenu['name'] == $cursubmenu) {
                    $curmenu = $menu['name'];
                    $nav = $submenu['text'];
                }
            }
        }
        
        // 面包屑
        $nav_link = array();
        $nav_link[] = array('title' => lang('homepage'), 'link' => SHOP_SITE_URL);
        if ($curmenu == '') {
            $nav_link[] = array('title' => '我的商城');
        } else {
            $nav_link[] = array('title' => '我的商城', 'link' => url('member/index'));
            $nav_link[] = array('title' => $nav);
        }


        $this->assign('nav_link_list', $nav_link);


        //当前一级菜单
        $this->assign('curmenu', $curmenu);
        //当前二级菜单
        $this->assign('cursubmenu', $cursubmenu);
    }

    /*
     * 获取卖家栏目列表,针对控制器下的栏目
     */

    protected function getMemberItemList() {
        return array();
    }

    /*
     * 获取卖家菜单列表
     */

    private function getMemberMenuList() {
        $menu_list = array(
            'trade' =>
            array(
                'name' => 'trade',
                'text' => '交易管理',
                'url' => url('Home/Memberorder/index'),
                'submenu' => array(
                    array('name' => 'member_order', 'text' => '实物订单', 'url' => url('Home/Memberorder/index'),),
                    array('name' => 'member_vr_order', 'text' => '虚拟订单', 'url' => url('Home/Membervrorder/index'),),
                    array('name' => 'member_favorites', 'text' => '我的收藏', 'url' => url('Home/Memberfavorites/fglist'),),
                    array('name' => 'member_evaluate', 'text' => '交易评价/晒单', 'url' => url('Home/Memberevaluate/index'),),
                    array('name' => 'predeposit', 'text' => '账户余额', 'url' => url('Home/Predeposit/index'),),
                    array('name' => 'member_points', 'text' => '我的积分', 'url' => url('Home/Memberpoints/index'),),
                    array('name' => 'member_voucher', 'text' => '我的代金券', 'url' => url('Home/Membervoucher/index'),),
                )
            ),
            'server' =>
            array(
                'name' => 'server',
                'text' => '客户服务',
                'url' => url('Home/Memberrefund/index'),
                'submenu' => array(
                    array('name' => 'member_refund', 'text' => '退款及退货', 'url' => url('Home/Memberrefund/index'),),
                    array('name' => 'member_complain', 'text' => '交易投诉', 'url' => url('Home/Membercomplain/index'),),
                    array('name' => 'member_consult', 'text' => '商品咨询', 'url' => url('Home/Memberconsult/index'),),
                    array('name' => 'member_inform', 'text' => '违规举报', 'url' => url('Home/Memberinform/index'),),
                    array('name' => 'member_mallconsult', 'text' => '平台客服', 'url' => url('Home/Membermallconsult/index'),),
                )
            ),
            'info' =>
            array(
                'name' => 'info',
                'text' => '资料管理',
                'url' => url('Home/Memberinformation/index'),
                'submenu' => array(
                    array('name' => 'member_information', 'text' => '账户信息', 'url' => url('Home/Memberinformation/index'),),
                    array('name' => 'member_security', 'text' => '账户安全', 'url' => url('Home/Membersecurity/index'),),
                    array('name' => 'member_address', 'text' => '收货地址', 'url' => url('Home/Memberaddress/index'),),
                    array('name' => 'member_message', 'text' => '我的消息', 'url' => url('Home/Membermessage/message'),),
                    array('name' => 'member_connect', 'text' => '第三方账号登录', 'url' => url('Home/Memberconnect/qqbind'),),
                )
            ),
            'inviter' =>
            array(
                'name' => 'inviter',
                'text' => '会员推广',
                'url' => url('Home/Memberinviter/index'),
                'submenu' => array(
                    array('name' => 'inviter_poster', 'text' => '推广海报', 'url' => url('Home/Memberinviter/index'),),
                    array('name' => 'inviter_user', 'text' => '推广会员', 'url' => url('Home/Memberinviter/user'),),
                    array('name' => 'inviter_order', 'text' => '推广佣金', 'url' => url('Home/Memberinviter/order'),),
                )
            ),
           /* array(
                'name' => 'sns',
                'text' => '应用管理',
                'url' => url('Home/memberflea/index'),
                'submenu' => array(
                    array('name' => 'member_flea', 'text' => '我的闲置', 'url' => url('Home/memberflea/index'),),
                    array('name' => 'member_snshome', 'text' => '个人主页', 'url' => url('Home/membersnshome/index'),),
                    array('name' => 'member_article', 'text' => '我的CMS', 'url' => url('Home/memberarticle/index'),),
                    array('name' => 'p_center', 'text' => '我的圈子', 'url' => url('Home/pcenter/index'),),
                    array('name' => 'home', 'text' => '我的微商城', 'url' => url('Home/home/index'),),
                )
            ),*/

        );
        return $menu_list;
    }

/**
 * 获得积分中心会员信息包括会员名、ID、会员头像、会员等级、经验值、等级进度、积分、已领代金券、已兑换礼品、礼品购物车
 */
public function pointshopMInfo($is_return = false)
{
    if (session('is_login') == '1') {
        $model_member = Model('member');
        if (!$this->member_info) {
            //查询会员信息
            $member_infotmp = $model_member->getMemberInfoByID(session('member_id'));
        }
        else {
            $member_infotmp = $this->member_info;
        }
        $member_infotmp['member_exppoints'] = intval($member_infotmp['member_exppoints']);

        //当前登录会员等级信息
        $membergrade_info = $model_member->getOneMemberGrade($member_infotmp['member_exppoints'], true);
        $member_info = array_merge($member_infotmp, $membergrade_info);
        $this->assign('member_info', $member_info);

        //查询已兑换并可以使用的代金券数量
        $model_voucher = Model('voucher');
        $vouchercount = $model_voucher->getCurrentAvailableVoucherCount(session('member_id'));
        $this->assign('vouchercount', $vouchercount);

        //购物车兑换商品数
        $pointcart_count = Model('pointcart')->countPointCart(session('member_id'));
        $this->assign('pointcart_count', $pointcart_count);

        //查询已兑换商品数(未取消订单)
        $pointordercount = Model('pointorder')->getMemberPointsOrderGoodsCount(session('member_id'));
        $this->assign('pointordercount', $pointordercount);
        if ($is_return) {
            return array(
                'member_info' => $member_info, 'vouchercount' => $vouchercount, 'pointcart_count' => $pointcart_count,
                'pointordercount' => $pointordercount
            );
        }
    }
}

}

?>
