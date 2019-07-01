<?php

namespace app\vlinker\Controller;
use think\Controller;

header('content-type:application:json;charset=utf8');
 
$origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';
$allow_origin = array(
    'http://117.78.26.155',
);
 
if(in_array($origin, $allow_origin)){
    header('Access-Control-Allow-Origin:'.$origin);
    header('Access-Control-Allow-Methods:POST');
    header('Access-Control-Allow-Headers:x-requested-with,content-type');
}

class BaseController extends Controller
{
    const POST = 'POST';
    protected $code = null;
    protected $return = null;
    protected $base = null;
    protected $key = null;
    protected $param = null;
    protected $method = null;
    protected $sdk = null;
    protected $vmid= null;

    public function _initialize()
    {

        parent::_initialize();
        
        

    }

}