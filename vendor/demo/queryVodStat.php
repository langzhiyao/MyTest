<?php
/**
 * 查询源站统计信息
 */
require '../../vod/service/SummaryService.php';
require '../../vod/model/QueryStatReq.php';

$req = new QueryStatReq();
$req ->setStartTime('20181019040013');
$req ->setEndTime('20181020040013');
$req ->setInterval(3600);

$rsp = "";
try {
    $rsp = SummaryService::QueryVodStat($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}