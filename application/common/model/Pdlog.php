<?php

namespace app\common\model;

use think\Model;

class Pdlog extends Model {

    public $page_info;
    /**
     * 增加帮助类型
     *
     * @param
     * @return int
     */
    public function addLog($type_array) {
        $log_id = db('pdlog')->insertGetId($type_array);
        return $log_id;
    }

    /**
     * 删除帮助类型记录
     *
     * @param
     * @return bool
     */
    public function delHelpType($condition) {
        if (empty($condition)) {
            return false;
        } else {
            $condition['help_code'] = 'auto'; //只有auto的可删除
            $result = db('helptype')->where($condition)->delete();
            return $result;
        }
    }

}
