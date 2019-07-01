<?php
/**
 * 查询媒资分类
 */

require '../../vod/service/CategoryService.php';
require '../../vod/model/QueryCategoryReq.php';

$req = new QueryCategoryReq();
$req->setId(21337);

$rsp = "";
try {
    $rsp = CategoryService::QueryAssetCategory($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}