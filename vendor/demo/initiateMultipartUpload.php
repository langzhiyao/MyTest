<?php

/**
 * 获取初始化分段上传任务签名字符串
 */

require '../../vod/service/UploadService.php';
require '../../vod/model/InitiateMultipartUploadReq.php';

$req = new InitiateMultipartUploadReq();

$req->setBucket("");
$req->setObjectKey("14ce1d4437164aba8b364ce15866154e/9dc85135cab675c38402e15ad880e7ce/dab0935e978602092c3755c11fc6fca1.mp4");
$req->setContentType("video/mp4");
$rsp = "";
try {
    $rsp = UploadService::InitiateMultipartUpload($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}