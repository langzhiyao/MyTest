<?php
include_once dirname(dirname(__DIR__)).'/generalRequest/class/CommonFunctions.php';
include_once dirname(dirname(__DIR__)).'/generalRequest/class/HttpResponse.php';
include_once dirname(dirname(__DIR__)).'/Auth/AuthAkSkRequest.php';
include_once dirname(dirname(__DIR__)).'/vod/model/CreateAssetRsp.php';
include_once dirname(dirname(__DIR__)).'/vod/model/CreateAssetByFileRsp.php';
include_once dirname(dirname(__DIR__)).'/vod/service/ConcurrentUploadPart.php';
include_once dirname(dirname(__DIR__)).'/vod/model/ConfirmAssetUploadReq.php';
require_once dirname(dirname(__DIR__)).'/Init.php';

class AssetService{
    /**
     * 只创建不上传，可选择其他方式上传
     * @param CreateAssetByFileReq $createAssetReq
     * @return null
     * @throws VodException
     */
    public static function CreateAsset(CreateAssetByFileReq $createAssetReq){
        $createAssetReq->validate();
        $param = $createAssetReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_POST);
        $authAkSkRequest->setUri('/asset',VERSION_1_0);
        $authAkSkRequest->setHeaders(array('Content-Type'=>APPLICATION_JSON));
        $authAkSkRequest->setBody(json_encode($param,JSON_UNESCAPED_UNICODE));
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getBody(),
                $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

    /**
     * 创建并上传媒资带OBS分段上传回调
     * @param CreateAssetByFileReq $createAssetReq
     * @return null
     * @throws VodException
     */
    public static function CreateAssetByObs(CreateAssetByFileReq $createAssetReq){
        $createAssetReq->validate();
        $param = $createAssetReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_POST);
        $authAkSkRequest->setUri('/asset',VERSION_1_0);
        $authAkSkRequest->setHeaders(array('Content-Type'=>APPLICATION_JSON));
        $authAkSkRequest->setBody(json_encode($param,JSON_UNESCAPED_UNICODE));
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getBody(),
                $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            if (!empty($response->getBody())){
                $vodRsp = json_decode($response->getBody(),false);
                if (property_exists($vodRsp,VIDEO_UPLOAD_URL)){
                    $vodUploadUrl = $vodRsp->{VIDEO_UPLOAD_URL};
                    $coverUploadUrl = $vodRsp->{COVER_UPLOAD_URL};
                    $subtitleUploadUrl = $vodRsp->{SUBTITLE_UPLOAD_URL};
                    $bucket = $vodRsp->{'target'}->{'bucket'};
                    $objectKey = $vodRsp->{'target'}->{'object'};
                    if (!empty($vodUploadUrl)){
                        $videoFileUrl = $createAssetReq->getVideoFileUrl();
                        if (!empty($videoFileUrl)){
                            $resp = ConcurrentUploadPart::upload($bucket,$objectKey,$videoFileUrl);
                            if ($resp->getAll()['HttpStatusCode'] === 200){
                                $confirmReq = new ConfirmAssetUploadReq();
                                $confirmReq ->setAssetId($vodRsp->{"asset_id"});
                                $confirmReq ->setStatus("CREATED");
                                $confirmRsp = self::ConfirmAssetUpload($confirmReq);
                                return $confirmRsp->getBody();
                            }
                        }
                    }
                    if (!empty($coverUploadUrl)){
                        $coverFileUrl = $createAssetReq->getCoverFileUrl();
                        if (!empty($coverFileUrl)){
                            $resp = ConcurrentUploadPart::upload($bucket,$objectKey,$coverFileUrl);
                            if ($resp->getAll()['HttpStatusCode'] === 200){
                                $confirmReq = new ConfirmAssetUploadReq();
                                $confirmReq ->setAssetId($vodRsp->{"asset_id"});
                                $confirmReq ->setStatus("CREATED");
                                $confirmRsp = self::ConfirmAssetUpload($confirmReq);
                                return $confirmRsp->getBody();
                            }
                        }
                    }
                    if (!empty($subtitleUploadUrl)){
                        $subtitleFileUrl = $createAssetReq->getSubtitleFileUrl();
                        if (!empty($subtitleFileUrl)){
                            $resp = ConcurrentUploadPart::upload($bucket,$objectKey,$subtitleFileUrl);
                            if ($resp->getAll()['HttpStatusCode'] === 200){
                                $confirmReq = new ConfirmAssetUploadReq();
                                $confirmReq ->setAssetId($vodRsp->{"asset_id"});
                                $confirmReq ->setStatus("CREATED");
                                $confirmRsp = self::ConfirmAssetUpload($confirmReq);
                                return $confirmRsp->getBody();
                            }
                        }
                    }
                }else{
                    var_dump($vodRsp);
                }
            }
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return null;
    }
    /**
     * 查询媒资请求
     * @param QueryAssetMetaReq $queryAssetReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function QueryAsset(QueryAssetMetaReq $queryAssetReq){
        $param = $queryAssetReq->buildQueryArray();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_GET);
        $authAkSkRequest->setUri('/asset/info',VERSION_1_0);;
        $authAkSkRequest->setQuery($param);
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getQuery(), $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

    /**
     * 查询指定媒资详细信息请求
     * @param QueryAssetDetailReq $queryAssetDetailReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function QueryAssetDetail(QueryAssetDetailReq $queryAssetDetailReq){
        $queryAssetDetailReq->validate();
        $param = $queryAssetDetailReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_GET);
        $authAkSkRequest->setUri('/asset/details',VERSION_1_0);;
        $authAkSkRequest->setQuery($param);
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getQuery(), $authAkSkRequest->getMethod(),
                $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

    /**
     * 查询媒资列表
     * @param QueryAssetListReq $queryAssetListReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function QueryAssetList(QueryAssetListReq $queryAssetListReq){
        $param = $queryAssetListReq->buildQueryArray();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_GET);
        $authAkSkRequest->setUri('/asset/list',VERSION_1_0);
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
     * 查询媒资密钥(终端播放HLS加密视频时，向租户管理系统请求密钥，
     * 租户管理系统先查询其本地有没有已缓存的密钥，没有时则调用此接口向VOD查询)
     * @param QueryAssetCiphersReq $queryAssetCiphersReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function QueryAssetCiphers(QueryAssetCiphersReq $queryAssetCiphersReq){
        $queryAssetCiphersReq->validate();
        $param = $queryAssetCiphersReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_GET);
        $authAkSkRequest->setUri('/asset/ciphers',VERSION_1_0);
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
     * 删除媒资请求
     * @param DeleteAssetReq $deleteAssetReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function DeleteAsset(DeleteAssetReq $deleteAssetReq){
        $deleteAssetReq->validate();
        $param = $deleteAssetReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_DELETE);
        $authAkSkRequest->setUri('/asset',VERSION_1_0);
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
     * 更新媒资属性信息请求
     * @param UpdateAssetMetaReq $updateAssetMetaReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function UpdateAssetMeta(UpdateAssetMetaReq $updateAssetMetaReq){
        $param = $updateAssetMetaReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_PUT);
        $authAkSkRequest->setUri('/asset/info',VERSION_1_0);
        $authAkSkRequest->setHeaders(array('Content-Type'=>APPLICATION_JSON));
        $authAkSkRequest->setBody(json_encode($param,JSON_UNESCAPED_UNICODE));
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getBody(),
                $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

    /**
     * 确认媒资上传请求
     * @param ConfirmAssetUploadReq $confirmAssetUploadReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function ConfirmAssetUpload(ConfirmAssetUploadReq $confirmAssetUploadReq){
        $confirmAssetUploadReq->validate();
        $param = $confirmAssetUploadReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_POST);
        $authAkSkRequest->setUri('/asset/status/uploaded',VERSION_1_0);
        $authAkSkRequest->setHeaders(array('Content-Type'=>APPLICATION_JSON));
        $authAkSkRequest->setBody(json_encode($param,JSON_UNESCAPED_UNICODE));
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getBody(),
                $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

    /**
     * 更新媒资请求
     * @param UpdateAssetReq $updateAssetReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function UpdateAsset(UpdateAssetReq $updateAssetReq){
        $param = $updateAssetReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_PUT);
        $authAkSkRequest->setUri('/asset',VERSION_1_0);
        $authAkSkRequest->setHeaders(array('Content-Type'=>APPLICATION_JSON));
        $authAkSkRequest->setBody(json_encode($param,JSON_UNESCAPED_UNICODE));
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getBody(),
                $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

    /**
     * 媒资处理请求
     * @param AssetProcessReq $assetProcessReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function ProcessAsset(AssetProcessReq $assetProcessReq){
        $assetProcessReq->validate();
        $param = $assetProcessReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_POST);
        $authAkSkRequest->setUri('/asset/process',VERSION_1_0);
        $authAkSkRequest->setHeaders(array('Content-Type'=>APPLICATION_JSON));
        $authAkSkRequest->setBody(json_encode($param,JSON_UNESCAPED_UNICODE));
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();


        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getBody(),
                $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

    /**
     * OBS一键发布桶授权请求
     * @param BucketAuthorizedReq $bucketAuthorizedReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function OBSBucketAuthorized(BucketAuthorizedReq $bucketAuthorizedReq){
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_PUT);
        $authAkSkRequest->setUri('/asset/authority',VERSION_1_0);
        $authAkSkRequest->setHeaders(array('Content-Type'=>APPLICATION_JSON));
        $authAkSkRequest->setBody(json_encode($bucketAuthorizedReq,JSON_UNESCAPED_UNICODE));
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getBody(),
                $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

    /**
     * OBS一键发布请求(需先进行OBS桶授权操作)
     * @param PublishAssetFromObsReq $publishAssetFromObsReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function OBSCreateAsset(PublishAssetFromObsReq $publishAssetFromObsReq){
        $param = $publishAssetFromObsReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_POST);
        $authAkSkRequest->setUri('/asset/reproduction',VERSION_1_0);
        $authAkSkRequest->setHeaders(array('Content-Type'=>APPLICATION_JSON));
        $authAkSkRequest->setBody(json_encode($param,JSON_UNESCAPED_UNICODE));
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getBody(),
                $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

    /**
     * 媒资CDN预热请求(需加速域名)
     * @param PreheatingAssetReq $preheatingAssetReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function PreheatingAsset(PreheatingAssetReq $preheatingAssetReq){
        $preheatingAssetReq->validate();
        $param = $preheatingAssetReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_POST);
        $authAkSkRequest->setUri('/asset/preheating',VERSION_1_0);
        $authAkSkRequest->setHeaders(array('Content-Type'=>APPLICATION_JSON));
        $authAkSkRequest->setBody(json_encode($param,JSON_UNESCAPED_UNICODE));
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getBody(),
                $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

    /**
     * 媒资发布请求
     * @param PublishAssetReq $publishAssetReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function PublishAsset(PublishAssetReq $publishAssetReq){
        $param = $publishAssetReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_POST);
        $authAkSkRequest->setUri('/asset/status/publish',VERSION_1_0);
        $authAkSkRequest->setHeaders(array('Content-Type'=>APPLICATION_JSON));
        $authAkSkRequest->setBody(json_encode($param,JSON_UNESCAPED_UNICODE));
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getBody(),
                $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

    /**
     * 媒资取消发布请求
     * @param PublishAssetReq $publishAssetReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function UnPublishAsset(PublishAssetReq $publishAssetReq){
        $param = $publishAssetReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_POST);
        $authAkSkRequest->setUri('/asset/status/unpublish',VERSION_1_0);
        $authAkSkRequest->setHeaders(array('Content-Type'=>APPLICATION_JSON));
        $authAkSkRequest->setBody(json_encode($param,JSON_UNESCAPED_UNICODE));
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getBody(),
                $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

    /**
     * 媒资提取音频请求
     * @param ExtractAudioTaskReq $extractAudioTaskReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function AudioExtract(ExtractAudioTaskReq $extractAudioTaskReq){
        $param = $extractAudioTaskReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_POST);
        $authAkSkRequest->setUri('/asset/extract_audio',VERSION_1_0);
        $authAkSkRequest->setHeaders(array('Content-Type'=>APPLICATION_JSON));
        $authAkSkRequest->setBody(json_encode($param,JSON_UNESCAPED_UNICODE));
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getBody(),
                $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

    /**
     * 媒资审核
     * @param AssetReviewReq $assetReviewReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function AssetReview(AssetReviewReq $assetReviewReq){
        $assetReviewReq->validate();
        $param = $assetReviewReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_POST);
        $authAkSkRequest->setUri('/asset/review',VERSION_1_0);
        $authAkSkRequest->setHeaders(array('Content-Type'=>APPLICATION_JSON));
        $authAkSkRequest->setBody(json_encode($param,JSON_UNESCAPED_UNICODE));
        $authAkSkRequest->sign();
        $authAkSkRequest->setRequestUrl();

        try {
            $response = CommonFunctions::http($authAkSkRequest->getRequestUrl(), $authAkSkRequest->getBody(),
                $authAkSkRequest->getMethod(), $authAkSkRequest->getHeaders());
            return $response;
        } catch (VodException $e) {
            echo $e->getErrorMessage();
        }
        return null;
    }

}