<?php

include_once dirname(dirname(__DIR__)).'/vod/model/CreateDomainAuthInfoReq.php';
include_once dirname(dirname(__DIR__)).'/vod/model/BaseResponse.php';
include_once dirname(dirname(__DIR__)).'/vod/model/CreateAuthInfoRsp.php';
include_once dirname(dirname(__DIR__)).'/util/DateUtil.php';
include_once dirname(dirname(__DIR__)).'/util/AesCipher.php';

class DomainUrlAuthService{
    public static function createAuthInfoUrl(CreateDomainAuthInfoReq $req){
        $rsp = new CreateAuthInfoRsp();
        $rsp->setHttpCode(BaseResponse::SUCCESS);

        try
        {
            $req->build();
            $req->validate();
            $path = $req->getPathFromOriginUrl();
            $data = substr($path,0,strripos($path,'/')+1).'$'.DateUtil::getUtcTime();
            $encryptInfo = AesCipher::encrypt($data, $req->getKey(), true);
            $rsp->setUrl($req->getOriginalUrl().'?auth_info='.urlencode($encryptInfo)."&vhost=".$req->getDomainName());
        }
        catch (Exception $e)
        {
            $rsp->setErrorCode('VOD.100021003');
            $rsp->setErrorMsg($e->getMessage());
            $rsp->setHttpCode(BaseResponse::FAIL);
        }
        return $rsp;
    }
}