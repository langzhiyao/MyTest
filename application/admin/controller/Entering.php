<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Entering extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/member.lang.php');
        Lang::load(APP_PATH . 'admin/lang/zh-cn/student.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
//        halt($class_name);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
        //获取省份
        $province = db('area')->field('area_id,area_parent_id,area_name')->where('area_parent_id=0')->select();
        //获取代理商
        $agent = db('company')->field('o_id,o_name')->where('o_del=1')->select();
        //获取学校
        $school = db('school')->field('schoolid,name')->where('isdel=1')->select();
        //所属银行
        $bank = db('bank')->where('status=1')->select();
        //收款账号
        $account = db('account')->where('status=1')->select();

        $this->assign('account',$account);
        $this->assign('bank',$bank);
        $this->assign('school',$school);
        $this->assign('agent',$agent);
        $this->assign('province',$province);
        $this->assign('action',$action);
    }

    /**
     * @desc 凭证列表
     * @author langzhiyao
     */
    public function entering() {
        if(session('admin_is_super') !=1 && !in_array(4,$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $where = ' 1=1 ';
        if(!empty($_GET)){
            if(!empty($_GET['name'])){
                $where .= ' AND schoolName LIKE "%'.trim($_GET["name"]).'%" ';
            }
            if(!empty($_GET['school'])){
                $where .= ' AND schoolId = "'.intval($_GET["school"]).'"';
            }
        }
        //查询绑定总数
        $list_count = db('entering')->where($where)->count();
        $this->assign('list_count',$list_count);

        $this->setAdminCurItem('entering');
        return $this->fetch();
    }

    /**
     * @desc 获取凭证分页数据
     * @author langzhiyao
     * @time 20190929
     */
    public function get_list(){
        $where = ' 1=1 ';
        if(!empty($_POST)){
            if(!empty($_POST['name'])){
                $where .= ' AND schoolName LIKE "%'.trim($_POST["name"]).'%" ';
            }
            if(!empty($_POST['school'])){
                $where .= ' AND schoolId = "'.intval($_POST["school"]).'"';
            }
        }

        $page_count = intval(input('post.page_count')) ? intval(input('post.page_count')) : 1;//每页的条数
        $start = intval(input('post.page')) ? (intval(input('post.page'))-1)*$page_count : 0;//开始页数

//        halt($start);
        //查询未绑定的摄像头
        $list = db('entering')->where($where)->limit($start,$page_count)->order('id DESC')->select();
        $list_count = db('entering')->where($where)->count();

        $html = '';
        if(!empty($list)){
            foreach($list as $key=>$value){
                $html .= '<tr class="hover">';
                $html .= '<td class="align-center" >'.$value["schoolName"].'</td>';
                $html .= '<td class="align-center">'.$value["paymentAccount"].'</td>';
                $html .= '<td class="align-center">'.$value["paymentPrice"].'</td>';
                switch ($value['paymentType']){
                    case 1:
                        $html .= '<td class="align-center">网银</td>';
                        break;
                    case 2:
                        $html .= '<td class="align-center">微信</td>';
                        break;
                    case 3:
                        $html .= '<td class="align-center">支付宝</td>';
                        break;
                    case 4:
                        $html .= '<td class="align-center">柜台</td>';
                        break;
                    case 5:
                        $html .= '<td class="align-center">ATM</td>';
                        break;
                    case 6:
                        $html .= '<td class="align-center">其他</td>';
                        break;
                }
                $html .= '<td class="align-center">'.date("Y-m-d H:i:s",$value["paymentTime"]).'</td>';
                $html .= '<td class="align-center">'.$value["paymentNumber"].'</td>';
                $html .= '<td class="align-center">'.$value["ReceivablesAccount"].'</td>';
                $html .= '<td class="align-center">'.$value["ReceivablesPrice"].'</td>';
                $html .= '<td class="align-center">'.date("Y-m-d H:i:s",$value["ReceivablesTime"]).'</td>';
                $html .= '<td class="align-center">'.date("Y-m-d H:i:s",$value["updateTime"]).'</td>';
                $html .= '<td class="w150 align-center">
                        <div class="layui-table-cell laytable-cell-9-8">
                           <a href="javascript:void(0)"  class="layui-btn  layui-btn-sm show" data-id="'.$value["id"].'" lay-event="reset">查看</a>
                           <a href="javascript:void(0)"  class="layui-btn  layui-btn-sm edit" data-id="'.$value["id"].'" lay-event="reset">修改</a>
                           <a href="javascript:void(0)"  class="layui-btn  layui-btn-sm import" data-id="'.$value["id"].'" lay-event="reset">批量导入并开通会员</a>';
                $html .=  '</div></td>';

                $html .= '</tr>';
            }
        }
        if($html == ''){
            $html .= '<tr class="no_data">
                    <td colspan="15">没有符合条件的记录</td>
                </tr>';
        }

        exit(json_encode(array('html'=>$html,'count'=>$list_count)));

    }

    /**
     * @desc 导入失败列表
     * @author langzhiyao
     */

    public function fail(){


        $this->setAdminCurItem('fail');
        return $this->fetch();
    }


    /**
     * @desc 导入成功列表
     * @author langzhiyao
     */
    public function success(){


        $this->setAdminCurItem('success');
        return $this->fetch();
    }

    /**
     * @desc 录入线下转账资金信息
     * @author langzhiyao
     */
    public function addEntering(){
        if(!empty($_POST)){
          /*  $provinceId = intval(input('post.provinceId'));
            $cityId = intval(input('post.cityId'));
            $areaId = intval(input('post.areaId'));
            $agentId = intval(input('post.agentId'));*/
            $schoolId = intval(input('post.schoolId'));
            $schoolAddress = trim(input('post.schoolAddress'));
            $schoolUserName = trim(input('post.schoolUserName'));
            $schoolMobile = trim(input('post.schoolMobile'));
            $paymentType = trim(input('post.paymentType'));
            $bank = intval(input('post.bank'));
            $paymentBankProvince = intval(input('post.paymentBankProvince'));
            $paymentBankCity = intval(input('post.paymentBankCity'));
            $paymentBankArea = intval(input('post.paymentBankArea'));
            $paymentBankAddress = trim(input('post.paymentBankAddress'));
            $paymentAccount = trim(input('post.paymentAccount'));
            $paymentPrice = trim(input('post.paymentPrice'));
            $paymentTime = trim(strtotime(input('post.paymentTime')));
            $paymentNumber = trim(input('post.paymentNumber'));
            $paymentImage = trim(input('post.paymentImage'));
            $ReceivablesAccount = trim(input('post.ReceivablesAccount'));
            $ReceivablesPrice = trim(input('post.ReceivablesPrice'));
            $ReceivablesTime = trim(strtotime(input('post.ReceivablesTime')));
            $content = trim(input('post.content'));
            //获取代理商名称
//            $agentName =$this->get_agentName($agentId);
            //获取学校名称
            $schoolName =$this->get_schoolName($schoolId);

            $data = array(
               /* 'provinceId'=>$provinceId,
                'cityId'=>$cityId,
                'areaId'=>$areaId,
                'agentId'=>$agentId,
                'agentName'=>$agentName,*/
                'schoolId'=>$schoolId,
                'schoolName'=>$schoolName,
                'schoolAddress'=>$schoolAddress,
                'schoolUserName'=>$schoolUserName,
                'schoolMobile'=>$schoolMobile,
                'paymentType'=>$paymentType,
                'paymentBank'=>$bank,
                'paymentBankProvince'=>$paymentBankProvince,
                'paymentBankCity'=>$paymentBankCity,
                'paymentBankArea'=>$paymentBankArea,
                'paymentBankAddress'=>$paymentBankAddress,
                'paymentAccount'=>$paymentAccount,
                'paymentPrice'=>$paymentPrice,
                'paymentTime'=>$paymentTime,
                'paymentNumber'=>$paymentNumber,
                'paymentImage'=>$paymentImage,
                'ReceivablesAccount'=>$ReceivablesAccount,
                'ReceivablesPrice'=>$ReceivablesPrice,
                'ReceivablesTime'=>$ReceivablesTime,
                'content'=>$content,
                'updateTime'=>time()
            );
            //判断付款单号
            $res = db('entering')->where('paymentNumber="'.$paymentNumber.'"')->find();
            if(!empty($res)){
                echo json_encode(array('code'=>100,'message'=>'保存失败,付款单号错误/已存在'));exit;
            }
            //添加
            $result = db('entering')->insert($data);
            if($result){
                echo json_encode(array('code'=>200,'message'=>'保存成功'));exit;
            }else{
                echo json_encode(array('code'=>100,'message'=>'保存失败'));exit;
            }
        }else{
            $id = intval(input('get.id'));
            if(!empty($id)){
                $entering = db('entering')->where('id="'.$id.'"')->find();
                $this->assign('entering',$entering);
            }
            $this->setAdminCurItem('entering');
            return $this->fetch();
        }

    }

    /**
     * @desc 录入线下转账资金信息
     * @author langzhiyao
     */
    public function editEntering(){
        if(!empty($_POST)){
            $id = intval(input('post.id'));
            $schoolId = intval(input('post.schoolId'));
            $schoolAddress = trim(input('post.schoolAddress'));
            $schoolUserName = trim(input('post.schoolUserName'));
            $schoolMobile = trim(input('post.schoolMobile'));
            $paymentType = trim(input('post.paymentType'));
            $bank = intval(input('post.bank'));
            $paymentBankProvince = intval(input('post.paymentBankProvince'));
            $paymentBankCity = intval(input('post.paymentBankCity'));
            $paymentBankArea = intval(input('post.paymentBankArea'));
            $paymentBankAddress = trim(input('post.paymentBankAddress'));
            $paymentAccount = trim(input('post.paymentAccount'));
            $paymentPrice = trim(input('post.paymentPrice'));
            $paymentTime = trim(strtotime(input('post.paymentTime')));
            $paymentNumber = trim(input('post.paymentNumber'));
            $paymentImage = trim(input('post.paymentImage'));
            $ReceivablesAccount = trim(input('post.ReceivablesAccount'));
            $ReceivablesPrice = trim(input('post.ReceivablesPrice'));
            $ReceivablesTime = trim(strtotime(input('post.ReceivablesTime')));
            $content = trim(input('post.content'));
            //获取学校名称
            $schoolName =$this->get_schoolName($schoolId);

            $data = array(
                /* 'provinceId'=>$provinceId,
                 'cityId'=>$cityId,
                 'areaId'=>$areaId,
                 'agentId'=>$agentId,
                 'agentName'=>$agentName,*/
                'schoolId'=>$schoolId,
                'schoolName'=>$schoolName,
                'schoolAddress'=>$schoolAddress,
                'schoolUserName'=>$schoolUserName,
                'schoolMobile'=>$schoolMobile,
                'paymentType'=>$paymentType,
                'paymentBank'=>$bank,
                'paymentBankProvince'=>$paymentBankProvince,
                'paymentBankCity'=>$paymentBankCity,
                'paymentBankArea'=>$paymentBankArea,
                'paymentBankAddress'=>$paymentBankAddress,
                'paymentAccount'=>$paymentAccount,
                'paymentPrice'=>$paymentPrice,
                'paymentTime'=>$paymentTime,
                'paymentNumber'=>$paymentNumber,
                'paymentImage'=>$paymentImage,
                'ReceivablesAccount'=>$ReceivablesAccount,
                'ReceivablesPrice'=>$ReceivablesPrice,
                'ReceivablesTime'=>$ReceivablesTime,
                'content'=>$content,
                'updateTime'=>time()
            );
            //判断付款单号
            $res = db('entering')->where('paymentNumber="'.$paymentNumber.'" AND id !="'.$id.'"')->find();
            if(!empty($res)){
                echo json_encode(array('code'=>100,'message'=>'保存失败,付款单号错误/已存在'));exit;
            }
            //修改
            $result = db('entering')->where('id="'.$id.'"')->update($data);
            if($result){
                echo json_encode(array('code'=>200,'message'=>'保存成功'));exit;
            }else{
                echo json_encode(array('code'=>100,'message'=>'保存失败'));exit;
            }
        }else{
            $id = intval(input('get.id'));
            if(!empty($id)){
                $entering = db('entering')->where('id="'.$id.'"')->find();
                $this->assign('entering',$entering);
            }
            $this->setAdminCurItem('entering');
            return $this->fetch();
        }

    }

    /**
     * @desc 上传图片
     * @author langzhiyao
     * @time 20181213
     */
    public function upload_img(){
        if(!empty($_FILES)){
            if ((($_FILES["file"]["type"] == "image/*") || ($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/png") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/pjpeg")))
            {
                if($_FILES["file"]["size"] < 8*1024*1024){
                    if ($_FILES["file"]["error"] > 0)
                    {
                        echo json_encode(array('code'=>100,'message'=>$_FILES["file"]["error"]));exit;
                    }
                    else
                    {
                        if (!empty($_FILES['file']['tmp_name'])) {
                            $file_object= request()->file('file');
                            $base_url=BASE_UPLOAD_PATH . '/admin/entering/';
                            $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
                            $file_name='entering_'.time().rand(1000,9999).".$ext";
                            $info = $file_object->rule('uniqid')->validate(['ext' => 'jpg,png,gif,jpeg'])->move($base_url,$file_name);
                            if (!$info) {
                                echo json_encode(array('code'=>100,'message'=>$file_object->getError()));exit;
                            }
                        } else {
                            echo json_encode(array('code'=>100,'message'=>'上传失败，请尝试更换图片格式或小图片'));exit;
                        }
                        $name_dir= '/admin/entering/'.$info->getFilename();
                        $file_dir=UPLOAD_SITE_URL.'/admin/entering/'.$info->getFilename();
//                        $result[] = array('message'=>'修改成功','avatar_url'=>$name_dir);
                        echo json_encode(array('code'=>200,'message'=>'修改成功','tmp_url'=>$file_dir,'url'=>$name_dir));exit;
                    }
                }else{

                }
            }
            else
            {
                echo json_encode(array('code'=>100,'message'=>'图片上传类型不符合，请重新上传'));exit;
            }
        }
    }

    /**
     * @desc 根据学校ID获取学校名称
     * @author langzhiyao
     * @time 20181212
     */
    public function get_schoolName($id){
        $result = db('school')->field('name')->where('schoolid="'.$id.'"')->find();

        return $result['name'];
    }
    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'entering',
                'text' => '线下资金转账凭证',
                'url' => url('Admin/Entering/entering')
            ),
            array(
                'name' => 'fail',
                'text' => '导入失败列表',
                'url' => url('Admin/Entering/fail')
            ),
            array(
                'name' => 'success',
                'text' => '导入成功列表',
                'url' => url('Admin/Entering/success')
            )

        );
        return $menu_array;
    }

}

?>
