<?php

namespace app\common\model;

use think\Db;
use think\Model;

class Member extends Model
{

    public $page_info;

    /**
     * 会员详细信息（查库）
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getMemberInfo($condition, $field = '*', $master = false)
    {
        $condition['is_del'] = isset($condition['is_del'])?$condition['is_del']:1;
        $res = db('member')->field($field)->where($condition)->master($master)->find();
        return $res;
    }

    /**
     * 查询副账号数量
     * @param  [type] $member_id [description]
     * @return [type]            [description]
     */
    public function getMemberViceAccount($member_id){
        $where = ' m.member_id = "'.$member_id.'"';
        $result = db('member')->alias('m')->field('m.member_id,m.is_owner')->where($where)->find();
        if($result['is_owner'] == 0){
            return $this->where('is_owner = "'.$result["member_id"].'"')->count();
        }else{
            return $this->where('(is_owner = "'.$result["is_owner"].'" AND member_id != "'.$member_id.'") OR member_id = "'.$result["is_owner"].'" ')->count();
        }
    }

    /**
     * 取得会员详细信息（优先查询缓存）
     * 如果未找到，则缓存所有字段
     * @param int $member_id
     * @param string $field 需要取得的缓存键值, 例如：'*','member_name,member_sex'
     * @return array
     */
    public function getMemberInfoByID($member_id, $fields = '*')
    {
        $member_info = rcache($member_id, 'member', $fields);
        if (empty($member_info)) {
            $member_info = $this->getMemberInfo(array('member_id' => $member_id), '*', true);
            wcache($member_id, $member_info, 'member');
        }
        return $member_info;
    }

    /**
     * 会员列表
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     */
    public function getMemberList($condition = array(), $field = '*', $page = 0, $order = 'member_id desc')
    {
        $condition['is_del'] = isset($condition['is_del'])?$condition['is_del']:1;
        if ($page) {
            $member_list = db('member')->field($field)->where($condition)
                // ->join('__SCHOOLTYPE__ b','','LEFT')
                ->order($order)
                ->paginate($page,false,['query' => request()->param()]);
            $this->page_info = $member_list;
            return $member_list->items();
        }
        else {
            return db('member')->where($condition)->field($field)->order($order)->select();
        }
    }

    public function ttttt($condition=array()){
        $list = db('classtype classtype')->join('__SCHOOLTYPE__ b','classtype.sc_id=b.sc_id','LEFT')->where(array(
            'cl_enabled'=>1
        ))->select();
        p($list);
    }

    /**
     * 会员数量
     * @param array $condition
     * @return int
     */
    public function getMemberCount($condition)
    {
        return db('member')->where($condition)->count();
    }

    /**
     * 编辑会员
     * @param array $condition
     * @param array $data
     */
    public function editMember($condition, $data)
    {
        $update = db('member')->where($condition)->update($data);
        if ($update && $condition['member_id']) {
            dcache($condition['member_id'], 'member');
        }
        return $update;
    }

    /**
     * 登录时创建会话SESSION
     *
     * @param array $member_info 会员信息
     */
    public function createSession($member_info = array(), $reg = false)
    {
        if (empty($member_info) || !is_array($member_info)) {
            return;
        }
        session('is_login', '1');
        session('member_id', $member_info['member_id']);
        session('member_name', $member_info['member_name']);
        session('member_email', $member_info['member_email']);
        session('is_buy', isset($member_info['is_buy']) ? $member_info['is_buy'] : 1);
        session('avatar', $member_info['member_avatar']);
        session('level_name', isset($member_info['level_name']) ? $member_info['level_name'] : '');
        session('member_exppoints', $member_info['member_exppoints']);  //经验值
        session('member_points', $member_info['member_points']);        //积分值
        // 头衔COOKIE
        $this->set_avatar_cookie();

        $seller_info = Model('seller')->getSellerInfo(array('member_id' => session('member_id')));
        if ($seller_info) {
            session('store_id', $seller_info['store_id']);
        }
        else {
            session('store_id', NULL);
        }

        if (trim($member_info['member_qqopenid'])) {
            session('openid', $member_info['member_qqopenid']);
        }
        if (trim($member_info['member_sinaopenid'])) {
            session('slast_key.uid', $member_info['member_sinaopenid']);
        }
        if (trim($member_info['member_wxopenid'])) {
            session('wxopenid', $member_info['member_wxopenid']);
        }
        if (trim($member_info['weixin_unionid'])) {
            session('wxunionid', $member_info['weixin_unionid']);
        }

        if (!$reg) {
            //添加会员积分
            $this->addPoint($member_info);
            //添加会员经验值
            $this->addExppoint($member_info);
        }

        if (!empty($member_info['member_login_time'])) {
            $update_info = array(
                'member_login_num' => ($member_info['member_login_num'] + 1), 'member_login_time' => TIMESTAMP,
                'member_old_login_time' => $member_info['member_login_time'], 'member_login_ip' => request()->ip(),
                'member_old_login_ip' => $member_info['member_login_ip']
            );
            $this->editMember(array('member_id' => $member_info['member_id']), $update_info);
        }
        cookie('cart_goods_num', '', -3600);
        // cookie中的cart存入数据库
        Model('cart')->mergecart($member_info, session('store_id'));
        // cookie中的浏览记录存入数据库
        Model('goodsbrowse')->mergebrowse(session('member_id'), session('store_id'));

        if (isset($member_info['auto_login']) && ($member_info['auto_login'] == 1)) {
            $this->auto_login();
        }
    }

    /**
     * 7天内自动登录 v3-b12
     */
    public function auto_login()
    {
        // 自动登录标记 保存7天
        cookie('auto_login', encrypt(session('member_id'), MD5_KEY), 7 * 24 * 60 * 60);
    }

    public function set_avatar_cookie()
    {
        cookie('member_avatar', session('avatar'), 365 * 24 * 60 * 60);
    }

    /**
     * 获取会员信息
     *
     * @param    array $param 会员条件
     * @param    string $field 显示字段
     * @return    array 数组格式的返回结果
     */
    public function infoMember($param, $field = '*')
    {
        if (empty($param))
            return false;

        //得到条件语句
        $condition_str = $this->getCondition($param);
        $param = array();
        $param['table'] = 'member';
        $param['where'] = $condition_str;
        $param['field'] = $field;
        $param['limit'] = 1;
        $member_list = Db::select($param);
        $member_info = $member_list[0];
        if (intval($member_info['store_id']) > 0) {
            $param = array();
            $param['table'] = 'store';
            $param['field'] = 'store_id';
            $param['value'] = $member_info['store_id'];
            $field = 'store_id,store_name,grade_id';
            $store_info = Db::select($param, $field);
            if (!empty($store_info) && is_array($store_info)) {
                $member_info['store_name'] = $store_info['store_name'];
                $member_info['grade_id'] = $store_info['grade_id'];
            }
        }
        return $member_info;
    }

    /**
     * 注册
     */
    public function register($register_info)
    {
        // 验证用户名是否重复
        $check_member_name = $this->getMemberInfo(array('member_name' => $register_info['member_name']));
        if (is_array($check_member_name) and count($check_member_name) > 0) {
            return array('error' => '用户名已存在');
        }

        // 会员添加
        $member_info = array();
        $member_info['member_name'] = $register_info['member_name'];
        $member_info['member_password'] = $register_info['member_password'];
        //添加邀请人(推荐人)会员积分
        $member_info['inviter_id'] = $register_info['inviter_id'];

        if (isset($register_info['member_mobile_bind'])) {
            $member_info['member_mobile_bind'] = $register_info['member_mobile_bind'];
            $member_info['member_mobile'] = $register_info['member_mobile'];
        }

        $insert_id = $this->addMember($member_info);
        if ($insert_id) {
            //添加会员积分
            if (config('points_isuse')) {
                Model('points')->savePointsLog('regist', array(
                    'pl_memberid' => $insert_id, 'pl_membername' => $register_info['member_name']
                ), false);
                //添加邀请人(推荐人)会员积分
                $inviter_name = db('member')->where('member_id', $member_info['inviter_id'])->find();
                Model('points')->savePointsLog('inviter', array(
                    'pl_memberid' => $register_info['inviter_id'], 'pl_membername' => $inviter_name['member_name'],
                    'invited' => $member_info['member_name']
                ));
            }

            // 添加默认相册
            $insert['ac_name'] = '买家秀';
            $insert['member_id'] = $insert_id;
            $insert['ac_des'] = '买家秀默认相册';
            $insert['ac_sort'] = 1;
            $insert['is_default'] = 1;
            $insert['upload_time'] = TIMESTAMP;
            db('snsalbumclass')->insert($insert);
            $member_info['member_id'] = $insert_id;
            $member_info = db('member')->where('member_id', $insert_id)->find();
            return $member_info;
        }
        else {
            return array('error' => '注册失败');
        }
    }

    /**
     * 注册商城会员
     *
     * @param    array $param 会员信息
     * @return    array 数组格式的返回结果
     */
    public function addMember($param)
    {
        if (empty($param)) {
            return false;
        }
        try {
            //            $this->startTrans();
            $this->startTrans();
            $member_info = array();
            $member_info['member_name'] = $param['member_name'];
            $member_info['member_password'] = $param['member_password'];
            if (isset($param['member_email'])) {
                $member_info['member_email'] = $param['member_email'];
            }
            $member_info['member_add_time'] = TIMESTAMP;
            $member_info['member_login_time'] = TIMESTAMP;
            $member_info['member_old_login_time'] = TIMESTAMP;
            $member_info['member_login_ip'] = request()->ip();
            $member_info['member_old_login_ip'] = $member_info['member_login_ip'];

            if (isset($param['member_truename'])) {
                $member_info['member_truename'] = $param['member_truename'];
            }
            if (isset($param['member_qq'])) {
                $member_info['member_qq'] = $param['member_qq'];
            }
            if (isset($param['member_sex'])) {
                $member_info['member_sex'] = $param['member_sex'];
            }
            if (isset($param['member_avatar'])) {
                $member_info['member_avatar'] = $param['member_avatar'];
            }
            if (isset($param['member_qqopenid'])) {
                $member_info['member_qqopenid'] = $param['member_qqopenid'];
            }
            if (isset($param['member_qqinfo'])) {
                $member_info['member_qqinfo'] = $param['member_qqinfo'];
            }
            if (isset($param['member_sinaopenid'])) {
                $member_info['member_sinaopenid'] = $param['member_sinaopenid'];
            }
            if (isset($param['member_sinainfo'])) {
                $member_info['member_sinainfo'] = $param['member_sinainfo'];
            }
            //添加邀请人(推荐人)会员积分
            if (isset($param['inviter_id'])) {
                $member_info['inviter_id'] = $param['inviter_id'];
            }

            // v3-b12 手机注册登录绑定
            if (isset($param['member_mobile_bind'])) {
                $member_info['member_mobile'] = $param['member_mobile'];
                $member_info['member_mobile_bind'] = $param['member_mobile_bind'];
            }
            if (isset($param['weixin_unionid'])) {
                $member_info['weixin_unionid'] = $param['weixin_unionid'];
                $member_info['weixin_info'] = $param['weixin_info'];
                $member_info['member_wxopenid'] = $param['member_wxopenid'];
            }
            $insert_id = db('member')->insertGetId($member_info);
            if (!$insert_id) {
                exception();
            }
            $insert = $this->addMemberCommon(array('member_id' => $insert_id));
            if (!$insert) {
                exception();
            }
            // 添加默认相册
            $insert = array();
            $insert['ac_name'] = '买家秀';
            $insert['member_id'] = $insert_id;
            $insert['ac_des'] = '买家秀默认相册';
            $insert['ac_sort'] = 1;
            $insert['is_default'] = 1;
            $insert['upload_time'] = TIMESTAMP;
            $rs = db('snsalbumclass')->insert($insert);

            //添加会员积分
            if (config('points_isuse')) {
                Model('points')->savePointsLog('regist', array(
                    'pl_memberid' => $insert_id, 'pl_membername' => $param['member_name']
                ), false);
            }
            $this->commit();
            return $insert_id;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * 会员登录检查
     *
     */
    public function checkloginMember()
    {
        if (session('is_login') == '1') {
            @header("Location: " . url('Home/Member/index'));
            exit();
        }
    }

    /**
     * 检查会员是否允许举报商品
     *
     */
    public function isMemberAllowInform($member_id)
    {
        $condition = array();
        $condition['member_id'] = $member_id;
        $member_info = $this->getMemberInfo($condition, 'inform_allow');
        if (intval($member_info['inform_allow']) === 1) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * 取单条信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getMemberCommonInfo($condition = array(), $fields = '*')
    {
        return db('membercommon')->where($condition)->field($fields)->find();
    }

    /**
     * 插入扩展表信息
     * @param unknown $data
     * @return Ambigous <mixed, boolean, number, unknown, resource>
     */
    public function addMemberCommon($data)
    {
        return db('membercommon')->insert($data);
    }

    /**
     * 编辑会员扩展表
     * @param unknown $data
     * @param unknown $condition
     * @return Ambigous <mixed, boolean, number, unknown, resource>
     */
    public function editMemberCommon($data, $condition)
    {
        return db('membercommon')->where($condition)->update($data);
    }

    /**
     * 添加会员积分
     * @param unknown $member_info
     */
    public function addPoint($member_info)
    {
        if (!config('points_isuse') || empty($member_info))
            return;

        //一天内只有第一次登录赠送积分
        if (trim(@date('Y-m-d', $member_info['member_login_time'])) == trim(date('Y-m-d')))
            return;

        //加入队列
        $queue_content = array();
        $queue_content['member_id'] = $member_info['member_id'];
        $queue_content['member_name'] = $member_info['member_name'];
        \mall\queue\QueueClient::push('addPoint', $queue_content);
    }

    /**
     * 添加会员经验值
     * @param unknown $member_info
     */
    public function addExppoint($member_info)
    {
        if (empty($member_info))
            return;

        //一天内只有第一次登录赠送经验值
        if (trim(@date('Y-m-d', $member_info['member_login_time'])) == trim(date('Y-m-d')))
            return;

        //加入队列
        $queue_content = array();
        $queue_content['member_id'] = $member_info['member_id'];
        $queue_content['member_name'] = $member_info['member_name'];
        \mall\queue\QueueClient::push('addExppoint', $queue_content);
    }

    /**
     * 取得会员安全级别
     * @param unknown $member_info
     */
    public function getMemberSecurityLevel($member_info = array())
    {
        $tmp_level = 0;
        if ($member_info['member_email_bind'] == '1') {
            $tmp_level += 1;
        }
        if ($member_info['member_mobile_bind'] == '1') {
            $tmp_level += 1;
        }
        if ($member_info['member_paypwd'] != '') {
            $tmp_level += 1;
        }
        return $tmp_level;
    }

    /**
     * 获得会员等级
     * @param bool $show_progress 是否计算其当前等级进度
     * @param int $exppoints 会员经验值
     * @param array $cur_level 会员当前等级
     */
    public function getMemberGradeArr($show_progress = false, $exppoints = 0, $cur_level = '')
    {
        $member_grade = config('member_grade') ? unserialize(config('member_grade')) : array();
        //处理会员等级进度
        if ($member_grade && $show_progress) {
            $is_max = false;
            if ($cur_level === '') {
                $cur_gradearr = $this->getOneMemberGrade($exppoints, false, $member_grade);
                $cur_level = $cur_gradearr['level'];
            }
            foreach ($member_grade as $k => $v) {
                if ($cur_level == $v['level']) {
                    $v['is_cur'] = true;
                }
                $member_grade[$k] = $v;
            }
        }
        return $member_grade;
    }

    /**
     * 将条件数组组合为SQL语句的条件部分
     *
     * @param    array $conditon_array
     * @return    string
     */
    private function getCondition($conditon_array)
    {
        $condition_sql = '';
        if ($conditon_array['member_id'] != '') {
            $condition_sql .= " and member_id= '" . intval($conditon_array['member_id']) . "'";
        }
        if ($conditon_array['member_name'] != '') {
            $condition_sql .= " and member_name='" . $conditon_array['member_name'] . "'";
        }
        if ($conditon_array['member_password'] != '') {
            $condition_sql .= " and member_password='" . $conditon_array['member_password'] . "'";
        }
        //是否允许举报
        if ($conditon_array['inform_allow'] != '') {
            $condition_sql .= " and inform_allow='{$conditon_array['inform_allow']}'";
        }
        //是否允许购买
        if ($conditon_array['is_buy'] != '') {
            $condition_sql .= " and is_buy='{$conditon_array['is_buy']}'";
        }
        //是否允许发言
        if ($conditon_array['is_allowtalk'] != '') {
            $condition_sql .= " and is_allowtalk='{$conditon_array['is_allowtalk']}'";
        }
        //是否允许登录
        if ($conditon_array['member_state'] != '') {
            $condition_sql .= " and member_state='{$conditon_array['member_state']}'";
        }
        if ($conditon_array['friend_list'] != '') {
            $condition_sql .= " and member_name IN (" . $conditon_array['friend_list'] . ")";
        }
        if ($conditon_array['member_email'] != '') {
            $condition_sql .= " and member_email='" . $conditon_array['member_email'] . "'";
        }
        if ($conditon_array['no_member_id'] != '') {
            $condition_sql .= " and member_id != '" . $conditon_array['no_member_id'] . "'";
        }
        if ($conditon_array['like_member_name'] != '') {
            $condition_sql .= " and member_name like '%" . $conditon_array['like_member_name'] . "%'";
        }
        if ($conditon_array['like_member_email'] != '') {
            $condition_sql .= " and member_email like '%" . $conditon_array['like_member_email'] . "%'";
        }
        if ($conditon_array['like_member_truename'] != '') {
            $condition_sql .= " and member_truename like '%" . $conditon_array['like_member_truename'] . "%'";
        }
        if ($conditon_array['in_member_id'] != '') {
            $condition_sql .= " and member_id IN (" . $conditon_array['in_member_id'] . ")";
        }
        if ($conditon_array['in_member_name'] != '') {
            $condition_sql .= " and member_name IN (" . $conditon_array['in_member_name'] . ")";
        }
        if ($conditon_array['member_qqopenid'] != '') {
            $condition_sql .= " and member_qqopenid = '{$conditon_array['member_qqopenid']}'";
        }
        if ($conditon_array['member_sinaopenid'] != '') {
            $condition_sql .= " and member_sinaopenid = '{$conditon_array['member_sinaopenid']}'";
        }
        if ($conditon_array['weixin_unionid'] != '') {
            $condition_sql .= " and member_wxunionid = '{$conditon_array['member_wxunionid']}'";
        }
        if ($conditon_array['member_wxopenid'] != '') {
            $condition_sql .= " and member_wxopenid = '{$conditon_array['member_wxopenid']}'";
        }

        return $condition_sql;
    }

    /**
     * 删除会员
     *
     * @param int $id 记录ID
     * @return array $rs_row 返回数组形式的查询结果
     */
    public function del($id)
    {
        if (intval($id) > 0) {
            $where = " member_id = '" . intval($id) . "'";
            $result = Db::delete('member', $where);
            return $result;
        }
        else {
            return false;
        }
    }

    /**
     * 获得某一会员等级
     * @param int $exppoints
     * @param bool $show_progress 是否计算其当前等级进度
     * @param array $member_grade 会员等级
     */
    public function getOneMemberGrade($exppoints, $show_progress = false, $member_grade = array())
    {
        if (!$member_grade) {
            $member_grade = config('member_grade') ? unserialize(config('member_grade')) : array();
        }
        if (empty($member_grade)) {//如果会员等级设置为空
            $grade_arr['level'] = -1;
            $grade_arr['level_name'] = '暂无等级';
            return $grade_arr;
        }

        $exppoints = intval($exppoints);

        $grade_arr = array();
        if ($member_grade) {
            foreach ($member_grade as $k => $v) {
                if ($exppoints >= $v['exppoints']) {
                    $grade_arr = $v;
                }
            }
        }
        //计算提升进度
        if ($show_progress == true) {
            if (intval($grade_arr['level']) >= (count($member_grade) - 1)) {//如果已达到顶级会员
                $grade_arr['downgrade'] = $grade_arr['level'] - 1; //下一级会员等级
                $grade_arr['downgrade_name'] = $member_grade[$grade_arr['downgrade']]['level_name'];
                $grade_arr['downgrade_exppoints'] = $member_grade[$grade_arr['downgrade']]['exppoints'];
                $grade_arr['upgrade'] = $grade_arr['level']; //上一级会员等级
                $grade_arr['upgrade_name'] = $member_grade[$grade_arr['upgrade']]['level_name'];
                $grade_arr['upgrade_exppoints'] = $member_grade[$grade_arr['upgrade']]['exppoints'];
                $grade_arr['less_exppoints'] = 0;
                $grade_arr['exppoints_rate'] = 100;
            }
            else {
                $grade_arr['downgrade'] = $grade_arr['level']; //下一级会员等级
                $grade_arr['downgrade_name'] = $member_grade[$grade_arr['downgrade']]['level_name'];
                $grade_arr['downgrade_exppoints'] = $member_grade[$grade_arr['downgrade']]['exppoints'];
                $grade_arr['upgrade'] = $member_grade[$grade_arr['level'] + 1]['level']; //上一级会员等级
                $grade_arr['upgrade_name'] = $member_grade[$grade_arr['upgrade']]['level_name'];
                $grade_arr['upgrade_exppoints'] = $member_grade[$grade_arr['upgrade']]['exppoints'];
                $grade_arr['less_exppoints'] = $grade_arr['upgrade_exppoints'] - $exppoints;
                $grade_arr['exppoints_rate'] = round(($exppoints - $member_grade[$grade_arr['level']]['exppoints']) / ($grade_arr['upgrade_exppoints'] - $member_grade[$grade_arr['level']]['exppoints']) * 100, 2);
            }
        }
        return $grade_arr;
    }

    /**
     * 登录生成token
     */
    public function _get_token($member_id, $member_name, $client)
    {
        $model_mb_user_token = Model('mbusertoken');

        //重新登录后以前的令牌失效
        //暂时停用
        //$condition = array();
        //$condition['member_id'] = $member_id;
        //$condition['client_type'] = $client;
        //$model_mb_user_token->delMbUserToken($condition);
        //生成新的token
        $mb_user_token_info = array();
        $token = md5($member_name . strval(TIMESTAMP) . strval(rand(0, 999999)));
        $mb_user_token_info['member_id'] = $member_id;
        $mb_user_token_info['member_name'] = $member_name;
        $mb_user_token_info['token'] = $token;
        $mb_user_token_info['login_time'] = TIMESTAMP;
        $mb_user_token_info['client_type'] = $client;

        $result = $model_mb_user_token->addMbUserToken($mb_user_token_info);

        if ($result) {
            return $token;
        }
        else {
            return null;
        }
    }

    public function getfby_member_id($member_id, $field='inviter_id')
    {
        $where['member_id']=$member_id;
        return db('member')->field($field)->where($where)->find();
    }

}
