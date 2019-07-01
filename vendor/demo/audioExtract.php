<?php

/**
 * 提取音频请求
 */

require '../../vod/service/AssetService.php';
require '../../vod/model/ExtractAudioTaskReq.php';

$req = new ExtractAudioTaskReq();
$req ->setAssetId('551a8d33caa77f4e1a1f7b1f59de4f6d');
$req ->setFormat('MP3');
$rsp = "";
try {
    $rsp = AssetService::AudioExtract($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}