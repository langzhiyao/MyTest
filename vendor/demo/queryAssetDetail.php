<?php
/**
 * 查询媒资详细信息请求
 */
require '../../vod/service/AssetService.php';
require '../../vod/model/QueryAssetDetailReq.php';

$req = new QueryAssetDetailReq();
$req ->setAssetId('9dc85135cab675c38402e15ad880e7ce');
$req ->setCategories(array('base_info','review_info'));

$rsp = "";
try {
    $rsp = AssetService::QueryAssetDetail($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}