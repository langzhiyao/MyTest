<?php

namespace app\common\model;


use think\Model;

class Inform extends Model {

	public $page_info;
	/**
	 * 查询举报数量
	 * @param array $condition
	 * @return int
	 */
	public function getInformCount($condition) {
		return db('inform')->where($condition)->count();
	}
	/*
	 * 构造条件
	 */
	private function getCondition($condition){
		$condition_str = '1=1' ;
		if(!empty($condition['inform_state'])) {
			$condition_str.= " and  inform_state = '{$condition['inform_state']}'";
		}
		if(!empty($condition['inform_goods_id'])) {
			$condition_str.= " and  inform_goods_id = '{$condition['inform_goods_id']}'";
		}
		if(!empty($condition['inform_id'])) {
			$condition_str.= " and  inform_id = '{$condition['inform_id']}'";
		}
		if(!empty($condition['inform_member_id'])) {
			$condition_str.= " and  inform_member_id = '{$condition['inform_member_id']}'";
		}
		if(!empty($condition['inform_store_id'])) {
			$condition_str.= " and  inform_store_id = '{$condition['inform_store_id']}'";
		}
		if(!empty($condition['inform_handle_type'])) {
			$condition_str.= " and  inform_handle_type = '{$condition['inform_handle_type']}'";
		}
		if(!empty($condition['inform_goods_name'])) {
			$condition_str.= " and  inform_goods_name like '%".$condition['inform_goods_name']."%'";
		}
		if(!empty($condition['inform_member_name'])) {
			$condition_str.= " and  inform_member_name like '%".$condition['inform_member_name']."%'";
		}
		if(!empty($condition['inform_type'])) {
			$condition_str.= " and  inform_subject_type_name like '%".$condition['inform_type']."%'";
		}
		if(!empty($condition['inform_subject'])) {
			$condition_str.= " and  inform_subject_content like '%".$condition['inform_subject']."%'";
		}
		if(!empty($condition['inform_datetime_start'])) {
			$condition_str.= " and  inform_datetime > '{$condition['inform_datetime_start']}'";
		}
		if(!empty($condition['inform_datetime_end'])) {
			$end = $condition['inform_datetime_end'] + 86400;
			$condition_str.= " and  inform_datetime < '$end'";
		}
		return $condition_str;
	}

	/*
	 * 增加
	 * @param array $param
	 * @return bool
	 */
	public function saveInform($param){

		return db('inform')->insert($param);

	}

	/*
	 * 更新
	 * @param array $update_array
	 * @param array $where_array
	 * @return bool
	 */
	public function updateInform($update_array, $where_array){

		$where = $this->getCondition($where_array) ;
		return db('inform')->where($where)->update($update_array);

	}

	/*
	 * 删除
	 * @param array $param
	 * @return bool
	 */
	public function dropInform($param){

		$where = $this->getCondition($param) ;
		return db('inform')->where($where)->delete();

	}

	/*
	 *  获得列表
	 *  @param array $condition
	 *  @param obj $page 	//分页对象
	 *  @return array
	 */
	public function getInform($condition='',$page='') {

		$where = $this->getCondition($condition);
		$order = isset($condition['order']) ? $condition['order']: ' inform_id desc ';
		$res= db('inform')->alias('i')->join('__INFORMSUBJECT__ s','i.inform_subject_id = s.inform_subject_id','LEFT')->where($where)->order($order)->paginate($page,false,['query' => request()->param()]);
		$this->page_info=$res;
		return $res->items();
	}

	/*
	 *   根据id获取举报详细信息
	 */
	public function getoneInform($inform_id) {
		$param = intval($inform_id);
		return db('inform')->where('inform_id',$param)->find();

	}

	/*
	 *  判断该商品是否正在被举报
	 *  @param int $goods_id 商品id
	 *  @return bool
	 */
	public function isProcessOfInform($goods_id) {

		$condition = array();
		$condition['inform_goods_id'] = $goods_id;
		$condition['inform_state'] = 1;
		$inform = $this->getInform($condition);
		if(count($inform) !== 0) {
			return true;
		}
		else {
			return false;
		}

	}
	/**
	 * 总数
	 *
	 */
	public function getCount($condition) {
		$condition_str	= $this->getCondition($condition);
		$count=db('inform')->where($condition_str)->count();
		return $count;
	}

}