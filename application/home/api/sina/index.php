<?php
//判断是否已经登录
if(session('slast_key'))
{
	@header("Location:".SHOP_SITE_URL);
	exit;
}
include_once(APP_PATH.DS.ATTACH_PATH.DS.'api'.DS.'sina'.DS.'config.php' );
include_once(APP_PATH.DS.ATTACH_PATH.DS.'api'.DS.'sina'.DS.'saetv2.ex.class.php' );
$o = new SaeTOAuthV2(WB_AKEY,WB_SKEY);
$code_url = $o->getAuthorizeURL(WB_CALLBACK_URL);
@header("location:$code_url");
exit;
?>