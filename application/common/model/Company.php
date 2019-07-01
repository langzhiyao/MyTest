<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/18
 * Time: 10:17
 */
namespace app\common\model;

use think\Model;

class Company extends Model {
    /**
     * 分子公司列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param number $page
     * @param string $limit
     * @return array
     */
    public function getOrganizeList($condition, $field = '*', $page = 0, $order = 'o_id desc', $limit = '') {
        if($limit) {
            return db('company')->where($condition)->field($field)->order($order)->page($page)->limit($limit)->select();
        }else{
            $res= db('company')->where($condition)->field($field)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info=$res;
            return $res->items();
        }
    }
    /**
     * 添加分子公司
     * @param array $insert
     * @return boolean
     */
    public function addOrganize($insert) {
        return db('company')->insert($insert);
    }
    /**
     * 取单个分子公司内容
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getOrganizeInfo($condition, $field = '*') {
        return db('company')->field($field)->where($condition)->find();
    }
    /**
     * 编辑分子公司
     * @param array $condition
     * @param array $update
     * @return boolean
     */
    public function editOrganize($condition, $update) {
        return db('company')->where($condition)->update($update);
    }

    /**
     * 根据admin_company_id判断是否特约代理
     * 特约代理根据admin_company_id查数据，否则根据地区查询
     * 公司角色：1，县区代理;2，省级代理；3，市级代理；4，特约代理；
     */
    public function getCondition($Id, $type=''){
        $condition = array();
        $companyInfo = db("company")->field("o_id,o_role,o_provinceid,o_cityid,o_areaid")->where(array('o_id'=>$Id))->find();
        if($companyInfo['o_role']==4){
            if($type=="member"){
                $schools = db("school")->field("schoolid,name")->where(array("isdel"=>1,'admin_company_id'=>$companyInfo['o_id']))->select();
                if(!empty($schools)){
                    $studentInfo = db('student')->field('s_id,s_name,s_ownerAccount')->where("s_schoolid in (".implode(',',array_column($schools,'schoolid')).") and s_del=1")->select();
                    foreach($studentInfo as $key=>$item){
                        if(!empty($item['s_ownerAccount'])){
                            $member_ids[] = $item['s_ownerAccount'];
                        }
                    }
                    $member_ids = array_unique($member_ids);
                    if(!empty($member_ids)){
                        $fu = db('member')->field("member_id")->where("is_owner in (".implode(',',$member_ids).")")->select();
                        foreach($fu as $F=>$it){
                            $fu_ac[] = $it['member_id'];
                        }
                    }
                }
                $member_ids = !empty($fu_ac)&&!empty($member_ids)?array_merge($member_ids,$fu_ac):"";
                $condition['member_id'] = array('in',$member_ids);
            }else{
                $condition['admin_company_id'] = $Id;
            }
        }elseif($companyInfo['o_role']==1){
            if(!empty($companyInfo['o_provinceid'])){
                if($type=="class"){
                    $condition['school_provinceid'] = $companyInfo['o_provinceid'];
                }elseif($type=="student") {
                    $condition['s_provinceid'] = $companyInfo['o_provinceid'];
                }elseif($type=="member"){
                    $condition['member_provinceid'] = $companyInfo['o_provinceid'];
                }else{
                    $condition['provinceid'] = $companyInfo['o_provinceid'];
                }
            }
            if(!empty($companyInfo['o_cityid'])){
                if($type=="class"){
                    $condition['school_cityid'] = $companyInfo['o_cityid'];
                }elseif($type=="student") {
                    $condition['s_cityid'] = $companyInfo['o_cityid'];
                }elseif($type=="member"){
                    $condition['member_cityid'] = $companyInfo['o_cityid'];
                }else{
                    $condition['cityid'] = $companyInfo['o_cityid'];
                }
            }
            if(!empty($companyInfo['o_areaid'])){
                if($type=="class"){
                    $condition['school_areaid'] = $companyInfo['o_areaid'];
                }elseif($type=="student"){
                    $condition['s_areaid'] = $companyInfo['o_areaid'];
                }elseif($type=="member"){
                    $condition['member_areaid'] = $companyInfo['o_areaid'];
                }else{
                    $condition['areaid'] = $companyInfo['o_areaid'];
                }
            }
        }elseif($companyInfo['o_role']==3){
            if(!empty($companyInfo['o_provinceid'])){
                if($type=="class"){
                    $condition['school_provinceid'] = $companyInfo['o_provinceid'];
                }elseif($type=="student"){
                    $condition['s_provinceid'] = $companyInfo['o_provinceid'];
                }elseif($type=="member"){
                    $condition['member_provinceid'] = $companyInfo['o_provinceid'];
                }else{
                    $condition['provinceid'] = $companyInfo['o_provinceid'];
                }
            }
            if(!empty($companyInfo['o_cityid'])){
                if($type=="class"){
                    $condition['school_cityid'] = $companyInfo['o_cityid'];
                }elseif($type=="student"){
                    $condition['s_cityid'] = $companyInfo['o_cityid'];
                }elseif($type=="member"){
                    $condition['member_cityid'] = $companyInfo['o_cityid'];
                }else{
                    $condition['cityid'] = $companyInfo['o_cityid'];
                }
            }
        }elseif($companyInfo['o_role']==2){
            if(!empty($companyInfo['o_provinceid'])){
                if($type=="class"){
                    $condition['school_provinceid'] = $companyInfo['o_provinceid'];
                }elseif($type=="student"){
                    $condition['s_provinceid'] = $companyInfo['o_provinceid'];
                }elseif($type=="member"){
                    $condition['member_provinceid'] = $companyInfo['o_provinceid'];
                }else{
                    $condition['provinceid'] = $companyInfo['o_provinceid'];
                }
            }
        }
        return $condition;
    }

    /**
     * 数量
     * @param array $condition
     * @return int
     */
    public function getAgentCount($condition)
    {
        return db('company')->where($condition)->count();
    }

}