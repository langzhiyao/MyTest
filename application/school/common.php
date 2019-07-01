<?php

function p($arr,$m='p'){
	if ($m=='p') {
		echo "<pre>";
		print_r($arr);
		echo "</pre>";
	}else{
		echo "<pre>";
		var_dump($arr);
		echo "</pre>";
	}
	
}

function axisFomat($str){
	$time_list = config('pkgs_list');
	return $time_list[$str];
}

function CalculationTime($order_info,$packagetime){
    $nowTime = !empty($packagetime['end_time'])?$packagetime['end_time']:$order_info['finnshed_time'];
    $pkg_length = $order_info['pkg_length'];    
    switch ($order_info['pkg_axis']) {
        case 'hour':
            // $endTime = strtotime("+{$pkg_length} hour",$nowTime);
            break;
        case 'day':
            // $endTime = strtotime("+{$pkg_length} day",$nowTime);
            break;
        case 'week':
            // $endTime = strtotime("+{$pkg_length} week",$nowTime);
            break;
        case 'mouth':
            // $endTime = strtotime("+{$pkg_length} month",$nowTime);
            $endTime = date('Y-m-d H:i:s',strtotime("{$nowTime}+{$pkg_length}month"));
            break;
        case 'quarter':
            $pkg_length*=3;
            // $endTime = strtotime("+{$pkg_length} month",$nowTime);
            $endTime = date('Y-m-d H:i:s',strtotime("{$nowTime}+{$pkg_length}month"));
            break;
        case 'half':
            $pkg_length*=6;
            // $endTime = strtotime("+{$pkg_length} month",$nowTime);
            break;
        case 'year':
            // $endTime = strtotime("+{$pkg_length} year",$nowTime);
            $endTime = date('Y-m-d H:i:s',strtotime("{$nowTime}+{$pkg_length}year"));

            break;
    }
    return $endTime;
}

function SexFomat($sex){
    return $sex == 0?'未设置或保密':($sex == 1 ?'男':'女');
}

function Fomat($time,$t='true'){
    if (!empty($time)&& $time !=0) {
        $h = ' H:i:s';
        if ($t='false') {
            return is_int($time)?date('Y-m-d',$time):$time;
        }
        return is_int($time)?date('Y-m-d H:i:s',$time):$time;

    }else{
        return '无';
    }
}

function schooltype($sctype){
    $sctype = explode(',',$sctype);
    $schooltypeList  = db('schooltype')->field('sc_id,sc_type')->select();
    $schooltypeList=array_column($schooltypeList,NULL,'sc_id');
    $type= '';
    foreach ($sctype as $k=>$v){
        $type .= ','.$schooltypeList[$v]['sc_type'];
    }
    return trim($type,',');
}

/**
 * 将银行卡中间八个字符隐藏为*
 */
function getHideBankCardNum($bank) {
    if ($bank) {
        $startNum = substr($bank,0,4);
        $endNum = substr($bank,-4,4);
        return $startNum.'**********'.$endNum;
    }
    return '未设置';
}

