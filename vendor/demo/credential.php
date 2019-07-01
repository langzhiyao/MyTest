<?php

/**
 * 获取临时授权请求示例
 */

require '../../vod/model/AuthObj.php';
require '../../vod/model/CredentialReq.php';
require '../../generalRequest/class/CommonFunctions.php';
require '../../generalRequest/class/HttpResponse.php';

//用户信息(华为云用户名，密码，domainName(默认与用户名一致)及过期时间(单位：秒))
const NAME='your name',PASSWORD='pwd',DOMAINNAME='your domainName',DURATION=3600;
//统一身份认证接口地址
const GET_TOKEN_URL='https://iam.myhuaweicloud.com/v3/auth/tokens';
const GET_SECURITY_TOKEN_URL='https://iam.myhuaweicloud.com/v3.0/OS-CREDENTIAL/securitytokens';

$getTokenReq = new AuthObj(NAME,PASSWORD,DOMAINNAME);
$getTokenUrl = GET_TOKEN_URL;
$getSecurityTokenUrl = GET_SECURITY_TOKEN_URL;
$tokenBody = json_encode($getTokenReq,JSON_UNESCAPED_UNICODE);
$tokenHeader = ['Content-Type'=>APPLICATION_JSON];
$headerData = [];
$token="";
try {
    $getTokenRsp = CommonFunctions::http($getTokenUrl, $tokenBody, HTTP_METHOD_POST, $tokenHeader);
    $headerData = explode(PHP_EOL,$getTokenRsp->getHeader());
    //从响应头中获取token
    foreach ($headerData as $key => $val){
        if ($val !== null && strstr($val,"X-Subject-Token")) $token = explode(": ",$val)[1];
    }
    //将获取的token装填到请求临时AKSK请求中
    $securityTokenHeader = ['Content-Type'=>APPLICATION_JSON,'X-Auth-Token'=>$token];
    $getSecurityTokenReq = new CredentialReq($token,DURATION);
    $securityTokenBody = json_encode($getSecurityTokenReq,JSON_UNESCAPED_UNICODE);
    $getSecurityTokenRsp = CommonFunctions::http($getSecurityTokenUrl, $securityTokenBody, HTTP_METHOD_POST, $securityTokenHeader);
    //输出结果
    echo $getSecurityTokenRsp->getBody();
} catch (VodException $e) {
    echo $e;
}
