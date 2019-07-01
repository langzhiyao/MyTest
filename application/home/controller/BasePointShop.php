<?php
namespace app\home\controller;
use think\Controller;
/**
 * 积分中心control父类
 */
class BasePointShop extends BaseHome {
    protected $member_info;
    public function _initialize() {
        parent::_initialize();
        //输出会员信息
        $this->member_info = $this->getMemberAndGradeInfo(true);
        $this->assign('member_info', $this->member_info);
        if (!config('site_state')){
            halt(config('closed_reason'));
        }
        
        //判断系统是否开启积分和积分中心功能
        if (config('points_isuse') != 1 || config('pointshop_isuse') != 1) {
            $this->error(lang('pointshop_unavailable'), url('index/index'));
        }
        $this->template_dir = 'default/member/' . strtolower(request()->controller()) . '/';
        $this->assign('index_sign', 'pointshop');
    }

    /**
     * 获得积分中心会员信息包括会员名、ID、会员头像、会员等级、经验值、等级进度、积分、已领代金券、已兑换礼品、礼品购物车
     */
    public function pointshopMInfo($is_return = false) {
        if (session('is_login') == '1') {
            $model_member = Model('member');
            if (!$this->member_info) {
                //查询会员信息
                $member_infotmp = $model_member->getMemberInfoByID(session('member_id'));
            } else {
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
                return array('member_info' => $member_info, 'vouchercount' => $vouchercount, 'pointcart_count' => $pointcart_count, 'pointordercount' => $pointordercount);
            }
        }
    }

}
