<?php
/**
 * 删除媒资请求
 *
 */
require '../../vod/service/AssetService.php';
require '../../vod/model/DeleteAssetReq.php';

$req = new DeleteAssetReq();
$req ->setAssetId('b0bf4d6700c39d0a665212b992310353');

$rsp = "";
try {
    $rsp = AssetService::DeleteAsset($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}