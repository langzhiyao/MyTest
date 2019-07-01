<?php
/**
 * 媒资取消发布
 *
 */
require '../../vod/service/AssetService.php';
require '../../vod/model/PublishAssetReq.php';

$req = new PublishAssetReq();
$req ->setAssetId(array('a1f1999768178369c2e42b0e97f8034c','sss'));

$rsp = "";
try {
    $rsp = AssetService::UnPublishAsset($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}