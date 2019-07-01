<?php
/**
 * 获取初始化分段上传已上传段签名字符串
 *
 */

require '../../vod/service/UploadService.php';
require '../../vod/model/ListPartsReq.php';

$req = new ListPartsReq();

$req->setBucket("obs-vod-6");
$req->setObjectKey("14ce1d4437164aba8b364ce15866154e/9dc85135cab675c38402e15ad880e7ce/dab0935e978602092c3755c11fc6fca1.mp4");
$req->setUploadId("000001668191AB701013958175545BF7");

$rsp = "";
try {
    $rsp = UploadService::ListParts($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}