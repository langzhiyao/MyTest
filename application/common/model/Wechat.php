<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/13
 * Time: 11:29
 */

namespace app\common\model;


use think\Model;

class Wechat extends Model
{
    public $page_info;

//获取公众号配置信息
    public function WxConfig(){
        return db('wxconfig')->find();
    }

    //关键字查询
    public function keyText($field='',$wh='',$order='t.createtime DESC'){
        $where="type = 'TEXT'";
        if(!empty($wh)) {
            $where .= ' and '.$wh;
        }
        $lists=db('wxkeyword')->alias('k')->join('__WXTEXT__ t','t.id=k.pid','LEFT')->where($where)->field($field)->order($order)->find();
        return $lists;
    }

    //会员查询
    public function member(){
        $info=db('member')->where('weixin_info is not null')->field('member_name,member_add_time,weixin_unionid,member_wxopenid,member_id')->paginate(8,false,['query' => request()->param()]);
        $this->page_info=$info;
        return $info->items();
    }

    //插入推送
    public function wxMsg($data){
        db('wxmsg')->insert($data);
    }

    public function msgList($where=''){
        $res=db('wxmsg')->alias('w')->join('__MEMBER__ m','w.member_id=m.member_id','LEFT')->where($where)->field('w.*,m.member_name')->order('createtime DESC')->paginate(10,false,['query' => request()->param()]);
        $this->page_info=$res;
        return $res->items();
    }
}