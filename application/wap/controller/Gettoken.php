<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/16
 * Time: 20:35
 */

namespace app\wap\controller;
vendor('qiniu.autoload');
use Qiniu\Auth as Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

class Gettoken
{
    /**
     * 获取七牛token
     */
    public function index()
    {
        //ak、sk信息
        $accessKey = 'V0Su976FmQMUBKKf9TLZIYao34G-l6RN_7zxhfFV';
        $secretKey = 'xvVkqpveV8myyeHYP4c_tghcPRUKUmvc2EqbOumG';
        $bucket='avatar';
        // 初始化签权对象
        $auth = new Auth($accessKey, $secretKey);
        //生成上传token
        $data['token'] = $auth->uploadToken($bucket);
        $data['bucket'] = 'avatar';
        $data['url']='http://avatar.xiangjianhai.com/';
        output_data($data);
    }
}
?>