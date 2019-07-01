<?php

namespace app\common\model;
use think\Model;

class School extends Model {
    public $page_info;
    
    /**
     * 取单条订单信息
     *
     * @param unknown_type $condition
     * @param array $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return unknown
     */
    public function getSchoolInfo($condition = array(), $fields = '*', $school = '', $group = '') {
        $school_info = db('school')->field($fields)->where($condition)->group($group)->order($school)->find();
        if (empty($school_info)) {
            return array();
        }
        return $school_info;
    }

    public function getSchoolById($id){
        return db('school')->where('schoolid',$id)->find();
    }
    public function getOrderCommonInfo($condition = array(), $field = '*') {
        return db('ordercommon')->where($condition)->find();
    }


    /**
     * 修改学校单个字段
     * @param  [type] $schoolid [description]
     * @param  [type] $key     [键名]
     * @param  [type] $velue   [键值]
     * @return [type]          [description]
     */
    public function school_set($schoolid,$key,$velue){
        return db('school')->where('schoolid', $schoolid)->setField($key, $velue);
    }

    /**
     * 取得学校列表(所有)
     * @param unknown $condition
     * @param string $page
     * @param string $field
     * @param string $school
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getSchoolList($condition, $page = '', $field = '*', $school = 'schoolid desc', $limit = '', $extend = array(), $master = false) {
        //$list_paginate = db('school')->alias('s')->join('__ADMIN__ a',' a.admin_id=s.option_id ','LEFT')->field($field)->where($condition)->order($school)->paginate($page,false,['query' => request()->param()]);
        $list_paginate = db('school')->field($field)->where($condition)->order($school)->paginate($page,false,['query' => request()->param()]);
        if(isset($condition['typeid']) && $condition['typeid']){
            $where = "FIND_IN_SET({$condition['typeid']},typeid) AND isdel = 1";
            if($condition['name']){
                $where .= ' AND name like "'.$condition['name'][1].'"';
            }
            if($condition['provinceid']){
                $where .= " AND provinceid=".$condition['provinceid'];
            }elseif($condition['cityid']){
                $where .= " AND cityid=".$condition['cityid'];
            }elseif($condition['areaid']){
                $where .= " AND areaid=".$condition['areaid'];
            }
            if($condition['admin_company_id']){
                //$where .= " AND a.admin_company_id=".$condition['a.admin_company_id'];
                $where .= " AND admin_company_id=".$condition['admin_company_id'];
            }
            if($condition['schoolid']){
                $where .= " AND schoolid=".$condition['schoolid'];
            }
            //$sql = "SELECT * FROM x_school s LEFT JOIN x_admin a ON a.admin_id=s.option_id WHERE $where  ORDER BY schoolid asc LIMIT 0,15";
            //$list_paginate = db('school')->alias('s')->join('__ADMIN__ a',' a.admin_id=s.option_id ','LEFT')->field($field)->where($where)->order($school)->paginate($page,false,['query' => request()->param()]);
            $list_paginate = db('school')->field($field)->where($where)->order($school)->paginate($page,false,['query' => request()->param()]);
        }

        $this->page_info = $list_paginate;
        $list = $list_paginate->items();

        if (empty($list))
            return array();
        return $list;
    }

    public function getAllAchool($condtion,$field ='*'){
        //$result = db('school')->alias('s')->join('__ADMIN__ a',' a.admin_id=s.option_id ','LEFT')->where($condtion)->select();
        $result = db('school')->where($condtion)->field($field)->select();
        //print_r(db('school')->getLastSql());die;
        return $result;
    }

    /**
     * 取得(买/卖家)订单某个数量缓存
     * @param string $type 买/卖家标志，允许传入 buyer、store
     * @param int $id   买家ID、店铺ID
     * @param string $key 允许传入  NewCount、PayCount、SendCount、EvalCount，分别取相应数量缓存，只许传入一个
     * @return array
     */
    public function getOrderCountCache($type, $id, $key) {
        if (!config('cache_open'))
            return array();
        $types = $id.'_ordercount' . $key;
        $order_info = cache::get($types);
        return !is_array($order_info) ? array($key => $order_info) : $order_info;
    }

    /**
     * 取得买卖家订单数量某个缓存
     * @param string $type $type 买/卖家标志，允许传入 buyer、store
     * @param int $id 买家ID、店铺ID
     * @param string $key 允许传入  NewCount、PayCount、SendCount、EvalCount，分别取相应数量缓存，只许传入一个
     * @return int
     */
    public function getOrderCountByID($type, $id, $key) {
        $cache_info = $this->getOrderCountCache($type, $id, $key);
        if (isset($cache_info[$key])&&is_numeric($cache_info[$key])) {
            //从缓存中取得
            $count = $cache_info[$key];
        } else {
            //从数据库中取得
            $field = $type == 'buyer' ? 'buyer_id' : 'store_id';
            $condition = array($field => $id);
            $func = 'getOrderState' . $key;
            $count = $this->$func($condition);
            $this->editOrderCountCache($type, $id, array($key => $count));
        }
        return $count;
    }


    /**
     * 取得订单数量
     * @param unknown $condition
     */
    public function getOrderCount($condition) {
        return db('order')->where($condition)->count();
    }

    /**
     * 插入订单表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addSchool($data) {
        $insert = db('school')->insertGetId($data);
        return $insert;
    }

    /**
     * 添加订单日志
     */
    public function addOrderLog($data) {
        $data['log_role'] = str_replace(array('buyer', 'seller', 'system', 'admin'), array('买家', '商家', '系统', '管理员'), $data['log_role']);
        $data['log_time'] = TIMESTAMP;
        return db('orderlog')->insertGetId($data);
    }

    /**
     * 更改学校信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editSchool($data, $condition, $limit = '') {

        $update = db('school')->where($condition)->limit($limit)->update($data);
        return $update;
    }


    /**
     * @name php获取中文字符拼音首字母
     * @param $str
     * @return null|string
     */
    public function getFirstCharter($str)
    {
        if (empty($str)) {
            return '';
        }
        if($str == "重"){
            return 'C';
        }
        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});
        $s1 = iconv('UTF-8', 'gb2312', $str);
        $s2 = iconv('gb2312', 'UTF-8', $s1);
        $s = $s2 == $str ? $s1 : $str;
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= -20319 && $asc <= -20284) return 'A';
        if ($asc >= -20283 && $asc <= -19776) return 'B';
        if ($asc >= -19775 && $asc <= -19219) return 'C';
        if ($asc >= -19218 && $asc <= -18711) return 'D';
        if ($asc >= -18710 && $asc <= -18527) return 'E';
        if ($asc >= -18526 && $asc <= -18240) return 'F';
        if ($asc >= -18239 && $asc <= -17923) return 'G';
        if ($asc >= -17922 && $asc <= -17418) return 'H';
        if ($asc >= -17417 && $asc <= -16475) return 'J';
        if ($asc >= -16474 && $asc <= -16213) return 'K';
        if ($asc >= -16212 && $asc <= -15641) return 'L';
        if ($asc >= -15640 && $asc <= -15166) return 'M';
        if ($asc >= -15165 && $asc <= -14923) return 'N';
        if ($asc >= -14922 && $asc <= -14915) return 'O';
        if ($asc >= -14914 && $asc <= -14631) return 'P';
        if ($asc >= -14630 && $asc <= -14150) return 'Q';
        if ($asc >= -14149 && $asc <= -14091) return 'R';
        if ($asc >= -14090 && $asc <= -13319) return 'S';
        if ($asc >= -13318 && $asc <= -12839) return 'T';
        if ($asc >= -12838 && $asc <= -12557) return 'W';
        if ($asc >= -12556 && $asc <= -11848) return 'X';
        if ($asc >= -11847 && $asc <= -11056) return 'Y';
        if ($asc >= -11055 && $asc <= -10247) return 'Z';
        return null;
    }

    /**
     * @name 获取某地区编码数
     * @param $str
     * @return null|string
     */
    public function getNumber($str){
        $where = "schoolCard like '%$str%'";
        $schoolInfo = db('school')->where($where)->order('schoolid desc')->limit(1)->find();
        $number = sprintf("%08d", substr($schoolInfo['schoolCard'],-8)+1);
        return $number;
    }

    /**
     * 数量
     * @param array $condition
     * @return int
     */
    public function getSchoolCount($condition)
    {
        return db('school')->where($condition)->count();
    }

}