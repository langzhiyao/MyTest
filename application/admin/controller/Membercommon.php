<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Membercommon extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/member.lang.php');
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $member_id=input('member_id');
        $menu_array = array(
            array(
                'name' => 'MemberInfo',
                'text' => '个人资料',
                'url' => url('Admin/Membercommon/MemberInfo',array('member_id'=>$member_id))
            ),
            array(
                'name' => 'RelationMember',
                'text' => '关联账号',
                'url' => url('Admin/Membercommon/RelationMember',array('member_id'=>$member_id))
            ),
            array(
                'name' => 'ChildrenBind',
                'text' => '绑定学生',
                'url' => url('Admin/Membercommon/ChildrenBind',array('member_id'=>$member_id))
            ),
            array(
                'name' => 'ChildrenCamera',
                'text' => '班级摄像头',
                'url' => url('Admin/Membercommon/ChildrenCamera',array('member_id'=>$member_id))
            ),
            array(
                'name' => 'ChildrenOrders',
                'text' => '订单信息',
                'url' => url('Admin/Membercommon/ChildrenOrders',array('member_id'=>$member_id))
            ),
            array(
                'name' => 'MemberCapital',
                'text' => '资金记录',
                'url' => url('Admin/Membercommon/MemberCapital',array('member_id'=>$member_id))
            ),
        );
        return $menu_array;
    }

    /**
     * 会员信息详情查看
     * @创建时间 2018-12-07T14:50:31+0800
     */
    public function MemberInfo()
    {
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID(intval(input('member_id')));
        // p($member_info);
        $this->assign($member_info);
        $this->setAdminCurItem('MemberInfo');
        return $this->fetch();
    }

    /**
     * 关联账号
     * @创建时间 2018-12-07T14:44:34+0800
     */
    public function RelationMember(){
        $member_id = intval(input('member_id'));
        $member = db('member')->field('member_id,member_paypwd,is_owner')->where('member_id',$member_id)->find();
        if($member['is_owner'] == 0){
            $where = ' is_owner = "'.$member_id.'"';
        }else{
            $where = ' (is_owner = "'.$member['is_owner'].'" OR member_id="'.$member['is_owner'].'") AND member_id != "'.$member_id.'"';
        }
        $account = db('member')->field('member_id,member_name,member_aboutname,member_mobile,is_owner')->where($where)->select();
        $this->assign('list',$account);
        $this->setAdminCurItem('RelationMember');
        return $this->fetch();
    }

    /**
     * 绑定孩子信息
     * @创建时间 2018-12-07T14:49:50+0800
     */
    public function ChildrenBind(){
        $member_id = intval(input('member_id'));
        $member_info = db('member')->where(array("member_id"=>$member_id))->find();
        $member = $member_info['is_owner']==0 ? $member_id : $member_info['is_owner'];
        $childs = db('student')->alias('s')
            ->join('__SCHOOL__ sc','sc.schoolid=s.s_schoolid','LEFT')
            ->join('__CLASS__ cl','cl.classid=s.s_classid','LEFT')
            ->field("s.s_id,s.s_name,s.s_sex,s.s_card,s.s_birthday,s.s_schoolid,sc.name as schoolname,sc.region,sc.typeid,s.s_classid,cl.classname,cl.classCard")
            ->where(array('s_del'=>1,'s_ownerAccount'=>$member))->select();
        $this->assign('childs',$childs);
        $this->setAdminCurItem('ChildrenBind');
        return $this->fetch();
    }

    /**
     * 孩子班级摄像头信息
     * @创建时间 2018-12-07T14:49:36+0800
     */
    public function ChildrenCamera(){
        $member_id = intval(input('member_id'));
        $member_info = db('member')->where(array("member_id"=>$member_id))->find();
        $member = $member_info['is_owner']==0 ? $member_id : $member_info['is_owner'];
        $childs = db('student')->alias('s')
            ->join('__SCHOOL__ sc','sc.schoolid=s.s_schoolid','LEFT')
            ->join('__CLASS__ cl','cl.classid=s.s_classid','LEFT')
            ->field('s.s_id,s.s_name,s.s_region,sc.schoolid,sc.name,sc.res_group_id,cl.classid,cl.classname,cl.classCard,cl.res_group_id as clres_group_id')
            ->where(array('s_del'=>1,'s_ownerAccount'=>$member))->select();
        $this->assign('childs',$childs);
        // p($childs);exit;
        if($member_id){
            $member_info = db("member")->where(array("member_id"=>$member_id))->find();
            $member_id = $member_info['is_owner']!=0 ? $member_info['is_owner'] : $member_id;
        }

        $limit = input('limit');
        if ($limit) {
            $page = input('page');
            $sid = input('s_id');
            $childInfo = db('student')->alias('s')
                            ->join('__SCHOOL__ sc','sc.schoolid=s.s_schoolid','LEFT')
                            ->join('__CLASS__ cl','cl.classid=s.s_classid','LEFT')
                            ->field('s.s_id,s.s_name,sc.schoolid,sc.name,sc.res_group_id as school_group_id,cl.classid,cl.classname,cl.classCard,cl.res_group_id as class_group_id')
                            ->where(array('s.s_del'=>1,'s.s_id'=>$sid))->find();
            $gid=[];
            if ($childInfo['school_group_id'])array_push($gid,$childInfo['school_group_id']);
            if ($childInfo['class_group_id'])array_push($gid,$childInfo['class_group_id']);
            if (!$gid)exit(json_encode(array('code' =>1,'msg'=>'该学生所在学校班级未绑定摄像头！')));

            $list = db('camera')->where('parentid','in',$gid)->paginate($page,false,['query' => request()->param()]);
            $list= $list->items();
            $date=date('H:i',time());
            foreach($list as $k=>$v){
                if($v['online']==0){
                    $list[$k]['statuses']=2;
                }else{
                    if($v['status']==1){
                        if(!empty($v['begintime'])&&!empty($v['endtime'])){
                            $begintime=date('H:i',$v['begintime']);
                            $endtime=date('H:i',$v['endtime']);
                            if($date<$begintime||$date>$endtime){
                                $list[$k]['statuses']=2;
                            }else{
                                $list[$k]['statuses']=1;
                            }
                        }else{
                            $list[$k]['statuses']=1;
                        }
                    }else{
                        $list[$k]['statuses']=2;
                    }
                }
            }
            //return $list;exit;
            $list_count = db('camera')->where('parentid','in',$gid)->count();
            $html = '';
            if(!empty($list)){
                foreach($list as $key=>$v){
                    if($v['statuses'] == 1){
                        $list[$key]['statusess']= '<b style="color:green;">在线</b>';
                    }else if($v['statuses'] == 2){
                        $list[$key]['statusess']= '<b style="color:red;">离线</b>';
                    }
                    $datainfo = json_encode($v);
                    $list[$key]['clic']="<a datainfo='".$datainfo."' id='rmt_" . $v['cid'] . "' href='javascript:;' onClick='rtmplay(" . $v['cid'] . ")'><img height='25px' src='/static/admin/images/doplayer.png'></a>";
                    if($v['is_classroom'] == 1){
                        $list[$key]['is_classrooms']= '<b style="color:red;">否</b>';
                    }else if($v['is_classroom'] == 2){
                        $list[$key]['is_classrooms']= '<b style="color:green;">是</b>';
                    }
                    if($v['status'] == 1){
                        $list[$key]['statuss']= '开启';
                    }else if($v['status'] == 2){
                        $list[$key]['statuss']= '关闭';
                    }
                    $list[$key]["sq_time"] = date('Y-m-d H:i:s',$v["sq_time"]);
                    if(!empty($v['begintime'])){
                        $list[$key]["begintime"] =date('H:i',$v["begintime"]);
                    }else{
                        $list[$key]["begintime"] ='未设置';
                    }
                    if(!empty($v['endtime'])){
                        $list[$key]["endtime"] =date('H:i',$v["endtime"]);
                    }else{
                        $list[$key]["endtime"] ='未设置';
                    }
                }
            }
            exit(json_encode(array('code' => '0', 'count' => $list_count,'data'=>$list,'msg'=>'')));
        }
        
        $this->setAdminCurItem('ChildrenCamera');
        return $this->fetch();
    }
    
    /**
     * 用户订单信息
     * @创建时间 2018-12-07T14:49:25+0800
     */
    public function ChildrenOrders(){
        $member_id = intval(input('member_id'));
        $OrderType = input('OrderType');
        if ($OrderType) {
            $limit = input('limit',30);
            switch ($OrderType) {
                case 'witch'://看孩订单
                    $witchWhere = [
                        'buyer_id' => $member_id,
                        'delete_state' =>0,
                        'pkg_type' => 1//1为看孩套餐
                    ];
                    $order = db('packagesorder')->field('pkg_name,s_id,add_time,order_state,order_amount,order_dieline,pkg_length,pkg_axis,FROM_UNIXTIME(add_time,\'%Y-%m-%d\') as starTime,FROM_UNIXTIME(order_dieline,\'%Y-%m-%d\') as endTime')->where($witchWhere)->order('order_id DESC')->paginate($limit,false,['var_page'=>'page']);
                    $count = $order->total();
                    $order = $order->items();
                    break;
                case 'teach'://教孩订单
                    $teachWhere = [
                        'buyer_id' => $member_id,
                        'delete_state' =>0,
                        // 'pkg_type' => 1
                    ];
                    $order = db('packagesorderteach')->field('order_name,order_tid,add_time,order_state,order_amount,order_state,order_dieline,FROM_UNIXTIME(add_time,\'%Y-%m-%d\') as starTime,FROM_UNIXTIME(order_dieline,\'%Y-%m-%d\') as endTime')->where($teachWhere)->order('order_id DESC')->paginate($limit,false,['var_page'=>'page']);
                    $count = $order->total();
                    $order = $order->items();
                    break;
                case 'rewitch'://重温课堂
                    $rewitchWhere = [
                        'buyer_id' => $member_id,
                        'delete_state' =>0,
                        'pkg_type' => 2 //2为回顾套餐
                    ];
                    $result = db('packagesorder')->field('pkg_name,s_id,add_time,order_state,order_amount,order_dieline,pkg_length,pkg_axis,FROM_UNIXTIME(add_time,\'%Y-%m-%d\') as starTime,FROM_UNIXTIME(order_dieline,\'%Y-%m-%d\') as endTime')->where($witchWhere)->order('order_id DESC')->paginate($limit,false,['var_page'=>'page']);
                    
                    $order = [];
                    $count = 0;
                    break;
                case 'shoporder'://商城订单
                    $order=$this->order_list($member_id);
                    $count = $order['count'];
                    $order = $order['list'];
                    $newOrder =[];
                    foreach ($order as $key => $v) {
                        $newOrder[$key]['goods_name'] = $v['order_list'][0]['extend_order_goods'][0]['goods_name'];
                        $newOrder[$key]['goods_num'] = $v['order_list'][0]['extend_order_goods'][0]['goods_num'];
                        $newOrder[$key]['state_desc'] = $v['order_list'][0]['state_desc'];
                        $newOrder[$key]['payment_name'] = $v['order_list'][0]['payment_name'];
                        $newOrder[$key]['add_time'] = date('Y-m-d H:i:s',$v['order_list'][0]['add_time']);
                        $newOrder[$key]['payment_time'] =empty($v['order_list'][0]['payment_time'])?'无': date('Y-m-d H:i:s',$v['order_list'][0]['payment_time']);
                        $newOrder[$key]['pay_amount'] = $v['pay_amount'];
                        $newOrder[$key]['order_sn'] = $v['order_list'][0]['order_sn'];
                    }
                    exit(json_encode(array('code' => '0', 'count' => $count,'data'=>$newOrder,'msg'=>'')));
                    break;
            }
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
                if (isset($value['add_time']))$order[$key]['add_time']=Fomat($value['add_time']);
                if (isset($value['order_state'])) {
                    if($value['order_state']==10)$order[$key]['order_state'] = "待支付";
                    if($value['order_state']==20)$order[$key]['order_state'] = "已支付";
                    if($value['order_state']==40)$order[$key]['order_state'] = "已完成";
                }
                if($value['endTime']=='1970-01-01')$order[$key]['endTime'] = "无";

            }
            exit(json_encode(array('code' => '0', 'count' =>$count,'data'=>$order,'msg'=>'')));
        }

        $this->setAdminCurItem('ChildrenOrders');
        return $this->fetch();
    }

    /**
     * 用户资金记录
     * @创建时间 2018-12-07T14:49:14+0800
     */
    public function MemberCapital(){
        $member_id = intval(input('member_id'));
        $input = input();
        if (input('t')) {
            $condition = array();
            $condition['lg_member_id'] = $member_id;
            if (isset($input['desc']) && !empty($input['desc'])) {
                $condition['lg_desc'] = ['like','%'.$input['desc'].'%'];
            }
            if (isset($input['timearund']) && !empty($input['timearund'])) {
                $timearund =trim(str_replace(' ', '', input('timearund')))  ;
                $timearund = explode('/', $timearund);
                $condition['lg_add_time'] = ['between',[$timearund[0],$timearund[1]]];
            }
            $model_pd = Model('predeposit');
            $list = $model_pd->getPdLogList($condition, 10, '*', 'lg_id desc');
            foreach ($list as $key => $v) {
                $list[$key]['lg_add_time']= date('Y-m-d H:i:s',$v['lg_add_time']);
                $availableFloat = (float) $v['lg_av_amount']; 
                if ($availableFloat < 0) {
                    $list[$key]['lg_av_amountout'] = "<sapn style='color: red;'>".$v['lg_av_amount']."</sapn>";
                    $list[$key]['lg_av_amountin']=0;
                } elseif ($availableFloat > 0) {
                    $list[$key]['lg_av_amountin'] = "<sapn style='color: green;'>".$v['lg_av_amount']."</sapn>";
                    $list[$key]['lg_av_amountout']=0;
                } else {
                    $list[$key]['lg_av_amount'] = "<sapn></sapn>";
                }
                $list[$key]['lg_freeze_amount'] = floatval($v['lg_freeze_amount']) ? (floatval($v['lg_freeze_amount']) > 0 ? '+' : null ).$v['lg_freeze_amount'] : null;;
            }
            exit(json_encode(array('code' => '0', 'count' => count($list),'data'=>$list,'msg'=>'')));
        }
        
        $banks = db('banks')->where(['member_id'=>$member_id,'is_default'=>1])->find();
        $memberInfo = db('member')->where(['member_id'=>$member_id])->find();
        $this->assign( $banks);
        $this->assign($memberInfo);
        $this->assign('list', $list);
        $this->setAdminCurItem('MemberCapital');
        return $this->fetch();
    }

    /**
     * 商城订单
     * @创建时间   2018-12-12T17:19:10+0800
     * @param  [type]                   $member_id [description]
     * @return [type]                              [description]
     */
    public function order_list($member_id) {
        $model_order = Model('order');
        $condition = array();
        $condition['buyer_id'] = $member_id;
        $page = intval(input('page',1));
        $limit = intval(input('limit'));
        $order_list_array = $model_order->getOrderListForAdmin($condition,$page,$limit,array('order_goods'));
        $count = $order_list_array['count'];
        $order_list_array = $order_list_array['list'];
        $order_group_list = $order_pay_sn_array = array();
        foreach ($order_list_array as $value) {
            //显示取消订单
            $value['if_cancel'] = $model_order->getOrderOperateState('buyer_cancel', $value);
            //显示收货
            $value['if_receive'] = $model_order->getOrderOperateState('receive', $value);
            //显示锁定中
            $value['if_lock'] = $model_order->getOrderOperateState('lock', $value);
            //显示物流跟踪
            $value['if_deliver'] = $model_order->getOrderOperateState('deliver', $value);

            //商品图
            if (isset($value['extend_order_goods'])) {
                foreach ($value['extend_order_goods'] as $k => $goods_info) {

                    if ($goods_info['goods_type'] == 5) {
                        unset($value['extend_order_goods'][$k]);
                    }else {
                        $value['extend_order_goods'][$k] = $goods_info;
                        $value['extend_order_goods'][$k]['goods_image_url'] = cthumb($goods_info['goods_image'], 240, $value['store_id']);
                    }
                }
            }
            $order_group_list[$value['pay_sn']]['order_list'][] = $value;
            //如果有在线支付且未付款的订单则显示合并付款链接
            if ($value['order_state'] == ORDER_STATE_NEW) {
                if(!isset($order_group_list[$value['pay_sn']]['pay_amount'])){
                    $order_group_list[$value['pay_sn']]['pay_amount'] = 0;
                }
                $order_group_list[$value['pay_sn']]['pay_amount'] += $value['order_amount'] - $value['rcb_amount'] - $value['pd_amount'];
            }
            $order_group_list[$value['pay_sn']]['add_time'] = $value['add_time'];

            //记录一下pay_sn，后面需要查询支付单表
            $order_pay_sn_array[] = $value['pay_sn'];
        }

        $new_order_group_list = array();
        foreach ($order_group_list as $key => $value) {
            $value['pay_sn'] = strval($key);
            $new_order_group_list[] = $value;
        }
        $orders= ['list'=>$new_order_group_list,'count'=>$count];
        return $orders;
        output_data(array('order_group_list' => $new_order_group_list), mobile_page($model_order->page_info));
    }


    public function Export_step(){
        echo '已关闭该功能！';
        exit;
        $student = db('import_student')->where('id','gt',24)->select();
        $this->createExcel($student);
    }

     /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel($data = array()) {
        $excel_obj = new \excel\Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id' => 's_title', 'Font' => array('FontName' => '宋体', 'Size' => '12', 'Bold' => '1')));
        //header

        $excel_data[0][] = array('styleid' => 's_title', 'data' => "学籍号");
        // $excel_data[0][] = array('styleid' => 's_title', 'data' => "学号");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "家长手机号");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "家长姓名（非必填）");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "家长性别（非必填）");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "学生姓名");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "学生性别（非必填）");
        // $excel_data[0][] = array('styleid' => 's_title', 'data' => "学生身份证号");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "所在学校（名称）");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "学校类型");
        $excel_data[0][] = array('styleid' => 's_title', 'data' => "所在班级");
        //data
        foreach ((array) $data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => $v['s_id']);
            // $tmp[] = array('data' => $v['s_id']);
            $tmp[] = array('data' => MobileFormat($v['m_mobile']));
            $tmp[] = array('data' => MobileFormat($v['m_name']));
            $tmp[] = array('data' => $v['m_sex']==1?'男':'女');
            $tmp[] = array('data' => $v['s_name']);
            $tmp[] = array('data' => $v['s_sex']==1?'男':'女');
            // $tmp[] = array('data' => CardFomat($v['s_card']));
            $tmp[] = array('data' => $v['school_name']);
            $tmp[] = array('data' => $v['school_type']);
            $tmp[] = array('data' => $v['class_name']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset("学生信息", CHARSET));
        $excel_obj->generateXML($excel_obj->charset("学生信息", CHARSET) . '-' . date('Y-m-d-H', time()));
    }

}

?>
