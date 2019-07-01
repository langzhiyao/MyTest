<?php

include_once 'BaseResponse.php';

class CreateAuthInfoRsp extends BaseResponse {

    private $url;

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}