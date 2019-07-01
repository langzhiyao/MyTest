<?php
/**
 * 获取分段上传合并段签名字符串
 */

require '../../vod/service/UploadService.php';
require '../../vod/model/CompleteMultipartUploadReq.php';

$req = new CompleteMultipartUploadReq();

$req->setBucket("obs-vod-6");
$req->setObjectKey("14ce1d4437164aba8b364ce15866154e/9dc85135cab675c38402e15ad880e7ce/dab0935e978602092c3755c11fc6fca1.mp4");
$req->setUploadId("00000166777134A810185F927F0C5770");

$rsp = "";
try {
    $rsp = UploadService::CompleteMultipartUpload($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}