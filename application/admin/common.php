<?php

function p($arr){
    if ($arr) {
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

function SexFomat($sex){
	return $sex == 0?'未设置或保密':($sex == 1 ?'男':'女');
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

/**
 * 隐藏身份证后四位
 * @创建时间  2018-12-19T18:30:05+0800
 * @param [type]                   $card [description]
 */
function CardFomat($card){
	if ($card) {
		$card = substr($card,0,-4);
		return $card.'****';
	}
    return '未设置';
}

/**
 * 隐藏手机号中间4位
 * @创建时间  2018-12-19T18:30:16+0800
 * @param [type]                   $mobile [description]
 */
function MobileFormat($mobile) {
	
	if ($mobile) {
		$startNum = substr($mobile,0,3);
		$endNum = substr($mobile,-4,4);
		return $startNum.'****'.$endNum;
	}
    return '未设置';
}




