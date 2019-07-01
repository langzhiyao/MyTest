<?php

namespace app\wap\controller;

use think\Lang;
use process\Process;
use cloud\RongCloud;
class Member extends MobileMember
{

    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'wap\lang\zh-cn\login.lang.php');
    }


    public function self_assets(){
        $self = array(
            'available_predeposit' =>ncPriceFormatb($this->member_info['available_predeposit']),
            'freeze_predeposit' =>ncPriceFormatb($this->member_info['freeze_predeposit']),
            'income' => '0.00',//收入金额
            'reflect' => '0.00',//提现金额
        );
        output_data($self);
    }

    public function RefreshPullOldOrder(){
        $input = input();
        if (isset($input['registrationID'])) {
            //删除之前绑定该 极光id的数据，
            db('memberjpush')->where('registrationID',$input['registrationID'])->delete();
            $jp = db('memberjpush')->where('member_id',$this->member_info['member_id'])->find();
            if ($jp) {
                if ($jp['registrationID']!=$input['registrationID']) {
                    //如果已经存在过记录，只修改极光id
                    db('memberjpush')->where('id', $jp['id'])->setField('registrationID',$input['registrationID']);
                }
            }else{
                //如果没有当前用户信息，写入
                db('memberjpush')->insertGetId(['member_id'=>$this->member_info['member_id'],'registrationID'=>$input['registrationID']]);
            }
        }
        $oldid = $this->member_info['oldid'];
        if (!$oldid) output_data(array('state'=>'不是老会员'));//不是老会员
        $trans = array(
            'member_id'=>$this->member_info['member_id'],
            'is_trans' =>['gt',0],
        );
        $isTrans = db('packagetrans')->where($trans)->find();
        if ($isTrans) output_data(array('state'=>'已转移完成'));//已转移完成
        //获取订单
        $orderCondition=array(
            'member_member_id' =>$oldid,
            'status' =>0,
        );
        $order = db('aaorder')->where($orderCondition)->select();
        if ($order) {
            $TrueOrder=[];
            foreach ($order as $key => $o) {
                if($o['status'] == 0)$TrueOrder[]=$o;
            }
            if (count($TrueOrder) ==0)output_data(array('state'=>'true'));
            $sign = [];
            $packageTime = [];
            foreach ($TrueOrder as $k => $t) {
                //获取订单详情
                $TrueOrder[$k]['orderitem'] = db('aaorderitem')->where('order_order_id',$t['id'])->find();
                //获取套餐时间
                $TrueOrder[$k]['allpackageeauthority'] = db('aallpackageeauthority')->where('member_member_id',$oldid)->find();
                $Student = db('student')->where('oldid',$t['student_student_id'])->field('s_id,s_name')->find();
                $packageTime[]=array(
                    'member_id' => $this->member_info['member_id'],
                    'member_name' => $this->member_info['member_name'],
                    's_id' => $Student['s_id'],
                    's_name' => $Student['s_name'],
                    'start_time' => strtotime($TrueOrder[$k]['allpackageeauthority']['liveBeginTime']),
                    'end_time' => strtotime($TrueOrder[$k]['allpackageeauthority']['liveDeadTime']),
                    'up_time' => strtotime($TrueOrder[$k]['allpackageeauthority']['lastTime']),
                    'up_desc' => $TrueOrder[$k]['allpackageeauthority']['lastTime'].'  '.$TrueOrder[$k]['allpackageeauthority']['name'],
                    'pkg_type' => 1,
                    'is_oldorder' => 2,
                );
            }
            $count = count($packageTime);
            if (!$count==1) {
                $res = db('packagetime')->insertGetId($packageTime[0]);
            }else{
                $start_time = [];
                $lastTime = [];
                foreach ($packageTime as $ke => $p){
                    $start_time[$k] =$p['start_time'];
                    $lastTime[$ke]  =$p['end_time'];
                } 
                $start_time =min($start_time);//最早购买的时间
                $lastTime = max($lastTime); // 最晚的结束时间
                $end_time = array_column($packageTime, 'end_time'); 
                $k = array_search($lastTime, $end_time);
                $newTime = $packageTime[0];
                $newTime['start_time'] = $start_time; //使用最早的开始时间
                $newTime['end_time'] = $packageTime[$k]['end_time'];//使用最晚的结束时间
                $res = db('packagetime')->insertGetId($newTime);
            }
            if($res){
                $transRes=db('packagetrans')->insertGetId(array(
                    'member_id' => $this->member_info['member_id'],
                    'is_trans' => $res,
                    'transtime' => TIMESTAMP,
                ));
            }
        }
        if($transRes){
            output_data(array('state'=>'转移完成'));    
        }else{
            output_data(array('state'=>'转移失败'));
        }
        
    }

    /**
     * @desc 个人信息
     * @author langzhiyao
     * @time 20181001
     */
    public function info(){
        $token = trim(input('post.key'));
        $member_id = intval(input('post.member_id'));
        $where = ' m.member_id = "'.$member_id.'"';
        if(empty($token)){
            output_error('缺少参数token');
        }
        if(empty($member_id)){
            output_error('缺少参数id');
        }
        $member = db('member')->alias('m')->field('m.member_id,m.member_paypwd')->where($where)->find();
        if(empty($member)){
            output_error('会员不存在，请联系管理员');
        }
        if(!empty($member_id)){
            $result = db('member')->alias('m')->field('m.member_id,m.member_nickname,m.member_avatar,m.member_identity,m.is_owner,m.member_age,m.member_sex,m.member_email,m.member_provinceid,m.member_cityid,m.member_areaid,m.member_jobid')->where($where)->find();

            if(!empty($result)){
                if(!empty($result['member_avatar'])){
                    $result['rel_member_avatar'] = UPLOAD_SITE_URL.$result['member_avatar'];
                }else{
                    $result['member_avatar'] = '/' . ATTACH_COMMON . '/' . 'default_user_portrait.png';
                    $result['rel_member_avatar'] = UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . 'default_user_portrait.png';
                }
                if($result['is_owner'] == 0){
                    $result['f_account_count'] =db('member')->where('is_owner = "'.$result["member_id"].'"')->count();
                }else{
                    $result['f_account_count'] =db('member')->where('(is_owner = "'.$result["is_owner"].'" OR member_id = "'.$result["is_owner"].'") AND member_id != "'.$member_id.'"')->count();
                }

                $result['province_name'] = db('area')->where('area_id = "'.$result["member_provinceid"].'"')->value('area_name');
                $result['city_name'] = db('area')->where('area_id = "'.$result["member_cityid"].'"')->value('area_name');
                $result['area_name'] = db('area')->where('area_id = "'.$result["member_areaid"].'"')->value('area_name');
                $result['job_name'] = db('industry')->where('id = "'.$result["member_jobid"].'"')->value('name');
                output_data($result);
            }else{
                output_error('该用户不存在');
            }
        }else{
            output_error('缺少参数id');
        }

    }
    /**
     * @desc 修改个人信息
     * @author langzhiyao
     * @time 20181001
     */
    public function addInfo(){
        $token = trim(input('post.key'));
        $member_id = intval(input('post.member_id'));

        if(empty($token)){
            output_error('缺少参数token');
        }
        if(empty($member_id)){
            output_error('缺少参数id');
        }
        $where = ' member_id = "'.$member_id.'"';
        $member = db('member')->field('member_id,member_paypwd')->where($where)->find();
        if(empty($member)){
            output_error('会员不存在，请联系管理员');
        }
        //判断是否有传的数据
        if(!empty($_POST)){
            $member_nickname = trim(input('post.member_nickname'));//昵称
            $member_identity = intval(input('post.member_identity'));//角色
            $member_age = trim(input('post.member_age'));//年龄
            $member_sex = intval(input('post.member_sex'));//性别
            $member_email = trim(input('post.member_email'));//邮箱
            $member_provinceid = intval(input('post.member_provinceid'));//省
            $member_cityid = intval(input('post.member_cityid'));//市
            $member_areaid = intval(input('post.member_areaid'));//区
            $member_jobid = intval(input('post.member_jobid'));//行业
            $member_avatar = trim(input('post.member_avatar'));//头像
            if(!empty($member_avatar)){
                $data = array(
                    'member_nickname' => $member_nickname,
                    'member_age' => $member_age,
                    'member_identity' => $member_identity,
                    'member_sex' => $member_sex,
                    'member_email' => $member_email,
                    'member_provinceid' => $member_provinceid,
                    'member_cityid' => $member_cityid,
                    'member_areaid' => $member_areaid,
                    'member_jobid' => $member_jobid,
                    'member_avatar' => $member_avatar,
                    'member_edit_time'=>time()
                );
            }else{
                $data = array(
                    'member_nickname' => $member_nickname,
                    'member_age' => $member_age,
                    'member_identity' => $member_identity,
                    'member_sex' => $member_sex,
                    'member_email' => $member_email,
                    'member_provinceid' => $member_provinceid,
                    'member_cityid' => $member_cityid,
                    'member_areaid' => $member_areaid,
                    'member_jobid' => $member_jobid,
                    'member_edit_time'=>time()
                );
            }

            //修改个人信息
            $result = db('member')->where($where)->update($data);
            if($result){
                //刷新融云 头像
                $RongCloud = new RongCloud();
                $RongCloud->user()->refresh($member['member_id'], $member_nickname, UPLOAD_SITE_URL.$member['member_avatar']);
                output_data(array('message'=>'修改成功'));
            }else{
                output_error('修改失败');
            }
        }else{
            output_error('网络错误');
        }
    }

    /**
     * @desc 我的订单
     * @author langzhiyao
     * @time 20181001
     */
    public function order(){
        $token = trim(input('post.key'));
        if(empty($token)){
            output_error('缺少参数token');
        }
        $member_id = intval(input('post.member_id'));
        if(empty($member_id)){
            output_error('缺少参数id');
        }
        $member_where = ' member_id = "'.$member_id.'"';
        $member = db('member')->field('member_id,member_paypwd')->where($member_where)->find();
        if(empty($member)){
            output_error('会员不存在，请联系管理员');
        }
        $type_id = intval(input('post.type_id'));//1.看孩 2.教孩  3.商城
        if(empty($type_id)){
            output_error('缺少参数type_id');
        }
        $where = ' o.buyer_id = "'.$member_id.'" AND o.delete_state = 0 ';
        $order ='';
        switch ($type_id){
            case 1:
                $order = db('packagesorder')->alias('o')->field('o.pkg_name,o.s_id,o.add_time,o.order_state,o.order_amount,o.order_dieline,FROM_UNIXTIME(o.add_time,\'%Y-%m-%d\') as starTime,FROM_UNIXTIME(o.order_dieline,\'%Y-%m-%d\') as endTime')->where($where)->order('order_id DESC')->select();
                break;
            case 2:
//                $order = db('packagesorderteach')->alias('o')->field('o.order_name,o.add_time,o.order_state,o.order_amount,FROM_UNIXTIME(o.add_time,\'%Y-%m-%d\') as add_time')->where($where)->order('order_id DESC')->select();
                break;
            case 3:
                $order = db('packagesorderteach')->alias('o')->field('o.order_name,o.order_tid,o.add_time,o.order_state,o.order_amount,o.order_state,o.order_dieline,FROM_UNIXTIME(o.add_time,\'%Y-%m-%d\') as starTime,FROM_UNIXTIME(o.order_dieline,\'%Y-%m-%d\') as endTime')->where($where)->order('order_id DESC')->select();
                break;
        }
        if(!empty($order)){
            foreach ($order as $key=>$value) {
                $order[$key]['order_amount'] = round($value['order_amount'],2);
                if(!empty($value['order_dieline'])){
                    if($value['order_dieline'] >=time()){
                        $order[$key]['is_gq'] = 1;
                    }else{
                        $order[$key]['is_gq'] = 2;
                    }
                }else{
                    $order[$key]['is_gq'] = 0;
                }

            }
        }
        output_data($order);


    }

    /**
     * @desc 我的收藏
     * @author langzhiyao
     * @time 20181001
     */
    public function collect(){
        $token = trim(input('post.key'));
        if(empty($token)){
            output_error('缺少参数token');
        }
        $member_id = intval(input('post.member_id'));
        if(empty($member_id)){
            output_error('缺少参数id');
        }
        $member_where = ' member_id = "'.$member_id.'"';
        $member = db('member')->field('member_id,member_paypwd')->where($member_where)->find();
        if(empty($member)){
            output_error('会员不存在，请联系管理员');
        }
        $type_id = intval(input('post.type_id'));//1.课件 2.商城
        if(empty($type_id)){
            output_error('缺少参数type_id');
        }
        $where = ' c.member_id = "'.$member_id.'" AND c.type_id = "'.$type_id.'" ';
        $collect ='';
        switch ($type_id){
            case 1:
                $collect = db('membercollect')->alias('c')->field('c.time,FROM_UNIXTIME(c.time,\'%Y-%m-%d\') as time,t.t_title,t.t_picture,t.t_userid,t.t_profile,t.t_price,m.member_nickname')->join('__TEACHCHILD__ t','t.t_id=c.collect_id','LEFT')->join('__MEMBER__ m','m.member_id=t.t_userid','LEFT')->where($where)->order('c.id DESC')->select();
                break;
            case 2:
                $collect = db('membercollect')->alias('c')->field('c.time,FROM_UNIXTIME(c.time,\'%Y-%m-%d\') as time,g.goods_name,g.goods_price,g.goods_image,g.evaluation_count')->join('__GOODS__ g','g.goods_id=c.collect_id','LEFT')->where($where)->order('c.id DESC')->select();
                break;
        }
        output_data($collect);

    }

    /**
     * @desc 修改密码
     * @author langzhiyao
     * @time 20181001
     */
    public function editPwd(){
        $token = trim(input('post.key'));
        if(empty($token)){
            output_error('缺少参数token');
        }
        $member_id = intval(input('post.member_id'));
        if(empty($member_id)){
            output_error('缺少参数id');
        }
        $oldPwd = trim(input('post.oldPwd'));
        $newPwd = trim(input('post.newPwd'));
        $reNewPwd = trim(input('post.reNewPwd'));
        if(empty($oldPwd) || empty($newPwd) || empty($reNewPwd)){
            output_error('传参不能为空');
        }
        $where = ' member_id = "'.$member_id.'"';
        $member = db('member')->field('member_id,member_password')->where($where)->find();
        if(!empty($member)){
            if($member['member_password'] != md5($oldPwd)){
                output_error('旧密码不正确，请重新填写');
            }
            if(strlen($newPwd) <6 || strlen($newPwd) >12){
                output_error('新密码长度必须在6~12位中');
            }

            if($newPwd != $reNewPwd){
                output_error('两次密码不一致，请重新填写');
            }
            $data = array(
                'member_password' => md5($newPwd)
            );
            $result = db('member')->where($where)->update($data);
            if($result){
                //发送站内信,提示修改密码
                $model_message = Model('message');
                $insert_arr = array();
                $insert_arr['from_member_id'] = 0;
                $insert_arr['member_id'] = $this->member_info['member_id'];
                $insert_arr['to_member_name'] = $this->member_info['member_name'];
                $insert_arr['message_title'] = '密码修改成功';
                $insert_arr['msg_content'] = '您于 '.date('Y-m-d H:i',time()).' 成功修改密码';
                $insert_arr['message_type'] = 1;
                $model_message->saveMessage($insert_arr);

                output_data(array('message'=>'修改成功'));
            }else{
                output_error('新密码和原密码一致，请重新修改');
            }
        }else{
            output_error('该会员不存在，请联系管理员');
        }

    }

    /**
     * @desc  支付密码
     * @author langzhiyao
     * @time 20181002
     */
    public function payPwd(){
        $token = trim(input('post.key'));
        if(empty($token)){
            output_error('缺少参数token');
        }
        $member_id = intval(input('post.member_id'));
        if(empty($member_id)){
            output_error('缺少参数id');
        }
        $where = ' member_id = "'.$member_id.'"';

        $member = db('member')->field('member_id,member_paypwd')->where($where)->find();
        if(empty($member)){
            output_error('会员不存在，请联系管理员');
        }
        output_data($member);

    }
    /**
     * @desc 支付密码修改
     * @author langzhiyao
     * @time 20181002
     */
    public function editPayPwd(){
        $token = trim(input('post.key'));
        if(empty($token)){
            output_error('缺少参数token');
        }
        $member_id = intval(input('post.member_id'));
        if(empty($member_id)){
            output_error('缺少参数id');
        }
        $where = ' member_id = "'.$member_id.'"';

        $member = db('member')->field('member_id,member_paypwd')->where($where)->find();
        if(empty($member)){
            output_error('会员不存在，请联系管理员');
        }

        $payPwd = trim(input('post.payPwd'));
        if(empty($payPwd)){
            output_error('密码不能为空');
        }
        if(strlen($payPwd) != 6){
            output_error('密码不符合要求，只能为6为数字');
        }
        $rePayPwd = trim(input('post.rePayPwd'));
        if($payPwd != $rePayPwd){
            output_error('两次密码不一致，请重新输入');
        }
        $data = array(
            'member_paypwd'=>md5($payPwd)
        );

        $result = db('member')->where($where)->update($data);

        if($result){
            //发送站内信,提示修改支付密码
            $model_message = Model('message');
            $insert_arr = array();
            $insert_arr['from_member_id'] = 0;
            $insert_arr['member_id'] = $this->member_info['member_id'];
            $insert_arr['to_member_name'] = $this->member_info['member_name'];
            $insert_arr['message_title'] = '支付密码修改成功';
            $insert_arr['msg_content'] = '您于 '.date('Y-m-d H:i',time()).' 成功修改支付密码,此密码将会在用余额支付的时候使用!';
            $insert_arr['message_type'] = 1;
            $model_message->saveMessage($insert_arr);
            output_data(array('message'=>'设置成功'));
        }else{
            output_error('新密码不能和原密码一样，请重新输入');
        }

    }

    /**
     * @desc 绑定学生
     * @author langzhiyao
     * @time 20181002
     */
    public function studentBind(){
        $token = trim(input('post.key'));
        if(empty($token)){
            output_error('缺少参数token');
        }
        $member_id = intval(input('post.member_id'));
        if(empty($member_id)){
            output_error('缺少参数member_id');
        }
        $where = ' member_id = "'.$member_id.'"';

        $member = db('member')->field('member_id,member_paypwd,is_owner')->where($where)->find();
        if(empty($member)){
            output_error('会员不存在，请联系管理员');
        }
        if($member['is_owner'] != 0){
            output_error('该手机号为副账号，不允许绑定孩子');
        }

        $name        = trim(input('post.name'));//姓名
        $sex         = intval(input('post.sex'));//性别
        $birthday    = trim(input('post.birthday'));//出生日期
        $province_id = intval(input('post.province'));//省ID
        $city_id     = intval(input('post.city'));//市ID
        $area_id     = intval(input('post.area'));//区ID
        $region      = $this->getAddress($province_id).' '.$this->getAddress($city_id).' '.$this->getAddress($area_id);
        $school_id   = intval(input('post.school'));//学校ID
        $grade_id    = intval(input('post.grade'));//年级ID
        $class_id    = intval(input('post.class'));//班级ID
        $classCard   = trim(input('post.class_code'));//班级识别码
        $card        = trim(input('post.card'));//学生身份证ID
        if(empty($name) || empty($school_id) || empty($grade_id) || empty($class_id) || empty($classCard)){
            output_error('传的参数不完整');
        }
        //判断该账号绑定孩子数量
        $student_num = db('student')->where('s_ownerAccount =  "'.$member_id.'"')->count();

        if($student_num >=3){
            output_error('绑定孩子数量超出限制，如有需要，请联系客服');
        }

        //判断识别码是否存在 并是不是这个班级的识别码
        $class = db('class')->field('classCard,classid,schoolid')->where(' classid =  "'.$class_id.'"')->find();
        if(empty($class)){
            output_error('班级不存在');
        }
        if($class['classCard'] != $classCard){
            output_error('选择班级和填写的班级识别码不一致');
        }
        //判断该学生是否有绑定人
        $student = db('student')->field('s_ownerAccount')->where(' s_card = "'.$card.'"')->find();
        $data = array(
            's_ownerAccount' => $member_id,
            's_name'         => $name,
            's_sex'          => $sex,
            's_classid'      => $class_id,
            's_schoolid'     => $school_id,
            's_sctype'       => $grade_id,
            's_birthday'     => $birthday,
            's_provinceid'   => $province_id,
            's_cityid'       => $city_id,
            's_areaid'       => $area_id,
            's_region'       => $region,
            's_card'         => $card,
            'classCard'      =>$classCard,
            's_createtime'   => date('Y-m-d H:i:s',time())
        );
        $sid = '';
        if(!empty($student)){
            if(!empty($student['s_ownerAccount'])){
                output_error('该学生已有绑定人，请联系管理员');
            }else{
                $stu = db('student')->field('s_id')->where(' s_card = "'.$card.'"')->find();
                $student = db('student')->where(' s_card = "'.$card.'"')->update($data);
                $sid = $stu['s_id'];
            }
        }else{
            $student = db('student')->insertGetId($data);

            if(!empty($member['classid'])){
                $updateMember['classid'] = trim(',',$member['classid'].','.$class_id);
            }else{
                $updateMember['classid'] = $class_id;
            }
            if(!empty($member['schoolid'])){
                $updateMember['schoolid'] = trim(',',$member['schoolid'].','.$school_id);
            }else{
                $updateMember['schoolid'] = $school_id;
            }
            //给家长绑定学校id和班级id
             db('member')->where('member_id',$member_id)->update($updateMember);
            $sid = $student;
        }   
            if($student){
                //发送站内信--未写
                // $model_message = Model('message');
                $insert_arr = array();
                $insert_arr['from_member_id'] = 0;
                $insert_arr['member_id'] = $this->member_info['member_id'];
                $insert_arr['to_member_name'] = $this->member_info['member_name'];
                $insert_arr['message_title'] = '学生绑定';
                // $insert_arr['msg_content'] = '您于 '.date('Y-m-d H:i',time()).' 绑定';
                $insert_arr['message_type'] = 1;
                // $model_message->saveMessage($insert_arr);

                output_data(array('message'=>'绑定成功','sid'=>$sid));
            }else{
                output_error('绑定失败');
            }

    }

    /**
     * @desc 副账号列表
     * @author langzhiyao
     * @time 20181002
     */
    public function account(){
        $token = trim(input('post.key'));
        if(empty($token)){
            output_error('缺少参数token');
        }
        $member_id = intval(input('post.member_id'));
        if(empty($member_id)){
            output_error('缺少参数id');
        }
        $member_where = ' member_id = "'.$member_id.'"';

        $member = db('member')->field('member_id,member_paypwd,is_owner')->where($member_where)->find();
        if(empty($member)){
            output_error('会员不存在，请联系管理员');
        }
        if($member['is_owner'] == 0){
            $where = ' is_owner = "'.$member_id.'"';
        }else{
            $where = ' (is_owner = "'.$member['is_owner'].'" OR member_id="'.$member['is_owner'].'") AND member_id != "'.$member_id.'"';
        }


        $account = db('member')->field('member_id,member_aboutname,member_mobile,is_owner')->where($where)->select();
        output_data($account);




    }
    /**
     * @desc 绑定副账号
     * @author langzhiyao
     * @time 20181002
     */
    public function accountBind(){
        $token = trim(input('post.key'));
        if(empty($token)){
            output_error('缺少参数token');
        }
        $member_id = intval(input('post.member_id'));
        if(empty($member_id)){
            output_error('缺少参数id');
        }
        $member_aboutname = trim(input('post.member_aboutname'));//关系名称
        $member_mobile = trim(input('post.member_mobile'));//手机号
        if(empty($member_aboutname) || empty($member_mobile)){
            output_error('传参数不正确');
        }
        $member_where = ' member_id = "'.$member_id.'"';

        $member = db('member')->field('member_id,member_mobile,is_owner')->where($member_where)->find();
        if(empty($member)){
            output_error('会员不存在，请联系管理员');
        }

        if($member['is_owner'] != 0){
            output_error('该手机号为副账号，不能添加');
        }
        //查询当前会员绑定的孩子
        $member_student = db('student')->field('s_card,s_ownerAccount')->where(' s_ownerAccount = "'.$member_id.'"')->select();
        //查询绑定手机号是否存在
        $member_mobile_where = ' member_mobile = "'.$member_mobile.'" ';
        $member_about = db('member')->where($member_mobile_where)->find();
        if($member['member_mobile'] == $member_mobile){
            output_error('不能添加自己为副账号');
        }
        if($member_about['is_owner'] != 0){
            output_error('该手机号已有归属主账号，不能重复添加');
        }
        $res = array();
        if(!empty($member_student)){
            foreach($member_student as $k=>$v){
                $res[] = $v['s_card'];
            }
        }

        if(!empty($member_about)){
            $data = array(
                'is_owner' => $member_id,
                'member_aboutname' => $member_aboutname,
                'member_add_time' =>time()
            );
            //判断该手机号绑定的孩子
            $student = db('student')->field('s_card')->where(' s_ownerAccount= "'.$member_about["member_id"].'"')->select();

            foreach ($student as $key => $s) {
                # code...
            }
            if(!empty($res)){
                if(!empty($student)){
                    if(count($student) > 1){
                        output_error('该手机号绑定有多个孩子，不能添加');
                    }else{
                        if(!empty($member_student[0]['s_card']) && in_array($member_student[0]['s_card'],$res)){
                            $result= db('member')->where('member_mobile="'.$member_mobile.'"')->update($data);
                            $log_msg = '【'.config('site_name').'】您于'.date("Y-m-d").'被账号'. '[' . $this->member_info['member_mobile'] . ']'.'添加为副账号，可共同使用想见孩平台';
                            if ($result) {
                                $sms = new \sendmsg\Sms();
                                $send = $sms->send($member_mobile,$log_msg);
                                if($send){
                                    output_data(array('message'=>'添加成功'));
                                }else{
                                    output_error('添加成功，短信发送失败，请联系平台管理员');
                                }
                            }else{
                                output_error('添加失败，请联系平台管理员');
                            }
                        }else{
                            output_error('该手机号绑定孩子和会员绑定孩子不一致，不能添加');
                        }
                    }
                }else{
                    $result = db('member')->where('member_mobile="'.$member_mobile.'"')->update($data);
                    $log_msg = '【'.config('site_name').'】您于'.date("Y-m-d").'被账号'. '[' .$this->member_info['member_mobile']. ']'.'添加为副账号，可共同使用想见孩平台';
                    if ($result) {
                        $sms = new \sendmsg\Sms();
                        $send = $sms->send($member_mobile,$log_msg);
                        if($send){
                            output_data(array('message'=>'添加成功'));
                        }else{
                            output_error('添加成功，短信发送失败，请联系平台管理员');
                        }
                    }else{
                        output_error('添加失败，请联系平台管理员');
                    }
                }
            }else{
                if(!empty($student)){
                    output_error('该手机号绑定有多个孩子，不能添加');
                }else{
                    $result = db('member')->where('member_mobile="'.$member_mobile.'"')->update($data);
                    $log_msg = '【'.config('site_name').'】您于'.date("Y-m-d").'被账号'. '[' . $this->member_info['member_mobile'] . ']'.'添加为副账号，可共同使用想见孩平台';
                    if ($result) {
                        $sms = new \sendmsg\Sms();
                        $send = $sms->send($member_mobile,$log_msg);
                        if($send){
                            output_data(array('message'=>'添加成功'));
                        }else{
                            output_error('添加成功，短信发送失败，请联系平台管理员');
                        }
                    }else{
                        output_error('添加失败，请联系平台管理员');
                    }
                }
            }


        }else{
            //生成数字字符随机 密码
            $pass = getRandomString(8,null,'n');
            $password= md5(trim($pass));
            $data = array(
                'is_owner' => $member_id,
                'member_aboutname' => $member_aboutname,
                'member_mobile' => $member_mobile,
                'member_name' => $member_mobile,
                'member_password' => $password,
                'member_add_time' =>time(),
                'member_mobile_bind' =>1
            );
            $result = db('member')->insert($data);

            $log_msg = '【'.config('site_name').'】您于'.date("Y-m-d").'被账号'. '[' . $this->member_info['member_mobile'] . ']'.'添加为副账号，可共同使用想见孩平台。      ';
            $log_msg .= '【登陆账号为：'.$member_mobile.'密码为：'.$pass.'】';
            if ($result) {
                $sms = new \sendmsg\Sms();
                $send = $sms->send($member_mobile,$log_msg);
                if($send){
                    output_data(array('message'=>'添加成功'));
                }else{
                    output_error('添加成功，短信发送失败，请联系平台管理员');
                }
            }else{
                output_error('添加失败，请联系平台管理员');
            }
        }
    }
    /**
     * @desc 解绑
     * @author langzhiyao
     * @time 20181002
     */
    public function accountDel(){
        $token = trim(input('post.key'));
        if(empty($token)){
            output_error('缺少参数token');
        }
        $member_id = intval(input('post.member_id'));
        if(empty($member_id)){
            output_error('缺少参数id');
        }
        $jb_id = intval(input('post.jb_id'));
        if(empty($jb_id)){
            output_error('缺少参数jb_id');
        }
        $where = ' member_id = "'.$member_id.'"';

        $member = db('member')->field('member_id,member_mobile')->where($where)->find();
        if(empty($member)){
            output_error('会员不存在，请联系管理员');
        }

        $jb_where = ' member_id = "'.$jb_id.'"';

        $jb_account = db('member')->field('member_id,is_owner,member_mobile')->where($jb_where)->find();
        if(empty($jb_account)){
            output_error('副账号不存在，请联系管理员');
        }

        if($jb_account['is_owner'] == 0){
            output_error('已解绑，无需重复操作');
        }
        if($member_id == $jb_account['is_owner']){
            $data = array(
                'is_owner'=>0,
                'member_aboutname' => ''
            );
            $res = db('member')->where($jb_where)->update($data);
            $log_msg = '【'.config('site_name').'】您于'.date("Y-m-d").'被账号'. '[' . $member['member_mobile'] . ']'.'解除绑定';
            if ($res) {
                $sms = new \sendmsg\Sms();
                $send = $sms->send($jb_account['member_mobile'],$log_msg);
                if($send){
                    output_data(array('message'=>'解帮成功'));
                }else{
                    output_error('解帮成功，短信发送失败，请联系平台管理员');
                }
            }else{
                output_error('已解绑，无需重复操作');
            }
        }else{
            output_error('解绑失败，该账号不属于该会员，请联系管理员解除');
        }

    }

    /**
     * @desc 获取绑定孩子数据
     * @author langzhiyao
     * @time 20181103
     */
    public function getStudents(){
        $token = trim(input('post.key'));
        if(empty($token)){
            output_error('缺少参数token');
        }
        $member_id = intval(input('post.member_id'));
        if(empty($member_id)){
            output_error('缺少参数id');
        }
        $version = trim(input('post.version'));



        $where = ' member_id = "'.$member_id.'"';

        $member = db('member')->field('member_id,is_owner')->where($where)->find();
        if(empty($member)){
            output_error('会员不存在，请联系管理员');
        }
        //获取绑定孩子
        if($member['is_owner'] == 0){
            //主账号绑定的孩子
            $students = db('student')->alias('s')
                ->field('s.s_id,s.s_name,s.s_sex,s.s_birthday,s.s_card,s.s_provinceid,s.s_cityid,s.s_areaid,s.s_region,s.classCard,s.s_schoolid,s.s_classid,s.s_sctype,sc.name,c.classname,st.sc_type,p.end_time')
                ->join('__SCHOOL__ sc','sc.schoolid = s.s_schoolid','LEFT')
                ->join('__SCHOOLTYPE__ st','st.sc_id = s.s_sctype','LEFT')
                ->join('__CLASS__ c','c.classid = s.s_classid','LEFT')
                ->join('__PACKAGETIME__ p','p.s_id = s.s_id','LEFT')
                ->where('s.s_ownerAccount = "'.$member_id.'"')
                ->group('s.s_id')
                ->select();
        }else{
            //副账号 显示起主账号绑定的孩子
            $students = db('student')->alias('s')
                ->field('s.s_id,s.s_name,s.s_sex,s.s_birthday,s.s_card,s.s_provinceid,s.s_cityid,s.s_areaid,s.s_region,s.classCard,s.s_schoolid,s.s_classid,s.s_sctype,sc.name,c.classname,st.sc_type,p.end_time')
                ->join('__SCHOOL__ sc','sc.schoolid = s.s_schoolid','LEFT')
                ->join('__SCHOOLTYPE__ st','st.sc_id = s.s_sctype','LEFT')
                ->join('__CLASS__ c','c.classid = s.s_classid','LEFT')
                ->join('__PACKAGETIME__ p','p.s_id = s.s_id','LEFT')
                ->where('s.s_ownerAccount = "'.$member['is_owner'].'"')
                ->group('s.s_id')
                ->select();
        }


        if(!empty($students)){
            foreach ($students as $k=>$v) {
                if(!empty($v['end_time'])){
                    $students[$k]['time'] = date('Y-m-d',$v['end_time']);
                }else{
                    $students[$k]['time'] = '请前往购买套餐';
                }
                if($version){
                    $students[$k]['is_version'] = $this->is_version($version);
                }else{
                    $students[$k]['is_version'] = true;
                }
                if(empty($v['s_region'])){
                    $students[$k]['s_region'] = $this->getAddress($v['s_provinceid']).' '.$this->getAddress($v['s_cityid']).' '.$this->getAddress($v['s_areaid']);
                }
            }
        }

        output_data($students);
    }

    /**
     * @desc 获取孩子信息
     * @author langzhiyao
     * @time 20181103
     */
    public function getStudent(){
        $token = trim(input('post.key'));
        if(empty($token)){
            output_error('缺少参数token');
        }
        $sid = intval(input('post.sid'));
        if(empty($token)){
            output_error('缺少参数sid');
        }
        $version = trim(input('post.version'));
        //获取绑定孩子
            $student = db('student')->alias('s')
                ->field('s.s_id,s.s_name,s.s_sex,s.s_birthday,s.s_card,s.s_provinceid,s.s_cityid,s.s_areaid,s.s_region,s.classCard,s.s_schoolid,s.s_classid,s.s_sctype,sc.name,c.classname,st.sc_type,p.end_time')
                ->join('__SCHOOL__ sc','sc.schoolid = s.s_schoolid',LEFT)
                ->join('__SCHOOLTYPE__ st','st.sc_id = s.s_sctype',LEFT)
                ->join('__CLASS__ c','c.classid = s.s_classid',LEFT)
                ->join('__PACKAGETIME__ p','p.s_id = s.s_id',LEFT)
                ->where('s.s_id = "'.$sid.'"')
                ->group('s.s_id')
                ->find();
        if(!empty($student)){
            if(!empty($student['end_time'])){
                $student['time'] = date('Y-m-d',$student['end_time']);
            }else{
                $student['time'] = '请前往购买套餐';
            }
            if($version){
                $student['is_version'] = $this->is_version($version);
            }else{
                $student['is_version'] = true;
            }

            if(empty($student['s_region'])){
                $student['s_region'] = $this->getAddress($student['s_provinceid']).' '.$this->getAddress($student['s_cityid']).' '.$this->getAddress($student['s_areaid']);
            }
        }
            output_data($student);

    }

    /**
     * @desc 判断是否位主账号
     * @author langzhiyao
     * @time 20181103
     */
    public function isOwner(){
        $token = trim(input('post.key'));
        if(empty($token)){
            output_error('缺少参数token');
        }
        $member_id = intval(input('post.member_id'));
        if(empty($member_id)){
            output_error('缺少参数id');
        }

        $where = ' member_id = "'.$member_id.'"';

        $member = db('member')->field('member_id,is_owner')->where($where)->find();
        if(empty($member)){
            output_error('会员不存在，请联系管理员');
        }
        output_data($member);


    }

/**
 * @desc 获取地址
 */
function getAddress($addressID){
    $res = db('area')->where('area_id = "'.$addressID.'"')->field('area_name')->find();

    return $res['area_name'];
}


    /**
     * @desc 判断版本号
     * @author langzhiyao
     * @time 20181121
     */
    public function is_version($version){
        //获取原有版本号
        $old_version = db('version_update')->field('version_num')->where('type=2')->order('id DESC')->find();
        $ios_version = explode('.',$old_version['version_num']);
        $ios_num = $ios_version[0]*100+$ios_version[1]*10+$ios_version[2];
        //得到传过来的版本号
        $new_ios_version = explode('.',$version);
        $new_ios_num = $new_ios_version[0]*100+$new_ios_version[1]*10+$new_ios_version[2];
        //判断
        if($ios_num >= $new_ios_num ){
            return true;
        }else{
            return false;
        }


    }

}

?>
