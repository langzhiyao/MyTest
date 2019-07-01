<?php
/**
 * 更新媒资请求
 *
 */
require '../../vod/service/AssetService.php';
require '../../vod/model/UpdateAssetReq.php';
require '../../vod/model/SubtitleReq.php';

$req = new UpdateAssetReq();
$subtitle = new SubtitleReq();
$req ->setAssetId('b0bf4d6700c39d0a665212b992310353');
$req ->setCoverId(0);
$req ->setCoverType('JPG');
$subtitle ->setId(1);
$subtitle ->setLanguage('CN');
$subtitle ->setDescription('subtitle test');
$subtitle ->setType('SRT');
$subtitle ->setMd5('SqcyFjJZoDZaP8oKIY6rgQ==');
$req ->setSubtitles(array($subtitle));
$rsp = "";
try {
    $rsp = AssetService::UpdateAsset($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}