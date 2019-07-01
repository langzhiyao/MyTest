<?php

namespace app\home\controller;

use think\Lang;
use think\Model;

class Storesnshome extends BaseStoreSns {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH.'home/lang/zh-cn/sellersns.lang.php');
    }

    /**
     * 查看店铺动态
     */
    public function index() {
        //获得店铺ID
        $sid = intval(input('param.sid'));
        $this->getStoreInfo($sid);

        // where 条件
        $where = array();
        $where['strace_state'] = 1;
        $where['strace_storeid'] = $sid;
        $type = input('type');
        if ($type != '') {
            switch (trim($type)) {
                case 'promotion':
                    $where['strace_type'] = array('in', array(4, 5, 6, 7, 8));
                    break;
                case 'new':
                    $where['strace_type'] = 3;
                    break;
                case 'hotsell':
                    $where['strace_type'] = 10;
                    break;
                case 'recommend':
                    $where['strace_type'] = 9;
                    break;
            }
        }
        $model_stracelog = Model('storesnstracelog');
        $strace_array = $model_stracelog->getStoreSnsTracelogList($where, '*', 'strace_id desc', 0, 40);
        // 整理
        if (!empty($strace_array) && is_array($strace_array)) {
            foreach ($strace_array as $key => $val) {
                if ($val['strace_content'] == '') {
                    $val['strace_goodsdata'] = json_decode($val['strace_goodsdata'], true);
                    $content = $model_stracelog->spellingStyle($val['strace_type'], $val['strace_goodsdata']);
                    $strace_array[$key]['strace_content'] = str_replace("%siteurl%", SHOP_SITE_URL . DS, $content);
                }
            }
        }
        $this->assign('strace_array', $strace_array);

        //允许插入新记录的最大条数
        $this->assign('max_recordnum', self::MAX_RECORDNUM);
        $this->assign('show_page', $model_stracelog->page_info->render());

        // 最多收藏的会员
        $favorites = Model('favorites')->getStoreFavoritesList(array('fav_id' => $sid), '*', 0, 'fav_time desc', 8);
        if (!empty($favorites)) {
            $memberid_array = array();
            foreach ($favorites as $val) {
                $memberid_array[] = $val['member_id'];
            }
            $favorites_list = Model('member')->getMemberList(array('member_id' => array('in', $memberid_array)), 'member_id,member_name,member_avatar');
            $this->assign('favorites_list', $favorites_list);
        }
        return $this->fetch($this->template_dir.'store_snshome');
    }

    /**
     * 评论前10条记录
     */
    public function commentt() {
        $stid = intval($_GET['id']);
        if ($stid > 0) {
            $model_storesnscomment = Model('store_sns_comment');
            //查询评论总数

            $where = array(
                'strace_id' => $stid,
                'scomm_state' => 1
            );
            $countnum = $model_storesnscomment->getStoreSnsCommentCount($where);

            //动态列表
            $commentlist = $model_storesnscomment->getStoreSnsCommentList($where, '*', 'scomm_id desc', 10);

            // 更新评论数量
            Model('store_sns_tracelog')->editStoreSnsTracelog(array('strace_comment' => $countnum), array('strace_id' => $stid));
        }
        $showmore = '0'; //是否展示更多的连接
        if ($countnum > count($commentlist)) {
            $showmore = '1';
        }
        $this->assign('countnum', $countnum);
        $this->assign('showmore', $showmore);
        $this->assign('showtype', 1); //页面展示类型 0表示分页 1表示显示前几条
        $this->assign('stid', $stid);


        //允许插入新记录的最大条数
        $this->assign('max_recordnum', self::MAX_RECORDNUM);

        $this->assign('commentlist', $commentlist);
        return $this->fetch($this->template_dir.'store_snscommentlist');
    }

    /**
     * 评论列表
     */
    public function commentlist() {
        $stid = intval($_GET['id']);
        if ($stid > 0) {
            $model_storesnscomment = Model('store_sns_comment');
            //查询评论总数
            $where = array(
                'strace_id' => $stid,
                'scomm_state' => 1
            );
            $countnum = $model_storesnscomment->getStoreSnsCommentCount($where);

            //评价列表
            $commentlist = $model_storesnscomment->getStoreSnsCommentList($where, '*', 'scomm_id desc', 0, 10);

            // 更新评论数量
            $commentlist = Model('store_sns_tracelog')->editStoreSnsTracelog(array('strace_comment' => $countnum), array('strace_id' => $stid));
        }

        $this->assign('commentlist', $commentlist);
        $this->assign('show_page', $model_storesnscomment->page_info->render());
        $this->assign('countnum', $countnum);
        $this->assign('stid', $stid);
        $this->assign('showtype', '0'); //页面展示类型 0表示分页 1表示显示前几条

        //允许插入新记录的最大条数
        $this->assign('max_recordnum', self::MAX_RECORDNUM);
        return $this->fetch($this->template_dir.'store_snscommentlist');
    }

    /**
     * 添加评论(访客登录后操作)
     */
    public function addcomment() {
        // 验证用户是否登录
        $this->checkLoginStatus();

        $stid = intval($_POST['stid']);
        if ($stid <= 0) {
            showDialog(lang('wrong_argument'), '', 'error');
        }
        $obj_validate = new Validate();
        $validate_arr[] = array("input" => $_POST["commentcontent"], "require" => "true", "message" => lang('sns_comment_null'));
        $validate_arr[] = array("input" => $_POST["commentcontent"], "validator" => 'Length', "min" => 0, "max" => 140, "message" => lang('sns_content_beyond'));
        //评论数超过最大次数出现验证码
        if (intval(cookie('commentnum')) >= self::MAX_RECORDNUM) {
            $validate_arr[] = array("input" => $_POST["captcha"], "require" => "true", "message" => lang('wrong_null'));
        }
        $obj_validate->validateparam = $validate_arr;
        $error = $obj_validate->validate();
        if ($error != '') {
            showDialog($error, '', 'error');
        }
        //发帖数超过最大次数出现验证码
        if (intval(cookie('commentnum')) >= self::MAX_RECORDNUM) {
            if (!checkSeccode($_POST['nchash'], $_POST['captcha'])) {
                showDialog(lang('wrong_checkcode'), '', 'error');
            }
        }
// 		//查询会员信息
        $model = Model();
        $member_info = $model->table('member')->where(array('member_state' => 1))->find(session('member_id'));
        if (empty($member_info)) {
            showDialog(lang('sns_member_error'), '', 'error');
        }
        $insert_arr = array();
        $insert_arr['strace_id'] = $stid;
        $insert_arr['scomm_content'] = $_POST['commentcontent'];
        $insert_arr['scomm_memberid'] = $member_info['member_id'];
        $insert_arr['scomm_membername'] = $member_info['member_name'];
        $insert_arr['scomm_memberavatar'] = $member_info['member_avatar'];
        $insert_arr['scomm_time'] = time();
        $result = Model('store_sns_comment')->saveStoreSnsComment($insert_arr);
        if ($result) {
            // 原帖增加评论次数
            $where = array('strace_id' => $stid);
            $update = array('strace_comment' => array('exp', 'strace_comment+1'));
            $rs = Model('store_sns_tracelog')->editStoreSnsTracelog($update, $where);
            //建立cookie
            if (cookie('commentnum') != null && intval(cookie('commentnum')) > 0) {
                setNcCookie('commentnum', intval(cookie('commentnum')) + 1, 2 * 3600); //保存2小时
            } else {
                setNcCookie('commentnum', 1, 2 * 3600); //保存2小时
            }
            $js = "$('#content_comment" . $stid . "').html('');";
            if ($_POST['showtype'] == 1) {
                $js .="$('#tracereply_" . $stid . "').load(SITE_URL+'home/storesnshome/commenttop?id=" . $stid . "');";
            } else {
                $js .="$('#tracereply_" . $stid . "').load(SITE_URL+'home/storesnshome/commentlist?id=" . $stid . "');";
            }
            showDialog(lang('sns_comment_succ'), '', 'succ', $js);
        }
    }

    /**
     * 添加转发
     */
    public function addforward() {
        // 验证用户是否登录
        $this->checkLoginStatus();

        $obj_validate = new Validate();
        $stid = intval($_POST["stid"]);
        $validate_arr[] = array("input" => $_POST["forwardcontent"], "validator" => 'Length', "min" => 0, "max" => 140, "message" => lang('sns_content_beyond'));
        //发帖数超过最大次数出现验证码
        if (intval(cookie('forwardnum')) >= self::MAX_RECORDNUM) {
            $validate_arr[] = array("input" => $_POST["captcha"], "require" => "true", "message" => lang('wrong_null'));
        }
        $obj_validate->validateparam = $validate_arr;
        $error = $obj_validate->validate();
        if ($error != '') {
            showDialog($error, '', 'error');
        }
        //发帖数超过最大次数出现验证码
        if (intval(cookie('forwardnum')) >= self::MAX_RECORDNUM) {
            if (!checkSeccode($_POST['nchash'], $_POST['captcha'])) {
                showDialog(lang('wrong_checkcode'), '', 'error');
            }
        }
        //查询会员信息
        $model = Model();
        $member_info = $model->table('member')->where(array('member_state' => 1))->find(session('member_id'));
        if (empty($member_info)) {
            showDialog(lang('sns_member_error'), '', 'error');
        }
        //查询原帖信息
        $model_stracelog = Model('store_sns_tracelog');
        $stracelog_info = $model_stracelog->getStoreSnsTracelogInfo(array('strace_id' => $stid));
        if (empty($stracelog_info)) {
            showDialog(lang('sns_forward_fail'), '', 'error');
        }
        if ($stracelog_info['strace_content'] == '') {
            $data = json_decode($stracelog_info['strace_goodsdata'], true);
            $stracelog_info['strace_content'] = $model_stracelog->spellingStyle($stracelog_info['strace_type'], $data);
        }

        $insert_arr = array();
        $insert_arr['trace_originalid'] = 0;
        $insert_arr['trace_originalmemberid'] = 0;
        $insert_arr['trace_originalstate'] = 0;
        $insert_arr['trace_memberid'] = $member_info['member_id'];
        $insert_arr['trace_membername'] = $member_info['member_name'];
        $insert_arr['trace_memberavatar'] = $member_info['member_avatar'];
        $insert_arr['trace_title'] = $_POST['forwardcontent'] ? $_POST['forwardcontent'] : lang('sns_forward');
        $insert_arr['trace_content'] = "<dl class=\"fd-wrap\">
														<dt>
															<h3><a href=\"{:url('storesnshome/index',['sid'=>$stracelog_info.strace_storeid])}\" target=\"_blank\">" . $stracelog_info['strace_storename'] . "</a>" . lang('ds_colon') . "
															" . $stracelog_info['strace_title'] . "</h3>
										      			</dt>
														<dd>" . $stracelog_info['strace_content'] . "</dd>
													<dl>";
        $insert_arr['trace_addtime'] = time();
        $insert_arr['trace_state'] = 0;
        $insert_arr['trace_privacy'] = 0;
        $insert_arr['trace_commentcount'] = 0;
        $insert_arr['trace_copycount'] = 0;
        $insert_arr['trace_orgcommentcount'] = 0;
        $insert_arr['trace_orgcopycount'] = 0;
        $insert_arr['trace_from'] = 2;
        $result = $model->table('sns_tracelog')->insert($insert_arr);
        if ($result) {
            //更新动态转发次数
            $where = array('strace_id' => $stid);
            $update = array('strace_spread' => array('exp', 'strace_spread+1'));
            Model('store_sns_tracelog')->editStoreSnsTracelog($update, $where);
            showDialog(lang('sns_forward_succ'), '', 'succ');
        } else {
            showDialog(lang('sns_forward_fail'), '', 'error');
        }
    }

    /**
     * 删除动态
     */
    public function deltrace() {
        // 验证用户是否登录
        $this->checkLoginStatus();

        $stid = intval(input('id'));
        if ($stid <= 0) {
           $this->error(lang('wrong_argument'));
        }
        //删除动态
        $result = Model('storesnstracelog')->delStoreSnsTracelog(array('strace_id' => $stid, 'strace_storeid' => session('store_id')));
        if ($result) {
            //删除对应的评论
            Model('storesnscomment')->delStoreSnsComment(array('strace_id' => $stid));
            $js = "$('[nc_type=\"tracerow_{$stid}\"]').remove();";
            $this->success(lang('ds_common_del_succ'));
        } else {
            $this->error(lang('ds_common_del_fail'));
        }
    }

    /**
     * 删除评论(访客登录后操作)
     */
    public function delcomment() {
        // 验证用户是否登录
        $this->checkLoginStatus();

        $scid = intval($_GET['scid']);
        $stid = intval($_GET['stid']);
        if ($scid <= 0 || $stid <= 0) {
            showDialog(lang('wrong_argument'), '', 'error');
        }
        // 查询评论相关信息
        $model_storesnscomment = Model('storesnscomment');
        $where = array('strace_id' => $stid, 'scomm_id' => $scid, 'scomm_memberid' => session('member_id')); // where条件
        $scomment_info = $model_storesnscomment->getStoreSnsCommentInfo($where);
        if (empty($scomment_info)) {
            showDialog(lang('wrong_argument'), '', 'error');
        }

        // 删除评论
        $result = $model_storesnscomment->delStoreSnsComment($where);
        if ($result) {
            // 更新动态统计信息
            $where = array('strace_id' => $scomment_info['strace_id']);
            $update = array('strace_comment' => array('exp', 'strace_comment-1'));
            Model('store_sns_tracelog')->editStoreSnsTracelog($update, $where);

            $js .="$('.comment-list [nc_type=\"commentrow_" . $scid . "\"]').remove();";
            showDialog(lang('ds_common_del_succ'), '', 'succ', $js);
        } else {
            showDialog(lang('ds_common_del_fail'), '', 'error');
        }
    }

    /**
     * 一条SNS动态及其评论
     */
    public function straceinfo() {
        $stid = intval($_GET['st_id']);
        if ($stid <= 0) {
            $this->error(lang('para_error'));
        }
        $model_stracelog = Model('store_sns_tracelog');
        $strace_info = $model_stracelog->getStoreSnsTracelogInfo(array('strace_id' => $stid));
        if (!empty($strace_info)) {
            if ($strace_info['strace_content'] == '') {
                $content = $model_stracelog->spellingStyle($strace_info['strace_type'], json_decode($strace_info['strace_goodsdata'], true));
                $strace_info['strace_content'] = str_replace("%siteurl%", SHOP_SITE_URL . DS, $content);
            }
        }
        $this->assign('strace_info', $strace_info);
        return $this->fetch($this->template_dir.'store_snstraceinfo');
    }

    /**
     * 验证用户是否登录
     */
    private function checkLoginStatus() {
        if (session('is_login') != 1) {
            @header("location: index.php/home/login/index?ref_url=" . urlencode('index.php/home/membersnshome/index'));
        }
    }

}

?>
