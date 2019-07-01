<?php
/**
 * 删除媒资分类
 */

require '../../vod/service/CategoryService.php';
require '../../vod/model/DeleteCategoryReq.php';

$req = new DeleteCategoryReq();
$req->setId(21337);

$rsp = "";
try {
    $rsp = CategoryService::DeleteAssetCategory($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}