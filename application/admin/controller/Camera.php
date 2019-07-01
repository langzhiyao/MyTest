<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;
use vomont\Vomont;

class Camera extends AdminControl
{

    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/look.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
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
        if(session('admin_is_super') !=1 && !in_array('4',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $where = ' status=2 ';
        if(!empty($_GET)){
            if(!empty($_GET['name'])){
                $where .= ' AND camera_name LIKE "%'.trim($_GET["name"]).'%" ';
            }
            if(!empty($_GET['province'])){
                $where .= ' AND province_id = "'.intval($_GET["province"]).'"';
            }
            if(!empty($_GET['city'])){
                $where .= ' AND city_id = "'.intval($_GET["city"]).'"';
            }
            if(!empty($_GET['area'])){
                $where .= ' AND area_id = "'.intval($_GET["area"]).'"';
            }
            if(!empty($_GET['school'])){
                $where .= ' AND school_id = "'.intval($_GET["school"]).'"';
            }
            if(!empty($_GET['grade'])){
                $where .= ' AND class_area LIKE "%'.trim($_GET["grade"]).'%"';
            }
            if(!empty($_GET['class'])){
                $where .= ' AND class_area LIKE "%'.trim($_GET["class"]).'%"';
            }
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

        $where = ' status=1 ';
        if(!empty($_POST)){
            if(!empty($_POST['name'])){
                $where .= ' AND camera_name LIKE "%'.trim($_POST["name"]).'%" ';
            }
            if(!empty($_POST['province'])){
                $where .= ' AND province_id = "'.intval($_POST["province"]).'"';
            }
            if(!empty($_POST['city'])){
                $where .= ' AND city_id = "'.intval($_POST["city"]).'"';
            }
            if(!empty($_POST['area'])){
                $where .= ' AND area_id = "'.intval($_POST["area"]).'"';
            }
            if(!empty($_POST['school'])){
                $where .= ' AND school_id = "'.intval($_POST["school"]).'"';
            }
            if(!empty($_POST['grade'])){
                $where .= ' AND class_area LIKE "%'.trim($_POST["grade"]).'%"';
            }
            if(!empty($_POST['class'])){
                $where .= ' AND class_area LIKE "%'.trim($_POST["class"]).'%"';
            }
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
        if(session('admin_is_super') !=1 && !in_array('8',$this->action)){
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
        if(session('admin_is_super') !=1 && !in_array('8',$this->action)){
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
        if(session('admin_is_super') !=1 && !in_array('8',$this->action)){
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
        if(session('admin_is_super') !=1 && !in_array('4',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $where = '';
        if(!empty($_GET)){
            $where = $this->_conditions($_GET);
        }

        $list_count = db('camera')->where($where)->count();

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
        if (isset($where['name']) && !empty($where['name'])) {
            $condition['name'] = array('LIKE','%'.$where['name'].'%');
        }
        $res = array();
        $name = false;
        if (isset($where['class']) && !empty($where['class']) ) {
            $class = $this->getResGroupIds(array('classname'=>$where['class']));
            if ($class) {
                $res=array_merge($res, $class);
            }
            unset($where);
            $name = 'true';
        }
        if (isset($where['grade']) && !empty($where['grade'])) {
            $grade = $this->getResGroupIds(array('schoolid'=>$where['school'],'sc_type'=>$where['grade']));
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
        if (isset($where['area']) && $where['area'] != 0 ) {
            $area = $this->getResGroupIds(array('areaid'=>$where['area']));
            unset($where['city']);
            unset($where['province']);
            $name = 'true';
            if ($area) {
                $res=array_merge($res, $area);
            }
        }
        if (isset($where['city']) && $where['city'] != 0 ) {
            $city = $this->getResGroupIds(array('cityid'=>$where['city']));
            unset($where['province']);
            $name = 'true';
            if ($city) {
                $res=array_merge($res, $city);
            }
        }
        if (isset($where['province']) && $where['province'] != 0 ) {
            $province = $this->getResGroupIds(array('provinceid'=>$where['province']));
            $name = 'true';
            if ($province) {
                $res=array_merge($res, $province);
            }
        }
        if ($name == 'true') {
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
            $schoolid=$where['schoolid'];
            unset($where['schoolid']);
            $sc_id = db('schooltype')->where($where)->value('sc_id');
            unset($where['sc_type']);
            $where[]=['exp','FIND_IN_SET('.$sc_id.',typeid)'];
            $where['schoolid']=$schoolid;
        }
        $classname = '';
        if (isset($where['classname']) && !empty($where['classname']) ) {
            $classname = $where['classname'];
            unset($where['classname']);
        }
        $where['res_group_id'] =array('gt',0);
        $Schoollist = $School->getAllAchool($where,'res_group_id');
        // p($where);exit;
        if (isset($where['provinceid']) && !empty($where['provinceid'])) {
            $where['school_provinceid'] =$where['provinceid'];
            unset($where['provinceid']);
        }
        if (isset($where['cityid']) && !empty($where['cityid'])) {
            $where['school_cityid'] =$where['cityid'];
            unset($where['cityid']);
        }
        if (isset($where['areaid']) && !empty($where['areaid'])) {
            $where['school_areaid'] =$where['areaid'];
            unset($where['areaid']);
        }
        if (isset($where['areaid']) && !empty($where['areaid'])) {
            $where['school_areaid'] =$where['areaid'];
            unset($where['areaid']);
        }
        if (!empty($classname)) {
            $where['classname'] = array('like','%'.$classname.'%');
            unset($Schoollist);
        }
        $res = array();
        $Classlist = $Class->getAllClasses($where,'res_group_id');
        $sc_resids=array_column($Schoollist, 'res_group_id');
        if ($sc_resids) {
            $res = $sc_resids;
//            array_push($res, $sc_resids);
        }
        $cl_resids=array_column($Classlist, 'res_group_id');
        if ($cl_resids) {
//            array_push($res, $cl_resids);
            if(empty($res)){
                $res = $cl_resids;
            }else{
                $res = array_merge($res,$cl_resids);
            }
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
        //查询已安装的摄像头
        $list = db('camera')->where($where)->limit($start,$page_count)->order('cid DESC')->select();
        $date=date('H:i',time());
        foreach($list as $k=>$v){
            if($v['online']==0){
                $list[$k]['statuses']=2;
            }else{
                if($v['status']==1){
                    if(!empty($v['begintime'])&&!empty($v['endtime'])){
                        $begintime=date('H:i',$v['begintime']);
                        $endtime=date('H:i',$v['endtime']);
                        if($date<$begintime||$date>$endtime){
                            $list[$k]['statuses']=2;
                        }else{
                            $list[$k]['statuses']=1;
                        }
                    }else{
                        $list[$k]['statuses']=1;
                    }
                }else{
                    $list[$k]['statuses']=2;
                }
            }
        }
        //return $list;exit;
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
                if($v['statuses'] == 1){
                    $html .= '<td class="align-center"><b style="color:green;">在线</b></td>';
                }else if($v['statuses'] == 2){
                    $html .= '<td class="align-center"><b style="color:red;">离线</b></td>';
                }
                $html .= '<td class="align-center">'.$v["parentid"].'</td>';
//                $html .= '<td class="align-center"><img src="'.$v["imageurl"].'" width="120" height="50"></td>';
                //if($v['is_rtmp']==2){
                    //$html .= '<td class="align-center">有人正在观看中▪▪▪</td>';
                //}else {
                    //$html .= '<td id="rmt_' . $v['cid'] . '" class="align-center"><a href="javascript:viod(0)" onClick="rtmplay(' . $v['cid'] . ')">点击播放</a></td>';
                //}
                //<img onClick="rtmplay('.$v['cid'].')" src="'.$v["imageurl"].'" width="120" height="50">
                if($v['is_classroom'] == 1){
                    //$html .= '<td class="align-center"><b style="color:red;">否</b></td>';
                    $html .= '<td class="align-center"><a id="dp_'.$v['cid'].'" statu="'.$v['is_classroom'].'" class="layui-unselect layui-form-checkbox" onclick="makedefault('.$v['cid'].','.$v['id'].');" ><span>启用</span><i class="layui-icon layui-icon-ok"></i></a></td>';
                }else if($v['is_classroom'] == 2){
                    //$html .= '<td class="align-center"><b style="color:green;">是</b></td>';
                    $html .= '<td class="align-center"><a id="dp_'.$v['cid'].'" statu="'.$v['is_classroom'].'" class="layui-unselect layui-form-checkbox layui-form-checked" onclick="makedefault('.$v['cid'].','.$v['id'].');" ><span>启用</span><i class="layui-icon layui-icon-ok"></i></a></td>';
                }
                if($v['status'] == 1){
                    //$html .= '<td class="align-center">开启</td>';
                    $html .= '<td class="align-center"><a id="dps_'.$v['cid'].'" statu="'.$v['status'].'" class="layui-unselect layui-form-checkbox layui-form-checked" onclick="makedefaults('.$v['cid'].');" ><span>启用</span><i class="layui-icon layui-icon-ok"></i></a></td>';
                }else if($v['status'] == 2){
                    //$html .= '<td class="align-center">关闭</td>';
                    $html .= '<td class="align-center"><a id="dps_'.$v['cid'].'" statu="'.$v['status'].'" class="layui-unselect layui-form-checkbox" onclick="makedefaults('.$v['cid'].');" ><span>启用</span><i class="layui-icon layui-icon-ok"></i></a></td>';
                }
                $html .= '<td class="align-left">'.date('Y-m-d H:i:s',$v["sq_time"]).'</td>';
                $start = trim($v['cid'].'_Start');
                $end = trim($v['cid'].'_End');
                $defulbegin =empty($v["begintime"])?'':date('H:i:s',$v["begintime"]);
                $defulend   =empty($v["endtime"])?'':date('H:i:s',$v["endtime"]);
                $html .= "<td class='align-center'>
                    开启时间：<input type='text' class='pictime' id='picktimeStart".$v['cid']."' onfocus='timesss(".'"'.$start.'"'.")' value='".$defulbegin."'/> <hr>
                    关闭时间：<input type='text' class='pictime' id='picktimeEnd".$v['cid']."' onfocus='timesss(".'"'.$end.'"'.")' value='".$defulend."' />
                    </td>";
                $html .='<td class="align-center"><a href="javascript:del('.$v["cid"].')" class="layui-btn layui-btn-xs">删除</a></td>';
                $html .= '</tr>';
            }
        }
        if($html == ''){
            $html .= '<tr class="no_data">
                    <td colspan="12">没有符合条件的记录</td>
                </tr>';
        }

        exit(json_encode(array('html'=>$html,'count'=>$list_count)));

    }
    /**
     * 摄像头删除
     */
    public function del(){
        $cid=input('param.cid');
        $model_camera = Model('camera');
        $result = $model_camera->camera_del($cid);
        if ($result) {
                $this->success('删除成功', 'Camera/entered');
        } else {
            $this->error('删除失败');
        }
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
                $data = !empty($datas['resources'])?$datas['resources']:'';
            }else{
                if(!empty($datas['resources'])){
                    $data = array_merge($data,$datas['resources']);
                }

            }
        }
        foreach($data as $k=>$v){
            $play=$v['deviceid'].'-'.$v['channelid'].',';
            $video=$vlink->Resources($accountid,$play);
            $data[$k]['imageurl']=$video['channels'][0]['imageurl'];
            $data[$k]['rtmpplayurl']=$video['channels'][0]['rtmpplayurl'];
            $data[$k]['is_rtmp']=1;
            $data[$k]['sq_time']=time();
            $data[$k]['status']=1;
            $data[$k]['is_classroom']=1;
        }
        $model_camera=Model('camera');
        $result=$model_camera->getCameraList('','','id');
        $ret=$this->get_diff_array_by_pk($data,$result);
        $sult=$model_camera->cameras_add($ret);
        if($sult){
            echo json_encode(array('count'=>$sult));
        }else{
            echo json_encode(array('count'=>0));
        }
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
        $key=$input['classroom'];
        $id=$input['id'];
        if($cid && $key ){
            $result = db('camera')->where('cid',$cid)->setField('is_classroom', $key);
            $vlink = new Vomont();
            $res= $vlink->SetLogin();
            $accountid=$res['accountid'];
            if($key==2) {
                $vlink->AaaStorage($accountid, $id);
            }else{
                $vlink->DelStorage($accountid, $id);
            }
            if ($result) {
                ds_json_encode('200', $msg.'设置成功');
            }else{
                ds_json_encode('100', $msg.'设置失败');
            }
        }else{
            ds_json_encode('100', '参数错误');;
        }
    }
    public function makedefaults(){
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
                'url' => url('Admin/Camera/entered')
            )
        );
        return $menu_array;
    }
}