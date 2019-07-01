<?php
namespace app\wap\controller;

use think\Lang;
use process\Process;

class Goods extends MobileMall
{

    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'wap\lang\zh-cn\login.lang.php');

    }

    /**
     * @desc 商城首页
     * @author langzhiyao
     * @time 20181019
     */
    public function index(){


        $upload_file = UPLOAD_SITE_URL . DS . ATTACH_ADV.'/';
        $upload_file2 = UPLOAD_SITE_URL . DS . ATTACH_GOODS.'/';
        $upload_file3 = UPLOAD_SITE_URL . DS . ATTACH_COMMON.'/';
//        UPLOAD_SITE_URL
        //获取轮播图
        $banner = db('adv')->field('adv_title,adv_link,adv_code')->where('ap_id =16 AND adv_enabled=1 AND is_show=1')->order('adv_sort asc')->select();
        if(!empty($banner)){
            foreach($banner as $k=>$v){
                $banner[$k]['adv_code'] = $upload_file.$v['adv_code'];
            }
        }

        //获取类别
        $type_1 = db('goodsclass')->field('gc_id,gc_name')->where('gc_show =1 AND gc_parent_id=0')->order('gc_sort asc')->limit('0,4')->select();

        /*if(!empty($type_1)){
            foreach ($type_1 as $ke=>$va) {
                $type_2= db('goodsclass')->field('gc_id,gc_name')->where('gc_show =1 AND gc_parent_id="'.$va["gc_id"].'"')->order('gc_sort asc')->find();
                if(!empty($type_2)){
                    $type[] = db('goodsclass')->field('gc_id,gc_name')->where('gc_show =1 AND gc_parent_id="'.$type_2["gc_id"].'"')->order('gc_sort asc')->find();
                }
            }
        }*/
        if(!empty($type_1)){
            foreach($type_1 as $k=>$v){
                $type_1[$k]['link'] =BASE_SITE_URL.DIR_WAP. '/tmpl/product_list.html?gc_id='.$v['gc_id'];
                $type_1[$k]['image'] =$upload_file3.'category-pic-' . $v['gc_id'] . '.jpg';
            }
        }
        //获取第一版广告位
        $gg_one = db('adv')->field('adv_title,adv_link,adv_code')->where('ap_id =17 AND adv_enabled=1 AND is_show=1')->order('adv_sort asc')->find();
        if(!empty($gg_one)){
            $gg_one['adv_code'] = $upload_file.$gg_one['adv_code'];
        }
        //获取商品
        $goods = db('goodscommon')->field('goods_commonid,goods_name,goods_jingle,goods_image,goods_price,goods_marketprice,store_id')->where('goods_state=1 AND goods_verify=1 AND goods_lock=0 AND is_virtual=0')->order('goods_commend desc')->limit(0,10)->select();
        if(!empty($goods)){
            foreach($goods as $key=>$val){
                $goods_id = db('goods')->field('goods_id')->where('goods_commonid = "'.$val["goods_commonid"].'"')->find();
                $goods[$key]['goods_image'] = $upload_file2.$val['store_id'].'/'.$val['goods_image'];
                $goods[$key]['link'] = BASE_SITE_URL.DIR_WAP. '/tmpl/product_detail.html?goods_id='.$goods_id['goods_id'];
            }
        }
        $result[] =array('gg'=>$gg_one,'cp'=>$goods);
        $arr[] =array('lb'=>$banner,'type'=>$type_1,'goods'=>$result);

        output_data($arr);

    }


}