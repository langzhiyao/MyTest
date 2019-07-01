<?php

namespace app\common\taglib;
use think\template\TagLib;

class Dstaglib extends TagLib{
    /**
     * 定义标签列表
     */
    protected $tags   =  [
        'adv' => ['attr' => 'limit,order,where,item', 'close' => 1],
    ];
    
    public function tagAdv($tag, $content) {
        $order = !empty($tag['order']) ? $tag['order'] : ''; //排序
        $limit = !empty($tag['limit']) ? $tag['limit'] : '1';
        $where = !empty($tag['where']) ? $tag['where'] : ''; //查询条件
        $item = !empty($tag['item']) ? $tag['item'] : 'item'; // 返回的变量item	
        $key = !empty($tag['key']) ? $tag['key'] : 'key'; // 返回的变量key
        $ap_id = !empty($tag['ap_id']) ? $tag['ap_id'] : '0'; // 返回的变量key
        
        $str = '<?php ';
        $str .= '$ap_id =' . $ap_id . ';';
        $str .= '$ad_position = db("advposition")->cache(3600)->column("ap_id,ap_name,ap_width,ap_height","ap_id");';
        $str .= '$result = db("adv")->where("ap_id=$ap_id  and adv_enabled = 1 and adv_start_date < ' . strtotime(date('Y-m-d H:00:00')) . ' and adv_end_date > ' . strtotime(date('Y-m-d H:00:00')) . ' ")->order("adv_sort desc")->cache(3600)->limit("' . $limit . '")->select();';
        $str .= '
if(!in_array($ap_id,array_keys($ad_position)) && $ap_id)
{
  db("advposition")->insert(array(
         "ap_id"=>$ap_id,
         "ap_name"=>CONTROLLER_NAME."页面自动增加广告位 $ap_id ",
  ));
  delCacheFile("temp"); // 删除缓存
  \think\Cache::clear();  
}


$c = ' . $limit . '- count($result); //  如果要求数量 和实际数量不一样 并且编辑模式
if($c > 0 && input("get.edit_ad"))
{
    for($i = 0; $i < $c; $i++) // 还没有添加广告的时候
    {
      $result[] = array(
          "adv_code" => "/public/images/not_adv.jpg",
          "adv_link" => config("url_domain_root")."index.php/Admin/Adv/adv_add/ap_id/$ap_id.html",
          "adv_title"   =>"暂无广告图片",
          "not_adv" => 1,
          "target" => 0,
          "ap_id"   =>$ap_id,
      );  
    }
}

foreach($result as $' . $key . '=>$' . $item . '):       

    $'.$item. '["position"] = $ad_position[$' . $item . '["ap_id"]]; 
    
    if(input("get.edit_ad") && !isset($' . $item . '["not_adv"]) )
    {
        
        $' . $item . '["style"] = "filter:alpha(opacity=50); -moz-opacity:0.5; -khtml-opacity: 0.5; opacity: 0.5"; // 广告半透明的样式
        $' . $item . '["adv_link"] = config("url_domain_root")."index.php/Admin/Adv/adv_edit/adv_id/".$' . $item . '[\'adv_id\'].".html";        
        $' . $item . '["adv_title"] = $ad_position[$' . $item . '["ap_id"]]["ap_name"]."===".$' . $item . '["adv_title"];
        $' . $item . '["target"] = 0;
    }
    ?>';
        $str .= $content;
        $str .= '<?php endforeach; ?>';
        if (!empty($str)) {
            return $str;
        }
        return;
    }
}

