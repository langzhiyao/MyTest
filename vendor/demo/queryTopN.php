<?php
/**
 * 查询TopN信息
 */
require '../../vod/service/SummaryService.php';
require '../../vod/model/QueryTopNReq.php';

$req = new QueryTopNReq();
$req ->setDomain("www.test.com");
$req ->setDate("20181010");
$rsp = "";
try {
    $rsp = SummaryService::QueryTopN($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}