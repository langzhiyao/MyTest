<?php

/**
 * 积分管理
 */

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Points extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/points.lang.php');
    }

    public function index() {
        if (!request()->isPost()) {
            $this->setAdminCurItem('index');
            return $this->fetch();
        } else {
            $data = [
                'member_name' => input('post.member_name'),
                'points_type' => input('post.points_type'),
                'points_num' => input('post.points_num'),
                'points_desc' => input('post.points_desc'),
            ];
            $rule = [
                ['member_name', 'require', lang('member_name_error')],
                ['points_num', 'number', lang('points_num_error')]
            ];
            $validate = new Validate();
            $validate_result = $validate->check($data, $rule);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            $member_name = $data['member_name'];
            $member_info = model('member')->getMemberInfo(array('member_name' => $member_name));
            if (!is_array($member_info) || count($member_info) <= 0) {
                $this->error(lang('member_error'));
            }
            if ($data['points_type'] == 2 && $data['points_num'] > $member_info['member_points']) {
                $this->error(lang('member_points_short_error') . $member_info['member_points']);
            }
            //积分数据记录
            $insert_arr['pl_memberid'] = $member_info['member_id'];
            $insert_arr['pl_membername'] = $member_info['member_name'];
            if ($data['points_type'] == 2) {
                $insert_arr['pl_points'] = -$data['points_num'];
            } else {
                $insert_arr['pl_points'] = $data['points_num'];
            }
            $insert_arr['pl_desc'] = $data['points_desc'];
            $insert_arr['pl_adminname']=session('admin_name');
            $obj_points = Model('points');

            $result = $obj_points->savePointsLog('system', $insert_arr);
            if ($result) {
                $this->success(lang('ds_common_op_succ'), 'Points/index');
            } else {
                $this->error(lang('error'));
            }
        }
    }

    //积分明细查询
    function pointslog() {
        if (!request()->isPost()) {
            $condition_arr = array();
            $mname = input('param.mname');
            if (!empty($mname)) {
                $condition_arr['pl_membername'] = array('like', '%' . $mname . '%');
            }
            $aname = input('param.aname');
            if (!empty($aname)) {
                $condition_arr['pl_membername'] = array('like', '%' . $aname . '%');
            }
            $stage = input('get.stage');
            if ($stage) {
                $condition_arr['pl_stage'] = trim($stage);
            }
            $saddtime = input('get.saddtime');
            $etime = input('get.etime');
            $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $saddtime);
            $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $etime);
            $start_unixtime = $if_start_time ? strtotime($saddtime) : null;
            $end_unixtime = $if_end_time ? strtotime($etime) : null;
            if ($start_unixtime || $end_unixtime) {
                $condition_arr['pl_addtime'] = array('between', array($start_unixtime, $end_unixtime));
            }
            
            
            $points_model = Model('points');
            $list_log = $points_model->getPointsLogList($condition_arr, 10, '*', '');

            $this->assign('pointslog', $list_log);
            $this->assign('page', $points_model->page_info->render());
            $this->setAdminCurItem('pointslog');
            return $this->fetch();
        }
    }

    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '管理',
                'url' => url('Admin/Points/index')
            ),
            array(
                'name' => 'pointslog',
                'text' => '积分明细',
                'url' => url('Admin/Points/pointslog')
            )
        );
        return $menu_array;
    }

}
