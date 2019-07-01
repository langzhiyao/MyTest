<?php

namespace app\school\controller;

use think\Lang;
use think\Validate;

class Stest extends AdminControl {

	public $SchoolInsertInfo;
	public $SchoolInsert;
	public $ClassInsertInfo;
	public $ClassInsert;
	public $True_School;
	public $True_Class;
	public $RecodeNum = 2000;
	public $RandTime ;
	public $StartTime ='2019-05-17 06:00:00';
	public $Endtime = '2019-05-21 22:00:00';

	function __construct(){
		$this->SchoolInsertInfo = [];
		$this->SchoolInsert = [];
		$this->ClassInsertInfo = [];
		$this->ClassInsert = [];
		$this->True_School = [];
		$this->True_Class = [];
		// exit;
		$this->RandTime =$this->randomDate($this->StartTime,$this->Endtime );
		// $name = CreatRandName();
	}
/**
 * 
DELETE FROM x_class WHERE `classid`>827;
DELETE FROM x_school WHERE `schoolid`>60;
DELETE FROM x_student WHERE `s_id`>8558;
DELETE FROM x_member WHERE `member_id`>100163;
DELETE FROM x_packagesorder WHERE `order_id`>2028;
 * @Author   Mr.Wang
 * @DateTime 2019-02-20
 */
	public function InsertDate(){
		// exit;
		@set_time_limit(0);
		$starttime = explode(' ',microtime());
		$schoolNum = ceil($this->RecodeNum / 300) ;
		$school = db('schoolcopy')->where('is_copy',1)->limit(0,$schoolNum)->select();
		$schoolIds =array_column($school, 'schoolid');
		$studentIds =[];
		$studnets = [];
		$schoolIdss = [];
		$start = 0 ;
		$model = db();
		// $model->startTrans();
		foreach ($school as $sck => $sc) {
			$studentNum = rand(200,300);
			$student = $student =db('studentcopy')->where('is_copy',1)->limit($start,$studentNum)->select();
			$studentid= array_column($student,'id');
			foreach($studentid as $k=>$sid)$studentIds[] =$sid;
			$schoolIdss[] =$sc['schoolid'];
			if ($start == 0) {
				$start = $studentNum;
			}else{
				$start += $studentNum;
			}
			unset($sc['schoolid']);
			unset($sc['is_copy']);
			//写入学校
			$InsertSchool = db('school')->insertGetId($sc);
			if (!$InsertSchool) {
				// $model->rollback();
				exit('第【'.($sck+1).'】个学校写入失败');
			}
			echo '<br/>第'.($sck+1).'个学校-【'.$sc['name'].'】 写入成功------------------------------------------------------------------<br/>';
			// //写入对应的班级
			$InsertClassroom= $this->InsertClassroom($InsertSchool,$sc,count($student));
			
			if (!$InsertClassroom) {
				// $model->rollback();
				exit('第【'.($sck+1).'】个学校【班级】入失败');
			}

			echo '第'.($sck+1).'个学校-【'.$sc['name'].'】 【'.$InsertClassroom.'个班级】写入成功------------------------------------------------------------------<br/>';
			//写入对应学生 写入对应家长
			
			$InsertStudent = $this->InsertStudent($InsertSchool,$sc,$student);
			
			if (!$InsertStudent) {
				// $model->rollback();
				exit('第【'.($sck+1).'】个学校【学生&& 家长】写入失败');
			}
			echo '第'.($sck+1).'个学校-【'.$sc['name'].'】 【'.$InsertStudent.'个学生 && 家长】写入成功------------------------------------------------------------------<br/>';

			// $InsertMember = $this->InsertMember($InsertSchool,$sc);
			
			// if (!$InsertMember) {
			// 	// $model->rollback();
			// 	exit('第【'.($sck+1).'】个学校【学生&& 家长】写入失败');
			// }
			// echo '第'.($sck+1).'个学校-【'.$sc['name'].'】 【'.$InsertMember.'个家长】写入成功------------------------------------------------------------------<br/>';
			// exit;
			// //写入对应订单
			$InsertOrder = $this->InsertOrder($InsertSchool);
			if (!$InsertOrder) {
				$model->rollback();
				exit('第【'.($sck+1).'】个学校【订单】写入失败');
			}
			echo '第'.($sck+1).'个学校-【'.$sc['name'].'】 【'.$InsertOrder.'个订单】写入成功------------------------------------------------------------------<br/>';

		}
		

		$schoolIdss=db('schoolcopy')->where('schoolid','in',$schoolIdss)->setField('is_copy',2);
		$studentIds = db('studentcopy')->where('id','in',$studentIds)->setField('is_copy',2);

		$endtime = explode(' ',microtime());
		$thistime = $endtime[0]+$endtime[1]-($starttime[0]+$starttime[1]);
		$thistime = round($thistime,3);
		echo "执行写入耗时：".$thistime." 秒。".time().'<br>';
		$model->commit();
	}

	/**
	 * 写入班级
	 * @Author   Mr.Wang
	 * @DateTime 2019-02-20
	 * @param    [type]     $schoolid   [学校id]
	 * @param    [type]     $schoolInfo [学校信息]
	 */
	public function InsertClassroom($schoolid,$schoolInfo,$studentNum){
		//设置班级数量
		$TotalClassRoomNum = ceil($studentNum / (rand(20,30)));
		//按照大中小三个年级分配平均几个班级
		$classRoomNum =RandDistribution($TotalClassRoomNum,3,2);
		$con = [
			1 => '小班',
			2 => '中班',
			3 => '大班'
		];
		$con1 = ['一','二','三','四','五','六','七','八','九','十'];
		$InsertClassroom = [];
    	$model_classes = model('Classes');
    	
    	$allNum = 0;
		foreach ($classRoomNum as $k => $v) {
			echo $con[$k].$v.'个<br/>';
			for ($i=0; $i < $v; $i++) { 
				// echo '---'.$con[$k].$con1[$i].'班<br/>';
				$classcard=$schoolInfo['schoolCard'].($model_classes->getNumber($schoolInfo['schoolCard']));
	    		$room = array(
					'classname'         => $con[$k].$con1[$i].'班', // 班级名称
					'classCard'         => $classcard, //班级标识号
					'schoolid'          => $schoolid, //学校id
					'school_provinceid' => $schoolInfo['provinceid'], //省
					'school_cityid'     => $schoolInfo['cityid'], //市
					'school_areaid'     => $schoolInfo['areaid'], //县
					'school_region'     => $schoolInfo['region'], //地址
					'typeid'            => $schoolInfo['typeid'], // 默认全是幼儿园的
					'desc'              => $schoolInfo['name'], // 班级备注
					// 'qr'                => $qr, //二维码地址
					'isdel'             => 1, //1未删除
					'createtime'        => $this->RandTime, //创建时间
					'updatetime'        => $this->RandTime, ///修改时间
					'option_id'         => 1, 
					'admin_company_id'  => 1,
					'res_group_id'      => 0,
					'is_true'           => 2,  //不是真实
	    		);
	    		$InsertClassroom[] =$room;
	    		$allNum++;
			}
		}

		$result = db('class')->insertAll($InsertClassroom);
		if ($result) {
			return $allNum;
		}else{
			return false;
		}
	}
	
	/**
	 * 写入学生家长
	 * @Author   Mr.Wang
	 * @DateTime 2019-02-20
	 * @param    [type]     $scid     [学校id]
	 * @param    [type]     $scinfo   [学校信息]
	 * @param    [type]     $students [学生信息]
	 */
	public function InsertStudent($scid,$scinfo,$students){
		// $st = end($students);
		// $unsetid = count($students) ;
		$rand = GetRand();
	    $rand->rndChinaName();

		$classRoom = db('class')->where(['schoolid'=>$scid,'is_true'=>2])->select();
		$classRoomNum = count($classRoom);
		$studentsNum = count($students);
		$distribution =RandDistribution($studentsNum,$classRoomNum,8);
		$student = [];
		foreach ($distribution as $k => $v) {
			$cl = end($classRoom);
			for ($i=0; $i < $v ; $i++) {
				$unsetStudentid = count($students) ;
				$unsetClassid = count($classRoom);
				$st = end($students);
				$classType = mb_substr($cl['classname'],0,2,"utf-8");
				switch ($classType) {
					case '小班':
						$age = rand(3,4);
						break;
					case '中班':
						$age = rand(4,5);
						break;
					case '大班':
						$age = rand(5,6);
						break;
				}
				$Y = date("Y",strtotime("-".$age."years"));
				$birthday = $this->randomDate($Y.'-01-01',$Y.'-12-30');

				$stud= array(
					's_name'            => $st['name'], //学生名字
					's_sex'             => $st['sex']==1?2:1, //性别：1，男；2，女
					's_classid'         => $cl['classid'], //班级id
					's_schoolid'        => $cl['schoolid'], //学校id
					's_sctype'          => 1, //学校类型id
					's_birthday'        => $birthday , //生日
					// 's_card'         => $s['idCard'], //学生身份证号
					's_provinceid'      => $cl['school_provinceid'], //省id
					's_cityid'          => $cl['school_cityid'], //市
					's_areaid'          => $cl['school_areaid'], //县
					's_region'          => empty($scinfo['address'])?$cl['school_region']:$scinfo['address'], //地址
					's_createtime'      => $this->randomDate($this->StartTime,$this->Endtime), //创建时间
					// 's_remark'       => $s['note'], //备注
					// 's_ownerAccount' => !empty($member_id)?$member_id:'', //学生绑定的家长账号id （主账户）
					'classCard'         => $cl['classCard'], //班级识别码（app绑定学生时添加）
					'is_true'           => 2, 
    			);
    			unset($students[$unsetStudentid-1]);
    			if($stud['s_name']){
    				//添加家长
		    		$xing = mb_substr($stud['s_name'],0,1,"utf-8")	;
					$ming = $rand->getName(4);
					if(mt_rand(0,100)>50)$ming .= $rand->getMing();
					$name = $xing.$ming;
					$mobile = randMobile(1);
					$Meb = [
						'member_name'           => $name,  //用户名称
						'member_nickname'       => $name,
						'member_identity'       => 1, // 身份
						'is_owner'              => 0, //是否主账号
						'member_age'            => 1, //年龄
						'member_truename'       => $name, //真实姓名
						'member_password'       => md5(123456), //密码 
						'member_mobile'         => $mobile, //手机号
						'member_mobile_bind'    => 1, //是否绑定手机

						'member_add_time'       => $this->randomDate($this->StartTime,$this->Endtime), //创建时间,
						'member_edit_time'      => $this->randomDate($this->StartTime,$this->Endtime), //创建时间, //修改时间
						'member_old_login_time' => $this->randomDate($this->StartTime,$this->Endtime), //创建时间, //会员上次登录时间
						'is_true'               => 2, 
						'member_provinceid'     => $scinfo['provinceid'], //省
						'member_cityid'         => $scinfo['cityid'], //市
						'member_areaid'         => $scinfo['areaid'], //县
						'member_areainfo'       => $scinfo['region'], //地址
						'classid'               => $cl['classid'],
					];
					$memInser = db('member')->insertGetId($Meb);

					if ($memInser) {
						$stud['s_ownerAccount'] = $memInser;
						$student[] = $stud;
					}else{
						exit('学生家长添加失败!');
					}

    			}
			}
    		unset($classRoom[$unsetClassid-1]);

		}
		$result = db('student')->insertAll($student);
		if ($result) {
			return $result;
		}else{
			return false;
		}
		
		
	}

	public function InsertMember($scid,$scinfo){
		$students = db('student')->where(['s_schoolid'=>$scid,'is_true'=>2])->field('s_id,s_name,s_classid,s_schoolid,s_createtime')->select();
		$rand = GetRand();
	    $rand->rndChinaName();
		foreach ($students as $k => $v) {
			$xing = mb_substr($v['s_name'],0,1,"utf-8")	;
			$ming = $rand->getName(4);
			if(mt_rand(0,100)>50)$ming .= $rand->getMing();
			$name = $xing.$ming;
			$mobile = randMobile(1);

			$Meb = [
				'member_name'           => $name,  //用户名称
				'member_nickname'       => $name,
				'member_identity'       => 1, // 身份
				'is_owner'              => 0, //是否主账号
				'member_age'            => 1, //年龄
				'member_truename'       => $name, //真实姓名
				'member_password'       => md5(123456), //密码 
				'member_mobile'         => randMobile(1), //手机号
				'member_mobile_bind'    => 1, //是否绑定手机
				'member_add_time'       => $v['s_createtime'],
				'member_edit_time'      => $v['s_createtime'], //修改时间
				'member_old_login_time' => $v['s_createtime'], //会员上次登录时间
				'is_true'               => 2, 
				'member_provinceid'     => $scinfo['provinceid'], //省
				'member_cityid'         => $scinfo['cityid'], //市
				'member_areaid'         => $scinfo['areaid'], //县
				'member_areainfo'       => $scinfo['region'], //地址
				'classid'               => $v['s_classid'], 
			];
			$memInser = db('member')->insertGetId($Meb);
			if ($memInser) {
				db('student')->where('s_id', $v['s_id'])->setField('s_ownerAccount', $memInser);
			}else{
				exit('家长添加失败！');
			}
		}
		return count($students);
		exit;
		
	   	
	}
	/**
	 * 写入订单
	 * @Author   Mr.Wang
	 * @DateTime 2019-02-20
	 * @param    [type]     $scid 学校id
	 */
	public function InsertOrder($scid){
		$students = db('student')
						 ->alias('s')
		                 ->join('__MEMBER__ m','s.s_ownerAccount=m.member_id','LEFT')
		                 ->join('__CLASS__ c','s.s_classid=c.classid','LEFT')
		                 ->where(['s.s_schoolid'=>$scid,'s.is_true'=>2])
		                 ->field('s.s_id,s.s_name,s.s_classid,s.s_schoolid,s.s_provinceid,s.s_createtime,s.s_cityid,s.s_areaid,s.s_region,m.member_id,m.member_name,m.member_mobile,c.desc,c.classname')
		                 ->select();

		$Pkgs=model('Pkgs');
        $packageInfo = $Pkgs->getPkgList(array('pkg_type'=>1,'pkg_enabled'=>1)); 
        
        $this->_logic_buy_1 = \model('buy_1','logic');
        $afterInsert = db('packagesorder')->field('order_id')->order('order_id desc')->find();
        $afterInsertid = $afterInsert['order_id'] + 1 ;
        $allOrder =[];
		foreach ($students as $k => $v) {
			$pay_sn = $this->_logic_buy_1->makePaySn($v['member_id']);
			$packRand = rand(0,count($packageInfo)-1);
			$order = [
				'pay_sn'           => $pay_sn,
				'buyer_id'         => $v['member_id'],
				'buyer_name'       => $v['member_name'],
				'buyer_mobile'     => $v['member_mobile'],
				'add_time'         => strtotime($v['s_createtime']),
				'payment_code'     => mt_rand(0,100)>50?'alipay_app':'wxpay_app',
				'order_from'       => mt_rand(0,100)>50?'android':'ios',
				'order_state'      => ORDER_STATE_SUCCESS,
				'order_sn'         => $this->_logic_buy_1->makeOrderSn($afterInsertid),
				'out_pay_sn'       => mt_rand(11,99).mt_rand(1000,9999).mt_rand(1000,9999).mt_rand(1000,9999).mt_rand(1000,9999),
				'payment_time'     => $v['s_createtime'],
				'finnshed_time'    => $v['s_createtime'],
				'pkg_id'           => $packageInfo[$packRand]['pkg_id'],
				'pkg_name'         => $packageInfo[$packRand]['pkg_name'],
				'pkg_price'        => $packageInfo[$packRand]['pkg_price'],
				'pkg_cprice'       => $packageInfo[$packRand]['pkg_cprice'],
				'pkg_type'         => $packageInfo[$packRand]['pkg_type'],
				'pkg_length'       => $packageInfo[$packRand]['pkg_length'],
				'pkg_axis'         => $packageInfo[$packRand]['pkg_axis'],
				'pkg_desc'         => $packageInfo[$packRand]['pkg_desc'],
				's_id'             => $v['s_id'],
				's_name'           => $v['s_name'],
				'schoolid'         => $v['s_schoolid'],
				'name'             => $v['desc'],
				'classid'          => $v['s_classid'],
				'classname'        => $v['classname'],
				'order_amount'     => $packageInfo[$packRand]['pkg_price'],
				'over_amount'      => $packageInfo[$packRand]['pkg_price'],
				'provinceid'       => $v['s_provinceid'],
				'cityid'           => $v['s_cityid'],
				'areaid'           => $v['s_areaid'],
				'is_true'           => 2,
			]; 
			$order_dieline = CalculationTime($order,['end_time'=>$order['payment_time']]);
			$order['order_dieline'] = strtotime($order_dieline);
			$order['payment_time'] = strtotime($v['s_createtime']);
			$order['finnshed_time'] = strtotime($v['s_createtime']);

			$allOrder[]=$order;
		}
		$result = db('packagesorder')->insertAll($allOrder);
		if ($result) {
			return $result;
		}else{
			return false;
		}

		
	}

	public function randomDate($begintime, $endtime="", $now = true) {
        $begin = strtotime($begintime);  
        $end = strtotime($endtime);
        $timestamp = rand($begin, $end);
        // d($timestamp);
        return $now ? date("Y-m-d H:i:s", $timestamp) : $timestamp;          
    }
	public function recordSchool(){
		@set_time_limit(0);
		$school = db('aaschool')->field('id,address,name,region_city_id,region_province_id,region_town_id,hMAccount_hmAccount_id')->select();
		$oldschool = $this->beforSchool();
		$ids = array_column($oldschool,'name');
        $model_school = model('School');
        echo '学校录入开始----------------------------------------------------------<br>';
		foreach ($school as $k => $v) {
			if (in_array($v['name'],$ids))unset($school[$k]);
			if (strstr($v['name'],'某')) unset($school[$k]);
			if (strstr($v['name'],'无多'))unset($school[$k]);
			if (strstr($v['name'],'测'))unset($school[$k]);
			if (strstr($v['name'],'模拟'))unset($school[$k]);
			if (strstr($v['name'],'t')) unset($school[$k]);
			if (strstr($v['name'],'单'))unset($school[$k]);
			if (empty($v['name']))unset($school[$k]);
			if (is_numeric($v['name']))unset($school[$k]);
			$oldarea = db('aaregion')->where('id',$v['region_town_id'])->value('name');

			$area = db('area')->where('area_name','like',$oldarea)->field('area_id,area_mergename,area_parent_id')->find();
	    	$city = db('area')->where('area_id',$area['area_parent_id'])->field('area_id,area_parent_id')->find();
	    	$province = db('area')->where('area_id',$city['area_parent_id'])->field('area_shortname,area_id')->find();
            $uniqueCard = "";
	    	if($province['area_shortname']){
                for($i=0;$i<strlen($province['area_shortname']);$i=$i+3){
                    $uniqueCard .= $model_school->getFirstCharter(substr($province['area_shortname'],$i,3));
                }
            }
            $number = $model_school -> getNumber($uniqueCard);
            
            $schooData = array(
				'name'             => $v['name'],
				'provinceid'       => $city['area_parent_id'], // 省
				'cityid'           => $city['area_id'],	//市
				'areaid'           => $area['area_id'],	//县
				'region'           => $area['area_mergename'], // 详细省市县
				'typeid'           => 1,	//学校类型 1幼儿园 2小学 3初中 4高中  5培训学校
				'address'          => empty($v['address'])?$area['area_mergename'].'-'.$v['name']:$v['address'], //学校地址
				// 'common_phone'     => '0',//电话
				'username'         => db('aauser')->where('id',$v['user_user_id'])->value('name'), //负责/联系人 性别
				'desc'             => $v['name'], //备注
				'createtime'       => $this->RandTime, //添加时间/
				'updatetime'       => $this->RandTime, ///修改时间
				'isdel'            => 1, //是否删除 1未删除 2已删除
				'option_id'        => 1, //添加人id
				'admin_company_id' => 1, //公司id，代表此学校是何公司名下
				// 'res_group_id'     => 0, //res_group_id
				'schoolCard'       => $uniqueCard.$number,
				'oldid'            => $v['id'],
	    	);
	    	$this->True_School[] = $schooData;
		}

		// db('schoolcopy')->insertAll($this->True_School);
		// p($this->True_School);
		// p(count($school));
	    // p($school);

	    exit;
	}
/***************************************************************************************************************************************************************************************************/
	public function StartTrans(){
		@set_time_limit(0);

    	exit;
    	$this->SchoolTrans();

    	
	}

	/**
	 * 学校导入
	 * @创建时间 2018-11-03T22:48:56+0800
	 */
    public function SchoolTrans(){
        $starttime = explode(' ',microtime());

		echo '学校录入开始----------------------------------------------------------<br>';
    	//真实幼儿园数据
		$oldschool = $this->beforSchool();
	    $Sclist = [];
	    $ScError = [];
	    foreach ($oldschool as $key => $v) {
	    	$data = db('aaschool')->where($v)->find();
	    	if($data){
	    		$Sclist[] = $data;
	    		
	    	}else{
	    		$ScError[] = $v;
	    	}
	    }
	    p($Sclist);exit;
	    if ($ScError) {
	    	echo '未查询到此数据！';
	    	p($ScError);exit;
	    }
		exit;

	    /**
	     * 存储对应学校信息
	     * @var [type]
	     */
        $model_school = model('School');
	    $this->SchoolInsertInfo =[];
    	$this->SchoolInsert = [];
	    foreach ($Sclist as $key => $school) {
	    	$oldarea = db('aaregion')->where('id',$school['region_town_id'])->value('name');

	    	$area = db('area')->where('area_name','like',$oldarea)->field('area_id,area_mergename,area_parent_id')->find();
	    	$city = db('area')->where('area_id',$area['area_parent_id'])->field('area_id,area_parent_id')->find();
	    	$province = db('area')->where('area_id',$city['area_parent_id'])->field('area_shortname,area_id')->find();
            $uniqueCard = "";
	    	if($province['area_shortname']){
                for($i=0;$i<strlen($province['area_shortname']);$i=$i+3){
                    $uniqueCard .= $model_school->getFirstCharter(substr($province['area_shortname'],$i,3));
                }
            }
            $number = $model_school -> getNumber($uniqueCard);
	    	$schooData = array(
				'name'             => $school['name'],
				'provinceid'       => $city['area_parent_id'], // 省
				'cityid'           => $city['area_id'],	//市
				'areaid'           => $area['area_id'],	//县
				'region'           => $area['area_mergename'], // 详细省市县
				'typeid'           => 1,	//学校类型 1幼儿园 2小学 3初中 4高中  5培训学校
				'address'          => $school['address'], //学校地址
				'common_phone'     => '0',//电话
				'username'         => db('aauser')->where('id',$school['user_user_id'])->value('name'), //负责/联系人 性别
				'desc'             => $school['name'], //备注
				'createtime'       => $school['createTime'], //添加时间/
				'updatetime'       => date('Y-m-d H:i:s',time()), ///修改时间
				'isdel'            => 1, //是否删除 1未删除 2已删除
				'option_id'        => 1, //添加人id
				'admin_company_id' => 1, //公司id，代表此学校是何公司名下
				'res_group_id'     => 0, //res_group_id
				'schoolCard'	  => $uniqueCard.$number,
				'oldid'	  => $school['id'],
	    	);
	    	$this->True_School[] = $schooData;
	    	$result = $model_school->addSchool($schooData);
    		$this->SchoolInsertInfo[$k][$key]['schoolname']=$schooData['name'];
    		if ($result) {
    			echo "学校 【".$schooData['name']."】 录入成功<br>";
    			$this->SchoolInsertInfo[$k][$key]['insert']='TRUE';
    			$this->SchoolInsert[] = $result;
    		}else{
    			echo "学校 【".$schooData['name']."】 录入失败----------<br>";
    			$this->SchoolInsertInfo[$k][$key]['insert']='FALSE';
    			$this->SchoolInsert[] = 'FALSE';

    		}
			

	    }

	    $thistime = $endtime[0]+$endtime[1]-($starttime[0]+$starttime[1]);
		$thistime = round($thistime,3);
		echo "学校录入耗时：".$thistime." 秒。".time().'<br>';
		echo "总共成功录入".count($this->SchoolInsert).'条。<br>';
		if (in_array('FALSE', $this->SchoolInsert)) {
			$fails = array_count_values($this->SchoolInsert);
			echo '错误数量：'.$fails['FALSE'].'个<br>';
		}
		echo '学校录入结束----------------------------------------------------------<br>';
    	$this->ClassTrans();

	    //插入学校信息
	    // db('school')->insertAll($this->True_School);
	    
    }

    /**
     * 班级录入
     * @创建时间 2018-11-03T22:49:05+0800
     */
    public function ClassTrans(){
    	$starttime = explode(' ',microtime());
    	echo '班级录入开始-----------------------------------------------------------------<br>';
    	$True_School = db('school')->where('oldid','not null')->select();
    	$model_classes = model('Classes');
    	$this->True_Class=[];
    	$this->ClassInsertInfo =[];
    	$this->ClassInsert = [];
    	foreach ($True_School as $k => $s) {
    		$oldClass = db('aaclassroom')->where('school_school_id',$s['oldid'])->select();
    		
    		if($oldClass)foreach ($oldClass as $key => $c) {
    			$classcard=$s['schoolCard'].($model_classes->getNumber($s['schoolCard']));
    			$qr = $this->MakeQr($classcard);
	    		$data = array(
					'classname'         => $c['name'], // 班级名称
					'classCard'         => $classcard, //班级标识号
					'schoolid'          => $s['schoolid'], //学校id
					'school_provinceid' => $s['provinceid'], //省
					'school_cityid'     => $s['cityid'], //市
					'school_areaid'     => $s['areaid'], //县
					'school_region'     => $s['region'], //地址
					'typeid'            => $s['typeid'], // 默认全是幼儿园的
					'desc'              => $s['desc'], // 班级备注
					'qr'                => $qr, //二维码地址
					'isdel'             => 1, //1未删除
					'createtime'        => $c['createTime'], //创建时间
					'updatetime'        => date('Y-m-d H:i:s',time()), ///修改时间
					'option_id'         => 1, 
					'admin_company_id'  => 1,
					'res_group_id'      => 0,
					'oldid'             => $c['id'],  //老数据班级id
	    		);

	    		$result = $model_classes->addClasses($data);
	    		$this->ClassInsertInfo[$k][$key]['classname']=$c['name'];
	    		if ($result) {
	    			echo "班级 【".$data['classname']."】 录入成功<br>";
	    			$this->ClassInsertInfo[$k][$key]['insert']='TRUE';
	    			$this->ClassInsert [] = $result;
	    		}else{
	    			echo "班级 【".$data['classname']."】 录入失败----------<br>";
	    			$this->ClassInsertInfo[$k][$key]['insert']='FALSE';
	    			$this->ClassInsert [] = 'FALSE';

	    		}
    			$this->True_Class[$k][$key]=$data;
    		}
    		
    	}
    	$endtime = explode(' ',microtime());
		$thistime = $endtime[0]+$endtime[1]-($starttime[0]+$starttime[1]);
		$thistime = round($thistime,3);
		echo "执行写入耗时：".$thistime." 秒。".time().'<br>';
		echo "总共成功写入班级".count($this->ClassInsert).'条。<br>';
		if (in_array('FALSE', $this->ClassInsert)) {
			$fails = array_count_values($this->ClassInsert);
			echo '错误数量：'.$fails['FALSE'].'个<br>';
		}
    	echo '班级录入结束-----------------------------------------------------------------<br>';

		// p($this->ClassInsert);
    	// p($this->ClassInsertInfo);
    	// p($this->True_Class);
    }

    /**
     * 家长会员录入
     * @创建时间 2018-11-03T22:49:11+0800
     */
    public function MemberTrans(){
    	@set_time_limit(0);
    	$starttime = explode(' ',microtime());
    	echo '家长录入开始-----------------------------------------------------------------<br>';
    	//获取班级老数据
    	$field='id,name,nickname,loginName,memberType,idCard,password,phone,createTime,lastTime,lastLoginTime,address,school_school_id,classroom_classroom_id,region_province_id,region_city_id,region_town_id,sex';
    	$True_Class = db('class')->where('oldid','not null')->select();
    	$oldMember = db('aamember')->field($field)->where('name','notlike',['%测%','%test%'],'OR')->select();
    	$data = [];
    	$left_menu=array_column($True_Class, 'oldid');
    	
    	foreach ($oldMember as $key => $m) {
    		if(empty($m['phone'])) continue;
    		if(strlen($m['phone']) != 11) continue;
			$data[$key]['member_name']           = $m['name'];  //用户名称
			$data[$key]['member_nickname']       = !empty($m['nickname'])?$m['nickname']:(!empty($m['loginName'])?$m['loginName']:$m['name']); // 昵称
			$data[$key]['member_identity']       = $m['memberType']==0?2:1; // 身份
			$data[$key]['is_owner']              =  0; //是否主账号
			$data[$key]['member_age']            = 1; //年龄
			$data[$key]['member_truename']       = $m['name']; //真实姓名
			$data[$key]['member_idcard']         = $m['idCard']; //真实姓名
			$data[$key]['member_password']       = $m['password']; //密码 
			$data[$key]['member_mobile']         = $m['phone']; //手机号
			$data[$key]['member_mobile_bind']    = empty($m['phone'])?0:1; //是否绑定手机
			$data[$key]['member_add_time']       = empty($m['createTime'])?TIMESTAMP:strtotime($m['createTime']); //会员添加时间
			$data[$key]['member_edit_time']      = strtotime($m['lastTime']); //修改时间
			$data[$key]['member_old_login_time'] = strtotime($m['lastLoginTime']); //会员上次登录时间
			$data[$key]['oldid']                 = $m['id']; //老数据id
    		$k = array_search($m['classroom_classroom_id'], $left_menu);
    		if($k){//如果存在绑定学校
				$data[$key]['member_provinceid'] = $True_Class[$k]['school_provinceid']; //省
				$data[$key]['member_cityid']     = $True_Class[$k]['school_cityid']; //市
				$data[$key]['member_areaid']     = $True_Class[$k]['school_areaid']; //县
				$data[$key]['member_areainfo']   = !empty($m['address'])?$m['address']:$True_Class[$k]['school_region']; //地址
				$data[$key]['classid']           = $True_Class[$k]['classid']; //地址
    		}else{
    			$data[$key]['member_provinceid'] = ''; //省
				$data[$key]['member_cityid']     = ''; //市
				$data[$key]['member_areaid']     = ''; //县
				$data[$key]['member_areainfo']   = ''; //地址
				$data[$key]['classid']           = ''; //地址
    		}
    	}
    	$MemberInsertInfo = [];
    	$MemberInsert = [];
    	foreach ($data as $ke => $d) {
    		$result = db('member')->insertGetId($d);
    		if ($result) {
    			echo "会员用户 【".(empty($d['member_name'])?$d['member_nickname']:$d['member_name'])."】 录入成功<br>";
    			$MemberInsertInfo[$ke]['insert']='TRUE';
    			$MemberInsert [] = $result;
    		}else{
    			echo "会员用户 【".(empty($d['member_name'])?$d['member_nickname']:$d['member_name'])."】 录入失败----------<br>";
    			$MemberInsertInfo[$ke]['insert']='FALSE';
    			$MemberInsert [] = 'FALSE';
    		}
    	}
    	$endtime = explode(' ',microtime());
		$thistime = $endtime[0]+$endtime[1]-($starttime[0]+$starttime[1]);
		$thistime = round($thistime,3);
		echo "执行写入耗时：".$thistime." 秒。".time().'<br>';
		echo "总共成功写入班级".count($MemberInsert).'条。<br>';
		if (in_array('FALSE', $MemberInsert)) {
			$fails = array_count_values($MemberInsert);
			echo '错误数量：'.$fails['FALSE'].'个<br>';
		}
    	echo '家长录入结束-----------------------------------------------------------------<br>';
    }

    /**
     * 学生录入
     * @创建时间 2018-11-03T22:49:26+0800
     */
    public function StudentTrans(){
    	@set_time_limit(0);
    	$starttime = explode(' ',microtime());
    	echo '学生录入开始-----------------------------------------------------------------<br>';
    	$True_Class = db('class')->where('oldid','not null')->select();
    	
    	$TrueStudent=[];
    	$memmmm =[];
    	foreach ($True_Class as $key => $c) {
    		$where = array(
    			'classroom_classroom_id'=>$c['oldid'],
    			// 'loginName' =>array('notlike',['%test%','%ceshi%','%测试%']),
    			// 'name' =>array('notlike',['%test%','%测试%','%ceshi%']),
    		);
    		$field = 'id,name,gender,birthday,idCard,guardianPhone,address,createTime,note';
    		$oldStudent = db('aastudent')->field($field)->where($where)->select();

    		if($oldStudent)foreach ($oldStudent as $k => $s) {
    			$oldmemberid=db('studentbindstudentmember')->where('student_bindStudentMember_id',$s['id'])->value('member_id');
    			$member_id=db('member')->where('oldid',$oldmemberid)->value('member_id');
    			if(!$member_id)$member_id = db('member')->where('member_mobile',$s['guardianPhone'])->value('member_id');	
    			if($member_id)$memmmm[]=$member_id;
    			$TrueStudent[]= array(
					's_name'         => $s['name'], //学生名字
					's_sex'          => $s['gender']==1?2:1, //性别：1，男；2，女
					's_classid'      => $c['classid'], //班级id
					's_schoolid'     => $c['schoolid'], //学校id
					's_sctype'       => 1, //学校类型id
					's_birthday'     => $s['birthday'], //生日
					's_card'         => $s['idCard'], //学生身份证号
					's_provinceid'   => $c['school_provinceid'], //省id
					's_cityid'       => $c['school_cityid'], //市
					's_areaid'       => $c['school_areaid'], //县
					's_region'       => empty($s['address'])?$c['school_region']:$s['address'], //地址
					's_createtime'   => $s['createTime'], //创建时间
					's_remark'       => $s['note'], //备注
					's_ownerAccount' => !empty($member_id)?$member_id:'', //学生绑定的家长账号id （主账户）
					'classCard'      => $c['classCard'], //班级识别码（app绑定学生时添加）
					'oldid'          => $s['id'], //老id
    			);
    		}
    	} 

    	$StudentInsertInfo = [];
    	$StudentInsert = [];
    	foreach ($TrueStudent as $key => $value) {
    		$result=db('student')->insertGetId($value);
    		if ($result) {

    			echo "学生 ID【".$result."】---姓名【".$value['s_name']."】 录入成功<br>";
    			$StudentInsertInfo[$key]['insert']='TRUE';
    			$StudentInsert [] = $result;
    		}else{
    			echo "学生 【".$value['s_name']."】 录入失败----------<br>";
    			$StudentInsertInfo[$key]['insert']='FALSE';
    			$StudentInsert [] = 'FALSE';
    		}
    	}

    	$endtime = explode(' ',microtime());
		$thistime = $endtime[0]+$endtime[1]-($starttime[0]+$starttime[1]);
		$thistime = round($thistime,3);
		echo "执行写入耗时：".$thistime." 秒。".time().'<br>';
		echo "总共成功写入班级".count($StudentInsert).'条。<br>';
		if (in_array('FALSE', $StudentInsert)) {
			$fails = array_count_values($StudentInsert);
			echo '错误数量：'.$fails['FALSE'].'个<br>';
		}
    	echo '学生录入结束-----------------------------------------------------------------<br>';
    }



    public function StudentChecking(){
    	$Student = db('student')->field('s_id,s_name,oldid')->where('s_ownerAccount=1')->select();
    	$Student1 = db('student_copy')->field('s_id,s_name,oldid')->where('s_ownerAccount=1')->select();
    	$aaa = [];
    	foreach ($Student1 as $key => $s) {
    		$aaa[] = db('studentbindstudentmember')->where('student_bindStudentMember_id',$s['oldid'])->select();
    	}
    	p($Student);
    	p($aaa);
    }
    /**
     * 订单录入
     * @创建时间 2018-11-04T21:30:35+0800
     */
    public function OrderTrans(){
    	@set_time_limit(0);
    	$starttime = explode(' ',microtime());
    	echo '订单录入开始-----------------------------------------------------------------<br>';
    	$True_Member = db('aaorder')
    					->alias('a')
    					->join('__AAORDERITEM__ m','m.order_order_id = a.id','RIGHT')
    	                // ->field('a.*,m.member_id,m.member_name,m.oldid,m.classid')
    	                ->select();
    	
    	$TrueStudent=[];
    	
    	p($True_Member);
    	// $OrderInsertInfo = [];
    	// $OrderInsert = [];
    	// foreach ($TrueStudent as $key => $value) {
    	// 	$result=db('student')->insertGetId($value);
    	// 	if ($result) {
    	// 		echo "学生 ID【".$result."】---姓名【".$value['s_name']."】 录入成功<br>";
    	// 		$OrderInsertInfo[$key]['insert']='TRUE';
    	// 		$OrderInsert [] = $result;
    	// 	}else{
    	// 		echo "学生 【".$value['s_name']."】 录入失败----------<br>";
    	// 		$OrderInsertInfo[$key]['insert']='FALSE';
    	// 		$OrderInsert [] = 'FALSE';
    	// 	}
    	// }

    	$endtime = explode(' ',microtime());
		$thistime = $endtime[0]+$endtime[1]-($starttime[0]+$starttime[1]);
		$thistime = round($thistime,3);
		echo "执行写入耗时：".$thistime." 秒。".time().'<br>';
		// echo "总共成功写入班级".count($OrderInsert).'条。<br>';
		// if (in_array('FALSE', $OrderInsert)) {
		// 	$fails = array_count_values($OrderInsert);
		// 	echo '错误数量：'.$fails['FALSE'].'个<br>';
		// }
    	echo '订单录入结束-----------------------------------------------------------------<br>';
    }
    public function MakeQr($classcard){
    	//生成二维码
        import('qrcode.index',EXTEND_PATH);
        $PhpQRCode = new \PhpQRCode();
        $PhpQRCode->set('pngTempDir', BASE_UPLOAD_PATH . DS . ATTACH_STORE . DS . 'class' . DS);
        // 生成班级二维码
        $PhpQRCode->set('date', $classcard);
        $PhpQRCode->set('pngTempName', $classcard . '.png');
        $qr=$PhpQRCode->init();
        $qr='/home/store/class/'.$qr;
        return $qr;
    }

    public function beforSchool(){
    	$oldschool = array(
	    	array(
	    		'name'=>'太谷二中启航',
	    		'region_province_id'=> '000000005e41e11b015e45eed6340007'
	    	),
			array(
				'name'=>'嘉禾幼儿园',
				'region_province_id'=> '000000005e41e11b015e45eed6340007'
			),
			array(
				'name'=>'金宝国际幼儿园',
				'region_province_id'=> '000000005e41e11b015e45eed6340007'
			),
			array(
				'name'=>'恒大绿州幼儿园',
				'region_province_id'=> '000000005e41e11b015e45eed6340007'
			),
			array(
				'name'=>'财大新秀双语幼儿园',
				'region_province_id'=> '000000005e41e11b015e45eed6340007'
			),
			array(
				'name'=>'太原龙之源',
				'region_province_id'=> '000000005e41e11b015e45eed6340007'
			),
			array(
				'name'=>'常乐幼儿园',
				'region_province_id'=> '000000005e41e11b015e45eed6340007'
			),
			array(
				'name'=>'天天向上幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'红星凯悦圆梦幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'红星凯悦艺童幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'零工厂教育机构',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'智鑫幼稚园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'优咪早教亲子幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'博格恩幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'哈尔滨市道外区博艺幼稚园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'红旗小区馨视界幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'欣梦圆教育',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'亲亲宝贝幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'希望启智幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'优佳幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'育才幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'新浪潮艺术教育机构',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'光明村阳光宝贝幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'山水幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'金色童年幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'晨曦幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'朵朵乐幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'新希望幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1641daa0295'
			),
			array(
				'name'=>'龙凤幼儿园一园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb16917ba02a5'
			),
			array(
				'name'=>'龙凤幼儿园三园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb16917ba02a5'
			),
			array(
				'name'=>'龙凤幼儿园二园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb16917ba02a5'
			),
			array(
				'name'=>'何仙幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb16917ba02a5'
			),
			array(
				'name'=>'成长印记幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb16460fb0297'
			),
			array(
				'name'=>'庄河育蕾幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb16460fb0297'
			),
			array(
				'name'=>'新育龙幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb16460fb0297'
			),
			array(
				'name'=>'周口冠博学校',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1627abb028f'
			),
			array(
				'name'=>'周口市实验中学第一附属幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1627abb028f'
			),
			array(
				'name'=>'实验中学第二附属幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1627abb028f'
			),
			array(
				'name'=>'实验中学第三附属幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1627abb028f'
			),
			array(
				'name'=>'艾菲儿双语幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1627abb028f'
			),
			array(
				'name'=>'郸城县秋霞个协幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1627abb028f'
			),
			array(
				'name'=>'郸城北关个协幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb1627abb028f'
			),
			array(
				'name'=>'武南镇小精灵幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb165fe99029c'
			),
			array(
				'name'=>'博睿幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb165fe99029c'
			),
			array(
				'name'=>'凉州区阳光一代幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb165fe99029c'
			),
			array(
				'name'=>'金色摇篮幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb165fe99029c'
			),
			array(
				'name'=>'凉州幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb165fe99029c'
			),
			array(
				'name'=>'小灵童幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb165fe99029c'
			),
			array(
				'name'=>'荣华幼儿园',
				'region_province_id'=> 'bbb1af9e5eaca9ad015eb165fe99029c'
			),
			array(
				'name'=>'南湖体育馆幼儿园',
				'region_province_id'=> '8a9e3e295e36483c015e368560360008'
			),
			array(
				'name'=>'美育教育中心新加坡分园',
				'region_province_id'=> '8a9e3e295e36483c015e368560360008'
			),
			array(
				'name'=>'戴氏教育',
				'region_province_id'=> '8a9e3e295e36483c015e368560360008'
			),
			array(
				'name'=>'远达美蒙幼稚园',
				'region_province_id'=> '8a9e3e295e36483c015e368560360008'
			),
			array(
				'name'=>'钻井大队幼儿园',
				'region_province_id'=> '8a9e3e295e36483c015e368560360008'
			),
	    );
		return $oldschool;
    }


}

?>
