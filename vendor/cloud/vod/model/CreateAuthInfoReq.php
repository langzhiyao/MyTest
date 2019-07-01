<?php
include_once 'BaseRequest.php';
include_once dirname(dirname(__DIR__)).'/Exception/VodException.php';

class CreateAuthInfoReq extends BaseRequest {
    private $userIp;

    private $url;

    private $assetId;

    private $key;

    private $checkLevel = 5;

    private static $checkLevelList = array(1,2,3,5);

    private static $needToCheckIpCheckLevelList = array(1,2);

    /**
     * @return mixed
     */
    public function getUserIp()
    {
        return $this->userIp;
    }

    /**
     * @param mixed $userIp
     */
    public function setUserIp($userIp)
    {
        $this->userIp = $userIp;
    }

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

    /**
     * @return mixed
     */
    public function getAssetId()
    {
        return $this->assetId;
    }

    /**
     * @param mixed $assetId
     */
    public function setAssetId($assetId)
    {
        $this->assetId = $assetId;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return int
     */
    public function getCheckLevel(): int
    {
        return $this->checkLevel;
    }

    /**
     * @param int $checkLevel
     */
    public function setCheckLevel(int $checkLevel)
    {
        $this->checkLevel = $checkLevel;
    }

    /**
     * @throws VodException
     */
    public function validate()
    {
        if(empty($this->url) || empty($this->assetId) || empty($this->key))
        {
            throw new VodException('VOD.100011001', 'Request parameters is invalid');
        }

        if(!in_array($this->checkLevel,self::$checkLevelList))
        {
            throw new VodException('VOD.100011001', 'Request parameters is invalid checkLevel is illegal');
        }

        if (in_array($this->checkLevel,self::$needToCheckIpCheckLevelList) && empty($this->userIp)){
            throw new VodException('VOD.100011001', 'Request parameters is invalid userIp is illegal');
        }
    }
}