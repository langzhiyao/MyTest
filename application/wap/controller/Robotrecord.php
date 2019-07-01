<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/16
 * Time: 20:12
 */

namespace app\wap\controller;


class Robotrecord extends MobileMember
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    //获取近7天的打卡记录
    public function record(){
        $student_id = input('post.student_id');
        if(empty($student_id)){
            output_error('student_id参数有误');
        }
        //前七天日期
        $days = array ();
        for($i=0;$i<=7;$i++) {
            $days[] = date("Y-m-d", strtotime(' -'. $i . 'day'));
        }
        $startTime = strtotime($days[7]);
        $endTime = strtotime($days[0]." 23:59:59");
        $robot_model = Model('Robotreport');
        $where = "student_id=".$student_id." and ioTime>=".$startTime." and ioTime<=".$endTime;
        $SNNumberInfo = $robot_model->getReportList($where);
        $weekarray=array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
        $path = "http://".$_SERVER['HTTP_HOST']."/uploads/home/robotvideo/";
        foreach($SNNumberInfo as $k=>$v){
            $SNNumberInfo[$k]['ioDate'] = date("Y-m-d",$v['ioTime']);
            $SNNumberInfo[$k]['ioTime'] = date("H:i:s",$v['ioTime']);
            $SNNumberInfo[$k]['ioVideo'] = $path.$v['ioVideo'];
        }
        foreach($SNNumberInfo as $key=>$item){
            if($item['ioFlag']==1){
                $result[$item['ioDate']]['come'] = $item;
                $result[$item['ioDate']]['come']['ioFlagName'] = "上学签到";
            }
            if(!isset($result[$item['ioDate']]['leave']) && $item['ioFlag']==2){
                $result[$item['ioDate']]['leave'] = $item;
                $result[$item['ioDate']]['leave']['ioFlagName'] = "放学签到";
            }
            if(date("Y-m-d",time()) == $item['ioDate']){
                $result[$item['ioDate']]['week'] = "今天";
            }else{
                $result[$item['ioDate']]['week'] = $weekarray[date("w",strtotime($item['ioDate']))];
            }
            $result[$item['ioDate']]['ioDate'] = $item['ioDate'];
        }

        foreach($days as $dd=>$yy){
            if(isset($result[$yy])){
                $result[$yy] == $result[$yy];
            }else{
                if(date("Y-m-d",time()) == $yy){
                    $result[$yy]['week'] = "今天";
                }else{
                    $result[$yy]['week'] = $weekarray[date("w",strtotime($yy))];
                }
                $result[$yy]['ioDate'] = $yy;
            }
        }
        if(!empty($result)){
            $result = $this->array_sort($result,'desc');
            $results[0] = array_values($result);
        }else{
            $results = [];
        }
        $studentInfo = db('student')->alias('s')
            ->join('__SCHOOL__ sc','sc.schoolid=s.s_schoolid','LEFT')
            ->join('__CLASS__ cl','cl.classid=s.s_classid','LEFT')
            ->field("s_id,s_name,sc.name,cl.classname")
            ->where(array("s_id"=>$student_id))->find();
        $name = $studentInfo['name']."-".$studentInfo['classname']."-".$studentInfo['s_name'];
        if(!empty($results[0])){
            $results[1]['name'] = $name;
        }else{
            $results['name'] = $name;
        }
        output_data($results);
    }

    function array_sort($arr,$orderby='asc'){
        $keysvalue = $new_array = array();
        foreach ($arr as $k=>$v){
            $keysvalue[$k] = $k;
        }
        if($orderby== 'asc'){
            asort($keysvalue);
        }else{
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k=>$v){
            $new_array[] = $arr[$k];
        }
        return $new_array;
    }

}