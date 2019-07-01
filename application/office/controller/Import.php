<?php

namespace app\office\controller;

use think\Lang;
use vomont\Vomont;

class Import extends AdminControl
{

    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'office/lang/zh-cn/student.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('office_gid') ,$perm_id);
        $this->assign('action',$action);
        //获取省份
        $province = db('area')->field('area_id,area_parent_id,area_name')->where('area_parent_id=0')->select();
        //获取学校
        $school = db('school')->field('schoolid,name')->where('isdel=1')->select();
        $this->assign('school',$school);
        $this->assign('province',$province);
    }

    /**
     * @desc 导入失败
     * @author 郎志耀
     * @time 20180926
     */
    public function index(){

        if(session('admin_is_super') !=1 && !in_array('4',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $where = ' status=2 ';
        if(!empty($_GET)){
            if(!empty($_GET['name'])){
                $where .= ' AND (m_name LIKE "%'.trim($_GET["name"]).'%" OR s_name LIKE "%'.trim($_GET["name"]).'%")';
            }
            if(!empty($_GET['province'])){
                $where .= ' AND province_id = "'.intval($_GET["province"]).'"';
            }
            if(!empty($_GET['city'])){
                $where .= ' AND city_id = "'.intval($_GET["city"]).'"';
            }
            if(!empty($_GET['area'])){
                $where .= ' AND area_id = "'.intval($_GET["area"]).'"';
            }
            if(!empty($_GET['school'])){
                $where .= ' AND school_id = "'.intval($_GET["school"]).'"';
            }
            if(!empty($_GET['grade'])){
                $where .= ' AND sc_id = "'.intval($_GET["grade"]).'"';
            }
            if(!empty($_GET['class'])){
                $where .= ' AND classid = "'.intval($_GET["class"]).'"';
            }
        }
        //查询绑定总数
        $list_count = db('import_student')->where($where)->count();
        $this->assign('list_count',$list_count);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    /**
     * @desc 导入成功
     * @author 郎志耀
     * @time 20180926
     */
    public function successin(){

        if(session('admin_is_super') !=1 && !in_array('4',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $where = ' status=1 ';
        if(!empty($_GET)){
            if(!empty($_GET['name'])){
                $where .= ' AND (m_name LIKE "%'.trim($_GET["name"]).'%" OR s_name LIKE "%'.trim($_GET["name"]).'%")';
            }
            if(!empty($_GET['province'])){
                $where .= ' AND province_id = "'.intval($_GET["province"]).'"';
            }
            if(!empty($_GET['city'])){
                $where .= ' AND city_id = "'.intval($_GET["city"]).'"';
            }
            if(!empty($_GET['area'])){
                $where .= ' AND area_id = "'.intval($_GET["area"]).'"';
            }
            if(!empty($_GET['school'])){
                $where .= ' AND school_id = "'.intval($_GET["school"]).'"';
            }
            if(!empty($_GET['grade'])){
                $where .= ' AND sc_id = "'.intval($_GET["grade"]).'"';
            }
            if(!empty($_GET['class'])){
                $where .= ' AND classid = "'.intval($_GET["class"]).'"';
            }
        }
        //查询绑定总数
        $list_count = db('import_student')->where($where)->count();
        $this->assign('list_count',$list_count);
        $this->setAdminCurItem('successin');
        return $this->fetch('success');
    }
    /**
     * @desc 获取分页数据
     * @author langzhiyao
     * @time 20190929
     */
    public function get_list(){
        $status = intval(input('get.status'));
        $where = ' status="'.$status.'" ';
        if(!empty($_POST)){
            if(!empty($_POST['name'])){
                $where .= ' AND (m_name LIKE "%'.trim($_GET["name"]).'%" OR s_name LIKE "%'.trim($_GET["name"]).'%")';
            }
            if(!empty($_POST['province'])){
                $where .= ' AND province_id = "'.intval($_POST["province"]).'"';
            }
            if(!empty($_POST['city'])){
                $where .= ' AND city_id = "'.intval($_POST["city"]).'"';
            }
            if(!empty($_POST['area'])){
                $where .= ' AND area_id = "'.intval($_POST["area"]).'"';
            }
            if(!empty($_POST['school'])){
                $where .= ' AND school_id = "'.intval($_POST["school"]).'"';
            }
            if(!empty($_GET['grade'])){
                $where .= ' AND sc_id = "'.intval($_GET["grade"]).'"';
            }
            if(!empty($_GET['class'])){
                $where .= ' AND classid = "'.intval($_GET["class"]).'"';
            }
        }

        $page_count = intval(input('post.page_count')) ? intval(input('post.page_count')) : 1;//每页的条数
        $start = intval(input('post.page')) ? (intval(input('post.page'))-1)*$page_count : 0;//开始页数

//        halt($start);
        //查询未绑定的摄像头
        $list = db('import_student')->where($where)->limit($start,$page_count)->order('time DESC')->select();
        $list_count = db('import_student')->where($where)->count();

        $html = '';
        if(!empty($list)){
            foreach($list as $key=>$value){
                $html .= '<tr class="hover">';
                if($value['reason_id'] == 1){
                    $html .= '<td class="align-center" style="color: red;" >'.$value["m_mobile"].'</td>';
                }else{
                    $html .= '<td class="align-center" >'.$value["m_mobile"].'</td>';
                }
                $html .= '<td class="align-center">'.$value["m_name"].'</td>';
                if($value['m_sex'] == 1){
                    $html .= '<td class="align-center">男</td>';
                }else if($value['m_sex'] == 2){
                    $html .= '<td class="align-center">女</td>';
                }else{
                    $html .= '<td class="align-center">保密</td>';
                }
                if($value['reason_id'] == 2){
                    $html .= '<td class="align-center" style="color: red;" >'.$value["s_name"].'</td>';
                }else{
                    $html .= '<td class="align-center">'.$value["s_name"].'</td>';
                }

                if($value['s_sex'] == 1){
                    $html .= '<td class="align-center">男</td>';
                }else if($value['_sex'] == 2){
                    $html .= '<td class="align-center">女</td>';
                }else{
                    $html .= '<td class="align-center">保密</td>';
                }
                if($value['reason_id'] == 3){
                    $html .= '<td class="align-center" style="color: red;" >'.$value["s_card"].'</td>';
                }else{
                    $html .= '<td class="align-center">'.$value["s_card"].'</td>';
                }
                $html .= '<td class="align-center">'.$value["school_name"].'</td>';
                if($value['reason_id'] == 4){
                    $html .= '<td class="align-center" style="color: red;" >'.$value["school_type"].'</td>';
                }else{
                    $html .= '<td class="align-center">'.$value["school_type"].'</td>';
                }
                if($value['reason_id'] == 5){
                    $html .= '<td class="align-center" style="color: red;" >'.$value["class_name"].'</td>';
                }else{
                    $html .= '<td class="align-center">'.$value["class_name"].'</td>';
                }
                $html .= '<td class="align-center">'.$value["address"].'</td>';
                $html .= '<td class="align-center">'.date('Y-m-d H:i:s',$value["time"]).'</td>';
                if($value['reason_id'] == 6){
                    $html .= '<td class="align-center" style="color: red;" >'.$value["t_name"].'</td>';
                }else{
                    $html .= '<td class="align-center">'.$value["t_name"].'</td>';
                }
                $html .= '<td class="align-center">'.round($value["t_price"],2).'元</td>';
                $html .= '<td class="align-center">'.intval($value["t_day"]).'天</td>';
                $html .= '<td class="w150 align-center">
                        <div class="layui-table-cell laytable-cell-9-8">
                           <a href="javascript:void(0)"  class="layui-btn  layui-btn-sm edit" data-id="'.$value["id"].'" lay-event="reset">修改</a>';
                $html .=  '</div></td>';

                $html .= '</tr>';
            }
        }
        if($html == ''){
            $html .= '<tr class="no_data">
                    <td colspan="15">没有符合条件的记录</td>
                </tr>';
        }

        exit(json_encode(array('html'=>$html,'count'=>$list_count)));

    }


    /**
     * @desc 确认导入excel表和学校
     * @author langzhiyao
     * @time 20180926
     */
    public function download(){
        if(session('admin_is_super') !=1 && !in_array('8',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $this->setAdminCurItem('camera');
        return $this->fetch('download');
    }

    /**
     * @desc 再次确认插入数据表的内容
     * @author langzhiyao
     * @time 20180926
     */
    public function excelTrue(){
        if(session('admin_is_super') !=1 && !in_array('8',$this->action)){
            $this->error(lang('ds_assign_right'));
        }

        $this->setAdminCurItem('camera');
        return $this->fetch('excelTrue');
    }

    /**
     * @desc 将exce数据插入表中
     * @author langzhiyao
     * @time 20180928
     */
    public function insert_excel(){
        if(session('admin_is_super') !=1 && !in_array('8',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
//        halt($_SESSION['excel']);
        if(!empty($_SESSION['excel'])){
            $excel_success = $_SESSION['excel']['excel_success_data'];
            $excel_fail = $_SESSION['excel']['excel_fail_data'];
            /*if(empty($_SESSION['excel'])){
                exit(json_encode(array('code'=>1,'msg'=>'没有符合的数据，请重新上传')));
            }*/

            //插入成功的数据
            //开启事务
            $model = Model('import_student');
            $model_member = Model('member');
            $model_student = Model('student');
            $model_order = Model('packagesorder');
            $model_order_time = Model('packagetime');
            $model->startTrans();
            $flag = false;
            if(!empty($excel_success)){
                foreach($excel_success as $key=>$value){
                    if($value['C'] == '男'){$value['C'] =1;}else{$value['C'] = 2;}//家长性别
                    if($value['E'] == '男'){$value['E'] =1;}else{$value['E'] = 2;}//学生性别
                    //生成学生身份证号
                    $s_card01 = '370213'.rand(2013,2015);
                    $s_card02 = rand(1,12);
                    if($s_card02 <10){
                        $s_card02 = '0'.$s_card02;
                    }
                    $s_card03 = rand(1,28);
                    if($s_card03 <10){
                        $s_card03 = '0'.$s_card03;
                    }
                    $s_card04 = rand(1000,9999);
                    $s_card = $s_card01.$s_card02.$s_card03.$s_card04;
                    //套餐ID
                    switch ($value['J']){
                        case '看孩套餐':
                            $t_id = 1;
                            break;
                        case '重温课堂套餐':
                            $t_id = 2;
                            break;
                        case '教孩套餐':
                            $t_id = 3;
                            break;
                        default:
                            $t_id = 4;
                            break;
                    }
                    $res = db('member')->field('member_id')->where(" `member_mobile` = '".$value["A"]."'")->find();
//                    if(!$res){

                        $data = array(
                            'm_mobile' => $value['A'],
                            'm_name' => $value['B'],
                            'm_sex' => $value['C'],
                            's_name' => $value['D'],
                            's_sex' => $value['E'],
//                            's_card' => $value['F'],
                            's_card' => $s_card,
                            'school_id' => $_SESSION['excel']['school']['schoolid'],
                            'school_name' => $_SESSION['excel']['school']['name'],
                            'province_id' => $_SESSION['excel']['school']['provinceid'],
                            'city_id' => $_SESSION['excel']['school']['cityid'],
                            'area_id' => $_SESSION['excel']['school']['areaid'],
                            'address' => $_SESSION['excel']['school']['address'],
                            'sc_id' => $value['sc_id'],
                            'school_type' => $value['H'],
                            'classid' => $value['classid'],
                            'class_name' => $value['I'],
                            't_id' => $t_id,
                            't_name' => $value['J'],
                            't_price' => $value['K'],
                            't_day' => $value['L'],
                            'content' => $value['M'],
                            'status' => 1,
                            'time' => time(),
                            'trueTime' => time(),
                        );
                        $import_data = $model->insertGetId($data);
                        if($import_data){
                            if($res){
                                $member_id = $res['member_id'];
                            }else{
                                //为家长开账户并发送短信通知
//                                $pass = getRandomString(6,null,'n');
                                $pass = '123456';
                                $member = array();
                                $member['member_name'] = empty($value['B'])?$value['A']:$value['B'];
                                $member['member_nickname'] = empty($value['B'])?$value['A']:$value['B'];
                                $member['member_password'] = md5(trim($pass));;
                                $member['member_mobile'] = $value['A'];
                                $member['member_provinceid'] = $_SESSION['excel']['school']['provinceid'];
                                $member['member_cityid'] = $_SESSION['excel']['school']['cityid'];
                                $member['member_areaid'] = $_SESSION['excel']['school']['areaid'];
                                $member['member_areainfo'] = $_SESSION['excel']['school']['address'];
                                $member['member_mobile_bind'] = 1;
                                $member_id = $model_member->insertGetId($member);

                            }
                            if ($member_id) {
                                //添加学生信息
                                $student_array=array(
                                    's_name' => $value['D'],
                                    's_sex' => $value['E'],
//                                    's_card' => $value['F'],
                                    's_card' => $s_card,
                                    's_schoolid' => $_SESSION['excel']['school']['schoolid'],
                                    's_sctype' => $value['sc_id'],//学校类型id
                                    's_classid' => $value['classid'],//班级id
                                    's_ownerAccount' => $member_id,
                                    's_provinceid' => $_SESSION['excel']['school']['provinceid'],
                                    's_cityid' => $_SESSION['excel']['school']['cityid'],
                                    's_areaid' => $_SESSION['excel']['school']['areaid'],
                                    's_region' => $_SESSION['excel']['school']['address'],
                                    'admin_company_id' => $_SESSION['excel']['school']['admin_company_id'],
                                    'option_id' => session('admin_id'),
                                    's_createtime' => date('Y-m-d',time()),

                                );
                                $student_id = $model_student->insertGetId($student_array);
                                if($student_id){

                                    //添加订单信息
                                    $this->_logic_buy_1 = \model('buy_1','logic');
                                    switch ($t_id){
                                        case 1://看孩套餐
                                            $pay_sn = $this->_logic_buy_1->makePaySn($member_id);
                                            //到期时间
                                            $endTime = time()+$value['L']*24*3600;
                                            $see_array = array(
                                                'pay_sn'=>$pay_sn,
                                                'buyer_id'=>intval($member_id),
                                                'buyer_name'=>trim($member['member_mobile']),
                                                'buyer_mobile'=>trim($member['member_mobile']),
                                                'add_time'=>time(),
                                                'payment_code'=>'offline',
                                                'payment_time'=>time(),
                                                'finnshed_time'=>time(),
                                                'pkg_name'=>trim($value['J']).'（线下）',
                                                'pkg_price'=>ncPriceFormatb($value['K']),
                                                'pkg_length'=>intval($value['L']),
                                                's_id'=>intval($student_id),
                                                's_name'=>trim($value['D']),
                                                'schoolid'=>intval($_SESSION['excel']['school']['schoolid']),
                                                'name'=>trim($_SESSION['excel']['school']['name']),
                                                'classid'=>intval($value['classid']),
                                                'classname'=>trim($value['I']),
                                                'order_amount'=>ncPriceFormatb($value['K']),
                                                'order_state'=>'40',
                                                'order_dieline'=>$endTime,
                                                'option_id'=>intval($_SESSION['excel']['school']['option_id']),
                                                'over_amount'=>ncPriceFormatb($value['K']),
                                                'admin_company_id'=>intval($_SESSION['excel']['school']['admin_company_id']),
                                            );
                                            $order_pay_id =$model_order->insertGetId($see_array);
                                            if($order_pay_id){
                                                $order_sn = $this->_logic_buy_1->makeOrderSn($order_pay_id);
                                                $order_pay = $model_order->where('order_id="'.$order_pay_id.'"')->update(array('order_sn'=>$order_sn));
                                                if($order_pay){
                                                    $desc = date('Y-m-d H:i',time()).'第一次购买看孩套餐,套餐到期时间:'.date('Y-m-d H:i',$endTime);
                                                    $see_end = array(
                                                        'member_id'=>intval($member_id),
                                                        'member_name'=>trim($member['member_mobile']),
                                                        's_id'=>intval($student_id),
                                                        's_name'=>trim($value['D']),
                                                        'start_time'=>time(),
                                                        'end_time'=>$endTime,
                                                        'up_time'=>time(),
                                                        'up_desc'=>$desc,
                                                    );
                                                    $order_pay_time = $model_order_time->insert($see_end);
                                                    if($order_pay_time){
                                                        $update_import = $model->where('id="'.$import_data.'"')->update(array('m_id'=>$member_id,'s_id'=>$student_id,'order_id'=>$order_pay_id));
                                                        if($update_import){
                                                            $flag = true;
                                                        }else{
                                                            $flag = false;
                                                        }
                                                    }else{
                                                        $flag = false;
                                                    }
                                                }else{
                                                    $flag = false;
                                                }
                                            }else{
                                                $flag = false;
                                            }
                                            break;
                                        case 2://重温课堂套餐
                                            break;
                                        case 3://教孩套餐
                                            break;
                                        case 4://为空
                                            $flag = true;
                                            break;
                                    }
                                }else{
                                    $flag = false;
                                }
                            }else{
                                $flag=false;
                            }
                        }else{
                            $flag = false;
                        }
                        if($flag){
                            //发送随机密码

                            //生成数字字符随机 密码
                            $sms_tpl = config('sms_tpl');
                            $tempId = $sms_tpl['sms_password_reset'];
                            $sms = new \sendmsg\Sms();
                            $pass = '您于'.date('Y-m-d H:i:s',time()).'开通想见孩账号，您的账号是:'.$member['member_mobile'].'密码是：'.$pass;
//                            $send = $sms->send($member['member_mobile'],$pass,$tempId);
//                            if($send){
                                $model->commit();
//                            }else{
//                                $model->rollback();
//                            }
                        }else{
                            $model->rollback();
                        }
//                    }
                }
            }
            //插入失败的数据
            if(!empty($excel_fail)){
                foreach($excel_fail as $key=>$value){
                    if($value['C'] == '男'){$value['C'] =1;}else{$value['C'] = 2;}//家长性别
                    if($value['E'] == '男'){$value['E'] =1;}else{$value['E'] = 2;}//学生性别
                    //生成学生身份证号
                    $s_card01 = '370213'.rand(2013,2015);
                    $s_card02 = rand(1,12);
                    if($s_card02 <10){
                        $s_card02 = '0'.$s_card02;
                    }
                    $s_card03 = rand(1,28);
                    if($s_card03 <10){
                        $s_card03 = '0'.$s_card03;
                    }
                    $s_card04 = rand(1000,9999);
                    $s_card = $s_card01.$s_card02.$s_card03.$s_card04;
                    //套餐ID
                    switch ($value['J']){
                        case '看孩套餐':
                            $t_id = 1;
                            break;
                        case '重温课堂套餐':
                            $t_id = 2;
                            break;
                        case '教孩套餐':
                            $t_id = 3;
                            break;
                        default:
                            $t_id = 4;
                            break;
                    }
                    $data = array(
                        'm_mobile' => $value['A'],
                        'm_name' => $value['B'],
                        'm_sex' => $value['C'],
                        's_name' => $value['D'],
                        's_sex' => $value['E'],
//                        's_card' => $value['F'],
                        's_card' => $s_card,
                        'school_id' => $_SESSION['excel']['school']['schoolid'],
                        'school_name' => $_SESSION['excel']['school']['name'],
                        'province_id' => $_SESSION['excel']['school']['provinceid'],
                        'city_id' => $_SESSION['excel']['school']['cityid'],
                        'area_id' => $_SESSION['excel']['school']['areaid'],
                        'address' => $_SESSION['excel']['school']['address'],
                        'sc_id' => '',
                        'school_type' => $value['H'],
                        'classid' => '',
                        'class_name' => $value['I'],
                        't_id' => $t_id,
                        't_name' => $value['J'],
                        't_price' => $value['K'],
                        't_day' => $value['L'],
                        'content' => $value['M'],
                        'status' => 2,
                        'time' => time(),
                        'trueTime' => time(),
                        'reason' => $value['error'],
                        'reason_id' => $value['error_id'],
                    );
                    $import_data = $model->insert($data);
                    if($import_data){
                        $model->commit();
                    }else{
                        $model->rollback();
                    }
                }
            }
            exit(json_encode(array('code'=>0,'msg'=>'导入成功')));
        }else{
            exit(json_encode(array('code'=>1,'msg'=>'上传的文件数据失效，请重新上传')));
        }

    }



    /**
     * @desc 摄像头 已录入管理
     * @author 郎志耀
     * @time 20180926
     */
    public function edit(){
        if(session('admin_is_super') !=1 && !in_array('3',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $id = input('get.id');
        $import = db('import_student')->where('id="'.$id.'"')->find();

        //学校
        $school_student = db('school')->field('schoolid,name')->where('isdel=1')->select();

        $this->assign('school',$school_student);
        $this->assign('import',$import);
        $this->setAdminCurItem('edit');
        return $this->fetch('edit');
    }




    /**
     * @desc 失败修改
     * @author langzhiyao
     * @time 20181114
     */
    public function failUpdate(){
        if(session('admin_is_super') !=1 && !in_array('3',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        if(!empty($_POST)){
            $id = intval(input('get.id'));
            //学校信息
            $school_info = db('school')->field('schoolid,name,provinceid,cityid,areaid,option_id,typeid,isdel,admin_company_id')->where('schoolid = "'.intval($_POST['school_id']).'" AND isdel=1')->find();
            if(empty($school_info)){
                output_error(array('msg'=>'该学校已被删除，请重新选择'));
            }
            $school_info['province'] = $this->get_address_name($school_info['provinceid']);
            $school_info['city'] = $this->get_address_name($school_info['cityid']);
            $school_info['area'] = $this->get_address_name($school_info['areaid']);
            $address = $school_info['province'].'-'.$school_info['city'].'-'.$school_info['area'];
            $school_info['address'] = $address;
            $sc_type = explode(',',$school_info['typeid']);
            //判断年级
            $is_grade = db('schooltype')->field('sc_id,sc_type')->where("`sc_id`='".intval($_POST['sc_id'])."'")->find();
            if(!$is_grade || !in_array($is_grade['sc_id'],$sc_type)){
                exit(json_encode(array('code'=>1,'msg'=>'该学校类型已被删除，请重新选择')));
            }

            $is_class = db('class')->field('classid,classname')->where("`schoolid`='".intval($school_info['schoolid'])."' AND `typeid`='".intval($is_grade['sc_id'])."' AND `classid`='".intval($_POST['class_id'])."' AND `isdel`=1")->find();
            if(!$is_class){
                exit(json_encode(array('code'=>1,'msg'=>'该学校班级已被删除，请重新选择')));
            }
            //开启事务
            $model = Model('import_student');
            $model_member = Model('member');
            $model_student = Model('student');
            $model_order = Model('packagesorder');
            $model_order_time = Model('packagetime');
            $model->startTrans();
            $flag = false;
            $t_id = $_POST['t_id'];
            //套餐ID
            switch ($t_id){
                case 1:
                    $t_name='看孩套餐';
                    break;
                case 2:
                    $t_name = '重温课堂套餐';
                    break;
                case 3:
                    $t_name = '教孩套餐';
                    break;
                default:
                    $t_name = '无套餐';
                    break;
            }
            $data = array(
                'm_mobile' => $_POST['m_mobile'],
                'm_name' => $_POST['m_name'],
                'm_sex' => $_POST['m_sex'],
                's_name' => $_POST['s_name'],
                's_sex' => $_POST['s_sex'],
                's_card' => $_POST['s_card'],
                'school_id' => $_POST['school_id'],
                'school_name' => $school_info['name'],
                'province_id' => $school_info['provinceid'],
                'city_id' => $school_info['cityid'],
                'area_id' => $school_info['areaid'],
                'address' => $school_info['address'],
                'sc_id' => $is_grade['sc_id'],
                'school_type' => $is_grade['sc_type'],
                'classid' => $is_class['classid'],
                'class_name' => $is_class['classname'],
                't_id' => $t_id,
                't_name' => $t_name,
                't_price' => $_POST['t_price'],
                't_day' => $_POST['t_day'],
                'content' => $_POST['content'],
                'status' => 1,
                'trueTime' => time(),
            );

            $import_data = $model->where('id="'.$id.'"')->update($data);
            if($import_data){
                $result = db('member')->field('member_id')->where("`member_mobile`='".trim($_POST['m_mobile'])."'")->find();
                if($result){
                    $member_id = $result['member_id'];
                }else{
                    //为家长开账户并发送短信通知
//                    $pass = getRandomString(6,null,'n');
                    $pass = '123456';
                    $member = array();
                    $member['member_name'] = empty($_POST['m_name'])?$_POST['m_mobile']:$_POST['m_name'];
                    $member['member_nickname'] = empty($_POST['m_name'])?$_POST['m_mobile']:$_POST['m_name'];
                    $member['member_password'] = md5(trim($pass));;
                    $member['member_mobile'] = $_POST['m_mobile'];
                    $member['member_provinceid'] = $school_info['provinceid'];
                    $member['member_cityid'] = $school_info['cityid'];
                    $member['member_areaid'] = $school_info['areaid'];
                    $member['member_areainfo'] = $school_info['address'];
                    $member['member_mobile_bind'] = 1;
                    $member_id = $model_member->insertGetId($member);
                }

                if ($member_id) {
                    //添加学生信息
                    $student_array=array(
                        's_name' => $_POST['s_name'],
                        's_sex' => $_POST['s_sex'],
                        's_card' => $_POST['s_card'],
                        's_schoolid' => $_POST['school_id'],
                        's_sctype' => $is_grade['sc_id'],//学校类型id
                        's_classid' => $is_class['classid'],//班级id
                        's_ownerAccount' => $member_id,
                        's_provinceid' => $school_info['provinceid'],
                        's_cityid' => $school_info['cityid'],
                        's_areaid' => $school_info['areaid'],
                        's_region' => $school_info['address'],
                        'admin_company_id' => $school_info['admin_company_id'],
                        'option_id' => session('admin_id'),
                        's_createtime' => date('Y-m-d',time()),

                    );
                    $student_id = $model_student->insertGetId($student_array);
                    if($student_id){
                        //添加订单信息
                        $this->_logic_buy_1 = \model('buy_1','logic');
                        switch ($t_id){
                            case 1://看孩套餐
                                $pay_sn = $this->_logic_buy_1->makePaySn($member_id);
                                //到期时间
                                $endTime = time()+intval($_POST['t_day'])*24*3600;
                                $see_array = array(
                                    'pay_sn'=>$pay_sn,
                                    'buyer_id'=>intval($member_id),
                                    'buyer_name'=>trim($member['member_mobile']),
                                    'buyer_mobile'=>trim($member['member_mobile']),
                                    'add_time'=>time(),
                                    'payment_code'=>'offline',
                                    'payment_time'=>time(),
                                    'finnshed_time'=>time(),
                                    'pkg_name'=>trim($t_name).'（线下）',
                                    'pkg_price'=>ncPriceFormatb($_POST['t_price']),
                                    'pkg_length'=>intval($_POST['t_day']),
                                    's_id'=>intval($student_id),
                                    's_name'=>trim($_POST['s_name']),
                                    'schoolid'=>intval($school_info['schoolid']),
                                    'name'=>trim($school_info['name']),
                                    'classid'=>intval($is_class['classid']),
                                    'classname'=>trim($is_class['classname']),
                                    'order_amount'=>ncPriceFormatb($_POST['t_price']),
                                    'order_state'=>'40',
                                    'order_dieline'=>$endTime,
                                    'option_id'=>intval($school_info['option_id']),
                                    'over_amount'=>ncPriceFormatb($_POST['t_price']),
                                    'admin_company_id'=>intval($school_info['admin_company_id']),
                                );
                                $order_pay_id =$model_order->insertGetId($see_array);
                                if($order_pay_id){
                                    $order_sn = $this->_logic_buy_1->makeOrderSn($order_pay_id);
                                    $order_pay = $model_order->where('order_id="'.$order_pay_id.'"')->update(array('order_sn'=>$order_sn));
                                    if($order_pay){
                                        $desc = date('Y-m-d H:i',time()).'第一次购买看孩套餐,套餐到期时间:'.date('Y-m-d H:i',$endTime);
                                        $see_end = array(
                                            'member_id'=>intval($member_id),
                                            'member_name'=>trim($member['member_mobile']),
                                            's_id'=>intval($student_id),
                                            's_name'=>trim($_POST['s_name']),
                                            'start_time'=>time(),
                                            'end_time'=>$endTime,
                                            'up_time'=>time(),
                                            'up_desc'=>$desc,
                                        );
                                        $order_pay_time = $model_order_time->insert($see_end);
                                        if($order_pay_time){
                                            $update_import = $model->where('id="'.$id.'"')->update(array('m_id'=>$member_id,'s_id'=>$student_id,'order_id'=>$order_pay_id));
                                            if($update_import){
                                                $flag = true;
                                            }else{
                                                $flag = false;
                                            }
                                        }else{
                                            $flag = false;
                                        }

                                    }else{
                                        $flag = false;
                                    }
                                }else{
                                    $flag = false;
                                }
                                break;
                            case 2://重温课堂套餐
                                break;
                            case 3://教孩套餐
                                break;
                            case 4://为空
                                $flag = true;
                                break;
                        }
                    }else{
                        $flag = false;
                    }
                }else{
                    $flag=false;
                }
            }else{
                $flag = false;
            }
            if($flag){
                //发送随机密码
                //生成数字字符随机 密码
                $sms_tpl = config('sms_tpl');
                $tempId = $sms_tpl['sms_password_reset'];
                $sms = new \sendmsg\Sms();
                $pass = '您于'.date('Y-m-d H:i:s',time()).'开通想见孩账号，您的账号是:'.$member['member_mobile'].'密码是：'.$pass;
                $send = $sms->send($member['member_mobile'],$pass,$tempId);
                if($send){
                    $model->commit();
                    exit(json_encode(array('code'=>200,'msg'=>'修改成功')));
                }else{
                    $model->rollback();
                    exit(json_encode(array('code'=>1,'msg'=>'修改失败')));
                }
            }else{
                $model->rollback();
                exit(json_encode(array('code'=>1,'msg'=>'修改失败')));
            }
        }
    }


    /**
     * @desc 摄像头 已录入管理
     * @author 郎志耀
     * @time 20180926
     */
    public function editSuccess(){
        if(session('admin_is_super') !=1 && !in_array('3',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $id = input('get.id');
        $import = db('import_student')->where('id="'.$id.'"')->find();

        //学校
        $school_student = db('school')->field('schoolid,name')->where('isdel=1')->select();

        $this->assign('school',$school_student);
        $this->assign('import',$import);
        $this->setAdminCurItem('editSuccess');
        return $this->fetch('editSuccess');
    }
    /**
     * @desc 成功修改
     * @author langzhiyao
     * @time 20181114
     */
    public function successUpdate(){
        if(session('admin_is_super') !=1 && !in_array('3',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        if(!empty($_POST)){
            $id = intval(input('get.id'));
            //学校信息
            $school_info = db('school')->field('schoolid,name,provinceid,cityid,areaid,option_id,typeid,isdel,admin_company_id')->where('schoolid = "'.intval($_POST['school_id']).'" AND isdel=1')->find();
            if(empty($school_info)){
                exit(json_encode(array('code'=>1,'msg'=>'该学校已被删除，请重新选择')));
            }
            $school_info['province'] = $this->get_address_name($school_info['provinceid']);
            $school_info['city'] = $this->get_address_name($school_info['cityid']);
            $school_info['area'] = $this->get_address_name($school_info['areaid']);
            $address = $school_info['province'].'-'.$school_info['city'].'-'.$school_info['area'];
            $school_info['address'] = $address;
            $sc_type = explode(',',$school_info['typeid']);
            //判断年级
            $is_grade = db('schooltype')->field('sc_id,sc_type')->where("`sc_id`='".intval($_POST['sc_id'])."'")->find();
            if(!$is_grade || !in_array($is_grade['sc_id'],$sc_type)){
                exit(json_encode(array('code'=>1,'msg'=>'该学校类型已被删除，请重新选择')));
            }

            $is_class = db('class')->field('classid,classname')->where("`schoolid`='".intval($school_info['schoolid'])."' AND `typeid`='".intval($is_grade['sc_id'])."' AND `classid`='".intval($_POST['class_id'])."' AND `isdel`=1")->find();
            if(!$is_class){
                exit(json_encode(array('code'=>1,'msg'=>'该学校班级已被删除，请重新选择')));
            }
            //开启事务
            $model = Model('import_student');
            $model_member = Model('member');
            $model_student = Model('student');
            $model_order = Model('packagesorder');
            $model_order_time = Model('packagetime');
            $model->startTrans();
            $flag = false;
            //获取m_id和s_id
            $m_s = $model->field('m_id,s_id,order_id,t_day')->where('id="'.$id.'"')->find();
            $t_id = $_POST['t_id'];
            //套餐ID
            switch ($t_id){
                case 1:
                    $t_name='看孩套餐';
                    break;
                case 2:
                    $t_name = '重温课堂套餐';
                    break;
                case 3:
                    $t_name = '教孩套餐';
                    break;
                default:
                    $t_name = '无套餐';
                    break;
            }
            $data = array(
                'm_mobile' => $_POST['m_mobile'],
                'm_name' => $_POST['m_name'],
                'm_sex' => $_POST['m_sex'],
                's_name' => $_POST['s_name'],
                's_sex' => $_POST['s_sex'],
                's_card' => $_POST['s_card'],
                'school_id' => $_POST['school_id'],
                'school_name' => $school_info['name'],
                'province_id' => $school_info['provinceid'],
                'city_id' => $school_info['cityid'],
                'area_id' => $school_info['areaid'],
                'address' => $school_info['address'],
                'sc_id' => $is_grade['sc_id'],
                'school_type' => $is_grade['sc_type'],
                'classid' => $is_class['classid'],
                'class_name' => $is_class['classname'],
                't_id' => $t_id,
                't_name' => $t_name,
                't_price' => $_POST['t_price'],
                't_day' => $_POST['t_day'],
                'content' => $_POST['content'],
                'status' => 1,
                'trueTime' => time(),
            );
            $import_data = $model->where('id="'.$id.'"')->update($data);
            if($import_data){
                if($m_s['m_id']){
                    //为家长开账户并发送短信通知
                    $pass = getRandomString(6,null,'n');
                    $member = array();
                    $member['member_name'] = empty($_POST['m_name'])?$_POST['m_mobile']:$_POST['m_name'];
                    $member['member_nickname'] = empty($_POST['m_name'])?$_POST['m_mobile']:$_POST['m_name'];
                    $member['member_password'] = md5(trim($pass));
                    $member['member_mobile'] = $_POST['m_mobile'];
                    $member['member_provinceid'] = $school_info['provinceid'];
                    $member['member_cityid'] = $school_info['cityid'];
                    $member['member_areaid'] = $school_info['areaid'];
                    $member['member_areainfo'] = $school_info['address'];
                    $member['member_mobile_bind'] = 1;
                    $member_id = $model_member->where('member_id="'.$m_s['m_id'].'"')->update($member);
                    if ($member_id) {
                        //添加学生信息
                        $student_array=array(
                            's_name' => $_POST['s_name'],
                            's_sex' => $_POST['s_sex'],
                            's_card' => $_POST['s_card'],
                            's_schoolid' => $_POST['school_id'],
                            's_sctype' => $is_grade['sc_id'],//学校类型id
                            's_classid' => $is_class['classid'],//班级id
                            's_ownerAccount' => $m_s['m_id'],
                            's_provinceid' => $school_info['provinceid'],
                            's_cityid' => $school_info['cityid'],
                            's_areaid' => $school_info['areaid'],
                            's_region' => $school_info['address'],
                            'admin_company_id' => $school_info['admin_company_id'],
                            'option_id' => session('admin_id'),
                            's_createtime' => date('Y-m-d',time()),
                        );
                        $student_id = $model_student->where('s_id="'.$m_s['s_id'].'"')->update($student_array);
                        if($student_id){
                            //添加订单信息
                            $this->_logic_buy_1 = \model('buy_1','logic');
                            switch ($t_id){
                                case 1://看孩套餐
                                    $pay_sn = $this->_logic_buy_1->makePaySn($m_s['m_id']);
                                    $order_endTime =$model_order->field('order_dieline')->where('order_id="'.$m_s['order_id'].'"')->find();
                                    //到期时间
                                    if($m_s['t_day'] > $_POST['t_day']){
                                        //原本的给多了 修改
                                        $endTime = $order_endTime['order_dieline']-(intval($m_s['t_day'])-intval($_POST['t_day']))*24*3600;
                                    }else if($m_s['t_day'] < $_POST['t_day']){
                                        $endTime = $order_endTime['order_dieline']+(intval($_POST['t_day'])-intval($m_s['t_day']))*24*3600;
                                    }else{
                                        $endTime = $order_endTime['order_dieline'];
                                    }
                                    $see_array = array(
                                        'pay_sn'=>$pay_sn,
                                        'buyer_id'=>intval($m_s['m_id']),
                                        'buyer_name'=>trim($member['member_mobile']),
                                        'buyer_mobile'=>trim($member['member_mobile']),
                                        'add_time'=>time(),
                                        'payment_code'=>'offline',
                                        'payment_time'=>time(),
                                        'finnshed_time'=>time(),
                                        'pkg_name'=>trim($t_name).'（线下）',
                                        'pkg_price'=>ncPriceFormatb($_POST['t_price']),
                                        'pkg_length'=>intval($_POST['t_day']),
                                        's_id'=>intval($m_s['s_id']),
                                        's_name'=>trim($_POST['s_name']),
                                        'schoolid'=>intval($school_info['schoolid']),
                                        'name'=>trim($school_info['name']),
                                        'classid'=>intval($is_class['classid']),
                                        'classname'=>trim($is_class['classname']),
                                        'order_amount'=>ncPriceFormatb($_POST['t_price']),
                                        'order_state'=>'40',
                                        'order_dieline'=>$endTime,
                                        'option_id'=>intval($school_info['option_id']),
                                        'over_amount'=>ncPriceFormatb($_POST['t_price']),
                                        'admin_company_id'=>intval($school_info['admin_company_id']),
                                    );
                                    $order_pay_id =$model_order->where('order_id="'.$m_s['order_id'].'"')->update($see_array);
                                    if($order_pay_id){
                                        $order_sn = $this->_logic_buy_1->makeOrderSn($m_s['order_id']);
                                        $order_pay = $model_order->where('order_id="'.$m_s['order_id'].'"')->update(array('order_sn'=>$order_sn));
                                        if($order_pay){
                                            $order_time = $model_order_time->field('up_desc')->where('s_id="'.$m_s['s_id'].'"')->find();
                                            $desc = $order_time['up_desc'].'&'.date('Y-m-d H:i',time()).'修改看孩套餐,套餐到期时间:'.date('Y-m-d H:i',$endTime);
                                            $see_end = array(
                                                'member_id'=>intval($m_s['m_id']),
                                                'member_name'=>trim($member['member_mobile']),
                                                's_id'=>intval($m_s['s_id']),
                                                's_name'=>trim($_POST['s_name']),
                                                'start_time'=>time(),
                                                'end_time'=>$endTime,
                                                'up_time'=>time(),
                                                'up_desc'=>$desc,
                                            );
                                            $order_pay_time = $model_order_time->where('s_id="'.$m_s['s_id'].'"')->update($see_end);
                                            if($order_pay_time){
                                                $flag = true;
                                            }else{
                                                $flag = false;
                                            }

                                        }else{
                                            $flag = false;
                                        }
                                    }else{
                                        $flag = false;
                                    }
                                    break;
                                case 2://重温课堂套餐
                                    break;
                                case 3://教孩套餐
                                    break;
                                case 4://为空
                                    $flag = true;
                                    break;
                            }
                        }else{
                            $flag = false;
                        }
                    }else{
                        $flag=false;
                    }
                }else{
                    $flag = false;
                }
            }else{
                $flag = false;
            }
            if($flag){
                //发送随机密码
                //生成数字字符随机 密码
                $sms_tpl = config('sms_tpl');
                $tempId = $sms_tpl['sms_password_reset'];
                $sms = new \sendmsg\Sms();
                $pass = '您于'.date('Y-m-d H:i:s',time()).'修改想见孩账号信息，您的新账号是:'.$member['member_mobile'].'密码是：'.$pass;
                $send = $sms->send($member['member_mobile'],$pass,$tempId);
                if($send){
                    $model->commit();
                    exit(json_encode(array('code'=>200,'msg'=>'修改成功')));
                }else{
                    $model->rollback();
                    exit(json_encode(array('code'=>1,'msg'=>'修改失败')));
                }
            }else{
                $model->rollback();
                exit(json_encode(array('code'=>1,'msg'=>'修改失败')));
            }
        }
    }


    /**
     * @desc 根据ID 获取地址名称
     * @author langzhiyao
     * @time 20180928
     */
    function get_address_name($id){

        $address_info = db('area')->field('area_id,area_name')->where('area_id= '.$id.'')->find();

        return $address_info['area_name'];

    }


    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '导入失败列表',
                'url' => url('Office/import/index')
            ),
            array(
                'name' => 'successin',
                'text' => '导入成功列表',
                'url' => url('Office/import/successin')
            )
        );
        return $menu_array;
    }


}