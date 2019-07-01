<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/3
 * Time: 9:46
 */

namespace app\common\model;


use think\Model;

class Flea extends Model
{
    public $page_info;

    /**
     * 商品保存
     *
     * @param    array $param 商品资料
     */
    public function saveGoods($param)
    {
        if (empty($param)) {
            return false;
        }
        $goods_array = array();
        $goods_array['goods_name'] = $param['goods_name'];
        $goods_array['gc_id'] = $param['gc_id'];
        $goods_array['gc_name'] = $param['gc_name'];
        $goods_array['member_id'] = $_SESSION['member_id'];
        $goods_array['member_name'] = $_SESSION['member_name'];
        $goods_array['goods_image'] = $param['goods_image'];
        $goods_array['flea_quality'] = $param['flea_quality'];
        $goods_array['flea_area_id'] = $param['flea_area_id'];
        $goods_array['flea_area_name'] = $param['flea_area_name'];
        $goods_array['flea_pname'] = $param['flea_pname'];
        $goods_array['flea_pphone'] = $param['flea_pphone'];
        $goods_array['goods_tag'] = $param['goods_tag'];
        $goods_array['goods_price'] = $param['goods_price'];
        $goods_array['goods_store_price'] = $param['goods_store_price'];
        $goods_array['goods_show'] = $param['goods_show'];
        $goods_array['goods_commend'] = $param['goods_commend'];
        $goods_array['goods_add_time'] = time();
        $goods_array['goods_body'] = $param['goods_body'];
        $goods_array['goods_keywords'] = $param['goods_keywords'];
        $goods_array['goods_description'] = $param['goods_description'];

        $result = db('s')->insert();
        return $result;
    }

    /**
     * 商品列表
     */
    public function listGoods($param, $page = '', $field = '*')
    {
        $condition_str = $this->getCondition($param);
        $order = $param['order'] ? $param['order'] : 'goods_id desc';
        $res = db('flea')->where($condition_str)->field($field)->order($order)->paginate($page);
        $this->page_info = $res;
        $list_goods = $res->items();
        return $list_goods;
    }

    /**
     * 他们正在卖的
     */
    public function saleGoods($param, $page = '', $field = '*')
    {
        $condition_str = $this->getCondition($param);
        $order = 'goods_id desc';
        $list_goods = db('flea')->alias('flea')->join('__MEMBER__ member', 'flea.member_id=member.member_id', 'LEFT')->field($field)->order($order)->where($condition_str)->paginate($page);
        $this->page_info = $list_goods;
        return $list_goods->items();
    }

    /**
     *    统计当前卖家正在出售闲置个数
     */
    public function statistic($member_id)
    {
        $where = 'and member.member_id=' . $member_id;
        $field = 'member.member_avatar,member.member_qq,member.member_id,member.member_name,count(*) as num';
        $group = 'member.member_id';
        $goods_array = db('flea')->alias('flea')->join('__MEMBER__ member', 'flea.member_id=member.member_id', 'LEFT')->field($field)->where($where)->group($group)->select();
        return $goods_array['0'];
    }

    /**
     * 闲置物品多图
     *
     * @param    array $param 列表条件
     * @param    array $field 显示字段
     */
    public function getListImageGoods($param, $field = '*')
    {
        if (empty($param)) {
            return false;
        }
        //得到条件语句
        $condition_str = $this->getCondition($param);
        $list_image = db('fleaupload')->field($field)->where($condition_str)->select();
        return $list_image;
    }

    /**
     * 得到商品所有缩略图，带商品路径
     *
     * @param    array $goods 商品列表
     */
    public function getThumb(&$goods, $path)
    {
        if (is_array($goods)) {
            foreach ($goods as $k => $v) {
                $goods[$k]['thumb_small'] = $path . $v['file_thumb'];
                $goods[$k]['thumb_mid'] = $path . str_replace('_small', '_mid', $v['file_thumb']);
                $goods[$k]['thumb_max'] = $path . str_replace('_small', '_max', $v['file_thumb']);
            }
        }
    }

    /**
     * 商品信息更新
     *
     * @param    array $param 列表条件
     * @param    int $goods_id 商品id
     */
    public function updateGoods($param, $goods_id)
    {
        if (empty($param)) {
            return false;
        }
        $update = false;
        if (is_array($goods_id))
            $goods_id = implode(',', $goods_id);
        //得到条件语句
        $update =  db('flea')->whereIn('goods_id',$goods_id)->update($param);
        return $update;
    }

    /**
     * 闲置物品数量
     *
     * @param    array $param 闲置物品资料
     */
    public function countGoods($param, $type = '')
    {
        if (empty($param)) {
            return false;
        }
        $condition_str = $this->getCondition($param);

        $goods_array = db('flea')->where($condition_str)->count();
        return $goods_array[0][0];
    }

    /**
     * 闲置物品删除
     *
     * @param    array $param 列表条件
     * @param    int $goods_id 商品id
     */
    public function dropGoods($goods_id)
    {
        if (empty($goods_id)) {
            return false;
        }
        $del_state = db('flea')->whereIn('goods_id', $goods_id);
        if ($del_state) {
            $image_more = db('fleaupload')->field('file_name')->whereIn('item_id', $goods_id)->whereIn('upload_type', '12,13')->select();
            if (is_array($image_more) && !empty($image_more)) {
                foreach ($image_more as $v) {
                    @unlink(BASE_UPLOAD_PATH . DS . ATTACH_MALBUM . DS . session('member_id') . DS . $v['file_name']);
                }
            }
            db('fleaupload')->whereIn('item_id', $goods_id)->whereIn('upload_type', '12,13')->select();
        }
        return true;
    }

    /**
     * 闲置物品多图删除
     *
     * @param    array $param 删除条件
     */
    public function dropImageGoods($param)
    {
        if (empty($param)) {
            return false;
        }
        //得到条件语句
        $condition_str = $this->getCondition($param);
        $image_more = db('fleaupload')->where($condition_str)->field('file_name')->select();
        if (is_array($image_more) && !empty($image_more)) {
            foreach ($image_more as $v) {
                @unlink(BASE_UPLOAD_PATH . DS . ATTACH_MALBUM . DS . session('member_id') . DS . $v['file_name']);
            }
        }
        $state = db('fleaupload')->where($condition_str)->delete();
        return $state;
    }
    // 	/**
    // 	 *	查询拥有闲置物品数量从多到少会员列表
    // 	 */
    // 	public function descmember(){
    // 		$param['table']='flea,member';
    // 		$param['field']='member.member_id,member.member_name,member.member_avatar';
    // 		$param['join_type']='left join';
    // 		$param['join_on']=array('member.member_id=flea.member_id');
    // 		$param['group']='flea.member_id';
    // 		$param['order']='count(*) desc';
    // 		return db::select($param);
    // 	}
    /**
     * 按所属分类查找闲置物品
     */
    public function getGoodsByClass($param)
    {
        $condition_str = $this->getCondition($param);
        $field = $param['field'] ? $param['field'] : '*';
        $order = $param['order'];
        $goods_array = db('flea')->alias('flea')->join('__FLEACLASS__ fleaclass','flea.gc_id=flea_class.gc_id')->where($condition_str)->field($field)->order($order)->select();
        return $goods_array;
    }

    /**
     * 查询闲置信息id
     */
    public function getFleaID()
    {
        $flea_ids = Db::select(array('table' => 'flea', 'field' => 'goods_id', 'limit' => '27'));
        return $flea_ids;
    }

    /**
     * 将条件数组组合为SQL语句的条件部分
     *
     * @param    array $condition_array
     * @return    string
     */
    private function getCondition($condition_array)
    {
        $condition_sql = '1=1';
        if (isset($condition_array['member_id']) && $condition_array['member_id'] != '') {
            $condition_sql .= " and member_id = " . $condition_array['member_id'];
        }
        if (isset($condition_array['image_store_id']) && $condition_array['image_store_id'] != '') {
            $condition_sql .= " and store_id=" . $condition_array['image_store_id'] . " and item_id=" . $condition_array['item_id'] . " and upload_type='" . $condition_array['image_type'] . "'";
        }
        //添加不等于某商品的条件
        if (isset($condition_array['goods_id_diff']) && $condition_array['goods_id_diff'] != 0) {
            $condition_sql .= " and goods_id<>" . $condition_array['goods_id_diff'];
        }
        if (isset($condition_array['gc_id_list']) && $condition_array['gc_id_list'] != '') {
            $condition_sql .= " and `flea`.gc_id IN (" . ltrim($condition_array['gc_id_list'], ',') . ")";
        }
        if (isset($condition_array['goods_id']) && $condition_array['goods_id'] != 0) {
            $condition_sql .= " and goods_id = " . $condition_array['goods_id'];
        }
        if (isset($condition_array['keyword']) && $condition_array['keyword'] != '') {
            $condition_sql .= " and goods_name LIKE '%" . $condition_array['keyword'] . "%'";
        }
        if (isset($condition_array['upload_id']) && $condition_array['upload_id'] != '') {
            $condition_sql .= " and upload_id=" . $condition_array['upload_id'];
        }
        if (isset($condition_array['goods_id_in'])) {
            if ($condition_array['goods_id_in'] == '') {
                $condition_sql .= " and `flea`.goods_id in ('') ";
            }
            else {
                $condition_sql .= " and `flea`.goods_id in({$condition_array['goods_id_in']})";
            }
        }
        if (isset($condition_array['gc_id']) && $condition_array['gc_id'] != '') {
            $condition_sql .= " and gc_id IN (" . $this->_getRecursiveClass(array($condition_array['gc_id'])) . ")";
            //$condition_sql	.= " and `goods`.gc_id IN ({$condition_array['gc_id']})";
        }
        if (isset($condition_array['gc_id_in'])) {
            if ($condition_array['gc_id_in'] == '') {
                $condition_sql .= " and `flea`.gc_id in ('') ";
            }
            else {
                $condition_sql .= " and `flea`.gc_id in({$condition_array['gc_id_in']})";
            }
        }
        if (isset($condition_array['key_input']) && $condition_array['key_input'] != '') {
            $condition_sql .= " and (goods_name LIKE '%{$condition_array['key_input']}%' or goods_tag like '%{$condition_array['key_input']}%')";
        }
        if (isset($condition_array['like_member_name']) && $condition_array['like_member_name'] != '') {
            $condition_sql .= " and member_name LIKE '%" . $condition_array['like_member_name'] . "%'";
        }
        /*	检索	*/
        if (isset($condition_array['pic_input']) && $condition_array['pic_input'] == 2) {
            $condition_sql .= " and goods_image <> ''";
        }
        if (isset($condition_array['body_input']) && $condition_array['body_input'] == 2) {
            $condition_sql .= " and goods_body <> ''";
        }
        if (isset($condition_array['seller_input']) && $condition_array['seller_input'] != '') {
            $condition_sql .= " and member_id = " . $condition_array['seller_input'];
        }
        if (isset($condition_array['quality_input']) && $condition_array['quality_input'] != '') {
            if ($condition_array['quality_input'] == 7) {
                $condition_sql .= " and flea_quality <= 7";
            }
            else {
                $condition_sql .= " and flea_quality >= " . $condition_array['quality_input'];
            }
        }
        if (isset($condition_array['start_input']) && $condition_array['start_input'] != '') {
            $condition_sql .= " and goods_store_price >= " . $condition_array['start_input'];
        }
        if (isset($condition_array['end_input']) && $condition_array['end_input'] != '') {
            $condition_sql .= " and goods_store_price <= " . $condition_array['end_input'];
        }
        if (isset($condition_array['areaid']) && $condition_array['areaid'] != '') {
            $condition_sql .= " and flea_area_id in (" . $condition_array['areaid'] . ")";
        }
        return $condition_sql;
    }

    /*
     * 递归得到商品分类的ID
     * @param array $class_list
     * @return string $class_string 逗号分割的分类ID及其子ID的字符串
     */
    private function _getRecursiveClass($class_id)
    {

        static $class_list = '';

        $id = implode(',', $class_id);
        $class_list .= ',' . $id;
        $temp_list = Db::select(array(
                                    'table' => 'flea_class', 'where' => 'gc_parent_id in (' . $id . ')',
                                    'field' => 'gc_id'
                                ));

        if (!empty($temp_list)) {

            $_tmp = array();    //取得ID组成的一维数组

            foreach ($temp_list as $key => $val) {

                $_tmp[] = $val['gc_id'];

            }
            unset($temp_list);
            $temp_list = $_tmp;

            $id = $this->_getRecursiveClass($temp_list);

        }

        return trim($class_list, ',');

    }
}