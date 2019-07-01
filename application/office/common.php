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

/**
 * 获取excel内容
 * @Author 老王
 * @创建时间   2019-01-22
 * @param  [type]     $filename [文件路径 名称]
 * @param  string     $exts     [后缀]
 * @param  int        $start    [开始行]
 * @param  string     $encode   [编码]
 */
function GetExcelContent($filename, $exts = 'xls' ,$start = 0,$encode = 'UTF-8') {
    vendor('PHPExcel.PHPExcel');
    //创建PHPExcel对象，注意，不能少了\
    $PHPExcel = new \PHPExcel();
    switch ($exts) {
        case 'xls':
            $PHPReader = new \PHPExcel_Reader_Excel5();
            break;
        case 'xlsx':
            $PHPReader = new \PHPExcel_Reader_Excel2007();
            break;
        default:
            $PHPReader = false;
            break;
    }
    if(!$PHPReader)exit('文件类型不符合！');
    //载入文件
    $PHPExcel = $PHPReader->load($filename, $encode);
    //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
    $currentSheet = $PHPExcel->getSheet($start);
    //获取总列数
    $allColumn = $currentSheet->getHighestColumn();
    //获取总行数
    $allRow = $currentSheet->getHighestRow();
    //循环获取表中的数据，$currentRow 表示当前行，从哪行开始读取数据，索引值从0开始
    for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
        //从哪列开始，A表示第一列
        for ($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++) {
            //数据坐标
            $address = $currentColumn . $currentRow;
            //读取到的数据，保存到数组$arr中
            $data[$currentRow][$currentColumn] = $currentSheet->getCell($address)->getValue();
        }
    }
    return $data;
}




