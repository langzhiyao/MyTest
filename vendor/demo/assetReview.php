<?php
/**
 * 媒资审核
 */

require '../../vod/service/AssetService.php';
require '../../vod/model/AssetReviewReq.php';

$req = new AssetReviewReq();
$review = new Review();
$review->setInterval(5);
$review->setPorn(-1);
$req ->setAssetId('551a8d33caa77f4e1a1f7b1f59de4f6d');
$req ->setReview($review);

$rsp = "";
try {
    $rsp = AssetService::AssetReview($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}