<?php
/**
 * key防盗链
 *
 */

require '../../vod/service/UrlAuthService.php';
require '../../vod/model/CreateAuthInfoReq.php';

$req = new CreateAuthInfoReq();
$req->setAssetId("3b54b0ed35781f75e1b4ff36fff6e946");
$req->setKey("1998");
$req->setUrl("https://vod.cn-north-1.huaweicloud.com/asset/3b54b0ed35781f75e1b4ff36fff6e946/play_video/%E5%A4%A7Q_H.264_480X270_HEAACV1_300.mp4");
$req->setUserIp("10.74.212.209");
$req->setCheckLevel(5);

$rsp = UrlAuthService::CreateDomainAuthInfoUrl($req);

echo $rsp->getUrl();



