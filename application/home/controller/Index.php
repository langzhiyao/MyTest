<?php

namespace app\home\controller;

use think\Lang;

class Index extends BaseMall {

    public function _initialize() {
        parent::_initialize();
        //强制跳转到商家登陆
        // $this->redirect('Sellerlogin/login');
        Lang::load(APP_PATH . 'home/lang/zh-cn/index.lang.php');
        Lang::load(APP_PATH . 'home/lang/zh-cn/sellergroupbuy.lang.php');
    }

    public function index() {
        // 如果是手机跳转到 手机模块
        if (true == isMobile()) {
            $this->redirect(WAP_SITE_URL);
            exit;
        }
        $this->assign('index_sign', 'index');

        $this->assign('recommend_list', model('goods')->getGoodsCommonOnlineList(array('goods_commend'=>1)));
        //限时折扣
        $this->assign('promotion_list', model('pxianshigoods')->getXianshiGoodsCommendList(4));
        $this->assign('new_list',  model('goods')->getGoodsOnlineList(array(),'*',0,'goods_salenum desc ,goods_addtime desc',6));
        $this->assign('groupbuy_list', model('groupbuy')->getGroupbuyCommendedList(5));

        //楼层数据
        $this->assign('floor1_block', $this->getFloorList(1));
        $this->assign('floor2_block', $this->getFloorList(4));
        $this->assign('floor3_block', $this->getFloorList(8));
        $this->assign('floor4_block', $this->getFloorList(9));
        $this->assign('floor5_block', $this->getFloorList(10));

        
        //友情链接
        $model_link = Model('link');
        $link_list = $model_link->getLinkList();
        $this->assign('link_list', $link_list);

        //显示订单信息
        if (session('is_login')) {
            //交易提醒 - 显示数量
            $model_order = Model('order');
            $member_order_info['order_nopay_count'] = $model_order->getOrderCountByID('buyer', session('member_id'), 'NewCount');
            $member_order_info['order_noreceipt_count'] = $model_order->getOrderCountByID('buyer', session('member_id'), 'SendCount');
            $member_order_info['order_noeval_count'] = $model_order->getOrderCountByID('buyer', session('member_id'), 'EvalCount');
            $this->assign('member_order_info', $member_order_info);
        }
        
        //SEO 设置
        $seo = Model('seo')->type('index')->show();
        $this->_assign_seo($seo);
//        halt($this->template_dir);
        return $this->fetch($this->template_dir . 'index');
    }

    function getFloorList($cate_id) {
        $cache_key = 'home-index-floor-'.$cate_id;
        $result = cache($cache_key);
        if (empty($result)) {
            //获取此楼层下的所有分类
            $goods_class_list = db('goodsclass')->where('gc_parent_id=' . $cate_id)->select();
            //获取每个分类下的商品
            $goods_list = array();
            $goods_list[0]['gc_name'] = '热门推荐';
            $goods_list[0]['gc_id'] = $cate_id;
            $goods_list[0]['gc_list'] = model('goods')->getGoodsCommonOnlineList(array('gc_id'=>$cate_id));
            
            $hot_goods_class_list = db('goodsclass')->where('gc_parent_id=' . $cate_id)->order('gc_sort desc')->limit(5)->select();
            foreach ($hot_goods_class_list as $key => $hot_goods_class) {
                $data = array();
                $data['gc_name'] = $hot_goods_class['gc_name'];
                $data['gc_id'] = $hot_goods_class['gc_id'];
                $data['gc_list'] = db('goods')->where(array('gc_id'=>$data['gc_id']))->limit(8)->select();
                $goods_list[] = $data;
            }
            $result['goods_list'] = $goods_list;
            $result['goods_class_list'] = $goods_class_list;
            cache($cache_key, $result, 3600);
        }
        return $result;
    }

    //json输出商品分类
    public function josn_class() {
        /**
         * 实例化商品分类模型
         */
        $model_class = Model('goodsclass');
        $goods_class = $model_class->getGoodsClassListByParentId(intval($_GET['gc_id']));
        $array = array();
        if (is_array($goods_class) and count($goods_class) > 0) {
            foreach ($goods_class as $val) {
                $array[$val['gc_id']] = array(
                    'gc_id' => $val['gc_id'], 'gc_name' => htmlspecialchars($val['gc_name']),
                    'gc_parent_id' => $val['gc_parent_id'], 'commis_rate' => $val['commis_rate'],
                    'gc_sort' => $val['gc_sort']
                );
            }
        }

        echo $_GET['callback'] . '(' . json_encode($array) . ')';
    }

    //闲置物品地区json输出
    public function flea_area() {
        if (intval($_GET['check']) > 0) {
            $_GET['area_id'] = $_GET['region_id'];
        }
        if (intval($_GET['area_id']) == 0) {
            return;
        }
        $model_area = Model('flea_area');
        $area_array = $model_area->getListArea(array('flea_area_parent_id' => intval($_GET['area_id'])), 'flea_area_sort desc');
        $array = array();
        if (is_array($area_array) and count($area_array) > 0) {
            foreach ($area_array as $val) {
                $array[$val['flea_area_id']] = array(
                    'flea_area_id' => $val['flea_area_id'],
                    'flea_area_name' => htmlspecialchars($val['flea_area_name']),
                    'flea_area_parent_id' => $val['flea_area_parent_id'], 'flea_area_sort' => $val['flea_area_sort']
                );
            }
        }
        if (intval($_GET['check']) > 0) {//判断当前地区是否为最后一级
            if (!empty($array) && is_array($array)) {
                echo 'false';
            } else {
                echo 'true';
            }
        } else {
            echo json_encode($array);
        }
    }

    //json输出闲置物品分类
    public function josn_flea_class() {
        /**
         * 实例化商品分类模型
         */
        $model_class = Model('flea_class');
        $goods_class = $model_class->getClassList(array('gc_parent_id' => intval($_GET['gc_id'])));
        $array = array();
        if (is_array($goods_class) and count($goods_class) > 0) {
            foreach ($goods_class as $val) {
                $array[$val['gc_id']] = array(
                    'gc_id' => $val['gc_id'], 'gc_name' => htmlspecialchars($val['gc_name']),
                    'gc_parent_id' => $val['gc_parent_id'], 'gc_sort' => $val['gc_sort']
                );
            }
        }
        /**
         * 转码
         */
        echo json_encode($array);
    }

    /**
     * json输出地址数组 原data/resource/js/area_array.js
     */
    public function json_area() {
        echo $_GET['callback'] . '(' . json_encode(Model('area')->getAreaArrayForJson()) . ')';
    }

    /**
     * json输出地址数组 v3-b12
     */
    public function json_area_show() {
        $area_info['text'] = Model('area')->getTopAreaName(intval($_GET['area_id']));
        echo $_GET['callback'] . '(' . json_encode($area_info) . ')';
    }

    //判断是否登录
    public function login() {
        echo (session('is_login') == '1') ? '1' : '0';
    }

    /**
     * 查询每月的周数组
     */
    public function getweekofmonth() {
        import('function.datehelper');
        $year = $_GET['y'];
        $month = $_GET['m'];
        $week_arr = getMonthWeekArr($year, $month);
        echo json_encode($week_arr);
        die;
    }

    /**
     * 头部最近浏览的商品
     */
    public function viewed_info() {
        $info = array();
        if (session('is_login') == '1') {
            $member_id = session('member_id');
            $info['m_id'] = $member_id;
            if (config('voucher_allow') == 1) {
                $time_to = time(); //当前日期
                $info['voucher'] = db('voucher')->where(array(
                            'voucher_owner_id' => $member_id, 'voucher_state' => 1,
                            'voucher_start_date' => array('elt', $time_to),
                            'voucher_end_date' => array('egt', $time_to)
                        ))->count();
            }
            $time_to = strtotime(date('Y-m-d')); //当前日期
            $time_from = date('Y-m-d', ($time_to - 60 * 60 * 24 * 7)); //7天前
            $info['consult'] = db('consult')->where(array(
                        'member_id' => $member_id, 'consult_reply_time' => array(
                            array(
                                'gt', strtotime($time_from)
                            ), array('lt', $time_to + 60 * 60 * 24), 'and'
                        )
                    ))->count();
        }
        $goods_list = Model('goodsbrowse')->getViewedGoodsList(session('member_id'), 5);
        if (is_array($goods_list) && !empty($goods_list)) {
            $viewed_goods = array();
            foreach ($goods_list as $key => $val) {
                $goods_id = $val['goods_id'];
                $val['url'] = url('Home/Goods/index', ['goods_id' => $goods_id]);
                $val['goods_image'] = thumb($val, 60);
                $viewed_goods[$goods_id] = $val;
            }
            $info['viewed_goods'] = $viewed_goods;
        }
        echo json_encode($info);
    }

}
