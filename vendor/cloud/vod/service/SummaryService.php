<?php
include_once dirname(dirname(__DIR__)).'/generalRequest/class/CommonFunctions.php';
include_once dirname(dirname(__DIR__)).'/generalRequest/class/HttpResponse.php';
include_once dirname(dirname(__DIR__)).'/Auth/AuthAkSkRequest.php';
require_once dirname(dirname(__DIR__)).'/Init.php';

class SummaryService{

    /**
     * 查询cdn统计
     * @param QueryStatReq $queryStatReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function QueryCdnStat(QueryStatReq $queryStatReq)
    {
        $param = $queryStatReq->reqCdnArray();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_GET);
        $authAkSkRequest->setUri('/asset/cdn-statistics',VERSION_1_0);
        $authAkSkRequest->setQuery($param);
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getQuery(),
                $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

    /**
     * 查询源站统计信息
     * @param QueryStatReq $queryStatReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function QueryVodStat(QueryStatReq $queryStatReq)
    {
        $param = $queryStatReq->reqVodArray();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_GET);
        $authAkSkRequest->setUri('/asset/vod-statistics',VERSION_1_0);
        $authAkSkRequest->setQuery($param);
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getQuery(),
                $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }


    /**
     * 查询TopN视频信息
     * @param QueryTopNReq $queryTopNReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function QueryTopN(QueryTopNReq $queryTopNReq)
    {
        $param = $queryTopNReq->buildQueryArray();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_GET);
        $authAkSkRequest->setUri('/asset/top-statistics',VERSION_1_0);
        $authAkSkRequest->setQuery($param);
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getQuery(),
                $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

    /**
     * 查询域名信息
     * @param QueryDomainReq $queryDomainReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function QueryDomain(QueryDomainReq $queryDomainReq)
    {
        $param = $queryDomainReq->queryDomainArray();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_GET);
        $authAkSkRequest->setUri('/asset/domains',VERSION_1_0);
        $authAkSkRequest->setQuery($param);
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getQuery(),
                $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }
}