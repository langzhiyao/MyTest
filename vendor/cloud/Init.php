<?php
include_once 'Autoloader/Autoloader.php';
//代理初始化
define('ENABLE_HTTP_PROXY', false);
define('HTTP_PROXY_IP', '127.0.0.1');
define('HTTP_PROXY_PORT', '8080');
//用户初始化
define('AK', 'RUTI1Z31NIFQIRY32HJF');
define('SK', 'Hf9MfBiAuoBn9dz2nS0B8Rt6sMzqaoOF0Mm90zL8');
define('PROJECT_ID', '27fa6300893642c8a00425218acdc5cb');
//点播服务域名
define('VOD_HOST', 'vod.cn-north-1.myhuaweicloud.com');
//http相关
define('APPLICATION_JSON', 'application/json');
define('HTTP_METHOD_POST', 'POST');
define('HTTP_METHOD_GET', 'GET');
define('HTTP_METHOD_DELETE', 'DELETE');
define('HTTP_METHOD_PUT', 'PUT');
define('VERSION_1_0', '1.0');
define('VERSION_1_1', '1.1');
//字段相关
define('VIDEO_UPLOAD_URL','video_upload_url');
define('COVER_UPLOAD_URL','cover_upload_url');
define('SUBTITLE_UPLOAD_URL','subtitle_upload_urls');
define('OBS_ENDPOINT','obs.myhwclouds.com');