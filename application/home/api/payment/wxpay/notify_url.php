<?php
/**
 * 接收微信支付异步通知回调地址
 */
halt(11);
trace('begin-notify','info');
$_GET['act']	= 'payment';
$_GET['op']		= 'wxpay_notify';
require_once(dirname(__FILE__).'/../../../index.php');
