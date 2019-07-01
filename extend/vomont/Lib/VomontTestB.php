<?php
/**
 * 物盟
 */
namespace vomont\Lib;


class VomontTestB
{
    private $appKey;
    private $appSecret;

    const   HTTP_HOST = 'http://127.0.0.1';    //服务地址

    /**
     * 参数初始化
     * @param $appKey
     * @param $appSecret
     * @param string $format
     */
    public function __construct($appKey='',$appSecret=''){
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
    }

    public function testb(){
        return 'lib/test';
    }
}