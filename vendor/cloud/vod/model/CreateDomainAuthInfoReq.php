<?php
include_once dirname(dirname(__DIR__)).'/Exception/VodException.php';
include_once dirname(dirname(__DIR__)).'/vod/model/BaseRequest.php';

class CreateDomainAuthInfoReq extends BaseRequest {

    private static $algorithms = array('algorithm_a','algorithm_b','algorithm_c','algorithm_d');

    private const  DEFAULT_DOMAIN = 'vod.cn-north-1.huaweicloud.com';

    /**
     * 未带加密信息的原始url，必填
     */
    private $originalUrl;

    /**
     * 加速域名，可选
     */
    private $domainName;

    /**
     * 加速域名上配置的密钥值，必填
     */
    private $key;

    /**
     * 使用的加密算法，假如不填，取默认算法D
     */
    private $algorithm;

    /**
     * @return mixed
     */
    public function getOriginalUrl()
    {
        return $this->originalUrl;
    }

    /**
     * @param mixed $originalUrl
     */
    public function setOriginalUrl($originalUrl)
    {
        $this->originalUrl = $originalUrl;
    }

    /**
     * @return mixed
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    /**
     * @param mixed $domainName
     */
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
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
     * @return mixed
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * @param mixed $algorithm
     */
    public function setAlgorithm($algorithm)
    {
        $this->algorithm = $algorithm;
    }


    /**
     * @return mixed|null
     * @throws VODException
     */
    public function getPathFromOriginUrl()
    {
        if(!empty($this->getOriginalUrl())){

            $path = parse_url($this->getOriginalUrl(),PHP_URL_PATH);

            if($path === false)
            {
                throw new VODException('VOD.100011001',' Get path from url fail');
            }
            return $path;
        }
        throw new VODException('VOD.100011001','Get path from url fail');
    }

    /**
     * @throws VodException
     */
    public function build()
    {
        if(empty($this->getAlgorithm()))
        {
            $this->setAlgorithm('algorithm_d');
        }

        if(! empty($this->getOriginalUrl()) && empty($this->getDomainName()))
        {
            try {
                $this->setDomainName(parse_url($this->getOriginalUrl(), PHP_URL_HOST));
                if (empty($this->getDomainName()) || $this->getDomainName() == self::DEFAULT_DOMAIN)
                {
                    throw new VodException('VOD.100011001', 'domainName is invalid');
                }
            } catch (Exception $e) {
                throw new VodException('VOD.100011001', "originalUrl is invalid" );
            }
        }
    }

    /**
     * @throws VodException
     */
    public function validate()
    {
        if (empty($this->key) || empty($this->originalUrl) || empty($this->domainName) )
        {
            throw new VodException('VOD.100011001', 'Request parameters is invalid');
        }

        if (!in_array($this->algorithm,self::$algorithms)){
            throw new VodException('VOD.100011001', 'algorithm is invalid');
        }
    }
}