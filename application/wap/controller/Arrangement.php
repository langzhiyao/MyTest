<?php

namespace app\wap\controller;

use think\captcha\Captcha;

class Arrangement extends MobileMember
{

    /**
     * app宝宝课程
     */
    public function index() {
        $school_id  = intval(input('post.school_id'));
        $class_id  = intval(input('post.class_id'));
        $weektype  = intval(input('post.weektype'));
        if (empty($school_id)) {
            output_error('参数有误');
        }
        $model_arrangement = Model('Arrangement');
        $data = $model_arrangement->getOneInfo(array('schoolid'=>$school_id,"classid"=>$class_id));
        if(!empty($data['content'])){
            $data['content']= json_decode($data['content'],true);
        }
        $week = date('w');
        //本周日期
        if($weektype==2){
            $weeks['Monday'] = date('Y-m-d',strtotime( '+'. 1-$week .' days' ));
            $weeks['Tuesday'] = date('Y-m-d',strtotime( '+'. 2-$week .' days' ));
            $weeks['Wednesday'] = date('Y-m-d',strtotime( '+'. 3-$week .' days' ));
            $weeks['Thursday'] = date('Y-m-d',strtotime( '+'. 4-$week .' days' ));
            $weeks['Friday'] = date('Y-m-d',strtotime( '+'. 5-$week .' days' ));
            $weeks['Saturday'] = date('Y-m-d',strtotime( '+'. 6-$week .' days' ));
            $weeks['Sunday'] = date('Y-m-d',strtotime( '+'. 7-$week .' days' ));
        }
        //上周日期
        if($weektype==1){
            $weeks['Monday'] = date('Y-m-d',strtotime( '-'. 6-$week .' days' ));
            $weeks['Tuesday'] = date('Y-m-d',strtotime( '-'. 5-$week .' days' ));
            $weeks['Wednesday'] = date('Y-m-d',strtotime( '-'. 4-$week .' days' ));
            $weeks['Thursday'] = date('Y-m-d',strtotime( '-'. 3-$week .' days' ));
            $weeks['Friday'] = date('Y-m-d',strtotime( '-'. 2-$week .' days' ));
            $weeks['Saturday'] = date('Y-m-d',strtotime( '-'. 1 -$week .' days' ));
            $weeks['Sunday'] = date('Y-m-d',strtotime( '-'. 0-$week .' days' ));
        }
        //下周日期
        if($weektype==3){
            $weeks['Monday'] = date('Y-m-d',strtotime( '+'. 8-$week .' days' ));
            $weeks['Tuesday'] = date('Y-m-d',strtotime( '+'. 9-$week .' days' ));
            $weeks['Wednesday'] = date('Y-m-d',strtotime( '+'. 10-$week .' days' ));
            $weeks['Thursday'] = date('Y-m-d',strtotime( '+'. 11-$week .' days' ));
            $weeks['Friday'] = date('Y-m-d',strtotime( '+'. 12-$week .' days' ));
            $weeks['Saturday'] = date('Y-m-d',strtotime( '+'. 13-$week .' days' ));
            $weeks['Sunday'] = date('Y-m-d',strtotime( '+'. 14-$week .' days' ));
        }
        foreach($data['content'] as $k=>$v){
            foreach($weeks as $key=>$wek){
                if($k==$key){
                    if(date("Y-m-d",time())==$wek){
                        $data['content'][$k]['date']['week'] = "今日";
                    }else{
                        $data['content'][$k]['date']['week'] = $this->get_week($wek);
                    }
                    $data['content'][$k]['date']['day'] = $wek;
                }
            }
        }
        if(!empty($data['content']['Monday'])){
            $argc[] = $data['content']['Monday'];
        }
        if(!empty($data['content']['Tuesday'])){
            $argc[] = $data['content']['Tuesday'];
        }
        if(!empty($data['content']['Wednesday'])){
            $argc[] = $data['content']['Wednesday'];
        }
        if(!empty($data['content']['Thursday'])){
            $argc[] = $data['content']['Thursday'];
        }
        if($data['content']['Friday']){
            $argc[] = $data['content']['Friday'];
        }
        if($data['content']['Saturday']){
            $argc[] = $data['content']['Saturday'];
        }
        if($data['content']['Sunday']){
            $argc[] = $data['content']['Sunday'];
        }
        if(!empty($argc)){
            $data['content'] = $argc;
        }
        $data = !empty($data)?[$data]:[];
        if($data){
            output_data($data);
        }else{
            output_data(array());
        }

    }

    /**
     * 获取下周一到下周日的数组日期
     * @param $date
     * @return array
     */
    function getNextWeekOf($date)
    {
        $dates = array();
        $time = strtotime($date . ' 12:00:00');
        $w = date('w', $time);

        if ($w == 0) {
            $nextMonday = 1;
        } else {
            $nextMonday = 7 - $w + 1;
        }
        for ($i = $nextMonday; $i < $nextMonday + 7; $i++) {
            $dates[] = date('Y-m-d', $time + 3600 * 24 * $i);
        }
        return $dates;

    }

    public function  get_week($date){
        //强制转换日期格式
        $date_str=date('Y-m-d',strtotime($date));

        //封装成数组
        $arr=explode("-", $date_str);

        //参数赋值
        //年
        $year=$arr[0];

        //月，输出2位整型，不够2位右对齐
        $month=sprintf('%02d',$arr[1]);

        //日，输出2位整型，不够2位右对齐
        $day=sprintf('%02d',$arr[2]);

        //时分秒默认赋值为0；
        $hour = $minute = $second = 0;

        //转换成时间戳
        $strap = mktime($hour,$minute,$second,$month,$day,$year);

        //获取数字型星期几
        $number_wk=date("w",$strap);

        //自定义星期数组
        $weekArr=array("周日","周一","周二","周三","周四","周五","周六");

        //获取数字对应的星期
        return $weekArr[$number_wk];
    }



}