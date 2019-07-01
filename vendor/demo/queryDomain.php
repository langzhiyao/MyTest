<?php
/**
 * 查询域名信息
 */

require '../../vod/service/SummaryService.php';
require '../../vod/model/QueryDomainReq.php';

$req = new QueryDomainReq();
$req->setDomain("www.test.com");
$rsp = "";
try {
    $rsp = SummaryService::QueryDomain($req);
    echo $rsp->getBody();
} catch (Exception $e) {
    echo $e;
}