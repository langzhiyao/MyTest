<?php

namespace app\common\model;

use think\Model;

class Adv extends Model {

    public $page_info;

    /**
     * 新增广告位
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function ap_add($param) {
        return db('advposition')->insertGetId($param);
    }

    /**
     * 新增广告
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function adv_add($param) {
        $result = db('adv')->insertGetId($param);
        $apId = (int) $param['ap_id'];
        dkcache("adv/{$apId}");
        return $result;
    }

    /**
     * 删除一条广告
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function adv_del($adv_id) {
        $condition['adv_id'] = $adv_id;
        $adv = db('adv')->where($condition)->find();
        if ($adv) {
            // drop cache
            $apId = (int) $adv['ap_id'];
            dkcache("adv/{$apId}");
        }
        @unlink(BASE_UPLOAD_PATH . DS . ATTACH_ADV. DS .$adv['adv_code']);
        return db('adv')->where($condition)->delete();
    }

    /**
     * 删除一个广告位
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function ap_del($ap_id) {
        $apId = (int) $ap_id;
        dkcache("adv/{$apId}");
        return db('advposition')->where('ap_id', $apId)->delete();
    }

    /**
     * 获取广告位列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function getApList($condition = array(), $page = '', $orderby = 'ap_id desc') {
        $condition['is_show'] = 1;
        if ($page) {
            $result = db('advposition')->where($condition)->order($orderby)->paginate($page, false, ['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            return db('advposition')->where($condition)->order($orderby)->select();
        }
    }

    public function getOneAp($condition = array()) {
        return db('advposition')->where($condition)->find();
    }

    public function getOneAdv($condition = array()) {
        return db('adv')->where($condition)->find();
    }

    /**
     * 根据条件查询多条记录
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function getList($condition = array(), $page = '', $limit = '', $orderby = 'adv_id desc') {
        if ($page) {
            $result = db('adv')->where($condition)->order($orderby)->paginate($page, false, ['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            return db('adv')->where($condition)->order($orderby)->select();
        }
    }

    /*手机端广告位获取*/
    public function mbadvlist($condition,$orderby='slide_sort desc'){
         return db('adv')->alias('a')->join('__ADVPOSITION__ n','a.ap_id=n.ap_id')->where($condition)->order($orderby)->select();
    }

    /**
     * 根据id查询一条记录
     *
     * @param int $id 广告id
     * @return array 一维数组
     */
    public function getOneById($id) {
        return db('adv')->where('adv_id', $id)->find();
    }

    /**
     * 更新记录
     *
     * @param array $param 更新内容
     * @return bool
     */
    public function adv_update($param) {
        $adv_array = db('adv')->where('adv_id', $param['adv_id'])->find();
        if ($adv_array) {
            // drop cache
            $apId = (int) $adv_array['ap_id'];
            dkcache("adv/{$apId}");
        }
        return db('adv')->where('adv_id', $param['adv_id'])->update($param);
    }

    /**
     * 更新广告位记录
     *
     * @param array $param 更新内容
     * @return bool
     */
    public function ap_update($param) {
        $apId = (int) $param['ap_id'];
        dkcache("adv/{$apId}");
        return db('advposition')->where('ap_id', $param['ap_id'])->update($param);
    }

    public function delapcache($id) {
        if (!is_numeric($id))
            return;

        dkcache("adv/{$id}");

        return true;
    }

    /**
     * 广告
     *
     * @return array
     */
    public function makeApAllCache() {
        if (config('cache_open')) {
            // *kcache() doesnt support iterating on keys
        } else {
            delCacheFile('adv');
        }

        $model = Model();
        $ap_list = db('advposition')->where(array('is_use' => 1))->select();
        $adv_list = db('adv')->where(array('adv_end_date' => array('gt', time())))->order('adv_sort, adv_id desc')->select();
        $array = array();
        foreach ((array) $ap_list as $v) {
            foreach ((array) $adv_list as $xv) {
                if ($v['ap_id'] == $xv['ap_id']) {
                    $v['adv_list'][] = $xv;
                }
            }

            // 写入缓存
            $apId = (int) $v['ap_id'];
            if (config('cache_open')) {
                wkcache("adv/{$apId}", $v);
            } else {
                write_file(RUNTIME_PATH. '/cache/adv/' . $apId . '.php', $v);
            }
        }
    }

    public function getApById($apId) {
        $apId = (int) $apId;
        return rkcache("adv/{$apId}", array($this, 'getApByCacheId'));
    }

    /**
     * 通过缓存id获取广告，生成缓存时使用
     *
     * @param $apCacheId 格式为 adv/{ap_id}
     */
    public function getApByCacheId($apCacheId) {
        $apId = substr($apCacheId, strlen('adv/'));
        return $this->getAp($apId);
    }

    /**
     * 生成广告位
     *
     * @param int $ap_id
     */
    protected function getAp($ap_id) {
        $ap_info = db('advposition')->where('ap_id', $ap_id)->find();
        $ap_info['adv_list'] = db('adv')->where(array(
                    'ap_id' => $ap_id,
                    'adv_end_date' => array('gt', time()),
                ))->order('adv_sort, adv_id desc')->select();
        return $ap_info;
    }

    /**
     * 删除缓存
     */
    public function dropApCacheByAdvIds($advIds) {
        $apIds = array_keys((array) db('adv')->field('ap_id')->where(array('adv_id' => array('in', (array) $advIds),))->select());
        //以ap_id 为键值
        $apIds = ds_changeArraykey($apIds, 'ap_id');
        foreach ($apIds as $apId) {
            $apId = (int) $apId;
            dkcache("adv/{$apId}");
        }
    }

}

?>
