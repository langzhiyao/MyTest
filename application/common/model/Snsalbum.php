<?php

namespace app\common\model;


use think\Model;

class Snsalbum extends Model
{
    public function getSnsAlbumClassDefault($member_id) {
        if(empty($member_id)) {
            return '0';
        }

        $condition = array();
        $condition['member_id'] = $member_id;
        $condition['is_default'] = 1;
        $info = db('snsalbumclass')->where($condition)->find();

        if(!empty($info)) {
            return $info['ac_id'];
        } else {
            return '0';
        }
    }
}