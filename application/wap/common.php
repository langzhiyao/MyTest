<?php

function AvatarFormat($avatar){
    if (is_numeric($avatar)) {
        $avatar = db('member')->where('member_id',$avatar)->value('member_avatar');
    }
    if(!empty($avatar)){
        $avatar = UPLOAD_SITE_URL.$avatar;
    }else{
        $avatar = UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . 'default_user_portrait.png';
    }
    return $avatar;
}


function CalculationTime($order_info,$packagetime){
    $nowTime = !empty($packagetime['end_time'])?$packagetime['end_time']:$order_info['finnshed_time'];
    
    $pkg_length = $order_info['pkg_length'];    
    switch ($order_info['pkg_axis']) {
        case 'hour':
            $endTime = strtotime("+{$pkg_length} hour",$nowTime);
            break;
        case 'day':
            $endTime = strtotime("+{$pkg_length} day",$nowTime);
            break;
        case 'week':
            $endTime = strtotime("+{$pkg_length} week",$nowTime);
            break;
        case 'mouth':
            $endTime = strtotime("+{$pkg_length} month",$nowTime);
            break;
        case 'quarter':
            $pkg_length*=3;
            $endTime = strtotime("+{$pkg_length} month",$nowTime);
            break;
        case 'half':
            $pkg_length*=6;
            $endTime = strtotime("+{$pkg_length} month",$nowTime);
            break;
        case 'year':
            $endTime = strtotime("+{$pkg_length} year",$nowTime);
            break;
    }
    return $endTime;
}

function axisFomat($str){
    $time_list = config('pkgs_list');
    return $time_list[$str];
}

function output_data($datas, $extend_data = array(),$codd=1,$isAssoc = 'false') {
    if(count($datas) == count($datas,1)){
        if(!empty($datas)){
            $datas2 = array(
                0=>$datas
            );
        }else{
            $datas2 = array();
        }
    }else{
        $datas2 = $datas;
    }
    $data = array();
    $data['code'] = isset($datas['error'])?'100':'200';
    if ($codd !=1) $data['code'] = '400';
//    $data['result']=isset($datas['error'])?array():($isAssoc == 'true'?(object)$datas2:$datas2);
    $data['result']=isset($datas['error'])?array():$datas2;

    $data['message'] = isset($datas['error'])?$datas['error']:'';
    if(!empty($extend_data)) {
        $data = array_merge($data, $extend_data);
    }

    if(!empty($_GET['callback'])) {
        echo $_GET['callback'].'('.json_encode($data).')';die;
    } else {
        echo json_encode($data);die;
    }
}
function output_datas($data,$transfer = 'true'){
    output_data($data, array(),1,$transfer);
}
function _checkAssocArray($arr){
    $index = 0;
    foreach (array_keys($arr) as $key) {
        if ($index++ != $key) return 'false';
    }
    return 'true';
}


function output_error($message, $extend_data = array(),$codd=1) {
    $datas = array('error' => $message);
    output_data($datas, $extend_data,$codd);
}

function mobile_page($page_info) {
    //输出是否有下一页
    $extend_data = array();
    if($page_info==''){
        $extend_data['page_total']=1;
        $extend_data['hasmore'] = false;
    }else {
        $current_page = $page_info->currentPage();
        if ($current_page <= 0) {
            $current_page = 1;
        }
        if ($current_page >= $page_info->lastPage()) {
            $extend_data['hasmore'] = false;
        }
        else {
            $extend_data['hasmore'] = true;
        }
        $extend_data['page_total'] = $page_info->lastPage();
    }
    return $extend_data;
}

function get_server_ip() {
    if (isset($_SERVER)) {
        if($_SERVER['SERVER_ADDR']) {
            $server_ip = $_SERVER['SERVER_ADDR'];
        } else {
            $server_ip = $_SERVER['LOCAL_ADDR'];
        }
    } else {
        $server_ip = getenv('SERVER_ADDR');
    }
    return $server_ip;
}

function http_get($url) {
    return file_get_contents($url);
}

function http_post($url, $param) {
    $postdata = http_build_query($param);

    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );

    $context  = stream_context_create($opts);

    return @file_get_contents($url, false, $context);
}

function http_postdata($url, $postdata) {
    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );

    $context  = stream_context_create($opts);

    return @file_get_contents($url, false, $context);
}
function p($arr){
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}