<?php
namespace app\wap\controller;

use think\Lang;
use think\Model;
use think\Validate;

class Memberwallet extends MobileMember
{

    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'wap\lang\zh-cn\login.lang.php');
    }

    /**
     * 获取钱包余额
     */
    function wallet(){
        $member_id = input('post.member_id');
        if(empty($member_id)){
            output_error('会员id不能为空');
        }
        $member_Model = Model("Member");
        $data = $member_Model->getMemberInfo(array('member_id'=>$member_id),"member_id,available_predeposit");
        if(empty($data)){
            output_error('无此会员');
        }
        if(!empty($data['available_predeposit'])){
            $data['available_predeposit'] = sprintf("%.2f",$data['available_predeposit']);
        }
        output_data($data);
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

    /**
     * 获取可提现金额
     * @创建时间 2018-12-06T11:59:39+0800
     */
    public function GetAvailableCash(){
        $self = array(
            'available_predeposit' =>ncPriceFormatb($this->member_info['available_predeposit']),
            'freeze_predeposit' =>ncPriceFormatb($this->member_info['freeze_predeposit']),
        );
        output_data($self);
    }

    /**
     * 申请提现
     * @创建时间   2018-12-06T15:32:06+0800
     * @return [type]                   [description]
     */
    public function StartAddCash() {
        $member_id = $this->member_info['member_id'];
        if (request()->isPost()) {
            $obj_validate = new Validate();
            $pdc_amount=abs(floatval(input('pdc_amount')));
            if(!$pdc_amount) output_error('提现金额不能为空或至少大于0！');
            $bid = intval(input('post.bid'));
            if (empty($bid) || $bid < 1) output_error('参数错误！');
            $Bank = model('Banks');
            $bankInfo = $Bank->getOneBanksByCard(array('member_id'=>$member_id,'bank_id'=>$bid));
            if(empty($bankInfo))output_error('此银行卡信息已被删除，或参数错误！');
            $data=[
                'pdc_amount' =>$pdc_amount,
                'pdc_bank_name'  =>$bankInfo['bank_name'].$bankInfo['bank_info'],
                'pdc_bank_no'  =>$bankInfo['bank_card'],
                'pdc_bank_user'  =>$bankInfo['true_name'],
                'password'      =>input("password")
            ];
            $rule=[
                ['pdc_amount','require|min:0.01','提现金额为大于或者等于0.01的数字'],
                ['pdc_bank_name','require','请填写收款银行'],
                ['pdc_bank_no','require','请填写收款账号'],
                ['pdc_bank_user','require','请填写收款人姓名'],
                ['password','require','请输入支付密码']
            ];

            $error = $obj_validate->check($data,$rule);
            if (!$error) output_error($obj_validate->getError());

            $model_pd = Model('predeposit');
            $model_member = Model('member');
            $member_info = $model_member->getMemberInfoByID($member_id);
            //验证支付密码
            if (md5(input('password')) != $member_info['member_paypwd']) output_error('支付密码错误！');
            //验证金额是否足够
            if (floatval($member_info['available_predeposit']) < $pdc_amount) output_error('预存款金额不足！');
            try {
                $model_pd->startTrans();
                $pdc_sn = $model_pd->makeSn();
                $data = array();
                $data['pdc_sn'] = $pdc_sn;
                $data['pdc_member_id'] = $member_info['member_id'];
                $data['pdc_member_name'] = $member_info['member_name'];
                $data['pdc_amount'] = $pdc_amount;
                $data['pdc_bank_name'] = $bankInfo['bank_name'].$bankInfo['bank_info'];
                $data['pdc_bank_no'] = $bankInfo['bank_card'];
                $data['pdc_bank_user'] = $bankInfo['true_name'];
                $data['pdc_add_time'] = TIMESTAMP;
                $data['pdc_payment_state'] = 0;
                $data['available_predeposit'] = $member_info['available_predeposit']-$pdc_amount;//记录余额
                $insert = $model_pd->addPdCash($data);
                if (!$insert) output_error('提现申请失败！');
                //冻结可用预存款
                $data = array();
                $data['member_id'] = $member_info['member_id'];
                $data['member_name'] = $member_info['member_name'];
                $data['amount'] = $pdc_amount;
                $data['order_sn'] = $pdc_sn;
                $model_pd->changePd('cash_apply', $data);
                $model_pd->commit();
                output_data(array('state'=>'ok','msg'=>'您的提现申请已成功提交，请等待系统处理！'));
            } catch (Exception $e) {
                $model_pd->rollback();
                output_error($e->getMessage());
            }
        }
    }

    /**
     * 统一发送身份验证码
     */
    public function send_auth_code()
    {
        $type = input('param.type');
        if (!in_array($type, array('email', 'mobile')))
            exit();

        $model_member = Model('member');
        $verify_code = rand(100, 999) . rand(100, 999);
        $data = array();
        $data['auth_code'] = $verify_code;
        $data['send_acode_time'] = time();
        $update = $model_member->editMemberCommon($data,array('member_id' =>$this->member_info['member_id']));

        if (!$update) {
            exit(json_encode(array('state' => 'false', 'msg' => '系统发生错误，如有疑问请与管理员联系')));
        }

        $model_tpl = Model('mailtemplates');
        $tpl_info = $model_tpl->getTplInfo(array('code' => 'authenticate'));

        $param = array();
        $param['send_time'] = date('Y-m-d H:i', TIMESTAMP);
        $param['verify_code'] = $verify_code;
        $param['site_name'] = config('site_name');
        $message = ncReplaceText($tpl_info['content'], $param);
        $sms = new \sendmsg\Sms();
        $result = $sms->send($this->member_info["member_mobile"], $message);
        if ($result) {
            exit(json_encode(array('state' => 'true', 'msg' => '验证码已发出，请注意查收')));
        }
        else {
            exit(json_encode(array('state' => 'false', 'msg' => '验证码发送失败')));
        }
    }

    
    /**
     * 获取默认银行卡信息
     * @创建时间 2018-12-05T10:42:07+0800
     */
    public function GetDefaultMemberCard(){
        $member_id = $this->member_info['member_id'];
        $Bank = model('Banks');
        $bank = $Bank->getOneBanksByCard(array('member_id'=>$member_id,'is_default'=>1));
        output_data($bank);
        
    }

    /**
     * 获取单条银行卡信息
     * @创建时间 2018-12-05T10:42:07+0800
     */
    public function GetOneMemberCard(){
        $member_id = $this->member_info['member_id'];
        $bid = trim(input('post.bid'));
        if (empty($bid) || $bid < 1) output_error('参数错误！');
        $Bank = model('Banks');
        $bank = $Bank->getOneBanksByCard(array('member_id'=>$member_id,'bank_id'=>$bid));
        if (!$bank) {
            $bank = $Bank->getOneBanksByCard(array('member_id'=>$member_id));
        }
        output_data($bank);
    }

    /**
     * 银行卡列表-所有
     * @创建时间 2018-12-04T14:51:15+0800
     */
    public function MemberBankCards(){
        $member_id = $this->member_info['member_id'];
        $Bank = model('Banks');
        $result=[];
        $CardList = $Bank->getAllBanks(array('member_id'=>$member_id));
        $result['count'] = count($CardList);
        $result['CardList'] = $CardList;
        output_data($result);
    }

    /**
     * 添加银行卡
     * @创建时间 2018-12-04T14:51:23+0800
     */
    public function AddMemberBankCard(){
        $member_id = $this->member_info['member_id'];
        $bankCard = trim(input('post.bank_card'));
        if (empty($bankCard)) output_error('银行卡号不能为空');
        $true_name = trim(input('post.true_name'));
        if (empty($true_name)) output_error('持卡人姓名不能为空');
        $bank_name = trim(input('post.bank_name'));
        if (empty($bank_name))output_error('银行名称不能为空');
        $bank_info = trim(input('post.bank_info'));
        if (empty($bank_info)) output_error('开户行地址不能为空');

        $Bank = model('Banks');
        $bank = $Bank->getOneBanksByCard(array('member_id'=>$member_id,'bank_card'=>$bankCard));
        if ($bank) output_error('当前卡号已存在');
        $Cardadd = array(
            'member_id'      => $member_id,
            'true_name'      => $true_name,
            'bank_info'      => $bank_info,
            'default_mobile' => input('post.default_mobile'),
            'is_default'     => input('post.is_default'),
            'bank_card'      => $bankCard,
            'bank_name'      => $bank_name,
            'creattime'      => time(),
            'updatetime'     => time(),
        );
        if ($Cardadd['is_default']==1) {
            $Bank->SetBanks(array('member_id'=>$member_id),'is_default',0);
        }
        $result = $Bank->addBanks($Cardadd);
        if ($result) {
            output_data(array('state'=>'ok','msg'=>'绑定成功'));
        }else{
            output_error('绑定失败，请重试！');
        }

    }

    /**
     * 编辑银行卡信息
     * @创建时间 2018-12-04T14:51:30+0800
     */
    public function EditMemberBankCard(){
        $member_id = $this->member_info['member_id'];
        $bid = trim(input('post.bid'));
        if (empty($bid) || $bid < 1) output_error('参数错误！');
        $bankCard = trim(input('post.bank_card'));
        if (empty($bankCard)) output_error('银行卡号不能为空');
        $true_name = trim(input('post.true_name'));
        if (empty($true_name)) output_error('持卡人姓名不能为空');
        $bank_name = trim(input('post.bank_name'));
        if (empty($bank_name))output_error('银行名称不能为空');
        $bank_info = trim(input('post.bank_info'));
        if (empty($bank_info)) output_error('开户行地址不能为空');

        $Bank = model('Banks');
        $bank = $Bank->getOneBanks($bid);
        if(!$bank)output_error('当前银行卡信息不存在，或已经删除！');
        $bankinfo = $Bank->getOneBanksByCard(array('member_id'=>$member_id,'bank_card'=>$bankCard));
        if (!empty($bankinfo) && $bankinfo['bank_id'] != $bid)output_error('当前银行卡号已存在，不能重复添加！');

        $CardEdit = array(
            'true_name'      => $true_name,
            'bank_info'      => $bank_info,
            'default_mobile' => input('post.default_mobile'),
            'is_default'     => input('post.is_default'),
            'bank_card'      => $bankCard,
            'bank_name'      => $bank_name,
            'updatetime'     => time(),
        );
        if ($CardEdit['is_default']==1) {
            $Bank->SetBanks(array('member_id'=>$member_id),'is_default',0);
        }
        $result = $Bank->editBanks($CardEdit,array('bank_id'=>$bid));
        if ($result) {
            output_data(array('state'=>'ok','msg'=>'修改成功！'));
        }else{
            output_error('修改失败，请重试！');
        }

    }

    /**
     * 删除银行卡信息
     * @创建时间 2018-12-04T14:51:38+0800
     */
    public function DelMemberBankCard(){
        $member_id = $this->member_info['member_id'];
        $bid = trim(input('post.bid'));
        if (empty($bid) || $bid < 1) output_error('参数错误！');
        $Bank = model('Banks');
        $bank = $Bank->getOneBanks($bid);
        if (!empty($bank)) {
            $Bank->delBanks(array('member_id'=>$member_id,'bank_id'=>$bid));
            output_data(array('state'=>'ok','msg'=>'删除成功！'));
        }else{
            output_error('此银行卡信息已删除或不是你的银行卡！');
        }

    }

    /**
     * 设置默认银行卡
     * @创建时间 2018-12-04T14:51:47+0800
     */
    public function SetDefautOfBankCard(){
        $member_id = $this->member_info['member_id'];
        $bid = trim(input('post.bid'));
        if (empty($bid) || $bid < 1) output_error('参数错误！');
        $is_default = input('post.is_default');
        $Bank = model('Banks');
        if ($is_default==1) {
            $Bank->SetBanks(array('member_id'=>$member_id),'is_default',0);
            $Bank->SetBanks(array('bank_id'=>$bid),'is_default',1);
        }else{
            $Bank->SetBanks(array('bank_id'=>$bid),'is_default',0);
        }
        output_data(array('state'=>'ok','msg'=>'操作成功！'));
    }

    /**
     * 获取提现明细列表
     * @创建时间 2018-12-24T11:38:34+0800
     */
    public function MemberCashList(){
        $condition = array();
        $condition['pdc_member_id'] = $this->member_info['member_id'];

        $sn_search = input('sn_search');
        if (!empty($sn_search)) {
            $condition['pdc_sn'] = $sn_search;
        }
        $paystate_search = input('paystate_search');
        if (isset($paystate_search)) {
            $condition['pdc_payment_state'] = intval($paystate_search);
        }
        $limit = input('limit');//每页多少条
        $result = db('pdcash')->where($condition)->order('pdc_id desc')->paginate($limit,false,['var_page'=>'page']);
        $cash_list = $result->items();
        //按时间排序 倒序
        // $cash_list=vsort($cash_list,$v='pdc_add_time',$order='desc');
        // foreach ($cash_list as $k => $v) {
            // $cash_list[$k]['grouptime'] = date('Y-m-d',$v['pdc_add_time']);
        // }
        //以时间分组
        // $cash_list=array_group_by($cash_list,'grouptime');
        $data=array(
            'count'=>$result->total(),
            'cash_list'=>$cash_list,
            'currentPage'=>$result->currentPage(),
        );
        output_data($data);
    }

    /**
     * 获取用户明细列表
     * @创建时间 2018-12-24T11:40:00+0800
     */
    public function MemberPdList(){
        $condition = array();
        $condition['lg_member_id'] = $this->member_info['member_id'];
        $member_id = $this->member_info['member_id'];
        $last_time = input('post.page');
        if($last_time){
            $last_info = db('pdlog')->where("lg_add_time <".$last_time." and lg_member_id=".$member_id."")->order('lg_add_time desc')->find();
            $strtime = strtotime(date("Y-m-d",$last_info['lg_add_time'])." 00:00:00");
            $endtime = $strtime+24*3600;
            $where=" lg_member_id=".$member_id." and lg_add_time<".$endtime." and lg_add_time>=".$strtime;
            $result = db('pdlog')->where($where)->order('lg_add_time desc')->select();
        }else{
            $result=db('pdlog')->where($condition)->order('lg_add_time desc')->limit('0,10')->select();
            if(count($result)==10){
                foreach($result as $kk=>$vv){
                    $time = $vv['lg_add_time'];
                }
                $strtime = strtotime(date("Y-m-d",$time)." 00:00:00");
                $endtime = $strtime+24*3600;
                $day = db('pdlog')->where("lg_member_id=".$member_id." and lg_add_time<".$endtime." and lg_add_time>=".$strtime)->order('lg_add_time desc')->select();
                $result=array_merge($result,$day);
                $result=array_unique($result, SORT_REGULAR);
            }
        }
        foreach($result as $k=>$v){
            $result[$k]['add_time'] = date("H:i:s",$v['lg_add_time']);
            $result[$k]['lg_av_amount'] = sprintf("%.2f",$v['lg_av_amount']);

            $result[$k]['date'] = date("Y-m-d",$v['lg_add_time']);
            if(date("Y-m-d",time()) == date("Y-m-d",$v['lg_add_time'])){
                $result[$k]['date'] = "今天";
            }
        }
        foreach($result as $key=>$item){
            $data[$item['date']][] = $item;
            $last_time = $item['lg_add_time'];
        }
        $datas = !empty($data) ? [$data] : $data;
        if(!empty($datas[0])){
            $datas[1]['time'] = !empty($last_time)?$last_time:"";
        }
        output_data($datas);
    }

}

?>
