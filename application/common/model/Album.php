<?php

namespace app\common\model;

use think\Model;

class Album extends Model {
    public $page_info;
    /**
     * 计算数量
     * 
     * @param array $condition 条件
     * $param string $table 表名
     * @return int
     */
    public function getAlbumPicCount($condition) {
        $result = db('albumpic')->where($condition)->count();
        return $result;
    }

    /**
     * 计算数量
     * 
     * @param array $condition 条件
     * $param string $table 表名
     * @return int
     */
    public function getCount($condition, $table = 'albumpic') {
        $result = db($table)->where($condition)->count();
        return $result;
    }

    /**
     * 获取单条数据
     * 
     * @param array $condition 条件
     * @param string $table 表名
     * @return array 一维数组
     */
    public function getOne($condition, $table = 'albumpic') {
        $resule = db($table)->where($condition)->find();
        return $resule;
    }

    /**
     * 分类列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function getClassList($condition, $page = '', $order = '') {
        $result = db('albumclass')->where($condition)->order($order)->select();
        return $result;
    }

    /**
     * 计算分类数量
     * 
     * @param int id
     * @return array 一维数组
     */
    public function countClass($id) {
        return db('albumclass')->where('store_id',$id)->count();
    }

    /**
     * 验证相册
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function checkAlbum($condition) {
        /**
         * 验证是否为有默认相册
         */
        $result = db('albumclass')->where($condition)->select();
        if (!empty($result)) {
            unset($result);
            return true;
        }
        unset($result);
        return false;
    }

    /**
     * 图片列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function getPicList($condition, $page = '', $field = '*',$order='apic_id desc') {
        if($page){
            $result = db('albumpic')->where($condition)->field($field)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        }else{
            $result = db('albumpic')->where($condition)->field($field)->order($order)->select();
            return $result;
        }
    }

    /**
     * 添加相册分类
     *
     * @param array $input
     * @return bool
     */
    public function addClass($input) {
        return db('albumclass')->insert($input);
    }

    /**
     * 添加相册图片
     *
     * @param array $input
     * @return bool
     */
    public function addPic($input) {
        $result = db('albumpic')->insert($input);
        return $result;
    }

    /**
     * 更新相册分类
     *
     * @param array $input
     * @param int $id
     * @return bool
     */
    public function updateClass($input, $id) {
        return db('albumclass')->where('aclass_id', $id)->update($input);
    }

    /**
     * 更新相册图片
     *
     * @param array $input
     * @param int $id
     * @return bool
     */
    public function updatePic($input, $condition) {
        $result = db('albumpic')->where($condition)->update($input);
       return $result;
    }

    /**
     * 删除分类
     *
     * @param string $id
     * @return bool
     */
    public function delClass($id) {
        return db('albumclass')->where('aclass_id', $id)->delete();
    }

    /**
     * 根据店铺id删除图片空间相关信息
     * 
     * @param int $id
     * @return bool
     */
    public function delAlbum($id) {
        $id = intval($id);
        db('albumclass')->where('store_id', $id)->delete();
        $pic_list = $this->getPicList(array("store_id" => $id), '', 'apic_cover');
        if (!empty($pic_list) && is_array($pic_list)) {
            $image_ext = explode(',', GOODS_IMAGES_EXT);
            foreach ($pic_list as $v) {
                foreach ($image_ext as $ext) {
                    $file = str_ireplace('.', $ext . '.', $v['apic_cover']);
                    @unlink(BASE_UPLOAD_PATH . DS . ATTACH_GOODS . DS . $id . DS . $file);
                }
            }
        }
        db('albumpic')->where('store_id', $id)->delete();
    }

    /**
     * 删除图片
     *
     * @param string $id
     * @param int $store_id
     * @return bool
     */
    public function delPic($id, $store_id) {
        $condition['apic_id'] = array('in',$id);
        $condition['store_id'] = $store_id;
        $pic_list = $this->getPicList($condition, '', 'apic_cover');
        /**
         * 删除图片
         */
        $res=del_goods_image($pic_list,$store_id);
        if($res['code']=='200') {
            return db('albumpic')->where($condition)->delete();
        }
    }

    /**
     * 查询单条分类信息
     *
     * @param int $id 活动id
     * @return array 一维数组
     */
    public function getOneClass($param) {
        return db('albumclass')->where($param)->find();
    }

    /**
     * 根据id查询一张图片
     *
     * @param int $id 活动id
     * @return array 一维数组
     */
    public function getOnePicById($param) {
        return db('albumpic')->where($param)->find();
    }

}
