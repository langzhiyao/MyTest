<?php
/**
 * 媒资发布请求
 *
 */
require '../../vod/service/AssetService.php';
require '../../vod/model/PublishAssetReq.php';

$req = new PublishAssetReq();
$req ->setAssetId(array(''));

$rsp = "";
try {
    $rsp = AssetService::PublishAsset($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}