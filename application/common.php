<?php

/* 引用全局定义 */
require __DIR__ . '/common_global.php';
/* 商品相关调用 */
require __DIR__ . '/common_goods.php';

/**
 * 随机生成手机号
 * @Author   Mr.Wang
 * @DateTime 2019-02-20
 * @param    integer    $num [生成手机号数量]
 * @return   [type]          [description]
 */
function randMobile($num = 1){
     //手机号2-3为数组
     $numberPlace = array(30,31,32,33,34,35,36,37,38,39,50,51,58,59,89);
     for ($i = 0; $i < $num; $i++){
      $mobile = 1;
      $mobile .= $numberPlace[rand(0,count($numberPlace)-1)];
      $mobile .= str_pad(rand(0,99999999),8,0,STR_PAD_LEFT);
      $result[] = $mobile;
     }
     if($num==1){
        $count = db('member')->where(['member_mobile'=>$mobile])->count();
        if ($count) {
            return randMobile($num = 1);
        }else{
            return $result[0];    
        }
     }else{
        return $result;   
     }
}

/**
 * 随机分配人数
 * @Author   Mr.Wang
 * @DateTime 2019-02-20
 * @param    [type]     $total [待划分的数字]
 * @param    [type]     $div   [分成的份数]
 * @param    [type]     $area  [各份数间允许的最大差值]
 */
function RandDistribution($total,$div,$area = 10){
     
    $average = round($total / $div);
    $sum = 0;
    $result = array_fill( 1, $div, 0 );
     
    for( $i = 1; $i < $div; $i++ ){
         //根据已产生的随机数情况，调整新随机数范围，以保证各份间差值在指定范围内
         if( $sum > 0 ){
          $max = 0;
          $min = 0 - round( $area / 2 );
         }elseif( $sum < 0 ){
          $min = 0;
          $max = round( $area / 2 );
         }else{
          $max = round( $area / 2 );
          $min = 0 - round( $area / 2 );
         }
         
         //产生各份的份额
         $random = rand( $min, $max );
         $sum += $random;
         $result[$i] = $average + $random;
    }
     
    //最后一份的份额由前面的结果决定，以保证各份的总和为指定值
    $result[$div] = $average - $sum;
    return $result;
}

/**
 * 随机生成中文名字 去除复姓
 * @Author   Mr.Wang
 * @DateTime 2019-02-19
 */
function CreatRandName(){
    $rand = new randChinaName();
    $rand->rndChinaName();
    $xing = $rand->getXing();
    $ming = $rand->getMing();
    return $rand->getName(2);
}

function GetRand(){
    return new randChinaName();
}

/**
 * 根据数组中某一个键值分组
 * @Author 老王
 * @创建时间   2018-12-24
 * @param  array                    $arr 要分组的数组
 * @param  string                   $key 分组的键名
 * @return array                        [description]
 */
function array_group_by($arr=array(), $key=''){
    $grouped = array();
    foreach ($arr as $value) {
        $grouped[$value[$key]][] = $value;
    }
    if (func_num_args() > 2) {
        $args = func_get_args();
        foreach ($grouped as $key => $value) {
            $parms = array_merge($value, array_slice($args, 2, func_num_args()));
            $grouped[$key] = call_user_func_array('array_group_by', $parms);
        }
    }
    return $grouped;
}


/**
 * 二维数组排序
 * @Author 老王
 * @创建时间   2018-12-24
 * @param  array                    $arr   [要排序的数组]
 * @param  string                   $v     [以数组中的某一个键值排序]
 * @param  string                   $order [正序 asc,倒序 desc]
 * @return [array]                          [返回排序后的数组]
 */
function vsort($arr=[],$v='',$order='asc'){
    if (empty($arr)) return $arr;
    if (empty($v)) return '请确认排序键值！';
    $list = array_column($arr,$v);
    switch ($order) {
        case 'asc':
            $order = SORT_ASC;
            break;
        case 'desc':
            $order = SORT_DESC;
            break;
        default:
            $order = SORT_ASC;
            break;
    }
    array_multisort($list,$order,$arr);
    return $arr;
}

/**
 * 生成随机数字字符串组合
 * @param  integer $len   [description]
 * @param  [type]  $chars [description]
 * @return [type]         [description]
 */
function getRandomString($len=6, $chars=null,$t = 'n'){
    if (is_null($chars)){
//        $chars = "abcdefghjkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ23456789";
        $chars = "1234567890";
    }
    mt_srand(10000000*(double)microtime());
    for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++){
        $str .= $chars[mt_rand(0, $lc)];  
    }
    if ($t=='u') return strtoupper($str);
    if ($t=='l') return strtolower($str);
    return $str;
}


/*
 * 更换数组的键值 为了应对 ->key
 */

function ds_changeArraykey($array, $key)
{
    $data = array();
    foreach ($array as $value) {
        $data[$value[$key]] = $value;
    }
    return $data;
}

/*
 * 编辑器内容
 */

function buildEditor($params = array())
{
    $name = isset($params['name']) ? $params['name'] : null;
    $theme = isset($params['theme']) ? $params['theme'] : 'normal';
    $content = isset($params['content']) ? $params['content'] : null;
    //http://fex.baidu.com/ueditor/#start-toolbar
    /* 指定使用哪种主题 */
    $themes = array(
        'normal' => "[   
           'fullscreen', 'source', '|', 'undo', 'redo', '|',   
           'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',   
           'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',   
           'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',   
           'directionalityltr', 'directionalityrtl', 'indent', '|',   
           'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',   
           'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',   
           'emotion',  'map', 'gmap',  'insertcode', 'template',  '|',   
           'horizontal', 'date', 'time', 'spechars', '|',   
           'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',   
           'searchreplace', 'help', 'drafts', 'charts'
       ]", 'simple' => " ['fullscreen', 'source', 'undo', 'redo', 'bold']",
    );
    switch ($theme) {
        case 'simple':
            $theme_config = $themes['simple'];
            break;
        case 'normal':
            $theme_config = $themes['normal'];
            break;
        default:
            $theme_config = $themes['normal'];
            break;
    }
    /* 配置界面语言 */
    switch (config('default_lang')) {
        case 'zh-cn':
            $lang = config('url_domain_root') . 'static/plugins/ueditor/lang/zh-cn/zh-cn.js';
            break;
        case 'en-us':
            $lang = config('url_domain_root') . 'static/plugins/ueditor/lang/en/en.js';
            break;
        default:
            $lang = config('url_domain_root') . 'static/plugins/ueditor/lang/zh-cn/zh-cn.js';
            break;
    }
    $include_js = '<script type="text/javascript" charset="utf-8" src="' . config('url_domain_root') . 'static/plugins/ueditor/ueditor.config.js"></script> <script type="text/javascript" charset="utf-8" src="' . config('url_domain_root') . 'static/plugins/ueditor/ueditor.all.min.js""> </script><script type="text/javascript" charset="utf-8" src="' . $lang . '"></script>';
    $str = <<<EOT
$include_js
<script type="text/javascript">
var ue = UE.getEditor('{$name}',{
    toolbars:[{$theme_config}],
        });
      ue.ready(function() {
       this.setContent('$content');	
})
</script>
EOT;
    return $str;
}
function buildEditors($params = array())
{
    $name = isset($params['name']) ? $params['name'] : null;
    $theme = isset($params['theme']) ? $params['theme'] : 'normal';
    $content = isset($params['content']) ? $params['content'] : null;
    //http://fex.baidu.com/ueditor/#start-toolbar
    /* 指定使用哪种主题 */
    $themes = array(
        'normal' => "[
           'fullscreen', 'source', '|', 'undo', 'redo', '|',
           'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
           'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
           'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
           'directionalityltr', 'directionalityrtl', 'indent', '|',
           'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
           'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
           'emotion',  'map', 'gmap',  'insertcode', 'template',  '|',
           'horizontal', 'date', 'time', 'spechars', '|',
           'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
           'searchreplace', 'help', 'drafts', 'charts'
       ]", 'simple' => " ['fullscreen', 'source', 'undo', 'redo', 'bold']",
    );
    switch ($theme) {
        case 'simple':
            $theme_config = $themes['simple'];
            break;
        case 'normal':
            $theme_config = $themes['normal'];
            break;
        default:
            $theme_config = $themes['normal'];
            break;
    }
    /* 配置界面语言 */
    switch (config('default_lang')) {
        case 'zh-cn':
            $lang = config('url_domain_root') . 'static/plugins/ueditor/lang/zh-cn/zh-cn.js';
            break;
        case 'en-us':
            $lang = config('url_domain_root') . 'static/plugins/ueditor/lang/en/en.js';
            break;
        default:
            $lang = config('url_domain_root') . 'static/plugins/ueditor/lang/zh-cn/zh-cn.js';
            break;
    }
    //$include_js = '<script type="text/javascript" charset="utf-8" src="' . config('url_domain_root') . 'static/plugins/ueditor/ueditor.config.js"></script> <script type="text/javascript" charset="utf-8" src="' . config('url_domain_root') . 'static/plugins/ueditor/ueditor.all.min.js""> </script><script type="text/javascript" charset="utf-8" src="' . $lang . '"></script>';
    $str = <<<EOT
$include_js
<script type="text/javascript">
var ue = UE.getEditor('{$name}',{
    toolbars:[{$theme_config}],
        });
      ue.ready(function() {
       this.setContent('$content');
})
</script>
EOT;
    return $str;
}

function ds_json_encode($code, $message, $result = array())
{
    echo json_encode(array(
                         'code' => $code, 'message' => $message, 'result' => $result
                     ));
    exit;
}

/**
 * 规范数据返回函数
 * @param unknown $code
 * @param unknown $msg
 * @param unknown $data
 * @return multitype:unknown
 */
function ds_callback($code, $msg = '', $data = array())
{
    return array('code' => $code, 'msg' => $msg, 'data' => $data);
}

/**
 * 格式化字节大小
 * @param  number $size 字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '')
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++)
        $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}


/*
 *   复制开始 
 */

/**
 * 产生验证码
 *
 * @param string $nchash 哈希数
 * @return string
 */
function makeSeccode($nchash)
{
    $seccode = random(6, 1);
    $seccodeunits = '';

    $s = sprintf('%04s', base_convert($seccode, 10, 23));
    $seccodeunits = 'ABCEFGHJKMPRTVXY2346789';
    if ($seccodeunits) {
        $seccode = '';
        for ($i = 0; $i < 4; $i++) {
            $unit = ord($s{$i});
            $seccode .= ($unit >= 0x30 && $unit <= 0x39) ? $seccodeunits[$unit - 0x30] : $seccodeunits[$unit - 0x57];
        }
    }
    cookie('seccode' . $nchash, encrypt(strtoupper($seccode) . "\t" . (time()) . "\t" . $nchash, MD5_KEY), 3600);
    return $seccode;
}

function getFlexigridArray($in_array, $fields_array, $data, $format_array)
{//多频道
    $out_array = $in_array;
    if ($out_array["operation"]) {
        $out_array["operation"] = "--";
    }


    if ($fields_array && is_array($fields_array)) {
        foreach ($fields_array as $key => $value) {
            $k = "";

            if (is_int($key)) {
                $k = $value;
            }
            else {
                $k = $key;
            }
            if (is_array($data) && array_key_exists($k, $data)) {
                $out_array[$k] = $data[$k];
                if ($format_array && in_array($k, $format_array)) {
                    $out_array[$k] = ncpriceformatb($data[$k]);
                }
            }
            else {
                $out_array[$k] = "--";
            }
        }
    }

    return $out_array;
}

function ncPriceFormatb($price)
{//多频道
    return number_format($price, 2, ".", "");
}


/**
 * 消息提示，主要适用于普通页面AJAX提交的情况
 *
 * @param string $message 消息内容
 * @param string $url 提示完后的URL去向
 * @param stting $alert_type 提示类型 error/succ/notice 分别为错误/成功/警示
 * @param string $extrajs 扩展JS
 * @param int $time 停留时间
 */
function showDialog($message = '', $url = '', $alert_type = 'error', $extrajs = '', $time = 2)
{
    $message = str_replace("'", "\\'", strip_tags($message));

    $paramjs = null;
    if ($url == 'reload') {
        $paramjs = 'window.location.reload()';
    }
    elseif ($url != '') {
        $paramjs = 'window.location.href =\'' . $url . '\'';
    }
    if ($paramjs) {
        $paramjs = 'function (){' . $paramjs . '}';
    }
    else {
        $paramjs = 'null';
    }
    $modes = array('error' => 'alert', 'succ' => 'succ', 'notice' => 'notice', 'js' => 'js');
    $cover = $alert_type == 'error' ? 1 : 0;
    $extra = 'showDialog(\'' . $message . '\', \'' . $modes[$alert_type] . '\', null, ' . ($paramjs ? $paramjs : 'null') . ', ' . $cover . ', null, null, null, null, ' . (is_numeric($time) ? $time : 'null') . ', null);';
    $extra = '<script type="text/javascript" reload="1">' . $extra . '</script>';
    if ($extrajs != '' && substr(trim($extrajs), 0, 7) != '<script') {
        $extrajs = '<script type="text/javascript" reload="1">' . $extrajs . '</script>';
    }
    $extra .= $extrajs;
    ob_end_clean();
    @header("Expires: -1");
    @header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
    @header("Pragma: no-cache");
    @header("Content-type: text/xml; charset=utf-8");

    $string = '<?xml version="1.0" encoding="utf-8"?>' . "\r\n";
    $string .= '<root><![CDATA[' . $message . $extra . ']]></root>';
    echo $string;
    exit;
}

/**
 * 取上一步来源地址
 *
 * @param
 * @return string 字符串类型的返回结果
 */
function getReferer()
{
    return empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];
}

/**
 * 加密函数
 *
 * @param string $txt 需要加密的字符串
 * @param string $key 密钥
 * @return string 返回加密结果
 */
function encrypt($txt, $key = '')
{
    if (empty($txt))
        return $txt;
    if (empty($key))
        $key = md5(MD5_KEY);
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
    $ikey = "-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
    $nh1 = rand(0, 64);
    $nh2 = rand(0, 64);
    $nh3 = rand(0, 64);
    $ch1 = $chars{$nh1};
    $ch2 = $chars{$nh2};
    $ch3 = $chars{$nh3};
    $nhnum = $nh1 + $nh2 + $nh3;
    $knum = 0;
    $i = 0;
    while (isset($key{$i}))
        $knum += ord($key{$i++});
    $mdKey = substr(md5(md5(md5($key . $ch1) . $ch2 . $ikey) . $ch3), $nhnum % 8, $knum % 8 + 16);
    $txt = base64_encode(time() . '_' . $txt);
    $txt = str_replace(array('+', '/', '='), array('-', '_', '.'), $txt);
    $tmp = '';
    $j = 0;
    $k = 0;
    $tlen = strlen($txt);
    $klen = strlen($mdKey);
    for ($i = 0; $i < $tlen; $i++) {
        $k = $k == $klen ? 0 : $k;
        $j = ($nhnum + strpos($chars, $txt{$i}) + ord($mdKey{$k++})) % 64;
        $tmp .= $chars{$j};
    }
    $tmplen = strlen($tmp);
    $tmp = substr_replace($tmp, $ch3, $nh2 % ++$tmplen, 0);
    $tmp = substr_replace($tmp, $ch2, $nh1 % ++$tmplen, 0);
    $tmp = substr_replace($tmp, $ch1, $knum % ++$tmplen, 0);
    return $tmp;
}

/**
 * 解密函数
 *
 * @param string $txt 需要解密的字符串
 * @param string $key 密匙
 * @return string 字符串类型的返回结果
 */
function decrypt($txt, $key = '', $ttl = 0)
{
    if (empty($txt))
        return $txt;
    if (empty($key))
        $key = md5(MD5_KEY);

    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
    $ikey = "-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
    $knum = 0;
    $i = 0;
    $tlen = @strlen($txt);
    while (isset($key{$i}))
        $knum += ord($key{$i++});
    $ch1 = @$txt{$knum % $tlen};
    $nh1 = strpos($chars, $ch1);
    $txt = @substr_replace($txt, '', $knum % $tlen--, 1);
    $ch2 = @$txt{$nh1 % $tlen};
    $nh2 = @strpos($chars, $ch2);
    $txt = @substr_replace($txt, '', $nh1 % $tlen--, 1);
    $ch3 = @$txt{$nh2 % $tlen};
    $nh3 = @strpos($chars, $ch3);
    $txt = @substr_replace($txt, '', $nh2 % $tlen--, 1);
    $nhnum = $nh1 + $nh2 + $nh3;
    $mdKey = substr(md5(md5(md5($key . $ch1) . $ch2 . $ikey) . $ch3), $nhnum % 8, $knum % 8 + 16);
    $tmp = '';
    $j = 0;
    $k = 0;
    $tlen = @strlen($txt);
    $klen = @strlen($mdKey);
    for ($i = 0; $i < $tlen; $i++) {
        $k = $k == $klen ? 0 : $k;
        $j = strpos($chars, $txt{$i}) - $nhnum - ord($mdKey{$k++});
        while ($j < 0)
            $j += 64;
        $tmp .= $chars{$j};
    }
    $tmp = str_replace(array('-', '_', '.'), array('+', '/', '='), $tmp);
    $tmp = trim(base64_decode($tmp));

    if (preg_match("/\d{10}_/s", substr($tmp, 0, 11))) {
        if ($ttl > 0 && (time() - substr($tmp, 0, 11) > $ttl)) {
            $tmp = null;
        }
        else {
            $tmp = substr($tmp, 11);
        }
    }
    return $tmp;
}

/**
 * 取得IP
 *
 *
 * @return string 字符串类型的返回结果
 */
function getIp()
{
    if (@$_SERVER['HTTP_CLIENT_IP'] && $_SERVER['HTTP_CLIENT_IP'] != 'unknown') {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (@$_SERVER['HTTP_X_FORWARDED_FOR'] && $_SERVER['HTTP_X_FORWARDED_FOR'] != 'unknown') {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return preg_match('/^\d[\d.]+\d$/', $ip) ? $ip : '';
}

/**
 * 读取目录列表
 * 不包括 . .. 文件 三部分
 *
 * @param string $path 路径
 * @return array 数组格式的返回结果
 */
function readDirList($path)
{
    if (is_dir($path)) {
        $handle = @opendir($path);
        $dir_list = array();
        if ($handle) {
            while (false !== ($dir = readdir($handle))) {
                if ($dir != '.' && $dir != '..' && is_dir($path . '/' . $dir)) {
                    $dir_list[] = $dir;
                }
            }
            return $dir_list;
        }
        else {
            return false;
        }
    }
    else {
        return false;
    }
}

/**
 * 转换特殊字符
 *
 * @param string $string 要转换的字符串
 * @return string 字符串类型的返回结果
 */
function replaceSpecialChar($string)
{
    $str = str_replace("\r\n", "", $string);
    $str = str_replace("\t", "    ", $string);
    $str = str_replace("\n", "", $string);
    return $string;
}


/**
 * 获取目录大小
 *
 * @param string $path 目录
 * @param int $size 目录大小
 * @return int 整型类型的返回结果
 */
function getDirSize($path, $size = 0)
{
    $dir = @dir($path);
    if (!empty($dir->path) && !empty($dir->handle)) {
        while ($filename = $dir->read()) {
            if ($filename != '.' && $filename != '..') {
                if (is_dir($path . '/' . $filename)) {
                    $size += getDirSize($path . '/' . $filename);
                }
                else {
                    $size += filesize($path . '/' . $filename);
                }
            }
        }
    }
    return $size ? $size : 0;
}

/**
 * 删除缓存目录下的文件或子目录文件
 *
 * @param string $dir 目录名或文件名
 * @return boolean
 */
function delCacheFile($dir)
{
    //防止删除cache以外的文件
    if (strpos($dir, '..') !== false)
        return false;
    $path = RUNTIME_PATH . '/' . $dir;
    if (is_dir($path)) {
        $file_list = array();
        readFileList($path, $file_list);
        if (!empty($file_list)) {
            foreach ($file_list as $v) {
                if (basename($v) != 'index.html')
                    @unlink($v);
            }
        }
    }
    else {
        if (basename($path) != 'index.html')
            @unlink($path);
    }
    return true;
}

/**
 * 获取文件列表(所有子目录文件)
 *
 * @param string $path 目录
 * @param array $file_list 存放所有子文件的数组
 * @param array $ignore_dir 需要忽略的目录或文件
 * @return array 数据格式的返回结果
 */
function readFileList($path, &$file_list, $ignore_dir = array())
{
    $path = rtrim($path, '/');
    if (is_dir($path)) {
        $handle = @opendir($path);
        if ($handle) {
            while (false !== ($dir = readdir($handle))) {
                if ($dir != '.' && $dir != '..') {
                    if (!in_array($dir, $ignore_dir)) {
                        if (is_file($path . '/' . $dir)) {
                            $file_list[] = $path . '/' . $dir;
                        }
                        elseif (is_dir($path . '/' . $dir)) {
                            readFileList($path . '/' . $dir, $file_list, $ignore_dir);
                        }
                    }
                }
            }
            @closedir($handle);
            //			return $file_list;
        }
        else {
            return false;
        }
    }
    else {
        return false;
    }
}

/**
 * 价格格式化
 *
 * @param int $price
 * @return string    $price_format
 */
function dsPriceFormat($price)
{
    $price_format = number_format($price, 2, '.', '');
    return $price_format;
}

/**
 * 价格格式化
 *
 * @param int $price
 * @return string    $price_format
 */
function dsPriceFormatForList($price)
{
    if ($price >= 10000) {
        return number_format(floor($price / 100) / 100, 2, '.', '') . lang('ten_thousand');
    }
    else {
        return lang('currency') . $price;
    }
}

/**
 * 二级域名解析
 * @return int 店铺id
 */
function subdomain()
{
    $store_id = 0;
    /**
     * 获得系统配置,二级域名功能是否开启
     */
    if (config('enabled_subdomain') == '1') {//开启了二级域名
        $line = @explode(SUBDOMAIN_SUFFIX, $_SERVER['HTTP_HOST']);
        $line = trim($line[0], '.');
        if (empty($line) || strtolower($line) == 'www')
            return 0;

        $model_store = Model('store');
        $store_info = $model_store->getStoreInfo(array('store_domain' => $line));
        //二级域名存在
        if ($store_info['store_id'] > 0) {
            $store_id = $store_info['store_id'];
            $_GET['store_id'] = $store_info['store_id'];
        }
    }
    return $store_id;
}

/**
 * 通知邮件/通知消息 内容转换函数
 *
 * @param string $message 内容模板
 * @param array $param 内容参数数组
 * @return string 通知内容
 */
function ncReplaceText($message, $param)
{
    if (!is_array($param))
        return false;
    foreach ($param as $k => $v) {
        $message = str_replace('{$' . $k . '}', $v, $message);
    }
    return $message;
}

/** @noinspection InconsistentLineSeparators */

/**
 * 字符串切割函数，一个字母算一个位置,一个字算2个位置
 *
 * @param string $string 待切割的字符串
 * @param int $length 切割长度
 * @param string $dot 尾缀
 */
function str_cut($string, $length, $dot = '')
{
    $string = str_replace(array(
                              '&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;',
                              '&middot;', '&hellip;'
                          ), array(' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
    $strlen = strlen($string);
    if ($strlen <= $length)
        return $string;
    $maxi = $length - strlen($dot);
    $strcut = '';

    $n = $tn = $noc = 0;
    while ($n < $strlen) {
        $t = ord($string[$n]);
        if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
            $tn = 1;
            $n++;
            $noc++;
        }
        elseif (194 <= $t && $t <= 223) {
            $tn = 2;
            $n += 2;
            $noc += 2;
        }
        elseif (224 <= $t && $t < 239) {
            $tn = 3;
            $n += 3;
            $noc += 2;
        }
        elseif (240 <= $t && $t <= 247) {
            $tn = 4;
            $n += 4;
            $noc += 2;
        }
        elseif (248 <= $t && $t <= 251) {
            $tn = 5;
            $n += 5;
            $noc += 2;
        }
        elseif ($t == 252 || $t == 253) {
            $tn = 6;
            $n += 6;
            $noc += 2;
        }
        else {
            $n++;
        }
        if ($noc >= $maxi)
            break;
    }
    if ($noc > $maxi)
        $n -= $tn;
    $strcut = substr($string, 0, $n);
    $strcut = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;'), $strcut);
    return $strcut . $dot;
}

/**
 * unicode转为utf8
 * @param string $str 待转的字符串
 * @return string
 */
function unicodeToUtf8($str, $order = "little")
{
    $utf8string = "";
    $n = strlen($str);
    for ($i = 0; $i < $n; $i++) {
        if ($order == "little") {
            $val = str_pad(dechex(ord($str[$i + 1])), 2, 0, STR_PAD_LEFT) . str_pad(dechex(ord($str[$i])), 2, 0, STR_PAD_LEFT);
        }
        else {
            $val = str_pad(dechex(ord($str[$i])), 2, 0, STR_PAD_LEFT) . str_pad(dechex(ord($str[$i + 1])), 2, 0, STR_PAD_LEFT);
        }
        $val = intval($val, 16); // 由于上次的.连接，导致$val变为字符串，这里得转回来。
        $i++; // 两个字节表示一个unicode字符。
        $c = "";
        if ($val < 0x7F) { // 0000-007F
            $c .= chr($val);
        }
        elseif ($val < 0x800) { // 0080-07F0
            $c .= chr(0xC0 | ($val / 64));
            $c .= chr(0x80 | ($val % 64));
        }
        else { // 0800-FFFF
            $c .= chr(0xE0 | (($val / 64) / 64));
            $c .= chr(0x80 | (($val / 64) % 64));
            $c .= chr(0x80 | ($val % 64));
        }
        $utf8string .= $c;
    }
    /* 去除bom标记 才能使内置的iconv函数正确转换 */
    if (ord(substr($utf8string, 0, 1)) == 0xEF && ord(substr($utf8string, 1, 2)) == 0xBB && ord(substr($utf8string, 2, 1)) == 0xBF) {
        $utf8string = substr($utf8string, 3);
    }
    return $utf8string;
}

/*
 * 重写$_SERVER['REQUREST_URI']
 */

function request_uri()
{
    if (isset($_SERVER['REQUEST_URI'])) {
        $uri = $_SERVER['REQUEST_URI'];
    }
    else {
        if (isset($_SERVER['argv'])) {
            $uri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['argv'][0];
        }
        else {
            $uri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
        }
    }
    return $uri;
}

/*
 * 自定义memory_get_usage()
 *
 * @return 内存使用额度，如果该方法无效，返回0
 */
if (!function_exists('memory_get_usage')) {

    function memory_get_usage()
    {//目前程序不兼容5以下的版本
        return 0;
    }

}

// 记录和统计时间（微秒）
function addUpTime($start, $end = '', $dec = 3)
{
    static $_info = array();
    if (!empty($end)) { // 统计时间
        if (!isset($_info[$end])) {
            $_info[$end] = microtime(TRUE);
        }
        return number_format(($_info[$end] - $_info[$start]), $dec);
    }
    else { // 记录时间
        $_info[$start] = microtime(TRUE);
    }
}

/**
 * 取得商品默认大小图片
 *
 * @param string $key 图片大小 small tiny
 * @return string
 */
function defaultGoodsImage($key)
{
    // $file = str_ireplace('.', '_' . $key . '.', config('default_goods_image'));
    $file = 'default_goods_image.png';
    return ATTACH_COMMON . '/' . $file;
}


function getChatGroupImg(){
    $file = 'default_chatgroup_image.png';
    return UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/'  . $file;
}

/**
 * 取得用户头像图片
 *
 * @param string $member_avatar
 * @return string
 */
function getMemberAvatar($member_avatar)
{
    if (empty($member_avatar)) {
        return UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . 'default_user_portrait.png';
    }
    else {

        if (file_exists(BASE_UPLOAD_PATH . '/' . ATTACH_AVATAR . '/' . $member_avatar)) {
            return UPLOAD_SITE_URL . '/' . ATTACH_AVATAR . '/' . $member_avatar;
        }
        else {
            return UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . 'default_user_portrait.png';
        }
    }
}

function getIconImage($icon,$key)
{   
    $key = explode('_',$key);
    $multiple = 'x'.$key[1];    
    if (empty($icon)) {
        return UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . 'default_user_portrait.png';
    }else {
        if (!file_exists(UPLOAD_SITE_URL . '/' . ATTACH_NAVICON . '/' .$multiple.'/'. $icon)) {
            return UPLOAD_SITE_URL . '/' . ATTACH_NAVICON . '/' .$multiple.'/'. $icon;
        }else {
            return UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . 'default_user_portrait.png';
        }
    }
}

/**
 * 成员头像
 * @param string $member_id
 * @return string
 */
function getMemberAvatarForID($id)
{
    if (file_exists(BASE_UPLOAD_PATH . '/' . ATTACH_AVATAR . '/avatar_' . $id . '.jpg')) {
        return UPLOAD_SITE_URL . '/' . ATTACH_AVATAR . '/avatar_' . $id . '.jpg';
    }
    else {
        if (config('default_user_portrait')) {
            return UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . config('default_user_portrait');
        }
        else {
            return UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . 'default_user_portrait.png';
        }

    }
}

/**
 * 成员头像 v3-b12-1
 * @param string $member_id
 * @return string
 */
function getMemberAvatarHttps($member_avatar)
{
    if (empty($member_avatar)) {
        return UPLOAD_SITE_URL. '/' . ATTACH_COMMON . '/' . 'default_user_portrait.png';
    }
    else if (file_exists(BASE_UPLOAD_PATH . '/' . ATTACH_AVATAR . '/' . $member_avatar)) {
        return UPLOAD_SITE_URL. '/' . ATTACH_AVATAR . '/' . $member_avatar;
    }
    else {
        return UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . 'default_user_portrait.png';
    }
}



/**
 * 取得店铺标志
 *
 * @param string $img 图片名
 * @param string $type 查询类型 store_logo/store_avatar
 * @return string
 */
function getStoreLogo($img, $type = 'store_avatar')
{
    if ($type == 'store_avatar') {
        if (empty($img)) {
            return UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . config('default_store_avatar');
        }
        else {
            $linfo = explode('_', $img);
            if (is_file(BASE_UPLOAD_PATH . '/' . ATTACH_STORE . '/' . $linfo['0'] . '/' . $img))
                return UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $linfo['0'] . '/' . $img;
            return UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . config('default_store_avatar');
        }
    }
    elseif ($type == 'store_logo') {
        if (empty($img)) {
            return UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . config('default_store_logo');
        }
        else {
            $linfo = explode('_', $img);
            return UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $linfo['0'] . '/' . $img;
        }
    }
}

/**
 * 获取文章URL
 */
function getCMSArticleUrl($article_id)
{
    if (URL_MODEL) {
        // 开启伪静态
        return CMS_SITE_URL . '/' . 'article-' . $article_id . '.html';
    }
    else {
        return CMS_SITE_URL . '/' . 'index.php?act=article&op=article_detail&article_id=' . $article_id;
    }
}

/**
 * 获取画报URL
 */
function getCMSPictureUrl($picture_id)
{
    if (URL_MODEL) {
        // 开启伪静态
        return CMS_SITE_URL . '/' . 'picture-' . $picture_id . '.html';
    }
    else {
        return CMS_SITE_URL . '/' . 'index.php?act=picture&op=picture_detail&picture_id=' . $picture_id;
    }
}

/**
 * 获取文章图片URL
 */
function getCMSArticleImageUrl($image_path, $image_name, $type = 'list')
{
    if (empty($image_name)) {
        return UPLOAD_SITE_URL . '/' . ATTACH_CMS . '/' . 'no_cover.png';
    }
    else {
        $image_array = unserialize($image_name);
        if (!empty($image_array['name'])) {
            $image_name = $image_array['name'];
        }
        if (!empty($image_array['path'])) {
            $image_path = $image_array['path'];
        }
        $ext_array = array('list', 'max');
        $file_path = ATTACH_CMS . '/' . 'article' . '/' . $image_path . '/' . str_ireplace('.', '_' . $type . '.', $image_name);
        if (file_exists(BASE_PATH . '/' . $file_name)) {
            $image_name = UPLOAD_SITE_URL . '/' . $file_path;
        }
        else {
            $image_name = UPLOAD_SITE_URL . '/' . ATTACH_CMS . '/' . 'no_cover.png';
        }
        return $image_name;
    }
}

/**
 * 获取文章图片URL
 */
function getCMSImageName($image_name_string)
{
    $image_array = unserialize($image_name_string);
    if (!empty($image_array['name'])) {
        $image_name = $image_array['name'];
    }
    else {
        $image_name = $image_name_string;
    }
    return $image_name;
}

/**
 * 获取CMS专题图片
 */
function getCMSSpecialImageUrl($image_name = '')
{
    return UPLOAD_SITE_URL. '/' . ATTACH_CMS . '/' . 'special' . '/' . $image_name;
}

/**
 * 获取CMS专题路径
 */
function getCMSSpecialImagePath($image_name = '')
{
    return BASE_UPLOAD_PATH . '/' . ATTACH_CMS . '/' . 'special' . '/' . $image_name;
}

/**
 * 获取CMS首页图片
 */
function getCMSIndexImageUrl($image_name = '')
{
    return UPLOAD_SITE_URL . '/' . ATTACH_CMS . '/' . 'index' . '/' . $image_name;
}

/**
 * 获取CMS首页图片路径
 */
function getCMSIndexImagePath($image_name = '')
{
    return BASE_UPLOAD_PATH . '/' . ATTACH_CMS . '/' . 'index' . '/' . $image_name;
}

/**
 * 获取CMS专题Url
 */
function getCMSSpecialUrl($special_id)
{
    return CMS_SITE_URL . '/' . 'index.php?act=special&op=special_detail&special_id=' . $special_id;
}

/**
 * 获取商城专题Url
 */
function getShopSpecialUrl($special_id)
{
    return SHOP_SITE_URL . '/' . 'index.php?act=special&op=special_detail&special_id=' . $special_id;
}

/**
 * 获取CMS专题静态文件
 */
function getCMSSpecialHtml($special_id)
{
    $special_file = BASE_UPLOAD_PATH . '/' . ATTACH_CMS . '/' . 'special_html' . '/' . md5('special' . intval($special_id)) . '.html';
    if (is_file($special_file)) {
        return $special_file;
    }
    else {
        return false;
    }
}

/**
 * 获取微商城个人秀图片地址
 */
function getMicroshopPersonalImageUrl($personal_info, $type = '')
{
    $ext_array = array('list', 'tiny');
    $personal_image_array = array();
    $personal_image_list = explode(',', $personal_info['commend_image']);
    if (!empty($personal_image_list)) {
        foreach ($personal_image_list as $value) {
            if (!empty($type) && in_array($type, $ext_array)) {
                $file_name = str_replace('.', '_' . $type . '.', $value);
            }
            else {
                $file_name = $value;
            }
            $file_path = $personal_info['commend_member_id'] . '/' . $file_name;
            if (is_file(BASE_UPLOAD_PATH . '/' . ATTACH_MICROSHOP . '/' . $file_path)) {
                $personal_image_array[] = UPLOAD_SITE_URL . '/' . ATTACH_MICROSHOP . '/' . $file_path;
            }
            else {
                $personal_image_array[] = getMicroshopDefaultImage();
            }
        }
    }
    else {
        $personal_image_array[] = getMicroshopDefaultImage();
    }
    return $personal_image_array;
}

function getMicroshopDefaultImage()
{
    return UPLOAD_SITE_URL . '/' . defaultGoodsImage('240');
}

/**
 * 获取开店申请图片
 */
function getStoreJoininImageUrl($image_name = '')
{
    return UPLOAD_SITE_URL . '/' . ATTACH_STORE_JOININ . '/' . $image_name;
}

/**
 * 获取开店装修图片地址
 */
function getStoreDecorationImageUrl($image_name = '', $store_id = null)
{
    if (empty($store_id)) {
        $image_name_array = explode('_', $image_name);
        $store_id = $image_name_array[0];
    }

    $image_path = '/' . ATTACH_STORE_DECORATION . '/' . $store_id . '/' . $image_name;
    if (is_file(BASE_UPLOAD_PATH . $image_path)) {
        return UPLOAD_SITE_URL . $image_path;
    }
    else {
        return '';
    }
}

/**
 * 获取运单图片地址
 */
function getWaybillImageUrl($image_name = '')
{
    $image_path = '/' . ATTACH_WAYBILL . '/' . $image_name;
    if (is_file(BASE_UPLOAD_PATH . $image_path)) {
        return UPLOAD_SITE_URL . $image_path;
    }
    else {
        return UPLOAD_SITE_URL . '/' . defaultGoodsImage('240');
    }
}

/**
 * 取得随机数
 *
 * @param int $length 生成随机数的长度
 * @param int $numeric 是否只产生数字随机数 1是0否
 * @return string
 */
function random($length, $numeric = 0)
{
    $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    $hash = '';
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed{mt_rand(0, $max)};
    }
    return $hash;
}


/**
 * sns表情标示符替换为html
 */
function parsesmiles($message)
{
    if (!empty($smilies_array) && is_array($smilies_array)) {
        $imagesurl = ROOT_PATH . 'static' . '/' . 'plugins' . '/' . 'js' . '/' . 'smilies' . '/' . 'images' . '/';
        $replace_arr = array();
        foreach ($smilies_array['replacearray'] AS $key => $smiley) {
            $replace_arr[$key] = '<img src="' . $imagesurl . $smiley['imagename'] . '" title="' . $smiley['desc'] . '" border="0" alt="' . $imagesurl . $smiley['desc'] . '" />';
        }

        $message = preg_replace($smilies_array['searcharray'], $replace_arr, $message);
    }

    return $message;
}

/**
 * 输出validate的验证信息
 *
 * @param array /string $error
 */
function showValidateError($error)
{
    if (!empty($_GET['inajax'])) {
        foreach (explode('<br/>', $error) as $v) {
            if (trim($v != '')) {
                showDialog($v, '', 'error', '', 3);
            }
        }
    }
    else {
        showDialog($error, '', 'error', '', 3);
    }
}

/**
 * 延时加载分页功能，判断是否有更多连接和limitstart值和经过验证修改的$delay_eachnum值
 * @param int $delay_eachnum 延时分页每页显示的条数
 * @param int $delay_page 延时分页当前页数
 * @param int $count 总记录数
 * @param bool $ispage 是否在分页模式中实现延时分页(前台显示的两种不同效果)
 * @param int $page_nowpage 分页当前页数
 * @param int $page_eachnum 分页每页显示条数
 * @param int $page_limitstart 分页初始limit值
 * @return array array('hasmore'=>'是否显示更多连接','limitstart'=>'加载的limit开始值','delay_eachnum'=>'经过验证修改的$delay_eachnum值');
 */
function lazypage($delay_eachnum, $delay_page, $count, $ispage = false, $page_nowpage = 1, $page_eachnum = 1, $page_limitstart = 1)
{
    //是否有多余
    $hasmore = true;
    $limitstart = 0;
    if ($ispage == true) {
        if ($delay_eachnum < $page_eachnum) {//当延时加载每页条数小于分页的每页条数时候实现延时加载，否则按照普通分页程序流程处理
            $page_totlepage = ceil($count / $page_eachnum);
            //计算limit的开始值
            $limitstart = $page_limitstart + ($delay_page - 1) * $delay_eachnum;
            if ($page_totlepage > $page_nowpage) {//当前不为最后一页
                if ($delay_page >= $page_eachnum / $delay_eachnum) {
                    $hasmore = false;
                }
                //判断如果分页的每页条数与延时加载每页的条数不能整除的处理
                if ($hasmore == false && $page_eachnum % $delay_eachnum > 0) {
                    $delay_eachnum = $page_eachnum % $delay_eachnum;
                }
            }
            else {//当前最后一页
                $showcount = ($page_totlepage - 1) * $page_eachnum + $delay_eachnum * $delay_page; //已经显示的记录总数
                if ($count <= $showcount) {
                    $hasmore = false;
                }
            }
        }
        else {
            $hasmore = false;
        }
    }
    else {
        if ($count <= $delay_page * $delay_eachnum) {
            $hasmore = false;
        }
        //计算limit的开始值
        $limitstart = ($delay_page - 1) * $delay_eachnum;
    }

    return array('hasmore' => $hasmore, 'limitstart' => $limitstart, 'delay_eachnum' => $delay_eachnum);
}

/**
 * 文件数据读取和保存 字符串、数组
 *
 * @param string $name 文件名称（不含扩展名）
 * @param mixed $value 待写入文件的内容
 * @param string $path 写入cache的目录
 * @param string $ext 文件扩展名
 * @return mixed
 */
function F($name, $value = null, $path = 'cache', $ext = '.php')
{
    if (strtolower(substr($path, 0, 5)) == 'cache') {
        $path = 'data/' . $path;
    }
    static $_cache = array();
    if (isset($_cache[$name . $path]))
        return $_cache[$name . $path];
    $filename = BASE_ROOT_PATH . '/' . $path . '/' . $name . $ext;
    if (!is_null($value)) {
        $dir = dirname($filename);
        if (!is_dir($dir))
            mkdir($dir);
        return write_file($filename, $value);
    }

    if (is_file($filename)) {
        $_cache[$name . $path] = $value = include $filename;
    }
    else {
        $value = false;
    }
    return $value;
}

/**
 * 内容写入文件
 *
 * @param string $filepath 待写入内容的文件路径
 * @param string /array $data 待写入的内容
 * @param  string $mode 写入模式，如果是追加，可传入“append”
 * @return bool
 */
function write_file($filepath, $data, $mode = null,$type=false)
{
    if (!is_array($data) && !is_scalar($data)) {
        return false;
    }
    if (!$type) {
        $data = var_export($data, true);
        $data = "<?php defined('TATX') or exit('Access Invalid!'); return " . $data . ";";    
    }
    $mode = $mode == 'append' ? FILE_APPEND : null;
    if (false === file_put_contents($filepath, ($data), $mode)) {
        return false;
    }
    else {
        return true;
    }
}

/**
 * 写入支付文件
 * @param  [string] $data     内容
 * @param  [string] $payment  [写入支付文件名称]
 * @param  [string] $filename [写入文件名称，为空则以时间命名]
 * @return [bool]           
 */
function write_payment($data, $payment,$filename=''){
    $path = './uploads/payment/'.$payment.'/'.date('Y-m-d').'_'.$filename.'.log';;
    $dir = dirname($path);
    if (!is_dir($dir))mkdir($dir);
    $result = write_file($path, $data.PHP_EOL, $mode = 'append',$type=true);
    return $result;
}
/**
 * 循环创建目录
 *
 * @param string $dir 待创建的目录
 * @param  $mode 权限
 * @return boolean
 */
function mk_dir($dir, $mode = '0777')
{
    if (is_dir($dir) || @mkdir($dir, $mode))
        return true;
    if (!mk_dir(dirname($dir), $mode))
        return false;
    return @mkdir($dir, $mode);
}


/**
 * 抛出异常
 *
 * @param string $error 异常信息
 */
function throw_exception($error)
{
    if (!defined('IGNORE_EXCEPTION')) {
        showMessage($error, '', 'exception');
    }
    else {
        exit();
    }
}

/**
 * 输出错误信息
 *
 * @param string $error 错误信息
 */
/* function halt($error){
  showMessage($error,'','exception');
  } */

/**
 * 去除代码中的空白和注释
 *
 * @param string $content 待压缩的内容
 * @return string
 */
function compress_code($content)
{
    $stripStr = '';
    //分析php源码
    $tokens = token_get_all($content);
    $last_space = false;
    for ($i = 0, $j = count($tokens); $i < $j; $i++) {
        if (is_string($tokens[$i])) {
            $last_space = false;
            $stripStr .= $tokens[$i];
        }
        else {
            switch ($tokens[$i][0]) {
                case T_COMMENT: //过滤各种PHP注释
                case T_DOC_COMMENT:
                    break;
                case T_WHITESPACE: //过滤空格
                    if (!$last_space) {
                        $stripStr .= ' ';
                        $last_space = true;
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr .= $tokens[$i][1];
            }
        }
    }
    return $stripStr;
}

/**
 * 取得对象实例
 *
 * @param object $class
 * @param string $method
 * @param array $args
 * @return object
 */
function get_obj_instance($class, $method = '', $args = array())
{
    static $_cache = array();
    $key = $class . $method . (empty($args) ? null : md5(serialize($args)));
    if (isset($_cache[$key])) {
        return $_cache[$key];
    }
    else {
        if (class_exists($class)) {
            $obj = new $class;
            if (method_exists($obj, $method)) {
                if (empty($args)) {
                    $_cache[$key] = $obj->$method();
                }
                else {
                    $_cache[$key] = call_user_func_array(array(&$obj, $method), $args);
                }
            }
            else {
                $_cache[$key] = $obj;
            }
            return $_cache[$key];
        }
        else {
            throw_exception('Class ' . $class . ' isn\'t exists!');
        }
    }
}

/**
 * 返回以原数组某个值为下标的新数据
 *
 * @param array $array
 * @param string $key
 * @param int $type 1一维数组2二维数组
 * @return array
 */
function array_under_reset($array, $key, $type = 1)
{
    if (is_array($array)) {
        $tmp = array();
        foreach ($array as $v) {
            if ($type === 1) {
                $tmp[$v[$key]] = $v;
            }
            elseif ($type === 2) {
                $tmp[$v[$key]][] = $v;
            }
        }
        return $tmp;
    }
    else {
        return $array;
    }
}

/**
 * KV缓存 读
 *
 * @param string $key 缓存名称
 * @param boolean $callback 缓存读取失败时是否使用回调 true代表使用cache.model中预定义的缓存项 默认不使用回调
 * @param callable $callback 传递非boolean值时 通过is_callable进行判断 失败抛出异常 成功则将$key作为参数进行回调
 * @return mixed
 */
function rkcache($key, $callback = false)
{
    $value = cache($key);
    if (empty($value) && $callback !== false) {
        if ($callback === true) {
            $callback = array(model('cache'), 'call');
        }

        if (!is_callable($callback)) {
            exception('Invalid rkcache callback!');
        }
        $value = call_user_func($callback, $key);
        wkcache($key, $value);
    }
    return $value;
}

/**
 * KV缓存 写
 *
 * @param string $key 缓存名称
 * @param mixed $value 缓存数据 若设为否 则下次读取该缓存时会触发回调（如果有）
 * @param int $expire 缓存时间 单位秒 null代表不过期
 * @return boolean
 */
function wkcache($key, $value, $expire = 7200)
{
    return cache($key, $value, $expire);
}

/**
 * KV缓存 删
 *
 * @param string $key 缓存名称
 * @return boolean
 */
function dkcache($key)
{
    return cache($key, NULL);
}

/**
 * 读取缓存信息
 *
 * @param string $key 要取得缓存键
 * @param string $prefix 键值前缀
 * @param string $fields 所需要的字段
 * @return array/bool
 */
function rcache($key = null, $prefix = '', $fields = '*')
{
    if ($key === null || !config('cache_open'))
        return array();
    if (!empty($prefix)) {
        $name = $prefix . $key;
    }
    else {
        $name = $key;
    }
    $cache_info = cache($name);
    //如果name值不存在，则默认返回 false。
    return $cache_info;
}

/**
 * 写入缓存
 *
 * @param string $key 缓存键值
 * @param array $data 缓存数据
 * @param string $prefix 键值前缀
 * @param int $period 缓存周期  单位分，0为永久缓存
 * @return bool 返回值
 */
function wcache($key = null, $data = array(), $prefix, $period = 0)
{
    if ($key === null || !config('cache_open') || !is_array($data))
        return;

    if (!empty($prefix)) {
        $name = $prefix . $key;
    }
    else {
        $name = $key;
    }
    $cache_info = cache($name, $data, 3600);
    //如果设置成功返回true，否则返回false。
    return $cache_info;
}

/**
 * 删除缓存
 * @param string $key 缓存键值
 * @param string $prefix 键值前缀
 * @return boolean
 */
function dcache($key = null, $prefix = '')
{
    if ($key === null || !config('cache_open'))
        return true;
    if (!empty($prefix)) {
        $name = $prefix . $key;
    }
    else {
        $name = $key;
    }
    return cache($name, NULL);
}

/**
 * 调用推荐位
 *
 * @param int $rec_id 推荐位ID
 * @return string 推荐位内容
 */
function rec($rec_id = null)
{
    import('recposition', EXTEND_PATH);
    return rec_position($rec_id);
}

/**
 * 加载完成业务方法的文件
 *
 * @param string $filename
 * @param string $file_ext
 */
function loadfunc($filename, $file_ext = '.php')
{
    if (preg_match('/^[\w\d\/_.]+$/i', $filename . $file_ext)) {
        $file = realpath(BASE_PATH . '/framework/function/' . $filename . $file_ext);
    }
    else {
        $file = false;
    }
    if (!$file) {
        exit($filename . $file_ext . ' isn\'t exists!');
    }
    else {
        require_once($file);
    }
}

/**
 * 实例化类
 *
 * @param string $model_name 模型名称
 * @return obj 对象形式的返回结果
 */
function nc_class($classname = null)
{
    static $_cache = array();
    if (!is_null($classname) && isset($_cache[$classname]))
        return $_cache[$classname];
    $file_name = BASE_PATH . '/framework/libraries/' . $classname . '.class.php';
    $newname = $classname . 'Class';
    if (file_exists($file_name)) {
        require_once($file_name);
        if (class_exists($newname)) {
            return $_cache[$classname] = new $newname();
        }
    }
    throw_exception('Class Error:  Class ' . $classname . ' is not exists!');
}

/**
 * 加载广告
 *
 * @param  $ap_id 广告位ID
 * @param $type 广告返回类型 html,js
 */
function loadadv($ap_id = null, $type = 'html')
{
    if (!is_numeric($ap_id))
        return false;
    return advshow($ap_id, $type);
}

/**
 * 取广告内容
 *
 * @param unknown_type $ap_id
 * @param unknown_type $type html,js,array
 */
function advshow($ap_id, $type = 'js')
{
    if ($ap_id < 1)
        return;
    $time = time();
    $ap_info = Model('adv')->getApById($ap_id);
    if (!$ap_info)
        return;
    $list = $ap_info['adv_list'];
    unset($ap_info['adv_list']);
    if (!$list)
        return;
    extract($ap_info);
    if ($is_use != '1') {
        return;
    }
    $adv_list = array();
    $adv_info = array();//异步调用的数组格式
    foreach ((array)$list as $k => $v) {
        if ($v['adv_start_date'] < $time && $v['adv_end_date'] > $time && $v['is_allow'] == '1') {
            $adv_list[] = $v;
        }
    }

    if (empty($adv_list)) {
        if ($ap_class == '1') {//文字广告
            $content .= "<a href=''>";
            $content .= $default_content;
            $content .= "</a>";
        }
        else {
            $width = $ap_width;
            $height = $ap_height;
            $content .= "<a href='' title='" . $ap_name . "'>";
            $content .= "<img style='width:{$width}px;height:{$height}px' border='0' src='";
            $content .= UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . $default_content;
            $content .= "' alt=''/>";
            $content .= "</a>";
            $adv_info['adv_title'] = $ap_name;
            $adv_info['adv_img'] = UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . $default_content;
            $adv_info['adv_link'] = '';
        }
    }
    else {
        $select = 0;
        if ($ap_display == '1') {//多广告展示
            $select = array_rand($adv_list);
        }
        $adv_select = $adv_list[$select];
        extract($adv_select);
        //图片广告
        if ($ap_class == '0') {
            $width = $ap_width;
            $height = $ap_height;
            $pic_content = unserialize($adv_content);
            $pic = $pic_content['adv_pic'];
            $url = $pic_content['adv_pic_url'];
            $content = "<a href='http://" . $pic_content['adv_pic_url'] . "' target='_blank' title='" . $adv_title . "'>";
            $content .= "<img style='width:{$width}px;height:{$height}px' border='0' src='";
            $content .= UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . $pic;
            $content .= "' alt='" . $adv_title . "'/>";
            $content .= "</a>";
            $adv_info['adv_title'] = $adv_title;
            $adv_info['adv_img'] = UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . $pic;
            $adv_info['adv_link'] = 'http://' . $pic_content['adv_pic_url'];
        }
        //文字广告
        if ($ap_class == '1') {
            $word_content = unserialize($adv_content);
            $word = $word_content['adv_word'];
            $url = $word_content['adv_word_url'];
            $content .= "<a href='http://" . $pic_content['adv_word_url'] . "' target='_blank'>";
            $content .= $word;
            $content .= "</a>";
        }
        //Flash广告
        if ($ap_class == '3') {
            $width = $ap_width;
            $height = $ap_height;
            $flash_content = unserialize($adv_content);
            $flash = $flash_content['flash_swf'];
            $url = $flash_content['flash_url'];
            $content .= "<a href='http://" . $url . "' target='_blank'><button style='width:" . $width . "px; height:" . $height . "px; border:none; padding:0; background:none;' disabled><object id='FlashID' classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' width='" . $width . "' height='" . $height . "'>";
            $content .= "<param name='movie' value='";
            $content .= UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . $flash;
            $content .= "' /><param name='quality' value='high' /><param name='wmode' value='opaque' /><param name='swfversion' value='9.0.45.0' /><!-- 此 param 标签提示使用 Flash Player 6.0 r65 和更高版本的用户下载最新版本的 Flash Player。如果您不想让用户看到该提示，请将其删除。 --><param name='expressinstall' value='";
            $content .= RESOURCE_SITE_URL . "/js/expressInstall.swf'/><!-- 下一个对象标签用于非 IE 浏览器。所以使用 IECC 将其从 IE 隐藏。 --><!--[if !IE]>--><object type='application/x-shockwave-flash' data='";
            $content .= UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . $flash;
            $content .= "' width='" . $width . "' height='" . $height . "'><!--<![endif]--><param name='quality' value='high' /><param name='wmode' value='opaque' /><param name='swfversion' value='9.0.45.0' /><param name='expressinstall' value='";
            $content .= RESOURCE_SITE_URL . "/js/expressInstall.swf'/><!-- 浏览器将以下替代内容显示给使用 Flash Player 6.0 和更低版本的用户。 --><div><h4>此页面上的内容需要较新版本的 Adobe Flash Player。</h4><p><a href='http://www.adobe.com/go/getflashplayer'><img src='http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif' alt='获取 Adobe Flash Player' width='112' height='33' /></a></p></div><!--[if !IE]>--></object><!--<![endif]--></object></button></a>";
        }
    }

    if ($type == 'array' && $ap_class == '0') {
        return $adv_info;
    }

    if ($type == 'js') {
        $content = "document.write(\"" . $content . "\");";
    }
    return $content;
}

/**
 * 格式化ubb标签
 *
 * @param string $theme_content /$reply_content 话题内容/回复内容
 * @return string
 */
function ubb($ubb)
{
    $ubb = str_replace(array(
                           '[B]', '[/B]', '[I]', '[/I]', '[U]', '[/U]', '[IMG]', '[/IMG]', '[/FONT]', '[/FONT-SIZE]',
                           '[/FONT-COLOR]'
                       ), array(
                           '<b>', '</b>', '<i>', '</i>', '<u>', '</u>', '<img class="pic" src="', '"/>', '</span>',
                           '</span>', '</span>'
                       ), preg_replace(array(
                                           "/\[URL=(.*)\](.*)\[\/URL\]/iU", "/\[FONT=([A-Za-z ]*)\]/iU",
                                           "/\[FONT-SIZE=([0-9]*)\]/iU", "/\[FONT-COLOR=([A-Za-z0-9]*)\]/iU",
                                           "/\[SMILIER=([A-Za-z_]*)\/\]/iU", "/\[FLASH\](.*)\[\/FLASH\]/iU", "/\\n/i"
                                       ), array(
                                           "<a href=\"$1\" target=\"_blank\">$2</a>", "<span style=\"font-family:$1\">",
                                           "<span style=\"font-size:$1px\">", "<span style=\"color:#$1\">",
                                           "<img src=\"" . CIRCLE_SITE_URL . '/templates/' . TPL_CIRCLE_NAME . "/images/smilier/$1.png\">",
                                           "<embed src=\"$1\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" wmode=\"opaque\" width=\"480\" height=\"400\"></embed>",
                                           "<br />"
                                       ), $ubb));
    return $ubb;
}

/**
 * 去掉ubb标签
 *
 * @param string $theme_content /$reply_content 话题内容/回复内容
 * @return string
 */
function removeUBBTag($ubb)
{
    $ubb = str_replace(array(
                           '[B]', '[/B]', '[I]', '[/I]', '[U]', '[/U]', '[/FONT]', '[/FONT-SIZE]', '[/FONT-COLOR]'
                       ), array(
                           '', '', '', '', '', '', '', '', ''
                       ), preg_replace(array(
                                           "/\[URL=(.*)\](.*)\[\/URL\]/iU", "/\[FONT=([A-Za-z ]*)\]/iU",
                                           "/\[FONT-SIZE=([0-9]*)\]/iU", "/\[FONT-COLOR=([A-Za-z0-9]*)\]/iU",
                                           "/\[SMILIER=([A-Za-z_]*)\/\]/iU", "/\[IMG\](.*)\[\/IMG\]/iU",
                                           "/\[FLASH\](.*)\[\/FLASH\]/iU", "<img class='pi' src=\"$1\"/>",
                                       ), array(
                                           "$2", "", "", "", "", "", "", ""
                                       ), $ubb));
    return $ubb;
}

/**
 * 话题图片绝对路径
 *
 * @param $param string 文件名称
 * @return string
 */
function themeImagePath($param)
{
    return BASE_UPLOAD_PATH . '/' . ATTACH_CIRCLE . '/theme/' . $param;
}

/**
 * 话题图片url
 *
 * @param $param string
 * @return string
 */
function themeImageUrl($param)
{
    return UPLOAD_SITE_URL . '/' . ATTACH_CIRCLE . '/theme/' . $param;
}

/**
 * 圈子logo
 *
 * @param $param string 圈子id
 * @return string
 */
function circleLogo($id)
{
    if (file_exists(BASE_UPLOAD_PATH . '/' . ATTACH_CIRCLE . '/group/' . $id . '.jpg')) {
        return UPLOAD_SITE_URL . '/' . ATTACH_CIRCLE . '/group/' . $id . '.jpg';
    }
    else {
        return UPLOAD_SITE_URL . '/' . ATTACH_CIRCLE . '/default_group_logo.gif';
    }
}

/**
 * sns 来自
 * @param $param string $trace_from
 * @return string
 */
function snsShareFrom($sign)
{
    switch ($sign) {
        case '1' :
        case '2' :
            return lang('sns_from') . '<a target="_black" href="' . SHOP_SITE_URL . '">' . lang('sns_shop') . '</a>';
            break;
        case '3' :
            return lang('sns_from') . '<a target="_black" href="' . MICROSHOP_SITE_URL . '">' . lang('ds_modules_microshop') . '</a>';
            break;
        case '4' :
            return lang('sns_from') . '<a target="_black" href="' . CMS_SITE_URL . '">CMS</a>';
            break;
        case '5' :
            return lang('sns_from') . '<a target="_black" href="' . CIRCLE_SITE_URL . '">' . lang('ds_circle') . '</a>';
            break;
    }
}

/**
 * 输出聊天信息
 *
 * @return string
 */
function getChat()
{
    return Chat::getChatHtml();
}

/**
 * 商城会员中心使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $act control文件名
 * @param string $op op方法名
 * @param array $args URL其它参数
 * @param string $store_domian 店铺二级域名
 * @return string
 */
/* function urlShop($act = '', $op = '', $args = array(), $store_domain = ''){

  // 如何使自营店则返回javascript:;

  //    if ($act == 'show_store' && $op != 'goods_all') {
  //        static $ownShopIds = null;
  //        if ($ownShopIds === null) {
  //            $ownShopIds = Model('store')->getOwnShopIds();
  //        }
  //        if (isset($args['store_id']) && in_array($args['store_id'], $ownShopIds)) {
  //            return 'javascript:;';
  //        }
  //    }

  // 开启店铺二级域名
  if (intval(config('enabled_subdomain')) == 1 && !empty($store_domain)){
  return 'http://'.$store_domain.'.'.SUBDOMAIN_SUFFIX.'/';
  }

  // 默认标志为不开启伪静态
  $rewrite_flag = false;

  // 如果平台开启伪静态开关，并且为伪静态模块，修改标志为开启伪静态
  $rewrite_item = array(
  'category:index',
  'channel:index',
  'goods:index',
  'goods:comments_list',
  'search:index',
  'show_store:index',
  'show_store:goods_all',
  'article:show',
  'article:article',
  'document:index',
  'brand:list',
  'brand:index',
  'promotion:index',
  'show_groupbuy:index',
  'show_groupbuy:groupbuy_detail',
  'show_groupbuy:groupbuy_list',
  'show_groupbuy:groupbuy_soon',
  'show_groupbuy:groupbuy_history',
  'show_groupbuy:vr_groupbuy_list',
  'show_groupbuy:vr_groupbuy_soon',
  'show_groupbuy:vr_groupbuy_history',
  'pointshop:index',
  'pointvoucher:index',
  'pointprod:pinfo',
  'pointprod:plist',
  'pointgrade:index',
  'pointgrade:exppointlog',
  'store_snshome:index',
  'special:special_list',
  'special:special_detail',
  );
  if(URL_MODEL && in_array($act.':'.$op, $rewrite_item)) {
  $rewrite_flag = true;
  $tpl_args = array();        // url参数临时数组
  switch ($act.':'.$op) {
  case 'search:index':
  if (!empty($args['keyword'])) {
  $rewrite_flag = false;
  break;
  }
  $tpl_args['cate_id'] = empty($args['cate_id']) ? 0 : $args['cate_id'];
  $tpl_args['b_id'] = empty($args['b_id']) || intval($args['b_id']) == 0 ? 0 : $args['b_id'];
  $tpl_args['a_id'] = empty($args['a_id']) || intval($args['a_id']) == 0 ? 0 : $args['a_id'];
  $tpl_args['key'] = empty($args['key']) ? 0 : $args['key'];
  $tpl_args['order'] = empty($args['order']) ? 0 : $args['order'];
  $tpl_args['type'] = empty($args['type']) ? 0 : $args['type'];
  $tpl_args['gift'] = empty($args['gift']) ? 0 : $args['gift'];
  $tpl_args['area_id'] = empty($args['area_id']) ? 0 : $args['area_id'];
  $tpl_args['curpage'] = empty($args['curpage']) ? 0 : $args['curpage'];
  $args = $tpl_args;
  break;
  case 'show_store:goods_all':
  if (isset($args['inkeyword'])) {
  $rewrite_flag = false;
  break;
  }
  $tpl_args['store_id'] = empty($args['store_id']) ? 0 : $args['store_id'];
  $tpl_args['stc_id'] = empty($args['stc_id']) ? 0 : $args['stc_id'];
  $tpl_args['key'] = empty($args['key']) ? 0 : $args['key'];
  $tpl_args['order'] = empty($args['order']) ? 0 : $args['order'];
  $tpl_args['curpage'] = empty($args['curpage']) ? 0 : $args['curpage'];
  $args = $tpl_args;
  break;
  case 'brand:list':
  $tpl_args['brand'] = empty($args['brand']) ? 0 : $args['brand'];
  $tpl_args['key'] = empty($args['key']) ? 0 : $args['key'];
  $tpl_args['order'] = empty($args['order']) ? 0 : $args['order'];
  $tpl_args['type'] = empty($args['type']) ? 0 : $args['type'];
  $tpl_args['gift'] = empty($args['gift']) ? 0 : $args['gift'];
  $tpl_args['area_id'] = empty($args['area_id']) ? 0 : $args['area_id'];
  $tpl_args['curpage'] = empty($args['curpage']) ? 0 : $args['curpage'];
  $args = $tpl_args;
  break;

  case 'show_groupbuy:index':
  case 'show_groupbuy:groupbuy_detail':
  break;

  case 'show_groupbuy:groupbuy_list':
  case 'show_groupbuy:groupbuy_soon':
  case 'show_groupbuy:groupbuy_history':
  $tpl_args['class'] = empty($args['class']) ? 0 : $args['class'];
  $tpl_args['s_class'] = empty($args['s_class']) ? 0 : $args['s_class'];
  $tpl_args['groupbuy_price'] = empty($args['groupbuy_price']) ? 0 : $args['groupbuy_price'];
  $tpl_args['groupbuy_order_key'] = empty($args['groupbuy_order_key']) ? 0 : $args['groupbuy_order_key'];
  $tpl_args['groupbuy_order'] = empty($args['groupbuy_order']) ? 0 : $args['groupbuy_order'];
  $tpl_args['curpage'] = empty($args['curpage']) ? 0 : $args['curpage'];
  $args = $tpl_args;
  break;

  case 'show_groupbuy:vr_groupbuy_list':
  case 'show_groupbuy:vr_groupbuy_soon':
  case 'show_groupbuy:vr_groupbuy_history':
  $tpl_args['vr_class'] = empty($args['vr_class']) ? 0 : $args['vr_class'];
  $tpl_args['vr_s_class'] = empty($args['vr_s_class']) ? 0 : $args['vr_s_class'];
  $tpl_args['vr_area'] = empty($args['vr_area']) ? 0 : $args['vr_area'];
  $tpl_args['vr_mall'] = empty($args['vr_mall']) ? 0 : $args['vr_mall'];
  $tpl_args['groupbuy_price'] = empty($args['groupbuy_price']) ? 0 : $args['groupbuy_price'];
  $tpl_args['groupbuy_order_key'] = empty($args['groupbuy_order_key']) ? 0 : $args['groupbuy_order_key'];
  $tpl_args['groupbuy_order'] = empty($args['groupbuy_order']) ? 0 : $args['groupbuy_order'];
  $tpl_args['curpage'] = empty($args['curpage']) ? 0 : $args['curpage'];
  $args = $tpl_args;
  break;

  case 'goods:comments_list':
  $tpl_args['goods_id'] = empty($args['goods_id']) ? 0 : $args['goods_id'];
  $tpl_args['type'] = empty($args['type']) ? 0 : $args['type'];
  $tpl_args['curpage'] = empty($args['curpage']) ? 0 : $args['curpage'];
  $args = $tpl_args;
  break;

  case 'pointgrade:exppointlog':
  $tpl_args['curpage'] = empty($args['curpage']) ? 0 : $args['curpage'];
  $args = $tpl_args;
  break;

  case 'promotion:index':
  $args = (empty($args['gc_id']) ? NULL : $args);
  break;
  default:
  break;
  }
  }

  return url($act, $op, $args, $rewrite_flag, SHOP_SITE_URL);
  } */

/**
 * 商城后台使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $act control文件名
 * @param string $op op方法名
 * @param array $args URL其它参数
 * @return string
 */
/* function urlAdmin($act = '', $op = '', $args = array()){
  return url($act, $op, $args, false, ADMIN_SITE_URL);
  } */

/**
 * CMS使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $act control文件名
 * @param string $op op方法名
 * @param array $args URL其它参数
 * @return string
 */
function urlCMS($act = '', $op = '', $args = array())
{
    return url($act, $op, $args, false);
}

/**
 * 圈子使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $act control文件名
 * @param string $op op方法名
 * @param array $args URL其它参数
 * @return string
 */
function urlCircle($act = '', $op = '', $args = array())
{
    return url($act, $op, $args, false, CIRCLE_SITE_URL);
}

/**
 * 微商城使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $act control文件名
 * @param string $op op方法名
 * @param array $args URL其它参数
 * @return string
 */
function urlMicroshop($act = '', $op = '', $args = array())
{
    return url($act, $op, $args, false, MICROSHOP_SITE_URL);
}

function urlMember($act = '', $op = '', $args = array())
{
    $rewrite_flag = false;
    $rewrite_item = array('article:show', 'article:article');
    if (URL_MODEL && in_array($act . ':' . $op, $rewrite_item)) {
        $rewrite_flag = true;
    }

    return url($act, $op, $args, $rewrite_flag, MEMBER_SITE_URL);
}

function urlLogin($act = '', $op = '', $args = array())
{
    return url($act, $op, $args, false, LOGIN_SITE_URL);
}

/**
 * 验证是否为平台店铺
 *
 * @return boolean
 */
function checkPlatformStore()
{
    return session('is_own_shop');
}

/**
 * 验证是否为平台店铺 并且绑定了全部商品类目
 *
 * @return boolean
 */
function checkPlatformStoreBindingAllGoodsClass()
{

    return checkPlatformStore() && session('bind_all_gc');
}

/**
 * 获得店铺状态样式名称
 * @param $param array $store_info
 * @return string
 */
function getStoreStateClassName($store_info)
{
    $result = 'open';
    if (intval($store_info['store_state']) === 1) {
        $store_end_time = intval($store_info['store_end_time']);
        if ($store_end_time > 0) {
            if ($store_end_time < TIMESTAMP) {
                $result = 'expired';
            }
            elseif (($store_end_time - 864000) < TIMESTAMP) {
                //距离到期10天
                $result = 'expire';
            }
        }
    }
    else {
        $result = 'close';
    }
    return $result;
}

/**
 * 将字符部分加密并输出
 * @param unknown $str
 * @param unknown $start 从第几个位置开始加密(从1开始)
 * @param unknown $length 连续加密多少位
 * @return string
 */
function encryptShow($str, $start, $length)
{
    $end = $start - 1 + $length;
    $array = str_split($str);
    foreach ($array as $k => $v) {
        if ($k >= $start - 1 && $k < $end) {
            $array[$k] = '*';
        }
    }
    return implode('', $array);
}

/**
 * CURL请求
 * @param $url 请求url地址
 * @param $method 请求方法 get post
 * @param null $postfields post数据数组
 * @param array $headers 请求header信息
 * @param bool|false $debug 调试开启 默认false
 * @return mixed
 */
function httpRequest($url, $method = "GET", $postfields = null, $headers = array(), $debug = false)
{
    $method = strtoupper($method);
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
    curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
            break;
    }
    $ssl = preg_match('/^https:\/\//i', $url) ? TRUE : FALSE;
    curl_setopt($ci, CURLOPT_URL, $url);
    if ($ssl) {
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
    }
    //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
    curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
    $response = curl_exec($ci);
    $requestinfo = curl_getinfo($ci);
    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);
        echo "=====info===== \r\n";
        print_r($requestinfo);
        echo "=====response=====\r\n";
        print_r($response);
    }
    curl_close($ci);
    return $response;
    //return array($http_code, $response,$requestinfo);
}

/**
 * 　　* 是否移动端访问
 * 　　*
 * 　　* @return bool
 * 　　*/
function isMobile()
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        return true;

    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])) {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic',
            'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb',
            'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
            return true;
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}