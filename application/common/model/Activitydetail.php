<?php

namespace app\common\model;


use think\Model;

class Activitydetail extends Model
{
    public $page_info;
    /**
     * 添加
     *
     * @param array $input
     * @return bool
     */
    public function add($input){
        return db('activitydetail')->insert($input);
    }
    /**
     * 更新
     *
     * @param array $input 更新内容
     * @param string $id 活动内容id
     * @return bool
     */
    public function updateinfo($input,$id){
        return db('activitydetail')->where('activity_detail_id in('.$id.')')->update($input);
    }
    /**
     * 根据条件更新
     *
     * @param array $input 更新内容
     * @param array $condition 更新条件
     * @return bool
     */
    public function updateList($input,$condition){
        $where=$this->getCondition($condition);
        return db('activitydetail')->where($where)->update($input);
    }
    /**
     * 删除
     *
     * @param string $id
     * @return bool
     */
    public function del($id){
        return db('activitydetail')->where('activity_detail_id in('.$id.')')->delete();
    }
    /**
     * 根据条件删除
     *
     * @param array $condition 条件数组
     * @return bool
     */
    public function delList($condition){
        $where = $this->getCondition($condition);
        return db('activitydetail')->where($where)->delete();
    }
    /**
     * 根据条件查询活动内容信息
     *
     * @param array $condition 查询条件数组
     * @param obj $page	分页对象
     * @return array 二维数组
     */
    public function getList($condition,$page=''){

        $where	= $this->getCondition($condition);
        $order	= isset($condition['order'])? $condition['order'] : '';
        $res=db('activitydetail')->alias('a')->where($where)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info=$res;
        return $res->items();
    }
    /**
     * 根据条件查询活动商品内容信息
     *
     * @param array $condition 查询条件数组
     * @param obj $page	分页对象
     * @return array 二维数组
     */
    public function getGoodsJoinList($condition,$page=''){

        $field	= 'a.*,g.*';
        $where	= $this->getCondition($condition);
        $order	= isset($condition['order'])?$condition['order']:'';
        $res=db('activitydetail')->alias('a')->join('__GOODS__ g','a.item_id=g.goods_id')->field($field)->where($where)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info=$res;
        return $res->items();
    }
    /**
     * 查询活动商品信息
     *
     * @param array $condition 查询条件数组
     * @param obj $page	分页对象
     * @return array 二维数组
     */
    public function getGoodsList($condition,$page=''){

        $field	= 'a.activity_detail_sort,g.goods_id,g.store_id,g.goods_name,g.goods_price,g.goods_image';
        $where	= $this->getCondition($condition);
        $order	= $condition['order'];
        $res= db('activitydetail')->alias('a')->join('__GOODS__ g','a.item_id=g.goods_id')->field($field)->where($where)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info=$res;
        return $res->items();
    }
    /**
     * 构造查询条件
     *
     * @param array $condition 查询条件数组
     * @return string
     */
    private function getCondition($condition){
        $conditionStr	= '1=1';
        if($condition['activity_id']>0){
            $conditionStr	.= " and a.activity_id = '{$condition['activity_id']}'";
        }
        if (isset($condition['activity_detail_id_in'])){
            if ($condition['activity_detail_id_in'] == ''){
                $conditionStr	.= " and activity_detail_id in ('')";
            }else{
                $conditionStr	.= " and activity_detail_id in ({$condition['activity_detail_id_in']})";
            }
        }
        if(isset($condition['activity_detail_state_in'])){
            if ($condition['activity_detail_state_in'] == ''){
                $conditionStr	.= " and activity_detail_state in ('')";
            }else{
                $conditionStr	.= " and activity_detail_state in ({$condition['activity_detail_state_in']})";
            }
        }
        if(isset($condition['activity_detail_state'])){
            $conditionStr	.= " and a.activity_detail_state='".$condition['activity_detail_state']."'";
        }
        if(isset($condition['gc_id'])){
            $conditionStr	.= " and g.gc_id='{$condition['gc_id']}'";
        }
        if(isset($condition['brand_id'])){
            $conditionStr	.= " and g.brand_id='{$condition['brand_id']}' ";
        }
        if(isset($condition['name'])){
            $conditionStr	.= " and g.goods_name like '%{$condition['name']}%'";
        }
        if(isset($condition['item_id'])&&intval($condition['item_id'])>0){
            $conditionStr	.= " and a.item_id='".intval($condition['item_id'])."'";
        }
        if(isset($condition['item_name'] )){
            $conditionStr	.= " and a.item_name like '%{$condition['item_name']}%'";
        }
        if(isset($condition['store_id'])&&intval($condition['store_id'])>0){
            $conditionStr	.= " and a.store_id='".intval($condition['store_id'])."'";
        }
        if(isset($condition['store_name'])){
            $conditionStr	.= " and a.store_name like '%{$condition['store_name']}%'";
        }
        if (isset($condition['goods_show'])) {
            $conditionStr	.= " and g.goods_show= '{$condition['goods_show']}'";
        }
        return $conditionStr;
    }
}