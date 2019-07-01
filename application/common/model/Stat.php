<?php

namespace app\common\model;

use think\Model;

class Stat extends Model
{

    public $page_info;

    /**
     * 查询新增会员统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @param boolean $lock 是否锁定
     * @return array
     */
    public function statByMember($where, $field = '*', $page = 0, $order = '', $group = '')
    {
        $res = db('member')->field($field)->where($where)->order($order)->group($group)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

    /**
     * 查询单条会员统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @return array
     */
    public function getoneByMember($where, $field = '*', $order = '', $group = '')
    {
        return db('member')->field($field)->where($where)->order($order)->group($group)->find();
    }

    /**
     * 查询单条店铺统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @return array
     */
    public function getoneByStore($where, $field = '*', $order = '', $group = '')
    {
        return db('store')->field($field)->where($where)->order($order)->group($group)->find();
    }

    /**
     * 查询店铺统计
     */
    public function statByStore($where, $field = '*', $page = 0, $order = '', $group = '')
    {
        $res = db('store')->field($field)->where($where)->group($group)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

    /**
     * 查询新增店铺统计
     */
    public function getNewStoreStatList($condition, $field = '*', $page = 0, $order = 'store_id desc', $limit = 0, $group = '')
    {
        $res = db('store')->field($field)->where($condition)->page($page)->group($group)->order($order)->paginate(10,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

    /**
     * 查询会员列表
     */
    public function getMemberList($where, $field = '*', $page = 0, $order = 'member_id desc', $group = '')
    {
        $res = db('member')->field($field)->where($where)->group($group)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

    /**
     * 调取店铺等级信息
     */
    public function getStoreDegree()
    {
        $tmp = db('storegrade')->field('sg_id,sg_name')->where(true)->select();
        $sd_list = array();
        if (!empty($tmp)) {
            foreach ($tmp as $k => $v) {
                $sd_list[$v['sg_id']] = $v['sg_name'];
            }
        }
        return $sd_list;
    }

    /**
     * 查询会员统计数据记录
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByStatmember($where, $field = '*', $page = 0, $limit = 0, $order = '', $group = '')
    {
        $res = db('statmember')->field($field)->where($where)->limit($limit)->order($order)->group($group)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

    /**
     * 查询商品数量
     */
    public function getGoodsNum($where)
    {
        $rs = db('goodscommon')->field('count(*) as allnum')->where($where)->select();
        return $rs[0]['allnum'];
    }

    /**
     * 获取预存款数据
     */
    public function getPredepositInfo($condition, $field = '*', $page = 0, $order = 'lg_add_time desc', $limit = 0, $group = '')
    {
        $res = db('pdlog')->field($field)->where($condition)->group($group)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

    /**
     * 获取结算数据
     */
    public function getBillList($condition, $type, $have_page = true)
    {
        switch ($type) {
            case 'os'://平台
                return db('orderstatis')->field('sum(os_order_totals) as oot,sum(os_order_return_totals) as oort,sum(os_commis_totals-os_commis_return_totals) as oct,sum(os_store_cost_totals) as osct,sum(os_result_totals) as ort')->where($condition)->select();
                break;
            case 'ob'://店铺
                $page = $have_page ? 15 : '';
                return db('orderbill')->alias('order_bill')->join('__STORE__ store', 'order_bill.ob_store_id=store.store_id', 'left')->field('order_bill.*,store.member_name')->where($condition)->order('ob_no desc')->paginate($page,false,['query' => request()->param()]);
                break;
        }
    }

    /**
     * 查询订单及订单商品的统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByOrderGoods($where, $field = '*', $page = 0, $limit = 0, $order = '', $group = '')
    {
        $res = db('ordergoods')->alias('ordergoods')->field($field)->join('__ORDER__ order', 'ordergoods.order_id=order.order_id', 'left')->where($where)->group($group)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

    /**
     * 查询订单及订单商品的统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByOrderLog($where, $field = '*', $page = 0, $limit = 0, $order = '', $group = '')
    {
        $res = db('orderlog')->alias('orderlog')->field($field)->join('__ORDER__ order', 'orderlog.order_id = order.order_id', 'left')->where($where)->group($group)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

    /**
     * 查询退款退货统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByRefundreturn($where, $field = '*', $page = 0, $limit = 0, $order = '', $group = '')
    {
        $res = db('refundreturn')->field($field)->where($where)->group($group)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

    /**
     * 查询店铺动态评分统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByStoreAndEvaluatestore($where, $field = '*', $page = 0, $limit = 0, $order = '', $group = '')
    {
        $res = db('evaluatestore')->alias('evaluatestore')->field($field)->join('__STORE__ store', 'evaluatestore.seval_storeid=store.store_id', 'left')->where($where)->group($group)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

    /**
     * 处理搜索时间
     */
    public function dealwithSearchTime($search_arr)
    {
        //初始化时间
        //天
        if (!isset($search_arr['search_time'])) {
            $search_arr['search_time'] = date('Y-m-d', time() - 86400);
        }
        $search_arr['day']['search_time'] = strtotime($search_arr['search_time']); //搜索的时间
        //周
        if (!isset($search_arr['searchweek_year'])) {
            $search_arr['searchweek_year'] = date('Y', time());
        }
        if (!isset($search_arr['searchweek_month'])) {
            $search_arr['searchweek_month'] = date('m', time());
        }
        if (!isset($search_arr['searchweek_week'])) {
            $search_arr['searchweek_week'] = implode('|', getWeek_SdateAndEdate(time()));
        }
        $weekcurrent_year = $search_arr['searchweek_year'];
        $weekcurrent_month = $search_arr['searchweek_month'];
        $weekcurrent_week = $search_arr['searchweek_week'];
        $search_arr['week']['current_year'] = $weekcurrent_year;
        $search_arr['week']['current_month'] = $weekcurrent_month;
        $search_arr['week']['current_week'] = $weekcurrent_week;

        //月
        if (!isset($search_arr['searchmonth_year'])) {
            $search_arr['searchmonth_year'] = date('Y', time());
        }
        if (!isset($search_arr['searchmonth_month'])) {
            $search_arr['searchmonth_month'] = date('m', time());
        }
        $monthcurrent_year = $search_arr['searchmonth_year'];
        $monthcurrent_month = $search_arr['searchmonth_month'];
        $search_arr['month']['current_year'] = $monthcurrent_year;
        $search_arr['month']['current_month'] = $monthcurrent_month;
        return $search_arr;
    }

    /**
     * 获得查询的开始和结束时间
     */
    public function getStarttimeAndEndtime($search_arr)
    {
        $stime=array();
        $etime=array();
        if (isset($search_arr['search_type'])&&$search_arr['search_type'] == 'day') {
            $stime = $search_arr['day']['search_time']; //今天0点
            $etime = $search_arr['day']['search_time'] + 86400 - 1; //今天24点
        }
        if (isset($search_arr['search_type'])&&$search_arr['search_type'] == 'week') {
            $current_weekarr = explode('|', $search_arr['week']['current_week']);
            $stime = strtotime($current_weekarr[0]);
            $etime = strtotime($current_weekarr[1]) + 86400 - 1;
        }
        if (isset($search_arr['search_type'])&&$search_arr['search_type'] == 'month') {
            $stime = strtotime($search_arr['month']['current_year'] . '-' . $search_arr['month']['current_month'] . "-01 0 month");
            $etime = getMonthLastDay($search_arr['month']['current_year'], $search_arr['month']['current_month']) + 86400 - 1;
        }
        return array($stime, $etime);
    }

    /**
     * 查询会员统计数据单条记录
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @return array
     */
    public function getOneStatmember($where, $field = '*', $order = '', $group = '')
    {
        return db('statmember')->field($field)->where($where)->group($group)->order($order)->find();
    }

    /**
     * 更新会员统计数据单条记录
     *
     * @param array $condition 条件
     * @param array $update_arr 更新数组
     * @return array
     */
    public function updateStatmember($where, $update_arr)
    {
        return db('statmember')->where($where)->update($update_arr);
    }

    /**
     * 查询订单的统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByOrder($where, $field = '*', $page = 0, $limit = 0, $order = '', $group = '')
    {
        $res = db('order')->field($field)->where($where)->group($group)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

    /**
     * 查询积分的统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByPointslog($where, $field = '*', $page = 0, $limit = 0, $order = '', $group = '')
    {

        $res = db('pointslog')->field($field)->where($where)->group($group)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

    /**
     * 删除会员统计数据记录
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function delByStatmember($where = array())
    {
        db('statmember')->where($where)->delete();
    }

    /**
     * 查询订单商品缓存的统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @return array
     */
    public function getoneByStatordergoods($where, $field = '*', $order = '', $group = '')
    {
        return db('statordergoods')->field($field)->where($where)->group($group)->order($order)->find();
    }

    /**
     * 查询订单商品缓存的统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByStatordergoods($where, $field = '*', $page = 0, $limit = 0, $order = '', $group = '')
    {

        $res = db('statordergoods')->field($field)->where($where)->group($group)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

    /**
     * 查询订单缓存的统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @return array
     */
    public function getoneByStatorder($where, $field = '*', $order = '', $group = '')
    {
        return db('statorder')->field($field)->where($where)->group($group)->order($order)->find();
    }

    /**
     * 查询订单缓存的统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByStatorder($where, $field = '*', $page = 0, $limit = 0, $order = '', $group = '')
    {

        $res = db('statorder')->field($field)->where($where)->group($group)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

    /**
     * 查询商品列表
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByGoods($where, $field = '*', $page = 0, $limit = 0, $order = '', $group = '')
    {

        $res = db('goods')->field($field)->where($where)->group($group)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

    /**
     * 查询流量统计单条记录
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @return array
     */
    public function getoneByFlowstat($tablename = 'flowstat', $where, $field = '*', $order = '', $group = '')
    {
        return db($tablename)->field($field)->where($where)->group($group)->order($order)->find();
    }

    /**
     * 查询流量统计记录
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByFlowstat($tablename = 'flowstat', $where, $field = '*', $page = 0, $limit = 0, $order = '', $group = '')
    {

        $res = db($tablename)->field($field)->where($where)->group($group)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info = $res;
        return $res->items();
    }

}
