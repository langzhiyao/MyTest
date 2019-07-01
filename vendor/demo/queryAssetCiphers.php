<?php
/**
 * 查询媒资密钥
 */
require '../../vod/service/AssetService.php';
require '../../vod/model/QueryAssetCiphersReq.php';

$req = new QueryAssetCiphersReq();
$req ->setAssetId('9dc85135cab675c38402e15ad880e7ce');

$rsp = "";
try {
    $rsp = AssetService::QueryAssetCiphers($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}