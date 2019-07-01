<?php
/**
 * 融云 Server API PHP 客户端
 * create by kitName
 * create datetime : 2016-09-05 
 * 
 * v2.0.1
 */
namespace cloud;

use cloud\Core\SendRequest;
use cloud\Methods\User;
use cloud\Methods\Message;
use cloud\Methods\Wordfilter;
use cloud\Methods\Group;
use cloud\Methods\Chatroom;
use cloud\Methods\Push;
use cloud\Methods\SMS;
class RongCloud
{   
    /**
     * 参数初始化
     * @param $appKey
     * @param $appSecret
     * @param string $format
     */
    public function __construct($format = 'json') {
        $RongCloudConfig = config('RongCloud');
        $appKey = $RongCloudConfig['appKey'];
        $appSecret = $RongCloudConfig['appSecret'];
        $jsonPath = "jsonsource/";
        $this->SendRequest = new SendRequest($appKey, $appSecret, $format);
    }
    
    public function User() {
        $User = new User($this->SendRequest);
        return $User;
    }
    
    public function Message() {
        $Message = new Message($this->SendRequest);
        return $Message;
    }
    
    public function Wordfilter() {
        $Wordfilter = new Wordfilter($this->SendRequest);
        return $Wordfilter;
    }
    
    public function Group() {
        $Group = new Group($this->SendRequest);
        return $Group;
    }
    
    public function Chatroom() {
        $Chatroom = new Chatroom($this->SendRequest);
        return $Chatroom;
    }
    
    public function Push() {
        $Push = new Push($this->SendRequest);
        return $Push;
    }
    
    public function SMS() {
        $SMS = new SMS($this->SendRequest);
        return $SMS;
    }
    
}