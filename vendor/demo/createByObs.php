<?php
/**
 * 创建媒资并上传请求
 *
 */

require '../../vod/service/AssetService.php';
require '../../vod/model/CreateAssetByFileReq.php';

$req = new CreateAssetByFileReq();
//字幕参数
$subtitle = new SubtitleReq();
$subtitle ->setId(1);
$subtitle ->setLanguage('CN');
$subtitle ->setDescription('php subtitle test');
$subtitle ->setType('SRT');
$req ->setTitle('测试上传视频');
$req ->setDescription('des');
$req ->setCategoryId(-1);
$req ->setVideoName('测试上传视频');
//媒资类型
$req ->setVideoType('MP4');
//封面类型
$req ->setCoverType('JPG');
//要上传的本地媒资、封面和字幕文件地址
$req ->setVideoFileUrl("D:\测试视频\small-file.mp4");
$req ->setCoverFileUrl("D:\测试视频\some.jpg");
$req ->setSubtitleFileUrl("D:\测试视频\\test.srt");
$req ->setSubtitles([$subtitle]);

$rsp = "";
try {
    $rsp = AssetService::CreateAssetByObs($req);
    echo $rsp;
} catch (VodException $e) {
    echo $e;
}