<?php

namespace app\common\model;


use think\Model;

class Storesnstracelog extends Model
{
    public $page_info;
    /**
     * 店铺动态列表
     *
     * @param unknown $condition
     * @param string $field
     * @param string $order
     * @param number $page
     * @return array
     */
    public function getStoreSnsTracelogList($condition, $field = '*', $order = 'strace_id desc',$limit = 0, $page = 0) {
        if($limit) {
            return db('storesnstracelog')->where($condition)->field($field)->order($order)->limit($limit)->page($page)->select();
        }else{
            $res = db('storesnstracelog')->where($condition)->field($field)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info=$res;
            return $res->items();
        }
    }

    /**
     * 获得店铺动态总数
     *
     * @param unknown $condition
     * @return array
     */
    public function getStoreSnsTracelogCount($condition) {
        return db('storesnstracelog')->where($condition)->count();
    }

    /**
     * 获取单条店铺动态
     *
     * @param unknown $condition
     * @return array
     */
    public function getStoreSnsTracelogInfo($condition) {
        return db('storesnstracelog')->where($condition)->find();
    }

    /**
     * 保存店铺动态
     *
     * @param array $insert
     * @param bool $replace
     * @return boolean
     */
    public function saveStoreSnsTracelog($insert, $replace = false) {
        return db('storesnstracelog')->insertGetId($insert, $replace);
    }

    /**
     * 保存店铺动态
     *
     * @param array $insert
     * @param bool $replace
     * @return boolean
     */
    public function saveStoreSnsTracelogAll($insert, $replace = false) {
        return db('storesnstracelog')->insertAll($insert, $replace);
    }

    /**
     * 更新店铺动态
     *
     * @param array $update
     * @param array $condition
     * @return boolean
     */
    public function editStoreSnsTracelog($update, $condition) {
        return db('storesnstracelog')->where($condition)->update($update);
    }

    /**
     * 删除店铺动态
     *
     * @param array $condition
     * @return boolean
     */
    public function delStoreSnsTracelog($condition) {
        return $this->where($condition)->delete();
    }

    /**
     * 拼写个类型样式
     * @param string $type 动态类型
     * @param array  $data 相关数据
     */
    public function spellingStyle($type,$data){
        //1'relay',2'normal',3'new',4'coupon',5'xianshi',6'mansong',7'bundling',8'groupbuy',9'recommend',10'hotsell'
        $rs = '';
        switch ($type){
            case '2':
                break;
            case '3':
                $rs = "<div class=\"fd-media\">
					<div class=\"goodsimg\"><a target=\"_blank\" href=\"" . url('goods/index', array('goods_id'=>$data['goods_id'])) . "\"><img src=\"" . cthumb($data['goods_image'], 240, $data['store_id']) . "\" onload=\"javascript:DrawImage(this,120,120);\" alt=\"" . $data['goods_name'] . "\"></a></div>
    					<div class=\"goodsinfo\">
    					<dl>
    					   <dt><i class=\"desc-type desc-type-new\">" . lang('store_sns_new_selease') . "</i><a target=\"_blank\" href=\"" . url('goods/index', array('goods_id'=>$data['goods_id'])) . "\">" . $data['goods_name'] . "</a></dt>
							<dd>" . lang('sns_sharegoods_price') . lang('ds_colon') . lang('currency') . dsPriceFormat($data['goods_price']) . "</dd>
							<dd>" . ($data['goods_transfee_charge'] == '1' ? lang('store_sns_free_shipping') : lang('sns_sharegoods_freight') . lang('ds_colon') . lang('currency') . dsPriceFormat($data['goods_freight'])) . "</dd>
	                  		<dd nctype=\"collectbtn_" . $data['goods_id'] . "\"><a href=\"javascript:void(0);\" onclick=\"javascript:collect_goods('" . $data['goods_id'] . "','succ','collectbtn_" . $data['goods_id'] . "');\">" . lang('sns_sharegoods_collect') . "</a></dd>
	                  	</dl>
	                  </div>
	             </div>";
                break;
            case '4':
                $rs = "<div class=\"fd-media\">
					<div class=\"goodsimg\"><a target=\"_blank\" href=\"" . url('coupon_store/detail', array('coupon_id' => $data['coupon_id'], 'id' => $data['store_id'])) . "\"><img src=\"" . $data['coupon_pic'] . "\" onerror=\"this.src='" . SHOP_TEMPLATES_URL . "/images/default_coupon_image.png'\" onload=\"javascript:DrawImage(this,120,120);\" alt=\"" . $data['coupon_title'] . "\"></a></div>
    					<div class=\"goodsinfo\">
    					<dl>
        					<dt><i class=\"desc-type desc-type-coupon\">" . lang('store_sns_coupon') . "</i><a target=\"_blank\" href=\"" . url('coupon_store/detail', array('coupon_id' => $data['coupon_id'], 'id' => $data['store_id'])) . "\">" . $data['coupon_title'] . "</a></dt>
        					<dd>" . lang('store_sns_coupon_price') . lang('ds_colon') . lang('currency') . dsPriceFormat($data['coupon_price']) . "</dd>
        					<dd>" . lang('store_sns_start-stop_time') . lang('ds_colon') . date('Y-m-d H:i', $data['coupon_start_date']) . "~" . date('Y-m-d H:i', $data['coupon_end_date']) . "</dd>
	                  	</dl>
	                  </div>
			        </div>";
                break;
            case '5':
                $rs = "<div class=\"fd-media\">
					<div class=\"goodsimg\"><a target=\"_blank\" href=\"" . url('goods/index', array('goods_id'=>$data['goods_id'])) . "\"><img src=\"" . cthumb($data['goods_image'], 240,$data['store_id']) . "\" onload=\"javascript:DrawImage(this,120,120);\" alt=\"" . $data['goods_name'] . "\"></a></div>
    					<div class=\"goodsinfo\">
    					<dl>
        					<dt><i class=\"desc-type desc-type-xianshi\">" . lang('store_sns_xianshi') . "</i><a target=\"_blank\" href=\"" . url('goods/index', array('goods_id'=>$data['goods_id'])) . "\">" . $data['goods_name'] . "</a></dt>
    						<dd>" . lang('sns_sharegoods_price') . lang('ds_colon') . lang('currency') . dsPriceFormat($data['goods_price']) . "</dd>
    						<dd>" . lang('store_sns_formerprice') . lang('ds_colon') . lang('currency') . dsPriceFormat($data['xianshi_price']) . "</dd>
    				        <dd nctype=\"collectbtn_" . $data['goods_id'] . "\"><a href=\"javascript:void(0);\" onclick=\"javascript:collect_goods('" . $data['goods_id'] . "','succ','collectbtn_" . $data['goods_id'] . "');\">" . lang('sns_sharegoods_collect') . "</a></dd>
	                  	</dl>
	                  </div>
    	             </div>";
                break;
            case '6':
                $rs = "<div class=\"fd-media\">
					<div class=\"goodsimg\"><a target=\"_blank\" href=\"" . url('show_store/index', array('store_id'=>$data['store_id'])) . "\"><img src=\"" . SHOP_TEMPLATES_URL . "/images/mjs-pic.gif\" onload=\"javascript:DrawImage(this,120,120);\" alt=\"".$data['ansong_name']."\"></a></div>
    					<div class=\"goodsinfo\">
    					<dl>
        					<dt><i class=\"desc-type desc-type-mansong\">" . lang('store_sns_mansong') . "</i><a target=\"_blank\" href=\"" . url('show_store/index', array('store_id'=>$data['store_id'])) . "\">" . $data['mansong_name'] . "</a></dt>
    						<dd>" . lang('store_sns_start-stop_time') . lang('ds_colon') . date('Y-m-d H:i', $data['start_time']) . "~" . date('Y-m-d H:i', $data['end_time']) . "</dd>
	                  	</dl>
				        </div>
    	             </div>";
                break;
            case '7':
                $rs = "<div class=\"fd-media\">
					<div class=\"goodsimg\"><a target=\"_blank\" href=\"" . url('goods/index', array('goods_id'=>$data['goods_id'])) . "\"><img src=\"" . cthumb($data['bl_img'], 240, $data['store_id']) . "\" onload=\"javascript:DrawImage(this,120,120);\" alt=\"" . $data['bl_name'] . "\"></a></div>
    					<div class=\"goodsinfo\">
    					<dl>
        					<dt><i class=\"desc-type desc-type-bundling\">" . lang('store_sns_bundling') . "</i><a target=\"_blank\" href=\"" . url('goods/index', array('goods_id'=>$data['goods_id'])) . "\">".$data['bl_name']."</a></dt>
    						<dd>" . lang('store_sns_bundling_price') . lang('ds_colon') . lang('currency') . dsPriceFormat($data['bl_discount_price']) . "</dd>
    			            <dd>" . (($data['bl_freight_choose']==1) ? lang('store_sns_free_shipping') : lang('sns_sharegoods_freight') . lang('ds_colon') . lang('currency') . dsPriceFormat($data['bl_freight'])) . "</dd>
						</dl>
                    </div>
                    </div>";
                break;
            case '8':
                $rs = "<div class=\"fd-media\">
					<div class=\"goodsimg\"><a target=\"_blank\" href=\"" . url('goods/index', array('goods_id'=>$data['goods_id'])) . "\"><img src=\"" . gthumb($data['group_pic'],'small',$data['store_id']) . "\" onload=\"javascript:DrawImage(this,120,120);\" alt=\"" . $data['group_name'] . "\"></a></div>
    					<div class=\"goodsinfo\">
    					<dl>
        					<dt><i class=\"desc-type desc-type-groupbuy\">" . lang('store_sns_gronpbuy') . "</i><a target=\"_blank\" href=\"" . url('goods /index', array('goods_id'=>$data['goods_id'])) . "\">" . $data['group_name'] . "</a></dt>
        					<dd>" . lang('store_sns_goodsprice') . lang('ds_colon') . lang('currency') . dsPriceFormat($data['goods_price']) . "</dd>
    				        <dd>" . lang('store_sns_groupprice') . lang('ds_colon') . lang('currency') . dsPriceFormat($data['groupbuy_price']) . "</dd>
    		                <dd>" . lang('store_sns_start-stop_time') . lang('ds_colon') . date('Y-m-d H:i', $data['start_time']) . "~" . date('Y-m-d H:i', $data['end_time']) . "</dd>
		                </dl>
					</div>
				</div>";
                break;
            case '9':
                $rs = "<div class=\"fd-media\">
    				<div class=\"goodsimg\"><a target=\"_blank\" href=\"" . url('goods/index', array('goods_id'=>$data['goods_id'])) . "\"><img src=\"" . thumb($data, 240) . "\" onload=\"javascript:DrawImage(this,120,120);\" alt=\"" . $data['goods_name'] . "\"></a></div>
    				<div class=\"goodsinfo\">
    				<dl>
    					<dt><i class=\"desc-type desc-type-recommend\">" . lang('store_sns_store_recommend') . "</i><a target=\"_blank\" href=\"" . url('goods/index', array('goods_id'=>$data['goods_id'])) . "\">" . $data['goods_name'] . "</a></dt>
				        <dd>" . lang('sns_sharegoods_price') . lang('ds_colon') . lang('currency') . dsPriceFormat($data['goods_price']) . "</dd>
		                <dd>" . lang('sns_sharegoods_freight') . lang('ds_colon') . lang('currency') . dsPriceFormat($data['goods_freight']) . "</dd>
		                <dd nctype=\"collectbtn_" . $data['goods_id'] . "\"><a href=\"javascript:void(0);\" onclick=\"javascript:collect_goods('" . $data['goods_id'] . "','succ','collectbtn_" . $data['goods_id'] . "');\">" . lang('sns_sharegoods_collect') . "</a></dd>
    				</dl>
                    </div>
	             </div>";
                break;
            case '10':
                $rs = "<div class=\"fd-media\">
                    <div class=\"goodsimg\"><a target=\"_blank\" href=\"" . url('goods/index', array('goods_id'=>$data['goods_id'])) . "\"><img src=\"" . thumb($data, 240) . "\" onload=\"javascript:DrawImage(this,120,120);\" alt=\"" . $data['goods_name'] . "\"></a></div>
					<div class=\"goodsinfo\">
						<dl>
							<dt><i class=\"desc-type desc-type-hotsell\">" . lang('store_sns_hotsell') . "</i><a target=\"_blank\" href=\"" . url('goods/index', array('goods_id'=>$data['goods_id'])) . "\">" . $data['goods_name'] . "</a></dt>
					        <dd>" . lang('sns_sharegoods_price') . lang('ds_colon') . lang('currency') . dsPriceFormat($data['goods_price']) . "</dd>
							<dd>" . lang('sns_sharegoods_freight') . lang('ds_colon') . lang('currency') . dsPriceFormat($data['goods_freight']) . "</dd>
	                  		<dd nctype=\"collectbtn_" . $data['goods_id'] . "\"><a href=\"javascript:void(0);\" onclick=\"javascript:collect_goods('" . $data['goods_id'] . "','succ','collectbtn_" . $data['goods_id']. "');\">" . lang('sns_sharegoods_collect') . "</a></dd>
	                  	</dl>
	                  </div>
    	             </div>";
                break;
        }
        return $rs;
    }
}