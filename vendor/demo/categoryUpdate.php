<?php
/**
 * 修改媒资分类
 */

require '../../vod/service/CategoryService.php';
require '../../vod/model/EditCategoryReq.php';

$req = new EditCategoryReq();
$req ->setId(21337);
$req ->setName('修改的分类');

$rsp = "";
try {
    $rsp = CategoryService::UpdateAssetCategory($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}