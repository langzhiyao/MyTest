<?php
include_once dirname(dirname(__DIR__)).'/generalRequest/class/CommonFunctions.php';
include_once dirname(dirname(__DIR__)).'/generalRequest/class/HttpResponse.php';
include_once dirname(dirname(__DIR__)).'/Auth/AuthAkSkRequest.php';
require_once dirname(dirname(__DIR__)).'/Init.php';

class CategoryService{
    /**
     * 创建媒资分类
     * @param CreateCategoryReq $createCategoryReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function CreateAssetCategory(CreateCategoryReq $createCategoryReq){
        $param = $createCategoryReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_POST);
        $authAkSkRequest->setUri('/asset/category',VERSION_1_0);
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
     * 查询媒资分类
     * @param QueryCategoryReq $categoryReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function QueryAssetCategory(QueryCategoryReq $categoryReq){
        $categoryReq->validate();
        $param = $categoryReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_GET);
        $authAkSkRequest->setUri('/asset/category',VERSION_1_0);
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
     * 删除媒资分类
     * @param DeleteCategoryReq $deleteCategoryReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function DeleteAssetCategory(DeleteCategoryReq $deleteCategoryReq){
        $deleteCategoryReq->validate();
        $param = $deleteCategoryReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_DELETE);
        $authAkSkRequest->setUri('/asset/category',VERSION_1_0);
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
     * 修改媒资分类
     * @param EditCategoryReq $editCategoryReq
     * @return HttpResponse|null
     * @throws Exception
     */
    public static function UpdateAssetCategory(EditCategoryReq $editCategoryReq){
        $param = $editCategoryReq->getSerializedNamedParam();
        $authAkSkRequest = new AuthAkSkRequest();
        $authAkSkRequest->setMethod(HTTP_METHOD_PUT);
        $authAkSkRequest->setUri('/asset/category',VERSION_1_0);
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