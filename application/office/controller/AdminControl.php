<?php

namespace app\office\controller;

use think\Controller;

class AdminControl extends Controller {

    /**
     * 管理员资料 name id group
     */
    protected $admin_info;

    protected $permission;
    public function _initialize() {
        $ControllerNmae = request()->controller();

        //个别不需要验证当前登陆身份的控制器
        $OverLimit = ['Mlselection','Common'];
        if (!empty($ControllerNmae) && in_array($ControllerNmae, $OverLimit)) {
            
        }else{
            $this->admin_info = $this->systemLogin();
            if ($this->admin_info['admin_id'] != 1) {
                // 验证权限
                $this->checkPermission();
    //            dump($this->permission);exit;
            }
        }
        $config_list = rkcache('config', true);
        config($config_list);
        
        $this->setMenuList();
        $_GET['page'] = isset($_GET['page'])?$_GET['page']:1;
    }

    /**
     * 取得当前管理员信息
     *
     * @param
     * @return 数组类型的返回结果
     */
    protected final function getAdminInfo() {
        return $this->admin_info;
    }

    /**
     * 系统后台登录验证
     *
     * @param
     * @return array 数组类型的返回结果
     */
    protected final function systemLogin() {
        $admin_info = array(
            'admin_id' => session('office_id'),
            'admin_name' => session('office_name'),
            'admin_gid' => session('office_gid'),
            'admin_is_super' => session('office_is_super'),
            'admin_company_id' => session('office_company_id'),
            'admin_school_id' => session('office_school_id')
        );
        if (empty($admin_info['admin_id']) || empty($admin_info['admin_name']) || !isset($admin_info['admin_gid']) || !isset($admin_info['admin_is_super'])) {
            session(null);
            $this->redirect('office/Login/index');
        }

        return $admin_info;
    }

    public function setMenuList() {
        $menu_list = $this->menuList();

        $menu_list=$this->parseMenu($menu_list);
        $this->assign('menu_list', $menu_list);
    }



    /**
     * 验证当前管理员权限是否可以进行操作
     *
     * @param string $link_nav
     * @return
     */
    protected final function checkPermission($link_nav = null){
        if ($this->admin_info['admin_is_super'] == 1) return true;

        $act = request()->controller();
        // halt($act);
        $op = request()->action();
//        halt($op);
//        halt($this->permission);
        if (empty($this->permission)){
            $gadmin = db('gadmin')->where(array('gid'=>$this->admin_info['admin_gid']))->find();
            $permission = decrypt($gadmin['limits'],MD5_KEY.md5($gadmin['gname']));
            $permission = explode('|',$permission);
            //一定有的权限
            $arr = array('Index','Dashboard');
            $this->permission =$permission =array_merge($arr,$permission);
        }else{
            $permission = $this->permission;
        }
//        halt($this->permission);
        //显示隐藏小导航，成功与否都直接返回
        if (is_array($link_nav)){
            if (!in_array("{$link_nav['act']}.{$link_nav['op']}",$permission) && !in_array($link_nav['act'],$permission)){
                return false;
            }else{
                return true;
            }
        }

        //以下几项不需要验证   langzhiyao修改不需要验证的控制器：Organizes  Schoolinfo 过滤
        $tmp = array('Index','Dashboard','Login','Organizes','Schoolinfo','Classesinfo','Studentinfo','Mlselection');
        if (in_array($act,$tmp)) return true;
        if (in_array($act,$permission) || in_array("$act.$op",$permission)){
            return true;
        }else{
            $extlimit = array('ajax','export_step1');
            if (in_array($op,$extlimit) && (in_array($act,$permission) || strpos(serialize($permission),'"'.$act.'.'))){
                return true;
            }
            //带前缀的都通过
            foreach ($permission as $v) {
                if (!empty($v) && strpos("$act.$op",$v.'_') !== false) {
                    return true;break;
                }
            }
        }
        $this->error(lang('ds_assign_right'));

    }

    /**
     * 过滤掉无权查看的菜单
     *
     * @param array $menu
     * @return array
     */
    private final function parseMenu($menu = array()) {
        if ($this->admin_info['admin_is_super'] == 1) {
            return $menu;
        }
//        halt(array_values($this->permission));
        foreach ($menu as $k => $v) {
            foreach ($v['children'] as $ck => $cv) {
                $tmp = explode(',', $cv['args']);
                //以下几项不需要验证
                $except = array('Index', 'Dashboard', 'Login','');
                if (in_array($tmp[1], $except))
                    continue;
                if (!in_array($tmp[1], array_values($this->permission))) {
                    unset($menu[$k]['children'][$ck]);
                }
            }
            if (empty($menu[$k]['children'])) {
                unset($menu[$k]);
                unset($menu[$k]['children']);
            }
        }
//        halt($menu);
        return $menu;
    }

    /**
     * 记录系统日志
     *
     * @param $lang 日志语言包
     * @param $state 1成功0失败null不出现成功失败提示
     * @param $admin_name
     * @param $admin_id
     */
    protected final function log($lang = '', $state = 1, $admin_name = '', $admin_id = 0) {
        if ($admin_name == '') {
            $admin_name = session('office_name');
            $admin_id = session('office_id');
        }
        $data = array();
        if (is_null($state)) {
            $state = null;
        } else {
            $state = $state ? '' : lang('ds_fail');
        }
        $data['content'] = $lang . $state;
        $data['admin_name'] = $admin_name;
        $data['createtime'] = TIMESTAMP;
        $data['admin_id'] = $admin_id;
        $data['ip'] = request()->ip();
        $data['url'] = request()->controller() . '&' . request()->action();
        $data['admin_company_id'] = db('admin')->where('admin_id',$admin_id)->value('admin_company_id');
        return db('adminlog')->insertGetId($data);
    }

    /**
     * 添加到任务队列
     *
     * @param array $goods_array
     * @param boolean $ifdel 是否删除以原记录
     */
    protected function addcron($data = array(), $ifdel = false) {
        $model_cron = Model('cron');
        if (isset($data[0])) { // 批量插入
            $where = array();
            foreach ($data as $k => $v) {
                if (isset($v['content'])) {
                    $data[$k]['content'] = serialize($v['content']);
                }
                // 删除原纪录条件
                if ($ifdel) {
                    $where[] = '(type = ' . $data['type'] . ' and exeid = ' . $data['exeid'] . ')';
                }
            }
            // 删除原纪录
            if ($ifdel) {
                $model_cron->delCron(implode(',', $where));
            }
            $model_cron->addCronAll($data);
        } else { // 单条插入
            if (isset($data['content'])) {
                $data['content'] = serialize($data['content']);
            }
            // 删除原纪录
            if ($ifdel) {
                $model_cron->delCron(array('type' => $data['type'], 'exeid' => $data['exeid']));
            }
            $model_cron->addCron($data);
        }
    }

    /**
     * 当前选中的栏目
     */
    protected function setAdminCurItem($curitem = '') {
        $this->assign('admin_item', $this->getAdminItemList());
        $this->assign('curitem', $curitem);
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        return array();
    }

    /*
     * 侧边栏列表
     */

    function menuList() {

        //获取父级所有栏目
        $menu = db('perms')->where(array('pid'=>0,'status'=>1))->select();
        if(!empty($menu)){
            foreach($menu as $key=>$val){
                $menu[$key]['text'] = lang($val['text']);
                //获取子目录
                $menu[$key]['children'] = db('perms')->where(array('pid'=>$val['permid'],'status'=>1))->select();
                if(!empty($menu[$key]['children'])){
                    foreach($menu[$key]['children'] as $k=>$v){
                        $menu[$key]['children'][$k]['text'] = lang($v['text']);
                    }
                }
                $menu[$key]['children'] = array_column($menu[$key]['children'],NULL,'name');
            }
        }
        $menu = array_column($menu,NULL,'name');

         return $menu;

    }

    /*
     * 权限选择列表
     */
    function limitList() {
        if ($this->admin_info['admin_is_super'] == 1) {
            //获取父级所有栏目
            $_limit = db('perms')->field('permid,name,text')->where("pid=0  AND permid != 1 AND status=1")->select();
            if(!empty($_limit)){
                foreach($_limit as $key=>$val){
                    $_limit[$key]['text'] = lang($val['text']);
                    //获取子目录
                    $_limit[$key]['child'] = db('perms')->field('permid,text,name,action')->where("pid='".$val['permid']."'  AND status=1")->select();
//                halt($_limit);
                    if(!empty($_limit[$key]['child'])){
                        foreach($_limit[$key]['child'] as $k=>$v){
                            $_limit[$key]['child'][$k]['text'] = lang($v['text']);
                            $_limit[$key]['child'][$k]['op'] = null;
                            $_limit[$key]['child'][$k]['act'] = ucfirst($v['name']);
                            $_limit[$key]['child'][$k]['action'] = explode(',',$_limit[$key]['child'][$k]['action']);
                            if(!empty($_limit[$key]['child'][$k]['action'])){
                                $array = array();
                                if(!empty($v['action'])){
                                    $actions= db('actions')->where("actid in ($v[action])")->select();
                                    if(!empty($actions)){
                                        foreach ($actions as $kk=>$vv){
                                            $array['id']=$vv['actid'];
                                            $array['actname']=$this->get_action($vv['actname']);
                                            $_limit[$key]['child'][$k]['action'][$kk] = $array;
                                        }
                                    }
                                }

                            }
                        }
                    }
                }
            }
        }else{
            //根据角色id获取栏目权限
            $ginfo = db('gadmin')->where('gid', $this->admin_info['admin_gid'])->find();
            //解析已有权限
            $hlimit = decrypt($ginfo['limits'], MD5_KEY . md5($ginfo['gname']));
            $nlimit = decrypt($ginfo['nav'], MD5_KEY . md5($ginfo['gname']));
            $ginfo['limits'] = explode('|', $hlimit);
            $ginfo['nav'] = explode('|', $nlimit);
            $nav = "'" . join("','", $ginfo['nav']) . "'"; //大类
            $limits = "'" . join("','", $ginfo['limits']) . "'";//中类

//            halt($limits);
            //获取父级所有栏目
            $_limit = db('perms')->field('permid,name,text')->where("pid=0 AND name IN ($nav) AND permid != 1  AND status=1")->select();
//            halt($_limit);
            if(!empty($_limit)){
                foreach($_limit as $key=>$val){
                    $_limit[$key]['text'] = lang($val['text']);
                    //获取子目录
                    $_limit[$key]['child'] = db('perms')->field('permid,text,name,action')->where("pid='".$val['permid']."' AND name IN ($limits)  AND status=1")->select();
//                halt($_limit);
                    if(!empty($_limit[$key]['child'])){
                        foreach($_limit[$key]['child'] as $k=>$v){
                            $_limit[$key]['child'][$k]['text'] = lang($v['text']);
                            $_limit[$key]['child'][$k]['op'] = null;
                            $_limit[$key]['child'][$k]['act'] = ucfirst($v['name']);
                            $_limit[$key]['child'][$k]['action'] = explode(',',$_limit[$key]['child'][$k]['action']);

                            //该角色拥有的小类
                            $operation = db('roleperms')->where('roleid="'.$this->admin_info['admin_gid'].'" AND permsid="'.$v["permid"].'"')->find();
                            if(!empty($operation)){
                                $array = array();
                                $actions= db('actions')->where("actid in ($operation[action])")->select();
                                foreach ($actions as $kk=>$vv){
                                    $array['id']=$vv['actid'];
                                    $array['actname']=$this->get_action($vv['actname']);
                                    $_limit[$key]['child'][$k]['action'][$kk] = $array;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $_limit;
    }


    /**
     * 获取后台操作权限
     * @param action $in
     */
    function get_action($in){
        //$s=array();
        switch ($in) {
            case 'Insert'://1
                return '增加';
                break;
            case 'Delete'://2
                return '删除';
                break;
            case 'Update'://3
                return '修改';
                break;
            case 'Select'://4
                return '浏览';
                break;
            case 'DownMember'://5
                return '所属下级';
                break;
            case 'AssignAccount'://6
                return '分配账号';
                break;
            case 'Export'://7
                return '导出';
                break;
            case 'import'://8
                return '导入';
                break;
            case 'DownLoad'://9
                return '下载';
                break;
            case 'AddClass'://10
                return '添加班级';
                break;
            case 'AddStudent'://11
                return '添加学生';
                break;
            case 'ResetPwd'://12
                return '重置密码';
                break;
            case 'Disable'://13
                return '禁用';
                break;
            case 'AddGrade'://14
                return '年级管理';
                break;
            case 'Check'://15
                return '审核';
                break;
            case 'Undercarriage'://16
                return '违规下架';
                break;
            case 'CashWithdrawal'://17
                return '提现';
                break;

        }
    }


    /**
     * @desc 根据当前用户角色和子目录id 获取该角色对此子目录的权限
     * @author langzhiyao
     * @time 2018/09/18
     */
    function get_role_perms($roleid,$perm_id){
            $result = db('roleperms')->field('action')->where(['roleid'=>$roleid,'permsid'=>$perm_id])->find();
            $action = '';
            if(!empty($result['action'])){
                $action = explode(',',$result['action']);
            }
            return $action;
    }
    /**
     * @desc 根据当前用户角色和子目录id 获取该角色对此子目录的权限
     * @author langzhiyao
     * @time 2018/09/18
     */
    function get_permid($class_name){
        $result = db('perms')->field('permid')->where(" name='$class_name' AND pid !=0 AND status=1")->find();
        $permsid = '';
        if(!empty($result['permid'])){
            $permsid = $result['permid'];
        }
        return $permsid;
    }




}

?>
