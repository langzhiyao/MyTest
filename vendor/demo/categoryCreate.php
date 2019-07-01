<?php
/**
 * 创建媒资分类
 */

require '../../vod/service/CategoryService.php';
require '../../vod/model/CreateCategoryReq.php';

$req = new CreateCategoryReq();
$req ->setName('新分类');
$req ->setParentId(0);

$rsp = "";
try {
    $rsp = CategoryService::CreateAssetCategory($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}