<?php
/**
 * 媒资处理请求
 *
 */

require '../../vod/service/AssetService.php';
require '../../vod/model/AssetProcessReq.php';

$req = new AssetProcessReq();
$req ->setAssetId('d5fb2d2573a88b2eb81057fb6a2ab483');
$req ->setTemplateGroupName('yunlongceshi1');

$rsp = "";
try {
    $rsp = AssetService::ProcessAsset($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}