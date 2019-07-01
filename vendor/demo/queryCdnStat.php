<?php
/**
 * 查询Cdn统计信息
 */
require '../../vod/service/SummaryService.php';
require '../../vod/model/QueryStatReq.php';

$req = new QueryStatReq();
$req->setDomain("www.example.com");
$req->setStatType("cdn_bw");

$rsp = "";
try {
    $rsp = SummaryService::QueryCdnStat($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}