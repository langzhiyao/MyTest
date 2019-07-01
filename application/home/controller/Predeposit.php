<?php

/**
 * 预存款管理
 */

namespace app\home\controller;

use think\Lang;
use think\Validate;

class Predeposit extends BaseMember {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/predeposit.lang.php');
    }

    /**
     * 充值添加
     */
    public function recharge_add() {
        if (!request()->isPost()) {
            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('predeposit');
            /* 设置买家当前栏目 */
            $this->setMemberCurItem('recharge_add');
            return $this->fetch($this->template_dir . 'pd_recharge_add');
        } else {
            $pdr_amount = abs(floatval(input('post.pdr_amount')));
            if ($pdr_amount <= 0) {
                $this->error(lang('predeposit_recharge_add_pricemin_error'));
            }
            $model_pdr = Model('predeposit');
            $data = array();
            $data['pdr_sn'] = $pay_sn = $model_pdr->makeSn();
            $data['pdr_member_id'] = session('member_id');
            $data['pdr_member_name'] = session('member_name');
            $data['pdr_amount'] = $pdr_amount;
            $data['pdr_add_time'] = TIMESTAMP;
            $insert = $model_pdr->addPdRecharge($data);
            if ($insert) {
                //转向到商城支付页面
                $this->redirect(url('Home/buy/pd_pay', ['pay_sn' => $pay_sn]));
            }
        }
    }

    /**
     * 平台充值卡
     */
    public function rechargecard_add() {
        if (!request()->isPost()) {
            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('predeposit');
            /* 设置买家当前栏目 */
            $this->setMemberCurItem('rechargecard_add');
            return $this->fetch($this->template_dir . 'rechargecard_add');
            return;
        } else {
            $sn = (string) input('post.rc_sn');
            if (!$sn || strlen($sn) > 50) {
                $this->error('平台充值卡卡号不能为空且长度不能大于50');
                exit;
            }

            try {
                $res=model('predeposit')->addRechargeCard($sn, $_SESSION);
                if($res['message']){
                    $this->error($res['message']);
                }
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success('平台充值卡使用成功', url('Home/predeposit/rcb_log_list'));
        }
    }

    /**
     * 充值列表
     */
    public function index() {
        $condition = array();
        $condition['pdr_member_id'] = session('member_id');
        $pdr_sn = input('pdr_sn');
        if (!empty($pdr_sn)) {
            $condition['pdr_sn'] = $pdr_sn;
        }

        $model_pd = Model('predeposit');
        $list = $model_pd->getPdRechargeList($condition, 10, '*', 'pdr_id desc');

        $this->assign('list', $list);
        $this->assign('page', $model_pd->page_info->render());

        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('predeposit');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('recharge_list');
        return $this->fetch($this->template_dir . 'pd_recharge_list');
    }

    /**
     * 查看充值详细
     *
     */
    public function recharge_show() {
        $pdr_id = intval(input('param.id'));
        if ($pdr_id <= 0) {
            showDialog(lang('predeposit_parameter_error'), '', 'error');
        }

        $model_pd = Model('predeposit');
        $condition = array();
        $condition['pdr_member_id'] = session('member_id');
        $condition['pdr_id'] = $pdr_id;
        $condition['pdr_payment_state'] = 1;
        $info = $model_pd->getPdRechargeInfo($condition);
        if (!$info) {
            showDialog(lang('predeposit_record_error'), '', 'error');
        }
        $this->assign('info', $info);
        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('predeposit');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('recharge_show');
        return $this->fetch($this->template_dir . 'recharge_show');
    }

    /**
     * 删除充值记录
     *
     */
    public function recharge_del() {
        $pdr_id = intval(input('param.id'));
        if ($pdr_id <= 0) {
            showDialog(lang('predeposit_parameter_error'), '', 'error');
        }

        $model_pd = Model('predeposit');
        $condition = array();
        $condition['pdr_member_id'] = session('member_id');
        $condition['pdr_id'] = $pdr_id;
        $condition['pdr_payment_state'] = 0;
        $result = $model_pd->delPdRecharge($condition);
        if ($result) {
            showDialog(lang('ds_common_del_succ'), 'reload', 'succ', 'CUR_DIALOG.close()');
        } else {
            showDialog(lang('ds_common_del_fail'), '', 'error');
        }
    }

    /**
     * 预存款变更日志
     */
    public function pd_log_list() {
        $condition = array();
        $condition['lg_member_id'] = session('member_id');

        $model_pd = Model('predeposit');
        $list = $model_pd->getPdLogList($condition, 10, '*', 'lg_id desc');

        $this->assign('page', $model_pd->page_info->render());
        $this->assign('list', $list);
        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('predeposit');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('loglist');
        return $this->fetch($this->template_dir . 'pd_log_list');
    }

    /**
     * 充值卡余额变更日志
     */
    public function rcb_log_list() {
        $list = db('rcblog')->where(array('member_id' => session('member_id'),))->order('id desc')->paginate(10);

        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('predeposit');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('rcb_log_list');
        $this->assign('show_page', $list->render());
        $this->assign('list', $list);
        return $this->fetch($this->template_dir . 'rcb_log_list');
    }

    /**
     * 申请提现
     */
    public function pd_cash_add() {
        if (request()->isPost()) {
            $obj_validate = new Validate();
            $pdc_amount=abs(floatval($_POST['pdc_amount']));
            $data=[
                'pdc_amount' =>$pdc_amount,
                'pdc_bank_name'  =>$_POST["pdc_bank_name"],
                'pdc_bank_no'  =>$_POST["pdc_bank_no"],
                'pdc_bank_user'  =>$_POST["pdc_bank_user"],
                'password'      =>$_POST["password"]
            ];

            $rule=[
                ['pdc_amount','require|min:0.01',lang('predeposit_cash_add_pricemin_error')],
                ['pdc_bank_name','require',lang('predeposit_cash_add_shoukuanbanknull_error')],
                ['pdc_bank_no','require',lang('predeposit_cash_add_shoukuanaccountnull_error')],
                ['pdc_bank_user','require',lang('predeposit_cash_add_shoukuannamenull_error')],
                ['password','require','请输入支付密码']
            ];

            $error = $obj_validate->check($data,$rule);
            if (!$error) {
                showDialog($obj_validate->getError(), '', 'error');
            }

            $model_pd = Model('predeposit');
            $model_member = Model('member');
            $member_info = $model_member->getMemberInfoByID(session('member_id'));
            //验证支付密码
            if (md5($_POST['password']) != $member_info['member_paypwd']) {
                showDialog('支付密码错误', '', 'error');
            }
            //验证金额是否足够
            if (floatval($member_info['available_predeposit']) < $pdc_amount) {
                showDialog(lang('predeposit_cash_shortprice_error'), url('Home/Predeposit/pd_cash_list'), 'error');
            }
            try {
                $model_pd->startTrans();
                $pdc_sn = $model_pd->makeSn();
                $data = array();
                $data['pdc_sn'] = $pdc_sn;
                $data['pdc_member_id'] = session('member_id');
                $data['pdc_member_name'] = session('member_name');
                $data['pdc_amount'] = $pdc_amount;
                $data['pdc_bank_name'] = $_POST['pdc_bank_name'];
                $data['pdc_bank_no'] = $_POST['pdc_bank_no'];
                $data['pdc_bank_user'] = $_POST['pdc_bank_user'];
                $data['pdc_add_time'] = TIMESTAMP;
                $data['pdc_payment_state'] = 0;
                $insert = $model_pd->addPdCash($data);
                if (!$insert) {
                    showDialog(lang('predeposit_cash_add_fail'),'','error');
                }
                //冻结可用预存款
                $data = array();
                $data['member_id'] = $member_info['member_id'];
                $data['member_name'] = $member_info['member_name'];
                $data['amount'] = $pdc_amount;
                $data['order_sn'] = $pdc_sn;
                $model_pd->changePd('cash_apply', $data);
                $model_pd->commit();
                showDialog(lang('predeposit_cash_add_success'), url('Home/Predeposit/pd_cash_list'), 'succ', 'CUR_DIALOG.close()');
            } catch (Exception $e) {
                $model_pd->rollback();
                showDialog($e->getMessage(), url('Home/Predeposit/pd_cash_list'), 'error');
            }
        }
    }

    /**
     * 提现列表
     */
    public function pd_cash_list() {
        $condition = array();
        $condition['pdc_member_id'] = session('member_id');

        $sn_search = input('post.sn_search');
        if (!empty($sn_search)) {
            $condition['pdc_sn'] = $sn_search;
        }
        $paystate_search = input('post.paystate_search');
        if (isset($paystate_search)) {
            $condition['pdc_payment_state'] = intval($paystate_search);
        }

        $cash_list = db('pdcash')->where($condition)->order('pdc_id desc')->paginate();
        $this->assign('list', $cash_list);
        $this->assign('page', $cash_list->render());

        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('predeposit');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('cashlist');
        return $this->fetch($this->template_dir . 'pd_cash_list');
    }

    /**
     * 提现记录详细
     */
    public function pd_cash_info() {
        $pdc_id = intval(input('param.id'));
        if ($pdc_id <= 0) {
            $this->error(lang('predeposit_parameter_error'), 'Home/predeposit/pd_cash_list');
        }
        $model_pd = Model('predeposit');
        $condition = array();
        $condition['pdc_member_id'] = session('member_id');
        $condition['pdc_id'] = $pdc_id;
        $info = $model_pd->getPdCashInfo($condition);
        if (empty($info)) {
            $this->error(lang('predeposit_record_error'), 'Home/predeposit/pd_cash_list');
        }

       $this->setMemberCurItem('cashinfo');
        $this->setMemberCurMenu('predeposit');
        $this->assign('info', $info);
        return $this->fetch($this->template_dir . 'pd_cash_info');
    }

    /**
     *    栏目菜单
     */
    function getMemberItemList() {
        $item_list = array(
            array(
                'name' => 'loglist',
                'text' => lang('明细列表'),
                'url' => url('Home/predeposit/pd_log_list'),
            ),
            array(
                'name' => 'recharge_list',
                'text' => lang('充值列表'),
                'url' => url('Home/predeposit/index'),
            ),
            array(
                'name' => 'cashlist',
                'text' => lang('提现列表'),
                'url' => url('Home/predeposit/pd_cash_list'),
            ),
            array(
                'name' => 'rcb_log_list',
                'text' => lang('充值卡余额'),
                'url' => url('Home/predeposit/rcb_log_list'),
            ),
        );

        if (request()->action() == 'rechargeinfo') {
            $item_list[] = array(
                'name' => 'rechargeinfo',
                'text' => lang('ds_member_path_predeposit_rechargeinfo'),
                'url' => url('Home/predeposit/rechargeinfo'),
            );
        }

        if (request()->action() == 'recharge_add') {
            $item_list[] = array(
                'name' => 'recharge_add',
                'text' => lang('在线充值'),
                'url' => url('Home/predeposit/recharge_add'),
            );
        }

        if (request()->action() == 'rechargecard_add') {
            $item_list[] = array(
                'name' => 'rechargecard_add',
                'text' => lang('充值卡充值'),
                'url' => url('Home/predeposit/rechargecard_add'),
            );
        }

        if (request()->action() == 'cashadd') {
            $item_list[] = array(
                'name' => 'cashadd',
                'text' => lang('ds_member_path_predeposit_cashadd'),
                'url' => url('Home/predeposit/cashadd'),
            );
        }

        if (request()->action() == 'cashinfo') {
            $item_list[] = array(
                'name' => 'cashinfo',
                'text' => lang('ds_member_path_predeposit_cashinfo'),
                'url' => url('Home/predeposit/cashinfo'),
            );
        }


        return $item_list;
    }

}
