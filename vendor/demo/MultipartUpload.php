<?php

/**
 * 获取分段上传签名字符串
 *
 */

require '../../vod/service/UploadService.php';
require '../../vod/model/MultipartUploadReq.php';

$req = new MultipartUploadReq();

$req->setBucket("ss");
$req->setObjectKey("14ce1d4437164aba8b364ce15866154e/9dc85135cab675c38402e15ad880e7ce/dab0935e978602092c3755c11fc6fca1.mp4");
$req->setUploadId("9527121");
$req->setContentMd5('xx');
$req->setPartNumber(1);

$rsp = "";
try {
    $rsp = UploadService::MultipartUpload($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}
