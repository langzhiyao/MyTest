<?php
function p($arr){
	echo "<pre>";
	print_r($arr);
	echo "</pre>";
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