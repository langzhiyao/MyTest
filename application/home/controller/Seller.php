<?php

namespace app\home\controller;

use think\Lang;

class Seller extends BaseSeller {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/seller.lang.php');
    }

    /**
     * 商户中心首页
     *
     */
    public function index() {
        // 店铺信息
        $store_info = $this->store_info;
        $store_info['reopen_tip'] = FALSE;
        if (intval($store_info['store_end_time']) > 0) {
            $store_info['store_end_time_text'] = date('Y-m-d', $store_info['store_end_time']);
            $reopen_time = $store_info['store_end_time'] - 3600 * 24 + 1 - TIMESTAMP;
            if (!session('is_own_shop') && $store_info['store_end_time'] - TIMESTAMP >= 0 && $reopen_time < 2592000) {
                //到期续签提醒(<30天)
                $store_info['reopen_tip'] = true;
            }
        } else {
            $store_info['store_end_time_text'] = lang('store_no_limit');
        }
        // 店铺等级信息
        $store_info['grade_name'] = $this->store_grade['sg_name'];
        $store_info['grade_goodslimit'] = $this->store_grade['sg_goods_limit'];
        $store_info['grade_albumlimit'] = $this->store_grade['sg_album_limit'];

        $this->assign('store_info', $store_info);
        // 商家帮助
        $model_help = Model('help');
        $condition = array();
        $condition['help_show'] = '1'; //是否显示,0为否,1为是
        $help_list = $model_help->getStoreHelpTypeList($condition, '', 6);
        $this->assign('help_list', $help_list);

        // 销售情况统计
        $field = ' COUNT(*) as ordernum,SUM(order_amount) as orderamount ';
        $where = array();
        $where['store_id'] = session('store_id');
        $where['order_isvalid'] = 1; //计入统计的有效订单
        // 昨日销量
        $where['order_add_time'] = array('between', array(strtotime(date('Y-m-d', (time() - 3600 * 24))), strtotime(date('Y-m-d', time())) - 1));
        $daily_sales = Model('stat')->getoneByStatorder($where, $field);
        $this->assign('daily_sales', $daily_sales);
        // 月销量
        $where['order_add_time'] = array('gt', strtotime(date('Y-m', time())));
        $monthly_sales = Model('stat')->getoneByStatorder($where, $field);
        $this->assign('monthly_sales', $monthly_sales);
        unset($field, $where);

        //单品销售排行
        //最近30天
        $stime = strtotime(date('Y-m-d', (time() - 3600 * 24))) - (86400 * 29); //30天前
        $etime = strtotime(date('Y-m-d', time())) - 1; //昨天23:59
        $where = array();
        $where['store_id'] = session('store_id');
        $where['order_isvalid'] = 1; //计入统计的有效订单
        $where['order_add_time'] = array('between', array($stime, $etime));
        $field = ' goods_id,goods_name,SUM(goods_num) as goodsnum,goods_image ';
        $orderby = 'goodsnum desc,goods_id';
        $goods_list = Model('stat')->statByStatordergoods($where, $field, 0, 8, $orderby, 'goods_id');
        unset($stime, $etime, $where, $field, $orderby);
        $this->assign('goods_list', $goods_list);
        
        if (!session('is_own_shop')) {
            
            if (config('groupbuy_allow') == 1) {
                // 抢购套餐
                $groupquota_info = Model('groupbuyquota')->getGroupbuyQuotaCurrent(session('store_id'));
                $this->assign('groupquota_info', $groupquota_info);
            }
            if (intval(config('promotion_allow')) == 1) {
                // 限时折扣套餐
                $xianshiquota_info = Model('pxianshiquota')->getXianshiQuotaCurrent(session('store_id'));
                $this->assign('xianshiquota_info', $xianshiquota_info);
                // 满即送套餐
                $mansongquota_info = Model('pmansongquota')->getMansongQuotaCurrent(session('store_id'));
                $this->assign('mansongquota_info', $mansongquota_info);
                // 优惠套装套餐
                $binglingquota_info = Model('pbundling')->getBundlingQuotaInfoCurrent(session('store_id'));
                $this->assign('binglingquota_info', $binglingquota_info);
                // 推荐展位套餐
                $boothquota_info = Model('pbooth')->getBoothQuotaInfoCurrent(session('store_id'));
                $this->assign('boothquota_info', $boothquota_info);
            }
            if (config('voucher_allow') == 1) {
                $voucherquota_info = Model('voucher')->getCurrentQuota(session('store_id'));
                $this->assign('voucherquota_info', $voucherquota_info);
            }
        } else {
            $this->assign('isOwnShop', true);
        }
        $phone_array = explode(',', config('site_phone'));
        $this->assign('phone_array', $phone_array);

        $this->assign('menu_sign', 'index');


        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('seller_index');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem();
        return $this->fetch($this->template_dir.'index');
    }

    /**
     * 异步取得卖家统计类信息
     *
     */
    public function statistics() {
        $add_time_to = strtotime(date("Y-m-d") + 60 * 60 * 24);   //当前日期 ,从零点来时
        $add_time_from = strtotime(date("Y-m-d", (strtotime(date("Y-m-d")) - 60 * 60 * 24 * 30)));   //30天前
        $goods_online = 0;      // 出售中商品
        $goods_waitverify = 0;  // 等待审核
        $goods_verifyfail = 0;  // 审核失败
        $goods_offline = 0;     // 仓库待上架商品
        $goods_lockup = 0;      // 违规下架商品
        $consult = 0;           // 待回复商品咨询
        $no_payment = 0;        // 待付款
        $no_delivery = 0;       // 待发货
        $no_receipt = 0;        // 待收货
        $refund_lock = 0;      // 售前退款
        $refund = 0;            // 售后退款
        $return_lock = 0;      // 售前退货
        $return = 0;            // 售后退货
        $complain = 0;          //进行中投诉

        $model_goods = Model('goods');
        // 全部商品数
        $goodscount = $model_goods->getGoodsCommonCount(array('store_id' => session('store_id')));
        // 出售中的商品
        $goods_online = $model_goods->getGoodsCommonOnlineCount(array('store_id' => session('store_id')));
        if (config('goods_verify')) {
            // 等待审核的商品
            $goods_waitverify = $model_goods->getGoodsCommonWaitVerifyCount(array('store_id' => session('store_id')));
            // 审核失败的商品
            $goods_verifyfail = $model_goods->getGoodsCommonVerifyFailCount(array('store_id' => session('store_id')));
        }
        // 仓库待上架的商品
        $goods_offline = $model_goods->getGoodsCommonOfflineCount(array('store_id' => session('store_id')));
        // 违规下架的商品
        $goods_lockup = $model_goods->getGoodsCommonLockUpCount(array('store_id' => session('store_id')));
        // 等待回复商品咨询
        $consult = Model('consult')->getConsultCount(array('store_id' => session('store_id'), 'consult_reply' => ''));

        // 商品图片数量
        $imagecount = Model('album')->getAlbumPicCount(array('store_id' => session('store_id')));

        $model_order = Model('order');
        // 交易中的订单
        $progressing = $model_order->getOrderCountByID('store', session('store_id'), 'TradeCount');
        // 待付款
        $no_payment = $model_order->getOrderCountByID('store', session('store_id'), 'NewCount');
        // 待发货
        $no_delivery = $model_order->getOrderCountByID('store', session('store_id'), 'PayCount');

        $model_refund_return = Model('refundreturn');
        // 售前退款
        $condition = array();
        $condition['store_id'] = session('store_id');
        $condition['refund_type'] = 1;
        $condition['order_lock'] = 2;
        $condition['refund_state'] = array('lt', 3);
        $refund_lock = $model_refund_return->getRefundReturnCount($condition);
        // 售后退款
        $condition = array();
        $condition['store_id'] = session('store_id');
        $condition['refund_type'] = 1;
        $condition['order_lock'] = 1;
        $condition['refund_state'] = array('lt', 3);
        $refund = $model_refund_return->getRefundReturnCount($condition);
        // 售前退货
        $condition = array();
        $condition['store_id'] = session('store_id');
        $condition['refund_type'] = 2;
        $condition['order_lock'] = 2;
        $condition['refund_state'] = array('lt', 3);
        $return_lock = $model_refund_return->getRefundReturnCount($condition);
        // 售后退货
        $condition = array();
        $condition['store_id'] = session('store_id');
        $condition['refund_type'] = 2;
        $condition['order_lock'] = 1;
        $condition['refund_state'] = array('lt', 3);
        $return = $model_refund_return->getRefundReturnCount($condition);

        $condition = array();
        $condition['accused_id'] = session('store_id');
        $condition['complain_state'] = array(array('gt', 10), array('lt', 90), 'and');
        $complain = db('complain')->where($condition)->count();

        //待确认的结算账单
        $model_bill = Model('bill');
        $condition = array();
        $condition['ob_store_id'] = session('store_id');
        $condition['ob_state'] = BILL_STATE_CREATE;
        $bill_confirm_count = $model_bill->getOrderBillCount($condition);

        //统计数组
        $statistics = array(
            'goodscount' => $goodscount,
            'online' => $goods_online,
            'waitverify' => $goods_waitverify,
            'verifyfail' => $goods_verifyfail,
            'offline' => $goods_offline,
            'lockup' => $goods_lockup,
            'imagecount' => $imagecount,
            'consult' => $consult,
            'progressing' => $progressing,
            'payment' => $no_payment,
            'delivery' => $no_delivery,
            'refund_lock' => $refund_lock,
            'refund' => $refund,
            'return_lock' => $return_lock,
            'return' => $return,
            'complain' => $complain,
            'bill_confirm' => $bill_confirm_count
        );
        exit(json_encode($statistics));
    }

    /**
     * 添加快捷操作
     */
    function quicklink_add() {
        if (!empty($_POST['item'])) {
            $_SESSION['seller_quicklink'][$_POST['item']] = $_POST['item'];
        }
        $this->_update_quicklink();
        echo 'true';
    }

    /**
     * 删除快捷操作
     */
    function quicklink_del() {
        if (!empty($_POST['item'])) {
            unset($_SESSION['seller_quicklink'][$_POST['item']]);
        }
        $this->_update_quicklink();
        echo 'true';
    }

    private function _update_quicklink() {
        $quicklink = implode(',', session('seller_quicklink'));
        $update_array = array('seller_quicklink' => $quicklink);
        $condition = array('seller_id' => session('seller_id'));
        $model_seller = Model('seller');
        $model_seller->editSeller($update_array, $condition);
    }

}

?>
