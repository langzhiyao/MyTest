<?php
/**
 * 确认媒资上传
 */

require '../../vod/service/AssetService.php';
require '../../vod/model/ConfirmAssetUploadReq.php';

$req = new ConfirmAssetUploadReq();

$req->setAssetId("df2a9536156451fc1b9f0e7bd19cc06c");
$req->setStatus("CREATED");

$rsp = "";
try {
    $rsp = AssetService::ConfirmAssetUpload($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}