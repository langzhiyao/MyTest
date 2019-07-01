<?php
/**
 * 查询媒资请求
 *
 */
require '../../vod/service/AssetService.php';
require '../../vod/model/QueryAssetMetaReq.php';

$req = new QueryAssetMetaReq();
//$req ->setAssetId(array('9dc85135cab675c38402e15ad880e7ce','9eeef0daf6b48e1d85172021f5c435f'));
$req ->setStatus(['PUBLISHED','DELETED']);
$req->setPage(1);
$req->setSize(2);

$rsp = "";
try {
    $rsp = AssetService::QueryAsset($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}