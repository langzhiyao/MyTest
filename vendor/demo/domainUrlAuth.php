<?php
/**
 * 域名key防盗链
 */

require '../../vod/service/DomainUrlAuthService.php';

$req = new CreateDomainAuthInfoReq();

$req->setKey("myKey");
$req->setDomainName("198.cdn-vod.huaweicloud.com");
$req->setOriginalUrl("https://198.cdn-vod.huaweicloud.com/asset/415a0be2400c010316fcecb7a334390b/cover/Cover0.jpg");

$rsp = DomainUrlAuthService::createAuthInfoUrl($req);

echo $rsp->getUrl();