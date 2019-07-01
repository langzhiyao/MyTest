<?php
/**
 * 更新媒资请求
 *
 */
require '../../vod/service/AssetService.php';
require '../../vod/model/UpdateAssetMetaReq.php';

$req = new UpdateAssetMetaReq();
$req ->setTitle('PHP更新媒资操作');
//$req ->setAssetId('b0bf4d6700c39d0a665212b992310353');
$req ->setDescription('PHP更新媒资操作');

$rsp = "";
try {
    $rsp = AssetService::UpdateAssetMeta($req);
    echo $rsp->getStatus();
} catch (Exception $e) {
    echo $e;
}