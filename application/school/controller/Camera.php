<?php

namespace app\school\controller;

use think\Lang;
use think\Validate;
use vomont\Vomont;

class Camera extends AdminControl
{

    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'school/lang/zh-cn/look.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name = strtolower(end(explode('\\',__CLASS__)));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('school_admin_gid') ,$perm_id);
        $this->assign('action',$action);
        //获取省份
        $province = db('area')->field('area_id,area_parent_id,area_name')->where('area_parent_id=0')->select();
        //获取学校
        $school = db('school')->field('schoolid,name')->select();
        $this->assign('school',$school);
        $this->assign('province',$province);
    }

    /**
     * @desc 摄像头管理
     * @author 郎志耀
     * @time 20180926
     */
    public function camera(){
        if(session('school_admin_is_super') !=1 && !in_array('4',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $where = ' status=2 ';
        if(!empty($_GET)){
            $where = $this->_conditions($_GET);
        }
        


        $page_count = intval(input('get.page_count')) ? intval(input('get.page_count')) : 1;//每页的条数
        $start = intval(input('get.page')) ? (intval(input('get.page'))-1)*$page_count : 0;//开始页数

        //查询未绑定的摄像头
//        $list = db('camera')->where($where)->limit($start,$page_count)->order('sq_time DESC')->select();
        $list_count = db('camera')->where($where)->count();

//        halt($list);

//        $this->assign('list',$list);
        $this->assign('list_count',$list_count);
        $this->setAdminCurItem('camera');
        return $this->fetch('camera');
    }
    /**
 * @desc 获取分页数据
 * @author langzhiyao
 * @time 20190929
 */
    public function get_list(){

        $where = ' status=2 ';
        
        if(!empty($_POST)){
            $where = $this->_conditions($_POST);
        }
        

        $page_count = intval(input('post.page_count')) ? intval(input('post.page_count')) : 1;//每页的条数
        $start = intval(input('post.page')) ? (intval(input('post.page'))-1)*$page_count : 0;//开始页数

//        halt($start);
        //查询未绑定的摄像头
        $list = db('camera')->where($where)->limit($start,$page_count)->order('sq_time DESC')->select();
        $list_count = db('camera')->where($where)->count();

        $html = '';
        if(!empty($list)){
            foreach($list as $key=>$value){
                $html .= '<tr class="hover">';
                $html .= '<td class="align-center">'.$value["camera_name"].'</td>';
                $html .= '<td class="align-center">'.$value["class_area"].'</td>';
                if($value['is_public_area'] == 1){
                    $html .= '<td class="align-center">是</td>';
                }else if($value['is_public_area'] == 2){
                    $html .= '<td class="align-center">否</td>';
                }
                $html .= '<td class="align-center">'.$value["school_name"].'</td>';
//                $html .= '<td class="align-center">'.$value["address"].'</td>';
                $html .= '<td class="align-center">'.date('Y-m-d H:i:s',$value["sq_time"]).'</td>';
                $html .= '<td class="align-center">'.$value["sn"].'</td>';
                $html .= '<td class="align-center">'.$value["key"].'</td>';
                $html .= '<td class="align-center">'.$value["agent_name"].'</td>';
                $html .= '<td class="align-center">'.$value["content"].'</td>';
                $html .= '<td class="align-center" style="color:#0FB700;">待绑定</td>';
                $html .= '<td class="w150 align-center">
                        <div class="layui-table-cell laytable-cell-9-8">
                           <a href="javascript:void(0)"  class="layui-btn  layui-btn-sm" lay-event="reset">绑定设备信息</a>';
                $html .=  '</div></td>';

                $html .= '</tr>';
            }
        }
        if($html == ''){
            $html .= '<tr class="no_data">
                    <td colspan="11">没有符合条件的记录</td>
                </tr>';
        }

        exit(json_encode(array('html'=>$html,'count'=>$list_count)));

    }


    /**
     * @desc 确认导入excel表和学校
     * @author langzhiyao
     * @time 20180926
     */
    public function download(){
        if(session('school_admin_is_super') !=1 && !in_array('8',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $this->setAdminCurItem('camera');
        return $this->fetch('download');
    }

    /**
     * @desc 再次确认插入数据表的内容
     * @author langzhiyao
     * @time 20180926
     */
    public function excelTrue(){
        if(session('school_admin_is_super') !=1 && !in_array('8',$this->action)){
            $this->error(lang('ds_assign_right'));
        }

        $this->setAdminCurItem('camera');
        return $this->fetch('excelTrue');
    }

    /**
     * @desc 将exce数据插入表中
     * @author langzhiyao
     * @time 20180928
     */
    public function insert_excel(){
        if(session('school_admin_is_super') !=1 && !in_array('8',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
//        halt($_SESSION['excel']);
        if(!empty($_SESSION['excel'])){
            $excel = $_SESSION['excel']['excel_data'];
            if(empty($excel)){
                exit(json_encode(array('code'=>1,'msg'=>'没有符合的数据，请重新上传')));
            }
            foreach($excel as $key=>$value){
                $res = db('camera')->field('id,sn,key')->where(" `key` = '".$value["G"]."'")->find();

                if($value['C'] == '是'){$value['C'] =1;}else{$value['C'] = 2;}

                if(!$res){
                    $data = array(
                        'camera_name' => $value['A'],
                        'class_area' => $value['B'],
                        'is_public_area' => $value['C'],
                        'school_id' => $_SESSION['excel']['school']['schoolid'],
                        'school_name' => $_SESSION['excel']['school']['name'],
                        'province_id' => $_SESSION['excel']['school']['provinceid'],
                        'city_id' => $_SESSION['excel']['school']['cityid'],
                        'area_id' => $_SESSION['excel']['school']['areaid'],
                        'address' => $_SESSION['excel']['school']['address'],
                        'sq_time' => time(),
                        'sn' => $value['F'],
                        'key' => $value['G'],
                        'content' => $value['I'],
                        'agent_id' => $_SESSION['excel']['agent']['agent_id'],
                        'agent_name' => $_SESSION['excel']['agent']['agent_name'],
                    );
                    db('camera')->insert($data);
                }
            }
            exit(json_encode(array('code'=>0,'msg'=>'导入成功，请前往绑定设备')));
        }else{
            exit(json_encode(array('code'=>1,'msg'=>'上传的文件数据失效，请重新上传')));
        }

    }



    /**
     * @desc 摄像头 已录入管理
     * @author 郎志耀
     * @time 20180926
     */
    public function entered(){
        if(session('school_admin_is_super') !=1 && !in_array('4',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $where = '';
        
        $where = $this->_conditions($_GET);

        $list_count = db('camera')->where($where)->count();
        // var_dump($list_count);exit;
        //年级类型
        $school_where=[];
        $school_where['schoolid'] = session('admin_school_id');
        $school_type = db('school')->field('typeid')->where($school_where)->find();
        if(!empty($school_type)){
            $type = explode(',',$school_type['typeid']);
            $grade = array();
            foreach($type as $key=>$val){
                $grade[]= db('schooltype')->field('sc_id,sc_type')->where('sc_id = "'.$val.'"')->order('sc_sort ASC')->find();
            }
            $this->assign('grade',$grade);
        }
        $this->assign('list_count',$list_count);
        $this->setAdminCurItem('entered');
        return $this->fetch('entered');
    }

    /**
     * 摄像头查询过滤
     * @创建时间   2018-11-03T00:39:28+0800
     * @param  [type]                   $where [description]
     * @return [type]                          [description]
     */
    public function _conditions($where){
        $where['school'] = session('admin_school_id');
        if (isset($where['name']) && !empty($where['name'])) {
            $condition['name'] = array('LIKE','%'.$where['name'].'%');
        }
        $res = array();
        $name = false;
        // p($where);
        if (isset($where['class']) && !empty($where['class']) ) {
            $class = $this->getResGroupIds(array('classname'=>$where['class']));
            if ($class) {
                $res=array_merge($res, $class);
            }
            unset($where);
            $name = 'true';
        }
        if (isset($where['grade']) && !empty($where['grade'])) {
            $grade = $this->getResGroupIds(array('sc_type'=>$where['grade']));
            unset($where['school']);
            unset($where['area']);
            unset($where['city']);
            unset($where['province']);
            $name = 'true';
            if ($grade) {
                $res=array_merge($res, $grade);
            }
        }
        if (isset($where['school']) && $where['school'] != 0 ) {
            $school = $this->getResGroupIds(array('schoolid'=>$where['school']));
            unset($where['area']);
            unset($where['city']);
            unset($where['province']);
            $name = 'true';
            if ($school) {
                $res=array_merge($res, $school);
            }
        }
        if ($name == 'true') {
            $res =is_array($res[0])?$res[0]:$res;
            $condition['parentid'] = array('in',$res);
        }
        return $condition;
    }

    /**
     * 查询学校和班级摄像头
     * @创建时间   2018-11-03T00:39:48+0800
     * @param  [type]                   $where [description]
     * @return [type]                          [description]
     */
    public function getResGroupIds($where){
        $School = model('School');
        $Class = model('Classes');
        
        if (isset($where['sc_type']) && !empty($where['sc_type'])) {
            $sc_id = db('schooltype')->where($where)->value('sc_id');
            unset($where['sc_type']);
            $where[]=['exp','FIND_IN_SET('.$sc_id.',typeid)'];
        }
        $classname = '';
        if (isset($where['classname']) && !empty($where['classname']) ) {
            $classname = $where['classname'];
            unset($where['classname']);
        }

        $where['res_group_id'] =array('gt',0);
        $Schoollist = $School->getAllAchool($where,'res_group_id');

        if (!empty($classname)) {
            $where['classname'] = $classname;
            unset($Schoollist);
        }
        $res = array();
        $where['schoolid'] = session('admin_school_id');
        $Classlist = $Class->getAllClasses($where,'res_group_id');
        $sc_resids=array_column($Schoollist, 'res_group_id');
        if ($sc_resids) {
            array_push($res, $sc_resids);
        }
        $cl_resids=array_column($Classlist, 'res_group_id');
        if ($cl_resids) {
            array_push($res, $cl_resids);
        }
        $ids = array_merge($sc_resids,$cl_resids);
        if ($ids) {
            return $ids;
        }else{
            return $res;
        }
    }

    /**
     * @desc 获取分页数据
     * @author langzhiyao
     * @time 20190929
     */
    public function get_entered_list(){

        $where = ' 1=1 ';
        if(!empty($_POST)){
            // p($_POST);exit;
            $cond = array();
            foreach ($_POST as $key => $p) {
                if(!in_array($key, ['page','page_count']) && !empty($p))$cond[$key]=$p;
            }
            if ($cond) {
                $where = $this->_conditions($cond);
            }
        }

        $page_count = intval(input('post.page_count')) ? intval(input('post.page_count')) : 1;//每页的条数
        $start = intval(input('post.page')) ? (intval(input('post.page'))-1)*$page_count : 0;//开始页数
        
//        halt($start);
        //查询未绑定的摄像头
        $list = db('camera')->where($where)->limit($start,$page_count)->order('cid DESC')->select();

        $list_count = db('camera')->where($where)->count();

        $html = '';
        if(!empty($list)){
            foreach($list as $key=>$v){
                $datainfo = json_encode($v);
                $html .= "<tr class='hover' id='tr_".$v['cid']."' datainfo='".$datainfo."'>";
                $html .= "<td class='align-center'><input type='checkbox' lay-skin='primary' name='cityId' class='cityId' lay-filter='c_one'  value='".$v['cid']."' ></td>";
                $html .= '<td class="align-center">'.$v["name"].'</td>';
                $html .= '<td class="align-center">'.$v["channelid"].'</td>';
                $html .= '<td class="align-center">'.$v["deviceid"].'</td>';
                $html .= '<td class="align-center">'.$v["id"].'</td>';
                if($v['online'] == 1){
                    $html .= '<td class="align-center"><b style="color:green;">在线</b></td>';
                }else if($v['online'] == 0){
                    $html .= '<td class="align-center"><b style="color:red;">离线</b></td>';
                }
                $html .= '<td class="align-center">'.$v["parentid"].'</td>';
                //$html .= '<td class="align-center"><img src="'.$v["imageurl"].'" width="120" height="50"></td>';
                //$html .= '<td id="rmt_'.$v['cid'].'" class="align-center"><a href="javascript:viod(0)" onClick="rtmplay('.$v['cid'].')">点击播放</a></td>';
                if($v['is_classroom'] == 1){
                    $html .= '<td class="align-center"><b style="color:red;">否</b></td>';
                }else if($v['is_classroom'] == 2){
                    $html .= '<td class="align-center"><b style="color:green;">是</b></td>';
                }
                if($v['status'] == 1){
                //     $html .= '<td class="align-center">开启</td>';
                    $html .= '<td class="align-center"><a id="dp_'.$v['cid'].'" statu="'.$v['status'].'" class="layui-unselect layui-form-checkbox layui-form-checked" onclick="makedefault('.$v['cid'].');" ><span>启用</span><i class="layui-icon layui-icon-ok"></i></a></td>';
                }else if($v['status'] == 2){
                    $html .= '<td class="align-center"><a id="dp_'.$v['cid'].'" statu="'.$v['status'].'" class="layui-unselect layui-form-checkbox" onclick="makedefault('.$v['cid'].');" ><span>启用</span><i class="layui-icon layui-icon-ok"></i></a></td>';
                //     $html .= '<td class="align-center">关闭</td>';
                }

                $html .= '<td class="align-left">'.date('Y-m-d H:i:s',$v["sq_time"]).'</td>';
                // $html .= '<td class="align-center">开启时间：21:05:39<hr>关闭时间：21:05:39</td>';
                $start = trim($v['cid'].'_Start');
                $end = trim($v['cid'].'_End');
                $defulbegin =empty($v["begintime"])?'':date('H:i:s',$v["begintime"]);
                $defulend   =empty($v["endtime"])?'':date('H:i:s',$v["endtime"]);
                $html .= "<td class='align-center'>
                    开启时间：<input type='text' class='pictime' id='picktimeStart".$v['cid']."' onfocus='timesss(".'"'.$start.'"'.")' value='".$defulbegin."'/> <hr>
                    关闭时间：<input type='text' class='pictime' id='picktimeEnd".$v['cid']."' onfocus='timesss(".'"'.$end.'"'.")' value='".$defulend."' /> 
                    </td>";
                    //<a class='layui-btn layui-btn-sm' do='{$start}' onClick='changetime($(this))'>设置</a>
                    //<a class='layui-btn layui-btn-sm' do='{$end}' onClick='changetime($(this))'>设置</a>
//                $html .= '<td class="align-center">'.$value["address"].'</td>';
//                $html .= '<td class="align-center">'.$value["deviceid"].'</td>';
//                $html .= '<td class="align-center">'.$value["id"].'</td>';
//                $html .= '<td class="align-center">'.$value["agent_name"].'</td>';
//                $html .= '<td class="align-center">'.$value["content"].'</td>';
//                $html .= '<td class="align-center" style="color:#E00515;">已录入</td>';
//                $html .= '<td class="w150 align-center">
//                        <div class="layui-table-cell laytable-cell-9-8">
//                           <a href="javascript:void(0)" onclick="return edit('.$value["id"].');" class="layui-btn  layui-btn-sm" lay-event="reset">修改设备信息</a>';
//                $html .=  '</div></td>';

                $html .= '</tr>';
            }
        }
        if($html == ''){
            $html .= '<tr class="no_data">
                    <td colspan="11">没有符合条件的记录</td>
                </tr>';
        }

        exit(json_encode(array('html'=>$html,'count'=>$list_count)));

    }
    /**
     * 自动导入摄像头
     */
    public function get_camera(){
        $model_school = Model('school');
        $condition=array();
        $condition['isdel']=1;
        $school=$model_school->getSchoolList($condition);
        $shu=array();
        foreach($school as $v){
            if($v['res_group_id']!=0){
                $shu[] = $v['res_group_id'];
            }
        }
        $model_class=Model('classes');
        $where=array();
        $where['isdel']=1;
        $class=$model_class->getAllClasses($where);
        foreach($class as $v){
            if($v['res_group_id']!=0){
                $shu[]=$v['res_group_id'];
            }
        }
        $vlink = new Vomont();
        $res= $vlink->SetLogin();
        $accountid=$res['accountid'];
        $data='';
        foreach($shu as $v){
            $datas=$vlink->SetPlay($accountid,$v);
            if(empty($data)) {
                $data = $datas['resources'];
            }else{
                $data[] = $datas['resources'];
            }
        }
        foreach($data as $k=>$v){
            $play=$v['deviceid'].'-'.$v['channelid'].',';
            $video=$vlink->Resources($accountid,$play);
            $data[$k]['imageurl']=$video['channels'][0]['imageurl'];
            $data[$k]['rtmpplayurl']=$video['channels'][0]['rtmpplayurl'];
            $data[$k]['sq_time']=time();
            $data[$k]['status']=1;
            $data[$k]['is_classroom']=1;
        }
        $model_camera=Model('camera');
        $result=$model_camera->getCameraList('','','id');
        $ret=$this->get_diff_array_by_pk($data,$result);
        $sult=$model_camera->cameras_add($ret);
        return $sult;
    }
    function get_diff_array_by_pk($arr1,$arr2,$pk='id'){
        try{
            $res=[];
            foreach($arr2 as $item) $tmpArr[$item[$pk]] = $item;
            foreach($arr1 as $v) if(! isset($tmpArr[$v[$pk]])) $res[] = $v;
            return $res;
        }catch (\Exception $exception){
            return $arr1;
        }
    }


    public function changetime(){
        $input = input();
        $cid = $input['cid'];
        $updata = array(
            'begintime' =>strtotime($input['starttime']),
            'endtime' =>strtotime($input['endtime'])
        );
        $starttime = 
        $endtime = 
        $result = db('camera')->where('cid',$cid)->update($updata);
        if ($result) {
            ds_json_encode('200', $msg.'设置成功');
        }else{
            ds_json_encode('100', $msg.'设置失败');
        }
        
    }
    public function changetimes(){
        $input = input();
        $cid['cid'] =array('in',$input['cid']);
        $updata = array(
            'begintime' =>strtotime($input['starttime']),
            'endtime' =>strtotime($input['endtime'])
        );
        $starttime =
        $endtime =
        $result = db('camera')->where($cid)->update($updata);
        if ($result) {
            ds_json_encode('200', $msg.'设置成功');
        }else{
            ds_json_encode('100', $msg.'设置失败');
        }

    }

    public function makedefault(){
        $input = input();
        $cid = $input['cid'];
        $key=$input['status'];
        if($cid && $key ){
            $result = db('camera')->where('cid',$cid)->setField('status', $key);
            if ($result) {
                ds_json_encode('200', $msg.'设置成功');
            }else{
                ds_json_encode('100', $msg.'设置失败');
            }
        }else{
            ds_json_encode('100', '参数错误');;
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'entered',
                'text' => '摄像头列表',
                'url' => url('School/Camera/entered')
            )
        );
        return $menu_array;
    }


}