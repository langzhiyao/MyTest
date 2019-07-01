<?php
/**
 * 查询媒资详细信息请求
 */
require '../../vod/service/AssetService.php';
require '../../vod/model/QueryAssetListReq.php';

$req = new QueryAssetListReq();
$req ->setAssetId(['9dc85135cab675c38402e15ad880e7ce','0b9ddcb288d5c023b20f99352676671d']);
try {
    $rsp = AssetService::QueryAssetList($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}