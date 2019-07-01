<?php

namespace app\home\controller;

use think\Lang;
use think\Validate;

class Store extends BaseStore {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/store.lang.php');
    }


    public function index() {
        if (!$this->store_decoration_only) {
            $goods_class = Model('goods');

            $condition = array();
            $condition['store_id'] = $this->store_info['store_id'];

            $model_goods = Model('goods'); // 字段
            $fieldstr = "goods_id,goods_commonid,goods_name,goods_jingle,store_id,store_name,goods_price,goods_promotion_price,goods_marketprice,goods_storage,goods_image,goods_freight,goods_salenum,color_id,evaluation_good_star,evaluation_count,goods_promotion_type";
            //得到最新12个商品列表
            $new_goods_list = $model_goods->getGoodsListByColorDistinct($condition, $fieldstr, 'goods_id desc', 12);

            $condition['goods_commend'] = 1;
            //得到12个推荐商品列表
            $recommended_goods_list = $model_goods->getGoodsListByColorDistinct($condition, $fieldstr, 'goods_id desc', 12);
            $goods_list = $this->getGoodsMore($new_goods_list, $recommended_goods_list);
            $this->assign('new_goods_list', $goods_list[1]);
            $this->assign('recommended_goods_list', $goods_list[2]);

            //幻灯片图片
            if ($this->store_info['store_slide'] != '' && $this->store_info['store_slide'] != ',,,,') {
                $this->assign('store_slide', explode(',', $this->store_info['store_slide']));
                $this->assign('store_slide_url', explode(',', $this->store_info['store_slide_url']));
            }
        } else {
            $this->assign('store_decoration_only', $this->store_decoration_only);
        }

        $this->assign('page', 'index');
        return $this->fetch($this->template_dir . 'index');
    }

    private function getGoodsMore($goods_list1, $goods_list2 = array()) {
        if (!empty($goods_list2)) {
            $goods_list = array_merge($goods_list1, $goods_list2);
        } else {
            $goods_list = $goods_list1;
        }
        // 商品多图
        if (!empty($goods_list)) {
            $goodsid_array = array();       // 商品id数组
            $commonid_array = array(); // 商品公共id数组
            $storeid_array = array();       // 店铺id数组
            foreach ($goods_list as $value) {
                $goodsid_array[] = $value['goods_id'];
                $commonid_array[] = $value['goods_commonid'];
                $storeid_array[] = $value['store_id'];
            }
            $goodsid_array = array_unique($goodsid_array);
            $commonid_array = array_unique($commonid_array);

            // 商品多图
            $goodsimage_more = Model('goods')->getGoodsImageList(array('goods_commonid' => array('in', $commonid_array)));

            foreach ($goods_list1 as $key => $value) {
                // 商品多图
                foreach ($goodsimage_more as $v) {
                    if ($value['goods_commonid'] == $v['goods_commonid'] && $value['store_id'] == $v['store_id'] && $value['color_id'] == $v['color_id']) {
                        $goods_list1[$key]['image'][] = $v;
                    }
                }
            }

            if (!empty($goods_list2)) {
                foreach ($goods_list2 as $key => $value) {
                    // 商品多图
                    foreach ($goodsimage_more as $v) {
                        if ($value['goods_commonid'] == $v['goods_commonid'] && $value['store_id'] == $v['store_id'] && $value['color_id'] == $v['color_id']) {
                            $goods_list2[$key]['image'][] = $v;
                        }
                    }
                }
            }
        }
        return array(1 => $goods_list1, 2 => $goods_list2);
    }

    public function show_article() {
        //判断是否为导航页面
        $model_store_navigation = Model('store_navigation');
        $store_navigation_info = $model_store_navigation->getStoreNavigationInfo(array('sn_id' => intval(input('param.sn_id'))));
        if (!empty($store_navigation_info) && is_array($store_navigation_info)) {
            $this->assign('store_navigation_info', $store_navigation_info);
            return $this->fetch($this->template_dir .'article');
        }
    }

    /**
     * 全部商品
     */
    public function goods_all() {

        $condition = array();
        $condition['store_id'] = $this->store_info['store_id'];
        $inkeyword = trim(input('get.inkeyword'));
        if ($inkeyword != '') {
            $condition['goods_name'] = array('like', '%' . $inkeyword . '%');
        }

        // 排序
        $order = input('get.order');
        $order = $order == 1 ? 'asc' : 'desc';
        $key = trim(input('get.key'));
        switch ($key) {
            case '1':
                $order = 'goods_id ' . $order;
                break;
            case '2':
                $order = 'goods_promotion_price ' . $order;
                break;
            case '3':
                $order = 'goods_salenum ' . $order;
                break;
            case '4':
                $order = 'goods_collect ' . $order;
                break;
            case '5':
                $order = 'goods_click ' . $order;
                break;
            default:
                $order = 'goods_id desc';
                break;
        }

        //查询分类下的子分类
        $stc_id = intval(input('get.stc_id'));
        if ($stc_id > 0) {
            $condition['goods_stcids'] = array('like', '%,' . $stc_id . ',%');
        }

        $model_goods = Model('goods');
        $fieldstr = "goods_id,goods_commonid,goods_name,goods_jingle,store_id,store_name,goods_price,goods_promotion_price,goods_marketprice,goods_storage,goods_image,goods_freight,goods_salenum,color_id,evaluation_good_star,evaluation_count,goods_promotion_type";

        $recommended_goods_list = $model_goods->getGoodsListByColorDistinct($condition, $fieldstr, $order, 24);
        $recommended_goods_list = $this->getGoodsMore($recommended_goods_list);
        $this->assign('recommended_goods_list', $recommended_goods_list[1]);
        
        /* 引用搜索相关函数 */
        require_once(APP_PATH . '/home/common_search.php');

        //输出分页
        $this->assign('show_page', $model_goods->page_info->render());
        $stc_class = Model('storegoodsclass');
        $stc_info = $stc_class->getStoreGoodsClassInfo(array('stc_id' => $stc_id));
        $this->assign('stc_name', $stc_info['stc_name']);
        $this->assign('page', 'index');

        return $this->fetch($this->template_dir .'goods_list');
    }

    /**
     * ajax获取动态数量
     */
    function ajax_store_trend_count() {
        $count = Model('store_sns_tracelog')->getStoreSnsTracelogCount(array('strace_storeid' => $this->store_info['store_id']));
        echo json_encode(array('count' => $count));
        exit;
    }

    /**
     * ajax 店铺流量统计入库
     */
    public function ajax_flowstat_record() {
        $store_id = intval(input('param.store_id'));
        if ($store_id <= 0 || session('store_id') == $store_id) {
            echo json_encode(array('done' => true, 'msg' => 'done'));
            die;
        }
        //确定统计分表名称
        $last_num = $store_id % 10; //获取店铺ID的末位数字
        $tablenum = ($t = intval(config('flowstat_tablenum'))) > 1 ? $t : 1; //处理流量统计记录表数量
        $flow_tablename = ($t = ($last_num % $tablenum)) > 0 ? "flowstat_$t" : 'flowstat';
        //判断是否存在当日数据信息
        $stattime = strtotime(date('Y-m-d', time()));
        $model = Model('stat');
        //查询店铺流量统计数据是否存在
       // halt($flow_tablename);
        $store_exist = $model->getoneByFlowstat($flow_tablename, array('stattime' => $stattime, 'store_id' => $store_id, 'type' => 'sum'));
        if (input('param.act_param') == 'goods' && input('param.op_param') == 'index') {//统计商品页面流量
            $goods_id = intval(input('param.goods_id'));
            if ($goods_id <= 0) {
                echo json_encode(array('done' => false, 'msg' => 'done'));
                die;
            }
            $goods_exist = $model->getoneByFlowstat($flow_tablename, array('stattime' => $stattime, 'goods_id' => $goods_id, 'type' => 'goods'));
        }
        //向数据库写入访问量数据
        $insert_arr = array();
        if ($store_exist) {
           db($flow_tablename)->where(array('stattime' => $stattime, 'store_id' => $store_id, 'type' => 'sum'))->setInc('clicknum', 1);
        } else {
            $insert_arr[] = array('stattime' => $stattime, 'clicknum' => 1, 'store_id' => $store_id, 'type' => 'sum', 'goods_id' => 0);
        }
        if (input('param.act_param') == 'goods' && input('param.op_param') == 'index') {//已经存在数据则更新
            if ($goods_exist) {
                $model->table($flow_tablename)->where(array('stattime' => $stattime, 'goods_id' => $goods_id, 'type' => 'goods'))->setInc('clicknum', 1);
            } else {
                $insert_arr[] = array('stattime' => $stattime, 'clicknum' => 1, 'store_id' => $store_id, 'type' => 'goods', 'goods_id' => $goods_id);
            }
        }
        if ($insert_arr) {
           db($flow_tablename)->insertAll($insert_arr);
        }
        echo json_encode(array('done' => true, 'msg' => 'done'));
    }

}
