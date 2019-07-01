<?php
/**
 * 媒资CDN预热
 */
require '../../vod/service/AssetService.php';
require '../../vod/model/PreheatingAssetReq.php';

$req = new PreheatingAssetReq();
$req ->setAssetId('a1f1999768178369c2e42b0e97f8034c');

$rsp = "";
try {
    $rsp = AssetService::PreheatingAsset($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}