<?php
namespace app\admin\controller;

use think\Lang;
use think\Validate;
use vomont\Vomont;
vendor('cloud.vod.service.AssetService');
vendor('cloud.vod.model.CreateAssetByFileReq');
vendor('cloud.vod.model.AssetProcessReq');
vendor('cloud.vod.model.QueryAssetDetailReq');
vendor('cloud.vod.model.AssetReviewReq');
vendor('cloud.vod.model.PublishAssetReq');

class Ass extends AdminControl
{
    public function index(){
        //print_r($_FILES);exit;
        $req = new \CreateAssetByFileReq();
//字幕参数
        //$subtitle = new \SubtitleReq();
        //$subtitle ->setId(1);
        //$subtitle ->setLanguage('CN');
        //$subtitle ->setDescription('php subtitle test');
        //$subtitle ->setType('SRT');
        $req ->setTitle($_FILES['file']['name']);
        $req ->setDescription('des');
        $req ->setCategoryId(-1);
        $req ->setVideoName($_FILES['file']['name']);
//媒资类型
        $req ->setVideoType('MP4');
//封面类型
        //$req ->setCoverType('JPG');
//要上传的本地媒资、封面和字幕文件地址
        $req ->setVideoFileUrl($_FILES['file']['tmp_name']);
// $req ->setCoverFileUrl("D:\测试视频\some.jpg");
// $req ->setSubtitleFileUrl("D:\测试视频\\test.srt");
        //$req ->setSubtitles([$subtitle]);

        $rsp = "";
        try {
            $rsp = \AssetService::CreateAssetByObs($req);
            $a=json_decode($rsp,true);


            $req = new \AssetProcessReq();
            $req ->setAssetId($a['asset_id']);
            $req ->setTemplateGroupName('system_template_group');
            $thumbnail = new \Thumbnail();
            $thumbnail ->setType('time');
            $thumbnail ->setTime(1);
            $thumbnail ->setCoverPosition(1);
            $thumbnail ->setFormat(1);
            $thumbnail ->setAspectRatio(1);
            $req ->setThumbnail($thumbnail);

            $rsp = "";
            try {
                $rsp = \AssetService::ProcessAsset($req);
                $result=$rsp->getBody();
                $b=json_decode($result,true);
                $req = new \AssetReviewReq();
                $review = new \Review();
                $review->setInterval(5);
                $review->setPorn(-1);
                $req ->setAssetId($b['asset_id']);
                $req ->setReview($review);

                $rsp = "";
                try {
                    $rsp = \AssetService::AssetReview($req);
                    $rsp->getBody();

                    $req = new \PublishAssetReq();
                    $req ->setAssetId(array($b['asset_id']));

                    $rsp = "";
                    try {
                        $rsp = \AssetService::PublishAsset($req);
                        $rsp->getBody();

                            $req = new \QueryAssetDetailReq();
                            $req ->setAssetId($b['asset_id']);
                            $req ->setCategories(array('base_info','review_info'));
                            $rsp = "";
                            try {
                                $rsp = \AssetService::QueryAssetDetail($req);
                                $res=$rsp->getBody();
                                $c=json_decode($res,true);
                                $c['base_info']['image_url']="http://video.xiangjianhai.com/asset/".$c['asset_id']."/cover/Cover0.jpg";
                                print_r($c);exit;
                            } catch (\Exception $e) {
                                echo $e;
                            }

                    } catch (\Exception $e) {
                        echo $e;
                    }

                } catch (\Exception $e) {
                    echo $e;
                }


            } catch (\Exception $e) {
                echo $e;
            }

        } catch (\VodException $e) {
            echo $e;
        }
    }
}