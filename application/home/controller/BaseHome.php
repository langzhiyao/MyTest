<?php

namespace app\home\controller;

use think\Controller;

/*
 * 基类
 */

class BaseHome extends Controller
{

    public function _initialize()
    {
        parent::_initialize();
        //自动加入配置
        $config_list = rkcache('config', true);
        $config_list['url_attach_common'] = config('url_domain_root') . 'uploads' . '/' . ATTACH_COMMON;
        config($config_list);
        if(!config('site_state')) {
            echo config('closed_reason');
            exit;
        }
        $this->checkMessage(); //短消息检查
        $this->article();
        $this->showCartCount();
        $this->assign('hot_search', @explode(',', config('hot_search'))); //热门搜索
        
        // 自定义导航
        $this->assign('navs', $this->_get_navs());
        //获取所有分类
        $this->assign('header_categories', $this->_get_header_categories());
        
        //此处调用可以在前端使用 $Think.config.xxx
        $this->assign('setting_config', $config_list);
    }
    
    
    //SEO 赋值
    function _assign_seo($seo)
    {
        $this->assign('html_title', $seo['html_title'] . config('site_name'));
        $this->assign('seo_keywords', $seo['seo_keywords']);
        $this->assign('seo_description', $seo['seo_description']);
    }

    /**
     * 检查短消息数量
     *
     */
    protected function checkMessage()
    {
        if (session('member_id') == '')
            return;
        //判断cookie是否存在
        $cookie_name = 'msgnewnum' . session('member_id');
        if (cookie($cookie_name) != null) {
            $countnum = intval(cookie($cookie_name));
        }
        else {
            $message_model = Model('message');
            $countnum = $message_model->countNewMessage(session('member_id'));
            cookie($cookie_name, "$countnum", 2 * 3600); //保存2小时
        }
        $this->assign('message_num', $countnum);
    }

    public function _get_navs()
    {
        $data = array(
            'header' => array(), 'middle' => array(), 'footer' => array(),
        );
        $rows = rkcache('nav', true);
        foreach ($rows as $row) {
            $data[$row['nav_location']][] = $row;
        }
        return $data;
    }

    public function _get_header_categories()
    {
        $model_class = Model('goodsclass');
        $goods_class = $model_class->get_all_category();
        return $goods_class;
    }

    /**
     * 显示购物车数量
     */
    protected function showCartCount()
    {
        if (cookie('cart_goods_num') != null) {
            $cart_num = intval(cookie('cart_goods_num'));
        }
        else {
            //已登录状态，存入数据库,未登录时，优先存入缓存，否则存入COOKIE
            if (session('member_id')) {
                $save_type = 'db';
            }
            else {
                $save_type = 'cookie';
            }
            $cart_num = Model('cart')->getCartNum($save_type, array('buyer_id' => session('member_id'))); //查询购物车商品种类
        }
        $this->assign('cart_goods_num', $cart_num);
    }

    /**
     * 输出会员等级
     * @param bool $is_return 是否返回会员信息，返回为true，输出会员信息为false
     */
    protected function getMemberAndGradeInfo($is_return = false)
    {
        $member_info = array();
        //会员详情及会员级别处理
        if (session('member_id')) {
            $model_member = model('member');
            $member_info = $model_member->getMemberInfoByID(session('member_id'));
            if ($member_info) {
                $member_gradeinfo = $model_member->getOneMemberGrade(intval($member_info['member_exppoints']));
                $member_info = array_merge($member_info, $member_gradeinfo);
            }
        }
        if ($is_return == true) {//返回会员信息
            return $member_info;
        }
        else {//输出会员信息
            $this->assign('member_info', $member_info);
        }
    }

    /**
     * 验证会员是否登录
     *
     */
    protected function checkLogin()
    {
        if (session('is_login') !== '1') {
            if (trim(request()->action()) == 'favoritesgoods' || trim(request()->action()) == 'favoritesstore') {
                echo json_encode(array('done' => false, 'msg' => lang('no_login')));
                die;
            }
            $ref_url = request_uri();
            if (input('get.inajax')) {
                showDialog('', '', 'js', "login_dialog();", 200);
            }
            else {
                @header("location: index.php/home/login/login?ref_url=" . urlencode($ref_url));
            }
            exit;
        }
    }

    /**
     * 添加到任务队列
     *
     * @param array $goods_array
     * @param boolean $ifdel 是否删除以原记录
     */
    protected function addcron($data = array(), $ifdel = false)
    {
        $model_cron = Model('cron');
        if (isset($data[0])) { // 批量插入
            $where = array();
            foreach ($data as $k => $v) {
                if (isset($v['content'])) {
                    $data[$k]['content'] = serialize($v['content']);
                }
                // 删除原纪录条件
                if ($ifdel) {
                    $where[] = '(type = ' . $data['type'] . ' and exeid = ' . $data['exeid'] . ')';
                }
            }
            // 删除原纪录
            if ($ifdel) {
                $model_cron->delCron(implode(',', $where));
            }
            $model_cron->addCronAll($data);
        }
        else { // 单条插入
            if (isset($data['content'])) {
                $data['content'] = serialize($data['content']);
            }
            // 删除原纪录
            if ($ifdel) {
                $model_cron->delCron(array('type' => $data['type'], 'exeid' => $data['exeid']));
            }
            $model_cron->addCron($data);
        }
    }

    //文章输出
    public function article()
    {
        $article = (rkcache("article"));
        if ($article != '') {
            $this->assign('show_article', $article['show_article']);
            $this->assign('article_list', $article['article_list']);
        }
        else {
            $model_article_class = Model('articleclass');
            $model_article = Model('article');
            $show_article = array(); //商城公告
            $article_list = array(); //下方文章
            $notice_class = array('notice');
            $code_array = array('member', 'store', 'payment', 'sold', 'service', 'about');
            $notice_limit = 5;
            $faq_limit = 5;

            $class_condition = array();
            $class_condition['home_index'] = 'home_index';
            $class_condition['order'] = 'ac_sort asc';
            $article_class = $model_article_class->getClassList($class_condition);

            $class_list = array();
            if (!empty($article_class) && is_array($article_class)) {
                foreach ($article_class as $key => $val) {
                    $ac_code = $val['ac_code'];
                    $ac_id = $val['ac_id'];
                    $val['list'] = array(); //文章
                    $class_list[$ac_id] = $val;
                }
            }

            $condition = array();
            $condition['article_show'] = '1';
            $condition['home_index'] = 'home_index';
            $condition['field'] = 'article.article_id,article.ac_id,article.article_url,article.article_title,article.article_time,article_class.ac_name,article_class.ac_parent_id';
            $condition['order'] = 'article_sort asc,article_time desc';
            $condition['limit'] = '300';
            $article_array = $model_article->getJoinList($condition);
            if (!empty($article_array) && is_array($article_array)) {
                foreach ($article_array as $key => $val) {
                    $ac_id = $val['ac_id'];
                    $ac_parent_id = $val['ac_parent_id'];
                    if ($ac_parent_id == 0) {//顶级分类
                        $class_list[$ac_id]['list'][] = $val;
                    }
                    else {
                        $class_list[$ac_parent_id]['list'][] = $val;
                    }
                }
            }

            if (!empty($class_list) && is_array($class_list)) {
                foreach ($class_list as $key => $val) {
                    $ac_code = $val['ac_code'];
                    if (in_array($ac_code, $notice_class)) {
                        $list = $val['list'];
                        array_splice($list, $notice_limit);
                        $val['list'] = $list;
                        $show_article[$ac_code] = $val;
                    }
                    if (in_array($ac_code, $code_array)) {
                        $list = $val['list'];
                        $val['class']['ac_name'] = $val['ac_name'];
                        array_splice($list, $faq_limit);
                        $val['list'] = $list;
                        $article_list[] = $val;
                    }
                }
            }
            wkcache('article', array('show_article' => $show_article, 'article_list' => $article_list,));

            $this->assign('show_article', $show_article);
            $this->assign('article_list', $article_list);
        }
    }

    /**
     * 自动登录 v3-b12
     */
    protected function auto_login()
    {
        $data = cookie('auto_login');
        if (empty($data)) {
            return false;
        }
        $model_member = Model('member');
        if (session('is_login')) {
            $model_member->auto_login();
        }
        $member_id = intval(decrypt($data, MD5_KEY));
        if ($member_id <= 0) {
            return false;
        }
        $member_info = $model_member->getMemberInfoByID($member_id);
        $model_member->createSession($member_info);
    }

}

?>
