<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/18
 * Time: 10:17
 */
namespace app\common\model;

use think\Model;

class Teachchild extends Model {
    /**
     * 课件列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param number $page
     * @param string $limit
     * @return array
     */
    public function getTeachchildList($condition, $field = '*', $page = 0, $order = 't_id desc', $limit = '') {
        if($limit) {
            return db('teachchild')->where($condition)->field($field)->order($order)->page($page)->limit($limit)->select();
        }else{
            $res= db('teachchild')->alias('s')
                ->join('__TEACHERCERTIFY__ te','te.member_id=s.t_userid','LEFT')
                ->join('__TEACHTYPE__ ty','ty.gc_id=s.t_type','LEFT')
                ->join('__TEACHTYPE__ ts','ts.gc_id=s.t_type2','LEFT')
                ->join('__TEACHTYPE__ tr','tr.gc_id=s.t_type3','LEFT')
                ->join('__TEACHTYPE__ tu','tu.gc_id=s.t_type4','LEFT')
                ->field('s.*,te.username,ty.gc_name as "t_typename",ts.gc_name as "t_type2name",tr.gc_name as "t_type3name",tu.gc_name as "t_type4name"')
                ->where($condition)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info=$res;
            return $res->items();
        }
    }

    //分页读取数据
    public function getPageTeachildList($where,$order,$page){
        $start = ($page-1)*10;
        $sql = "select t_id,t_url,t_videoimg,t_picture,t_title,t_profile,t_price,t_userid,t_author from x_teachchild where ".$where." order by ".$order." limit ".$start.",10";
        $result = $this->query($sql);
        return $result;
    }
    /**
     * 添加课件
     * @param array $insert
     * @return boolean
     */
    public function addTeachchild($insert) {
        return db('teachchild')->insert($insert);
    }
    /**
     * 取单个课件内容
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getTeachchildInfo($condition, $field = '*') {
        return db('teachchild')->alias('s')
                ->join('__TEACHERCERTIFY__ te','te.member_id=s.t_userid','LEFT')
                ->join('__TEACHTYPE__ ty','ty.gc_id=s.t_type','LEFT')
                ->join('__TEACHTYPE__ ts','ts.gc_id=s.t_type2','LEFT')
                ->join('__TEACHTYPE__ tr','tr.gc_id=s.t_type3','LEFT')
                ->join('__TEACHTYPE__ tu','tu.gc_id=s.t_type4','LEFT')
                ->field('s.*,te.username as "name",ty.gc_name as "t_typename",ts.gc_name as "t_type2name",tr.gc_name as "t_type3name",tu.gc_name as "t_type4name"')
                ->where($condition)->find();
    }
    /**
     * 编辑课件
     * @param array $condition
     * @param array $update
     * @return boolean
     */
    public function editTeachchild($condition, $update) {
        return db('teachchild')->where($condition)->update($update);
    }

    public function delVideo($condition)
    {
        return db('teachchild')->where($condition)->delete();
    }

    /**
     * 视频数量
     * @param array $condition
     * @return int
     */
    public function getVideoCount($condition)
    {
        return db('teachchild')->where($condition)->count();
    }

}