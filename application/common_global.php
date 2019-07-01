<?php
error_reporting(E_ERROR);
define('MD5_KEY', 'a2382918dbb49c8643f19bc3ab90ecf9');

/*

 * 
 */
define('CHARSET','UTF-8');
define('TATX',TRUE);

define('HTTP_URL','114.116.142.79:8080/?');

define('BASE_SITE_URL', $config['url_domain_root']);
define('UPLOAD_SITE_URL',BASE_SITE_URL.'uploads');
define('BASE_UPLOAD_PATH',ROOT_PATH.'/public/uploads');
define('CHAT_SITE_URL', BASE_SITE_URL.'static/chat');

define('TIMESTAMP',time());
define('DIR_SHOP','shop');
define('DIR_CMS','cms');
define('DIR_CIRCLE','circle');
define('DIR_MICROSHOP','microshop');
define('DIR_ADMIN','admin');
define('DIR_OFFICE','office');
define('DIR_API','api');
define('DIR_MOBILE','mobile');
define('DIR_APP','wap');
define('DIR_WAP','wap');

define('DIR_RESOURCE','data/resource');
define('DIR_UPLOAD','public/uploads');

define('ATTACH_PATH','home');
define('ATTACH_COMMON',ATTACH_PATH.'/common');
define('ATTACH_AVATAR',ATTACH_PATH.'/avatar');
define('ATTACH_INVITER',ATTACH_PATH.'/inviter');
define('ATTACH_EDITOR',ATTACH_PATH.'/editor');
define('ATTACH_MEMBERTAG',ATTACH_PATH.'/membertag');
define('ATTACH_STORE',ATTACH_PATH.'/store');
define('ATTACH_GOODS',ATTACH_PATH.'/store/goods');
define('ATTACH_STORE_DECORATION',ATTACH_PATH.'/store/decoration');
define('ATTACH_LOGIN',ATTACH_PATH.'/login');
define('ATTACH_WAYBILL',ATTACH_PATH.'/waybill');
define('ATTACH_ARTICLE',ATTACH_PATH.'/article');
define('ATTACH_BRAND',ATTACH_PATH.'/brand');
define('ATTACH_COMPLAIN',ATTACH_PATH.'/complain');
define('ATTACH_GOODS_CLASS','shop/goods_class');
define('ATTACH_DELIVERY','/delivery');
define('ATTACH_ADV',ATTACH_PATH.'/adv');
define('ATTACH_ACTIVITY',ATTACH_PATH.'/activity');
define('ATTACH_WATERMARK',ATTACH_PATH.'/watermark');
define('ATTACH_POINTPROD',ATTACH_PATH.'/pointprod');
define('ATTACH_GROUPBUY',ATTACH_PATH.'/groupbuy');
define('ATTACH_LIVE_GROUPBUY',ATTACH_PATH.'/livegroupbuy');
define('ATTACH_SLIDE',ATTACH_PATH.'/store/slide');
define('ATTACH_VOUCHER',ATTACH_PATH.'/voucher');
define('ATTACH_STORE_JOININ',ATTACH_PATH.'/store_joinin');
define('ATTACH_REC_POSITION',ATTACH_PATH.'/rec_position');
define('ATTACH_MOBILE','mobile');
define('ATTACH_WAP','wap');
define('ATTACH_NAVICON',ATTACH_MOBILE.'/navicon');
define('ATTACH_CIRCLE','circle');
define('ATTACH_CMS','cms');
define('ATTACH_LIVE','live');
define('ATTACH_MALBUM',ATTACH_PATH.'/member');
define('ATTACH_MICROSHOP','microshop');
define('TPL_SHOP_NAME','default');
define('TPL_CIRCLE_NAME', 'default');
define('TPL_MICROSHOP_NAME', 'default');
define('TPL_CMS_NAME', 'default');
define('TPL_ADMIN_NAME', 'default');
define('TPL_DELIVERY_NAME', 'default');
define('TPL_MEMBER_NAME', 'default');

define('DEFAULT_CONNECT_SMS_TIME', 60);//倒计时时间

/*
 * 商家入驻状态定义
 */
//新申请
define('STORE_JOIN_STATE_NEW', 10);
//完成付款
define('STORE_JOIN_STATE_PAY', 11);
//初审成功
define('STORE_JOIN_STATE_VERIFY_SUCCESS', 20);
//初审失败
define('STORE_JOIN_STATE_VERIFY_FAIL', 30);
//付款审核失败
define('STORE_JOIN_STATE_PAY_FAIL', 31);
//开店成功
define('STORE_JOIN_STATE_FINAL', 40);

//默认颜色规格id(前台显示图片的规格)
define('DEFAULT_SPEC_COLOR_ID', 1);


/**
 * 商品图片
 */
define('GOODS_IMAGES_WIDTH', '60,240,360,1280');
define('GOODS_IMAGES_HEIGHT', '60,240,360,12800');
define('GOODS_IMAGES_EXT', '_60,_240,_360,_1280');

/**
 *  订单状态
 */
//已取消
define('ORDER_STATE_CANCEL', 0);
//已产生但未支付
define('ORDER_STATE_NEW', 10);
//已支付
define('ORDER_STATE_PAY', 20);
//已发货
define('ORDER_STATE_SEND', 30);
//已收货，交易成功
define('ORDER_STATE_SUCCESS', 40);
//未付款订单，自动取消的天数
define('ORDER_AUTO_CANCEL_DAY', 3);
//已发货订单，自动确认收货的天数
define('ORDER_AUTO_RECEIVE_DAY', 7);
//兑换码支持过期退款，可退款的期限，默认为7天
define('CODE_INVALID_REFUND', 7);
//默认未删除
define('ORDER_DEL_STATE_DEFAULT', 0);
//已删除
define('ORDER_DEL_STATE_DELETE', 1);
//彻底删除
define('ORDER_DEL_STATE_DROP', 2);
//订单结束后可评论时间，15天，60*60*24*15
define('ORDER_EVALUATE_TIME', 1296000);
//抢购订单状态
define('OFFLINE_ORDER_CANCEL_TIME', 3);//单位为天

define('MOBILE_SITE_URL', 'http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'index.php')).'mobile');
define('SHOP_SITE_URL', 'http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'index.php')).'home');
define('WAP_SITE_URL', 'http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'index.php')).'wap');

define('APP_SITE_URL', 'http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'index.php')).'app');


?>
