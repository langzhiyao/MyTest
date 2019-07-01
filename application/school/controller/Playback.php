<?php

namespace app\school\controller;

use think\Lang;
use think\Validate;
/**
 * 套餐展示
 */
class Playback extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'school/lang/zh-cn/pkgs.lang.php');
    }

    public function playback_manage(){
        $PlayBack = model('Playback');
        $condition = array(); 
        $condition['type'] =intval(input('param.ptype'))?intval(input('param.ptype')):1;
        if ( $condition['type'] == 1) {
            $this->setAdminCurItem('playback');
        }else{
            $this->setAdminCurItem('playback_manage');
        }   
        $id = intval(input('pid'));        
        $backList = $PlayBack->get_playback_List($condition, '6' ,'up_time desc');
         
        foreach ($backList as $key => &$n) {
            $week=explode(',',$n['week']);
            $n['week'] = '';
            foreach(config('week') as $k=>$v){
                if(in_array($k,$week)){
                    $n['week'] .=$v.'&nbsp;';
                }
            }
        }
        
        $this->assign('backList', $backList);
        $this->assign('type', $condition['type']);
        if($id){
            $editInfo = $PlayBack->getOneById($id);

            $editInfo['start_time'] = date('H:i:s',$editInfo['start_time']);
            $editInfo['end_time'] = date('H:i:s',$editInfo['end_time']);
            $this->assign('editInfo', $editInfo);
        }
        // p($backList);exit;
        $this->assign('page', $PlayBack->page_info->render());
        
        
        return $this->fetch('playback_manage');
    }

    public function playback_edit(){        
        if (request()->isPost()) {
            $PlayBack = Model('Playback');
            $pid = intval(input('post.pid'));
            $param =array(); 
            $param['start_time'] = strtotime(trim(input('post.start_time')));
            $param['end_time']   = strtotime(trim(input('post.end_time')));
            $param['cut_time']   = intval(input('post.cut_time'));
            $param['type']       = intval(input('post.type'));
            $param['replay']     = intval(input('post.replay'));            
            $param['camera']     = intval(input('post.camera'));            
            $param['pl_enabled'] = 1;            
            $param['up_time']    = time();
            if ($param['replay']==1) {
                $param['week'] = '0,1,2,3,4,5,6';
            }else{
                $week = input('post.week/a');
                $str= '';
                foreach ($week as $k => $v)$str.= ','.$v;
                $param['week'] = trim($str,',');
            }
            $param['belong']     = $this->admin_info['admin_id']; 
            if ($param['type']==1) {
                $log = '回放时间设置';
            }else{
                $log = '重温课堂设置';
            }
            if ($pid) {
                $param['pid'] = $pid;
                $result = $PlayBack->playback_update($param);
                if ($result) {
                    $PlayBack->makeDefaut($pid,$param['type']);
                    $this->log($log . '[' . $pid . ']', null);
                    $this->success($log.'成功', url('Admin/Playback/playback_manage',['ptype'=>$param['type']]));
                }
            }else{
                $result = $PlayBack->playback_add($param);
                if ($result) {
                    $PlayBack->makeDefaut($result,$param['type']);
                    $this->log($log . '[' . $result . ']', null);
                    $this->success($log.'成功', url('Admin/Playback/playback_manage',['ptype'=>$param['type']]));
                }
            }
        }
        exit;
    }

    /**
     *
     * 删除套餐
     */
    public function playback_del() {
        $PlayBack = Model('Playback');
        /**
         * 删除套餐
         */
        $pid = intval(input('param.pid'));
        
        $result = $PlayBack->playback_del($pid);
        

        if (!$result) {
            $this->error('删除失败');
        } else {
            if (intval(input('param.ptype'))==1) {
                $log = '回放时间删除';
            }else{
                $log = '重温课堂删除';
            }
            $this->log($log . '[' . $pid . ']', null);
            $this->success(lang($log.'成功'));
        }
    }



    /**
     *
     * ajaxOp
     */
    public function makedefault() {
        $pid = intval(input('post.pid'));
        $PlayBack = Model('Playback');
        $type= input('post.ptype');
        $param['pid'] = $pid;
        $param['up_time'] = time();
        $param['pl_enabled'] = 1;
        $PlayBack->playback_update($param);
        $PlayBack->makeDefaut($pid,$type);
        
        exit(json_encode(array('p'=>$pid)));
        // $this->success($log.'成功', url('Admin/Playback/playback_manage',['ptype'=>$param['type']]));
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'playback',
                'text' => lang('playback'),
                'url' => url('Admin/Playback/playback_manage',['ptype'=>1])
            ),
            array(
                'name' => 'playback_manage',
                'text' => lang('playback_manage'),
                'url' => url('Admin/Playback/playback_manage',['ptype'=>2])
            )
        );
        
        return $menu_array;
    }

}

?>
