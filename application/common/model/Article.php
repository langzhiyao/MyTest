<?php
namespace app\common\model;
use think\Model;

class Article extends Model
{
    public $page_info;
    /**
     * 列表
     *
     * @param array $condition 检索条件
     * @param obj $page 分页
     * @return array 数组结构的返回结果
     */
    public function getArticleList($condition,$page=''){
        $condition_str = $this->_condition($condition);
        $order	= (empty($condition['order'])?'article_sort asc,article_time desc':$condition['order']);
        $result =db('article')->alias('article')->where($condition_str)->order($order)->paginate($page,false,['query' => request()->param()]);
        $this->page_info=$result;
        return $result->items();
    }

    /**
     * 连接查询列表
     *
     * @param array $condition 检索条件
     * @param obj $page 分页
     * @return array 数组结构的返回结果
     */
    public function getJoinList($condition,$page=''){
        $condition_str	= $this->_condition($condition);
        $field	= empty($condition['field'])?'*':$condition['field'];;
        $join_type	= empty($condition['join_type'])?'left':$condition['join_type'];
        $where = $condition_str;
        $order	= empty($condition['order'])?'article.article_sort':$condition['order'];
        $result = db('article')->alias('article')->join('__ARTICLECLASS__ article_class','article.ac_id=article_class.ac_id',$join_type)->where($where)->order($order)->field($field)->select();
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

        if (isset($condition['article_show'])&&$condition['article_show'] != ''){
            $condition_str .= " and article.article_show = '". $condition['article_show'] ."'";
        }
        if (isset($condition['ac_id'])&&$condition['ac_id'] != ''){
            $condition_str .= " and article.ac_id = '". $condition['ac_id'] ."'";
        }
        if (isset($condition['ac_ids'])&&$condition['ac_ids'] != ''){
            //if(is_array($condition['ac_ids']))$condition['ac_ids']	= implode(',',$condition['ac_ids']);
            $condition_str .= " and article.ac_id in(". $condition['ac_ids'] .")";
        }
        if (isset($condition['like_title'])&&$condition['like_title'] != ''){
            $condition_str .= " and article.article_title like '%". $condition['like_title'] ."%'";
        }
        if (isset($condition['home_index'])&&$condition['home_index'] != ''){
            $condition_str .= " and (article_class.ac_id <= 7 or (article_class.ac_parent_id > 0 and article_class.ac_parent_id <= 7))";
        }
        return $condition_str;
    }

    /**
     * 取单个内容
     *
     * @param int $id ID
     * @return array 数组类型的返回结果
     */
    public function getOneArticle($id){
        $result = db('article')->where('article_id',intval($id))->find();
        return $result;
    }

    /**
     * 新增
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function add($param){
        $result = db('article')->insertGetId($param);
        return $result;
    }

    /**
     * 更新信息
     *
     * @param array $param 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function updateinfo($param,$article_id){
        $result = db('article')->where("article_id=".$article_id)->update($param);
        return $result;
    }

    /**
     * 删除
     *
     * @param int $id 记录ID
     * @return bool 布尔类型的返回结果
     */
    public function del($id){
        return db('article')->where("article_id=".$id)->delete();
    }
}