<?php

include_once 'signer.php';
include_once 'AuthRequest.php';

class AuthAkSkRequest extends AuthRequest {

    private $requestUrl;

    /**
     * @return string
     */
    public function getRequestUrl()
    {
        return $this->requestUrl;
    }

    /**
     *  拼接url地址
     */
    public function setRequestUrl()
    {
        $this->requestUrl = parent::getScheme().'://'.parent::getHost().self::getUri();
    }

    public function sign(){
        $sign = new Signer();
        $sign->AppKey=AK;
        $sign->AppSecret=SK;
        $sign->Sign($this);
        return $sign;
    }

}