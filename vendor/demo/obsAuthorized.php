<?php
/**
 * OBS桶授权请求
 *
 */
require '../../vod/service/AssetService.php';
require '../../vod/model/BucketAuthorizedReq.php';

$req = new BucketAuthorizedReq();
$req ->setProjectId('14ce1d4437164aba8b364ce15866154e');
$req ->setBucket('obs-gxh');
$req ->setOperation('1');

$rsp = "";
try {
    $rsp = AssetService::OBSBucketAuthorized($req);
    echo $rsp->getStatus();
} catch (Exception $e) {
    echo $e;
}

