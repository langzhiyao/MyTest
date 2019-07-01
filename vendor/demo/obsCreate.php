<?php
/**
 * OBS转存方式创建媒资
 */

require '../../vod/service/AssetService.php';
require '../../vod/model/PublishAssetFromObsReq.php';
require '../../vod/model/ObsObjInfo.php';

$req = new PublishAssetFromObsReq();
$obsObj = new ObsObjInfo();
$obsObj ->setBucket('obs-gxh');
$obsObj ->setLocation('southchina');
$obsObj ->setObject('GG_MP4.mp4');
$req ->setInput($obsObj);
$req ->setTitle('PHP测试OBS转存');
$req ->setDescription('20180917');
$req ->setCategoryId(-1);
$req ->setTags('test');
$req ->setVideoType('MP4');
$req ->setAutoPublish(1);
$req ->setTemplateGroupName('yunlongceshi1');

$rsp = "";
try {
    $rsp = AssetService::OBSCreateAsset($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}


