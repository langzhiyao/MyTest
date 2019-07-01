<?php

namespace app\common\model;

use think\Model;

class Companylog extends Model {

    public $page_info;
    /**
     * 增加帮助类型
     *
     * @param
     * @return int
     */
    public function addLog($type_array) {
        $log_id = db('companylog')->insertGetId($type_array);
        return $log_id;
    }

}
