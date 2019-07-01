<?php
include_once dirname(dirname(__DIR__)).'/generalRequest/class/CommonFunctions.php';
include_once dirname(dirname(__DIR__)).'/generalRequest/class/HttpResponse.php';
include_once dirname(dirname(__DIR__)).'/Auth/AuthAkSkRequest.php';
require_once dirname(dirname(__DIR__)).'/Init.php';

class UploadService{
    const HTTP_URI = '/asset/authority';
    /**
     * 获取初始化分段上传签名字符串
     * @param InitiateMultipartUploadReq $initiateMultipartUploadReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function InitiateMultipartUpload(InitiateMultipartUploadReq $initiateMultipartUploadReq)
    {
        $initiateMultipartUploadReq->validate();
        $param = $initiateMultipartUploadReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_GET);
        $authAkSkRequest->setUri(self::HTTP_URI,VERSION_1_1);
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
     * 获取分段上传签名字符串
     * @param MultipartUploadReq $multipartUploadReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function MultipartUpload(MultipartUploadReq $multipartUploadReq)
    {
        $multipartUploadReq->validate();
        $param = $multipartUploadReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_GET);
        $authAkSkRequest->setUri(self::HTTP_URI,VERSION_1_1);
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
     * 获取列举分段上传已上传段签名字符串
     * @param ListPartsReq $listPartsReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function ListParts(ListPartsReq $listPartsReq)
    {
        $listPartsReq->validate();
        $param = $listPartsReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_GET);
        $authAkSkRequest->setUri(self::HTTP_URI,VERSION_1_1);
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
     * 获取分段上传合并段签名字符串
     * @param CompleteMultipartUploadReq $completeMultipartUploadReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function CompleteMultipartUpload(CompleteMultipartUploadReq $completeMultipartUploadReq)
    {
        $completeMultipartUploadReq->validate();
        $param = $completeMultipartUploadReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_GET);
        $authAkSkRequest->setUri(self::HTTP_URI,VERSION_1_1);
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