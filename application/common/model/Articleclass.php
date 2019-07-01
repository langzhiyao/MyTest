<?php
namespace app\common\model;
use think\Model;

class Articleclass extends Model
{
    
    /**
     * 类别列表
     *
     * @param array $condition 检索条件
     * @return array 数组结构的返回结果
     */
    public function getClassList($condition){
        $condition_str = $this->_condition($condition);
        $order	= empty($condition['order'])?'ac_parent_id asc,ac_sort asc,ac_id asc':$condition['order'];
        $result = db('articleclass')->where($condition_str)->order($order)->select();
        return $result;
    }

    /**
     * 构造检索条件
     *
     * @param int $id 记录ID
     * @return string 字符串类型的返回结果
     */
    private function _condition($condition){
        $condition_str = '1=1';

        if (isset($condition['ac_parent_id'])&&$condition['ac_parent_id'] != ''){
            $condition_str .= " and ac_parent_id = '". intval($condition['ac_parent_id']) ."'";
        }
        if (isset($condition['no_ac_id'])&&$condition['no_ac_id'] != ''){
            $condition_str .= " and ac_id != '". intval($condition['no_ac_id']) ."'";
        }
        if (isset($condition['ac_name'])&&$condition['ac_name'] != ''){
            $condition_str .= " and ac_name = '". $condition['ac_name'] ."'";
        }
        if (isset($condition['home_index'])&&$condition['home_index'] != ''){
            $condition_str .= " and ac_id <= 7";
        }

        return $condition_str;
    }

    /**
     * 取单个分类的内容
     *
     * @param int $id 分类ID
     * @return array 数组类型的返回结果
     */
    public function getOneClass($id){
        if (intval($id) > 0){
            $value = intval($id);
            $result = db('articleclass')->where(array('ac_id'=>$value))->find();
            return $result;
        }else {
            return false;
        }
    }

    /**
     * 新增
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function add($param){
        $result = db('articleclass')->insertGetId($param);
        return $result;
    }

    /**
     * 更新信息
     *
     * @param array $param 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function updateinfo($param,$ac_id){
        $result =db('articleclass')->where("ac_id = '". $param['ac_id'] ."'")->update($param);
        return $result;
    }

    /**
     * 删除分类
     *
     * @param int $id 记录ID
     * @return bool 布尔类型的返回结果
     */
    public function del($id){
        return db('articleclass')->where("ac_id = '". intval($id) ."'")->delete();
    }

    /**
     * 取分类列表，按照深度归类
     *
     * @param int $show_deep 显示深度
     * @return array 数组类型的返回结果
     */
    public function getTreeClassList($show_deep='2'){
        $condition	= array();
        $class_list = $this->getClassList($condition);
        $show_deep = intval($show_deep);
        $result = array();
        if(is_array($class_list) && !empty($class_list)) {
            $result = $this->_getTreeClassList($show_deep,$class_list);
        }
        return $result;
    }

    /**
     * 递归 整理分类
     *
     * @param int $show_deep 显示深度
     * @param array $class_list 类别内容集合
     * @param int $deep 深度
     * @param int $parent_id 父类编号
     * @param int $i 上次循环编号
     * @return array $show_class 返回数组形式的查询结果
     */
    private function _getTreeClassList($show_deep,$class_list,$deep=1,$parent_id=0,$i=0){
        static $show_class = array();//树状的平行数组
        if(is_array($class_list) && !empty($class_list)) {
            $size = count($class_list);
            if($i == 0) $show_class = array();//从0开始时清空数组，防止多次调用后出现重复
            for ($i;$i < $size;$i++) {//$i为上次循环到的分类编号，避免重新从第一条开始
                $val = $class_list[$i];
                $ac_id = $val['ac_id'];
                $ac_parent_id	= $val['ac_parent_id'];
                if($ac_parent_id == $parent_id) {
                    $val['deep'] = $deep;
                    $show_class[] = $val;
                    if($deep < $show_deep && $deep < 2) {//本次深度小于显示深度时执行，避免取出的数据无用
                        $this->_getTreeClassList($show_deep,$class_list,$deep+1,$ac_id,$i+1);
                    }
                }
                if($ac_parent_id > $parent_id) break;//当前分类的父编号大于本次递归的时退出循环
            }
        }
        return $show_class;
    }

    /**
     * 取指定分类ID下的所有子类
     *
     * @param int/array $parent_id 父ID 可以单一可以为数组
     * @return array $rs_row 返回数组形式的查询结果
     */
    public function getChildClass($parent_id){
        $all_class = $this->getClassList(array('order'=>'ac_parent_id asc,ac_sort asc,ac_id asc'));
        if (is_array($all_class)){
            if (!is_array($parent_id)){
                $parent_id = array($parent_id);
            }
            $result = array();
            foreach ($all_class as $k => $v){
                $ac_id	= $v['ac_id'];//返回的结果包括父类
                $ac_parent_id	= $v['ac_parent_id'];
                if (in_array($ac_id,$parent_id) || in_array($ac_parent_id,$parent_id)){
                    $result[] = $v;
                }
            }
            return $result;
        }else {
            return false;
        }
    }
}