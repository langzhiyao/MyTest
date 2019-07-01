<?php
namespace app\wap\controller;
use cloud\RongCloud;

class Chat extends MobileMember
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * 生成第三方token  融云
     * @return [type] [description]
     */
    public function GetRongCloudToken(){

        // $RongCloud = new \cloud\RongCloud();
        $RongCloud = new RongCloud();
        if(!empty($this->member_info['member_avatar'])){
            $this->member_info['member_avatar'] = UPLOAD_SITE_URL.$this->member_info['member_avatar'];
        }else{
            $this->member_info['member_avatar'] = UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . 'default_user_portrait.png';
        }
        if (empty($this->member_info['member_nickname'])) $this->member_info['member_nickname'] = $this->member_info['member_name'];
        // 获取 Token 方法
        $result = $RongCloud->user()->getToken($this->member_info['member_id'], $this->member_info['member_nickname'], $this->member_info['member_avatar']);
        $result = json_decode($result,TRUE);
        if ($result['code']==200) {
            output_data(array(
                'CloudUserId' => $result['userId'],
                'CloudToken'  => $result['token']
            ));
        }else{
            output_error($result['errorMessage']);
        }
    }

    /**
     * 刷新Token
     */
    public function RefreshRongCloudToken(){
        $RongCloud = new RongCloud();
        if(!empty($this->member_info['member_avatar'])){
            $this->member_info['member_avatar'] = UPLOAD_SITE_URL.$this->member_info['member_avatar'];
        }else{
            $this->member_info['member_avatar'] = UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . 'default_user_portrait.png';
        }
        if (empty($this->member_info['member_nickname'])) $this->member_info['member_nickname'] = $this->member_info['member_name'];
        $result = $RongCloud->user()->refresh($this->member_info['member_id'], $this->member_info['member_nickname'], $this->member_info['member_avatar']);
        $result = json_decode($result,TRUE);
        if ($result['code']==200) {
            output_data(array('state'=>TRUE));
        }else{
            output_error('刷新失败！');
        }

    }


    /**
     * 检查用户在线状态
     */
    public function CheckOnline(){        
        $userId = input('post.user_id');
        $RongCloud = new RongCloud();
        $result = $RongCloud->user()->checkOnline($userId);
        $result = json_decode($result,TRUE);
        output_data($result);
    }

    /**
     * 添加用户到黑名单方法（每秒钟限 100 次） 
     */
    public function AddBlacklist(){
        $userId = input('post.user_id');
        $RongCloud = new RongCloud();
        $result = $RongCloud->user()->addBlacklist($this->member_info['member_id'],$userId);
        $result = json_decode($result,TRUE);
        output_data($result);
    }

    /**
     * 获取某用户的黑名单列表方法（每秒钟限 100 次）
     */
    public function QueryBlacklist(){
        $userId = input('post.user_id');
        if (!$userId) $userId = $this->member_info['member_id'];
        $RongCloud = new RongCloud();
        $result = $RongCloud->user()->queryBlacklist($userId);
        $result = json_decode($result,TRUE);
        output_data($result);

    }

    /**
     * 从黑名单中移除用户方法（每秒钟限 100 次） 
     */
    public function RemoveBlacklist(){
        $userId = input('post.user_id');
        $RongCloud = new RongCloud();
        $result = $RongCloud->user()->removeBlacklist($this->member_info['member_id'],$userId);
        $result = json_decode($result,TRUE);
        output_data($result);

    }
    
    /**
     * 查询账号 查询人物信息
     * @return [type] [description]
     */
    public function FriendSerch(){
        $friend_mobile = input('post.mobile');
        $Member = model('Member');
        if(!$friend_mobile)output_error('账号不能为空！');
        $friendInfo = $Member->getMemberInfo(array('member_mobile'=>$friend_mobile));
        if (!$friendInfo) output_error('没有此账号！');
        $Area = model('Area');
        $areas=!empty($friendInfo['member_areaid'])?$Area->areaName($friendInfo['member_areaid']):$Area->areaName($friendInfo['member_provinceid']);

        $Friendly = model('Friendly');
        //查询双方关系
        $myexits = array(
            'member_id' => $friendInfo['member_id']  ,
            'friend_id' =>$this->member_info['member_id'] ,
        );
        $myexits = $Friendly->getOne($myexits);
        $state = 4;
        if($myexits)switch ($myexits['relation_state']) {
            case '1'://已发送过好友申请
                $state = 1;
                break;
            case '2'://双方已经是好友关系
                $state = 2;
                break;
            case '3'://对方已忽略过我发送的好友申请
                $state = 3;
                break;
        }
        if(!empty($friendInfo['member_avatar'])){
            $friendInfo['member_avatar'] = UPLOAD_SITE_URL.$friendInfo['member_avatar'];
        }else{
            $friendInfo['member_avatar'] = UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . 'default_user_portrait.png';
        }
        $output = array(
            'member_id'     => $friendInfo['member_id'],
            'member_name'   => $friendInfo['member_name'],
            'member_mobile' => $friendInfo['member_mobile'],
            'friend_remark' => empty($myexits['friend_remark'])?'':$myexits['friend_remark'],
            'apply_remark'  => empty($frexits['apply_remark'])?'':$frexits['apply_remark'],
            'avatar'        => $friendInfo['member_avatar'],
            'area'          => empty($areas)?'':$areas,
            'state'         => $state,
        );
        output_data($output);
    
    }

    /**
     * 查询账号 查询人物信息
     * @return [type] [description]
     */
    public function FriendSerchByUid(){
        $friend_member_id = input('post.member_id');
        $Member = model('Member');
        if(!$friend_member_id)output_error('账号不能为空！');
        $friendInfo = $Member->getMemberInfo(array('member_id'=>$friend_member_id));
        if (!$friendInfo) output_error('没有此账号！');
        $Area = model('Area');
        $areas=!empty($friendInfo['member_areaid'])?$Area->areaName($friendInfo['member_areaid']):$Area->areaName($friendInfo['member_provinceid']);

        $Friendly = model('Friendly');
        //查询双方关系
        $myexits = array(
            'member_id' => $this->member_info['member_id'] ,
            'friend_id' => $friendInfo['member_id'] ,
        );
        $myexits = $Friendly->getOne($myexits);
        //查询的朋友是否给我发送过好友申请
        $frexits = array(
            'member_id' => $friend_member_id,
            'friend_id' => $this->member_info['member_id'] ,
        );
        $frexits = $Friendly->getOne($frexits);
        $state = 4;
        if($frexits)switch ($frexits['relation_state']) {
            case '1'://已发送过好友申请
                $state = 1;
                break;
            case '2'://双方已经是好友关系
                $state = 2;
                break;
            case '3'://对方已忽略过我发送的好友申请
                $state = 3;
                break;
        }
        if(!empty($friendInfo['member_avatar'])){
            $friendInfo['member_avatar'] = UPLOAD_SITE_URL.$friendInfo['member_avatar'];
        }else{
            $friendInfo['member_avatar'] = UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . 'default_user_portrait.png';
        }
        $output = array(
            'member_id'     => $friendInfo['member_id'],
            'member_name'   => $friendInfo['member_name'],
            'member_mobile' => $friendInfo['member_mobile'],
            'friend_remark' => empty($myexits['friend_remark'])?'':$myexits['friend_remark'],
            'apply_remark'  => empty($frexits['apply_remark'])?'':$frexits['apply_remark'],
            'avatar'        => $friendInfo['member_avatar'],
            'area'          => empty($areas)?'':$areas,
            'state'         => $state,
        );
        output_data($output);

    }

    /**
     * 添加好友 --申请   发送人消息 ，可直接设置备注
     * @return [type] [description]
     */
    public function SendFriendlyMessage(){ 
        $friend_mobile = input('post.mobile');
        $apply_remark = input('post.apply_remark');
        $friend_remark = input('post.friend_remark');
        $Member = model('Member');
        if(!$friend_mobile)output_error('账号不能为空！');
        $friendInfo = $Member->getMemberInfo(array('member_mobile'=>$friend_mobile));
        if (!$friendInfo) output_error('没有此账号！');
        $Friendly = model('Friendly');
        //我是否发送过好友申请
        $myexits = array(
            'member_id' => $this->member_info['member_id'] ,
            'friend_id' => $friendInfo['member_id'] ,
        );
        $myexits = $Friendly->getOne($myexits);

        if ($myexits) {
            if($myexits['relation_state'] == 1 || $myexits['relation_state'] == 3){
                $creat_time = $Friendly->friendly_set($myexits['id'],'creat_time',time());
                //如果已经发过申请，并且未成为好友，将会重新发送
                $Friendly->friendly_set($myexits['id'],'relation_state',1);
                if(!$creat_time)output_error('已发送过好友申请!');
                output_data(array('state'=>TRUE,'msg'=>'已发送好友申请!'));
            }
            if($myexits['relation_state'] == 2)output_error('对方已是好友关系!');
            // if($myexits['relation_state'] == 3)output_error('对方已忽略申请!');
        }
        //查询的朋友是否给我发送过好友申请
        $frexits = array(
            'member_id' => $friendInfo['member_id'] ,
            'friend_id' => $this->member_info['member_id'] ,
        );
        $frexits = $Friendly->getOne($frexits);
        $state = 1;
        if($frexits){
            if($myexits['relation_state'] == 2 && $frexits['relation_state'] == 2 )output_error('双方已是好友关系!');
            if($myexits['relation_state'] == 1){
                $state = 2;
            }
        }
        $apply = array(
            'member_id'      => $this->member_info['member_id'] ,
            'friend_id'      => $friendInfo['member_id'] ,
            'relation_state' => $state,
            'apply_remark'   => !empty($apply_remark)?$apply_remark:'用户'.$this->member_info['member_name'].'申请成为你的好友',
            'friend_remark'  => !empty($friend_remark)?$friend_remark:$this->member_info['member_name'],
            'creat_time'     => time(),
            'from_type'      => $this->member_info['member_id'] 
        );     
        //如果查询的朋友已发送过申请，并且我也没有忽略  将直接成为好友 
        if($state==2)$Friendly->friendly_set($frexits['id'],'relation_state',$state);
        $applyResult = $Friendly->friendly_add($apply);

        if(!$applyResult)output_error('申请失败，请检查网络！');

        output_data(array('state'=>TRUE,'msg'=>'已发送好友申请!'));
    }

    /**
     * 新朋友列表
     */
    public function NewFriendsList(){
        $member_id = $this->member_info['member_id'];
        $Friendly = model('Friendly');
        $condition=array(
            'friend_id' => $member_id,
        );
        $friendlist =$Friendly->get_friendly_Lists($condition);
        $ids = array();
        foreach ($friendlist as $key => $value) {
            $ids[] =$value['member_id'];
        }
        $Member = model('Member');
        $where =array(
            'member_id' =>array('in',$ids)
        );
        $field ='member_id,member_name,member_mobile,member_avatar';
        $memberList = $Member->getMemberList($where,$field);
        $output =$this->mergeMember($friendlist,$memberList);
        output_data($output);
    }

    public function mergeMember($friendlist,$memberList){
        $output =array();
        $left_menu=array_column($memberList, 'member_id');
        foreach ($friendlist as $k => $v) {            
            $key = array_search($v['member_id'], $left_menu);            
            if($v['member_id'] == $memberList[$key]['member_id'])$output[$k]=array(
                'member_id'     => $memberList[$key]['member_id'],
                'member_name'   => $memberList[$key]['member_name'],
                'member_mobile' => $memberList[$key]['member_mobile'],
                'avatar'        => AvatarFormat($memberList[$key]['member_avatar']),
                'state'         => $v['relation_state'],
                'creat_time'    => $v['creat_time'],
                'friend_remark' => $v['friend_remark'],
                'apply_remark'  => $v['apply_remark'],
            );
        }
        return $output;

    }

    /**
     * 同意好友申请
     */
    public function AgreeMakeFriendly(){
        $friend_member_id = input('post.member_id');
        $Member = model('Member');
        if(!$friend_member_id)output_error('好友账号不能为空！');
        $Friendly = model('Friendly');

        //查询的朋友是否给我发送过好友申请
        $frexits = array(
            'member_id' => $friend_member_id ,
            'friend_id' => $this->member_info['member_id'] ,
        );
        $frexits = $Friendly->getOne($frexits);
        $myexits = array(
            'member_id' => $this->member_info['member_id'] ,
            'friend_id' => $friend_member_id ,
        );
        $myexits = $Friendly->getOne($myexits);
        if(!$frexits)output_error('未收到该账号的好友申请！');
        if($frexits['relation_state']==3)output_error('你已经忽略过此好友申请！');
        $oreday = false;
        if($myexits){
            if($myexits['relation_state'] ==2 && $frexits['relation_state'] == 2)output_error('你们已经是好友关系啦！');
            $oreday = true;
        }
        $friendInfo = $Member->getMemberInfo(array('member_id'=>$friend_member_id));
        $time = time();
        try {
            $Friendly->startTrans();
            //双方成为好友
            if ($oreday) { // 已存在申请，直接成为好友
                $Friendly->friendly_set($myexits['id'],'relation_state',2);
                $Friendly->friendly_set($myexits['id'],'up_time',$time);
            }else{
                $insert =array(
                    'member_id' => $this->member_info['member_id'] ,
                    'friend_id' => $friend_member_id,
                    'relation_state' => 2,
                    'apply_remark' => $frexits['apply_remark'],
                    'friend_remark' =>$friendInfo['member_name'] ,
                    'creat_time' => $time,
                    'from_type' => $friend_member_id,
                    'up_time' => $time,
                );
                $Friendly->friendly_add($insert);
            }
            $Friendly->friendly_set($frexits['id'],'relation_state',2);
            $Friendly->friendly_set($frexits['id'],'up_time',$time);
            $Friendly->commit();
            $msg = 'true';
        } catch (Exception $e) {
            $Friendly->rollback();
            $msg =$e->getMessage();
        }
        if($msg != 'true')output_error($msg);
        output_data(array('state'=>$msg));

    }

    /**
     * 忽略好友申请
     */
    public function NeglectFriendlyApply(){
        $friend_member_id = input('post.member_id');
        $Member = model('Member');
        if(!$friend_member_id)output_error('好友账号不能为空！');
        $Friendly = model('Friendly');

        //查询的朋友是否给我发送过好友申请
        $frexits = array(
            'member_id' => $friend_member_id ,
            'friend_id' => $this->member_info['member_id'] ,
        );
        $frexits = $Friendly->getOne($frexits);
        if(!$frexits)output_error('未收到该账号的好友申请！');
        if($frexits['relation_state']==3)output_error('你已经忽略过此好友申请！');
        if($frexits['relation_state']==2)output_error('你们已经是好友啦，如需忽略，请删除好友！');

        $param =array(
            'id'             =>$frexits['id'],
            'relation_state' =>3,
            'up_time'        =>time()
        );
        $result = $Friendly->friendly_update($param);
        if(!$result)output_error('请求失败，请检查网络设置！');
        output_data(array('state'=>'true'));
    }

    /**
     * 修改好友备注
     */
    public function ModifyFriendNameRemarks(){
        $friend_member_id = input('post.member_id');
        $friend_remark = input('post.friend_remark');
        $Member = model('Member');
        if(!$friend_member_id)output_error('好友账号不能为空！');
        if(!$friend_remark)output_error('备注名称不能为空！');
        $Friendly = model('Friendly');

        //查询的朋友是否给我发送过好友申请
        $frexits = array(
            'member_id' => $this->member_info['member_id'] ,
            'friend_id' => $friend_member_id,
        );
        $frexits = $Friendly->getOne($frexits);
        if(!$frexits)output_error('你们还不是好友关系！');
        if($frexits['relation_state']==1)output_error('你还未同意成为好友关系！');
        if($frexits['friend_remark']==$friend_remark)output_data(array('state'=>'true'));
        $result = $Friendly->friendly_set($frexits['id'],'friend_remark',$friend_remark);
        if(!$result)output_error('修改备注请求失败，请检查网络设置！');
        output_data(array('state'=>'true'));
    }

    /**
     * 删除好友关系
     */
    public function DeleteFriendlyRelationship(){
        $friend_member_id = input('post.member_id');
        $Member = model('Member');
        if(!$friend_member_id)output_error('好友账号不能为空！');
        $Friendly = model('Friendly');

        //查询是否是朋友关系
        $frexits = array(
            'member_id' => $friend_member_id ,
            'friend_id' => $this->member_info['member_id'] ,
            'relation_state'=>2,
        );
        $frexits = $Friendly->getOne($frexits);
        $myexits = array(
            'member_id' => $this->member_info['member_id'] ,
            'friend_id' => $friend_member_id ,
            'relation_state'=>2,
        );
        $myexits = $Friendly->getOne($myexits);
        //是否是好友关系
        if(!$frexits || !$myexits)output_error('还未创建好友关系！');

        $del=array(
            'id' =>array('in',[$frexits['id'],$myexits['id']]),
        );
        $result = $Friendly->friendly_delAll($del);
        if(!$result)output_error('删除好友请求失败，请检查网络设置！');
        output_data(array('state'=>'true'));
    }

    /**
     * 查询未处理的好友申请数量
     */
    public function UntreatedMessageCount(){
        $member_id = $this->member_info['member_id'];
        $Friendly = model('Friendly');
        $condition=array(
            'friend_id' => $member_id,
            'relation_state' => 1,
        );
        $friendlist =$Friendly->getFriendlyCount($condition);

        output_data(array('count'=>$friendlist));
    }

    /**
     * 获取所有好友列表
     */
    public function GetALLFriendList(){
        $member_id = $this->member_info['member_id'];
        $Friendly = model('Friendly');
        $condition=array(
            'member_id' => $member_id,
            'relation_state' => 2,
        );
        $friendlist =$Friendly->get_friendly_Lists($condition);
        if(!$friendlist)output_data(array());
        $ids = array();
        foreach ($friendlist as $key => $value) {
            $ids[] =$value['friend_id'];
        }
        $Member = model('Member');
        $where =array(
            'member_id' =>array('in',$ids)
        );
        $field ='member_id,member_name,member_mobile,member_avatar';
        $memberList = $Member->getMemberList($where,$field);

        $output =array();
        $left_menu=array_column($memberList, 'member_id');
        foreach ($friendlist as $k => $v) {            
            $key = array_search($v['friend_id'], $left_menu); 
            if($v['friend_id'] == $memberList[$key]['member_id'])$output[$k]=array(
                'member_id'     => $memberList[$key]['member_id'],
                'member_name'   => $memberList[$key]['member_name'],
                'member_mobile' => $memberList[$key]['member_mobile'],
                'avatar'        => AvatarFormat($memberList[$key]['member_avatar']),
                'creat_time'    => $v['creat_time'],
                'friend_remark' => $v['friend_remark'],
            );
        }
        output_data($output);
    }

    //群聊 ***********************************************************************************************
    


    /**
     * 创建群组方法（创建群组，并将用户加入该群组，用户将可以收到该群的消息，同一用户最多可加入 500 个群，每个群最大至 3000 人，App 内的群组数量没有限制.）
     * 对于同一个聊天室，只存储该聊天室的 50 条最新消息，也就是说移动端用户进入聊天室时，最多能够拉取到最新的 50 条消息。
     * @param  userId:要加入群的用户 Id。（必传）
     * @param  groupId:创建群组 Id。（必传）
     * @param  groupName:群组 Id 对应的名称。（必传）
     */
    public function MakeGroupChat(){
        $input = input();
        $group_owner_id  = isset($input['owner_id'])?(!empty($input['owner_id'])?$input['owner_id']:$this->member_info['member_id']):$this->member_info['member_id'];
        $groupName = isset($input['groupName'])?$input['groupName']:$this->member_info['member_name'].'建立的群聊';
        if(empty($groupName))$groupName = $this->member_info['member_name'].'创建的群聊';
        //获取群员id
        $members = $input['members'];
        // $members = explode(',', $members);
        array_push($members, $this->member_info['member_id']);
        $Member = model('Member');
        $where =array(
            'member_id' =>array('in',$members)
        );
        $field ='member_id,member_name,member_mobile,member_avatar';
        $memberList = $Member->getMemberList($where,$field);

        $memberCount = count($memberList);
        if($memberCount<2)output_error('创建群组最少需要3个人！');
        if($memberCount>2999)output_error('同一群组最多只能存在3000个人！');
        $time = time();
        $create = array(
            'createTime' => $time,
            'lastTime' => $time,
            'groupState' => 1,
            'groupName' => $groupName,
            'sortNo' => $time,
            'user_creator_id' => $group_owner_id,
            'user_editor_id' => $group_owner_id,
            'group_owner_id' => $group_owner_id,
        );
        $Group = model('Chatgroup');
        //创建群聊
        $groupId = $Group->chatgroup_add($create);
        if (!$groupId) output_error('群组创建失败！');

        
        $groupMembers = array();

        foreach ($memberList as $k => $v) {
            $groupMembers[$k]=array(
                'group_id' => $groupId,
                'member_id' => $v['member_id'],
                'member_name' =>$v['member_name'],
                'member_avatar' =>$v['member_avatar'],
                'group_member_name' =>$v['member_name'],//群员 群备注
                'join_time' => $time,
                'invite_member' => $group_owner_id,
            );
            $UserIds[] = $v['member_id'];
        }


        //添加群员
        $createMembers = $Group->chatgroup_addAll($groupMembers);
        if(!$createMembers)output_error('群员添加失败！');
        //设置群员数量
        $Group->chatgroup_set($groupId,'member_count',$memberCount);
        //往融云发送建群请求
        $RongCloud = new RongCloud();
        $result = $RongCloud->group()->create($UserIds, $groupId, $groupName);
        $result = json_decode($result,TRUE);
        if ($result['code']==200) {
            output_data(array(
                'groupId' =>$groupId,
                'groupName' =>$groupName,
                'groupImg' =>getChatGroupImg(),
                'state' => 'true'

            ));
        }else{
            output_error('修改失败！');
        }
        // output_data($result);

    }

    /**
     * 获取群组信息
     */
    public function GetChatGroupInfo(){
        $input = input();
        $groupId = isset($input['group_id'])?$input['group_id']:0;
        if($groupId==0)output_error('群ID错误！');
        $Group = model('Chatgroup');
        $groupInfo = $Group->getOneById($groupId);
        if(!$groupInfo)output_error('没有此群的信息，可能已经被群主解散！');
        if(empty($groupInfo['groupImg']))$groupInfo['groupImg']=getChatGroupImg();
        if(empty($groupInfo['groupIntro']))$groupInfo['groupIntro']='';
        if(empty($groupInfo['groupNotice']))$groupInfo['groupNotice']='';
        output_data($groupInfo);
    }

    /**
     * 邀请人加入群聊
     */
    public function GroupChatMemberInvite(){
        $input = input();
        $groupId = isset($input['group_id'])?$input['group_id']:0;
        if($groupId==0)output_error('群ID错误！');
        //获取群员id
        $members = $input['members'];
        // $members = explode(',', $members);

        $Group = model('Chatgroup');
        $groupInfo = $Group->getOneById($groupId);
        if(!$groupInfo)output_error('没有此群的信息，可能已经被群主解散！');

        $GroupmemberList = $Group->get_chatgroupmember_List(array('group_id'=>$groupId),'id,member_id');

        //过滤已存在用户
        if($GroupmemberList)foreach ($GroupmemberList as $key => $value) {
            if(in_array($value['member_id'], $members)){
                unset($GroupmemberList[$key]);
                unset($members[array_search($value['member_id'], $members)]);
            }

        }
        //如果全部过滤完 直接返回
        if(!$members)output_error('邀请的人已经存在此群里！');

        sort($members);
        $Member = model('Member');
        $where =array(
            'member_id' =>array('in',$members)
        );
        $field ='member_id,member_name,member_mobile,member_avatar';
        $memberList = $Member->getMemberList($where,$field);


        //过滤不存在的用户id
        $insertMemberArr=array();
        foreach ($memberList as $k => $v) {
            if(in_array($v['member_id'], $members)){
                $insertMemberArr[]=$v['member_id'];
            }
        }
        //获取不存在用户的id
        $left_menu=array_column($memberList, 'member_id');
        foreach ($members as $m => $s) {
            if ($s == $left_menu[array_search($s, $left_menu)])unset($members[$m]);
        }
        sort($members);
        //如果用户不存在   错误id 将会存在于 $members 数组里面
        if(!$insertMemberArr)output_error('邀请的人已经存在此群或某用户不存在！');
        $memberCount = count($memberList);
        $sumCount =$groupInfo['member_count'] + $memberCount ;
        if($sumCount < 2 )output_error('创建群组最少需要3个人！');
        if($sumCount >3000)output_error('同一群组最多只能存在3000个人！');

        $time = time();        
        $groupMembers = array();
        foreach ($memberList as $k => $v) {
            $groupMembers[$k]=array(
                'group_id' => $groupId,
                'member_id' => $v['member_id'],
                'member_name' =>$v['member_name'],
                'member_avatar' =>$v['member_avatar'],
                'group_member_name' =>$v['member_name'],//群员 群备注
                'join_time' => $time,
                'invite_member' => $this->member_info['member_id'],
            );
            $UserIds[] = $v['member_id'];
        }
        //添加群员
        $createMembers = $Group->chatgroup_addAll($groupMembers);
        if(!$createMembers)output_error('群员添加失败！');
        //设置群员数量
        $Group->chatgroup_set($groupId,'member_count',$sumCount);
        //往融云发送建群请求
        $RongCloud = new RongCloud();
        $result = $RongCloud->group()->join($UserIds, $groupInfo['group_id'], $groupInfo['groupName']);
        $result = json_decode($result,TRUE);
        if ($result['code']==200) {
            output_data(array(
                'state' => 'true'
            ));
        }else{
            output_error('修改失败！');
        }

    }


    /**
     * 群聊列表
     */
    public function GroupChatList(){
        $member_id = $this->member_info['member_id'];
        $Group = model('Chatgroup');
        $where = array(
            'member_id'=>$member_id
        );
        $field = 'id,member_id,group_id,member_name,join_time';
        $groupmemberList = $Group->get_chatgroupmember_List($where,$field);
        $groupIds=array();
        foreach ($groupmemberList as $key => $value) {
            $groupIds[] = $value['group_id'];
        }
        $condition =array(
            'group_id' =>array('in',$groupIds)
        );
        $field = 'group_id,groupImg,groupState,groupName,group_owner_id,member_count';
        $groupList = $Group->get_chatgroup_List($condition,$field);
        if($groupList)foreach ($groupList as $k => &$v) {
            if(empty($v['groupImg']))$v['groupImg']=getChatGroupImg();
        }
        unset($v);
        output_data($groupList);
    }

    /**
     * 群聊用户列表
     */
    public function GroupChatMemberList(){
        $input = input();
        $member_id = $this->member_info['member_id'];
        $groupId = isset($input['group_id'])?$input['group_id']:0;
        if($groupId==0)output_error('群ID错误！');
        $Group = model('Chatgroup');
        $groupInfo = $Group->getOneById($groupId);
        if(!$groupInfo)output_error('没有此群的信息，可能已经被群主解散！');
        $MemberList = $Group->get_chatgroupmember_List(array('group_id'=>$groupId));
        if($MemberList)foreach ($MemberList as $k => &$v) {
            $v['member_avatar']  = AvatarFormat($v['member_id']);
            $v['group_owner_id']  = $groupInfo['group_owner_id'];
        }
        unset($v);
        output_data($MemberList);

    }

    /**
     * 修改群名称
     */
    public function ModifyGroupChatName(){
        $input = input();
        $member_id = $this->member_info['member_id'];
        $groupId = isset($input['group_id'])?$input['group_id']:0;
        $groupName = isset($input['group_name'])?trim($input['group_name']):$this->member_info['member_name'].'创建的群聊';
        if(empty($groupName))$groupName = $this->member_info['member_name'].'创建的群聊';
        if($groupId==0)output_error('群ID错误！');
        $Group = model('Chatgroup');
        $groupInfo = $Group->getOneById($groupId);
        if(!$groupInfo)output_error('没有此群的信息，可能已经被群主解散！');
        if($groupName == $groupInfo['groupName'])output_data(array('state'=>'true'));
        $exits = $Group->getChatmember(array('group_id'=>$groupId,'member_id'=>$member_id));
        if(!$exits)output_error('非法请求,你已被踢出群聊！');
        if($groupInfo['group_owner_id'] != $member_id) output_error('只有群主能修改群名称！');
        $result = $Group->chatgroup_set($groupId,'groupName',trim($groupName));
        if(!$result)output_error('修改失败，请检查网络原因！');
        $RongCloud = new RongCloud();
        $result = $RongCloud->group()->refresh($groupId, $groupName);
        $result = json_decode($result,TRUE);
        if ($result['code']==200) {
            output_data(array(
                'state' => 'true'
            ));
        }else{
            output_error('修改失败！');
        }
        

    }

    /**
     * 删除群员
     */
    public function DeleteGroupMember(){
        $input = input();
        $member_id = $this->member_info['member_id'];
        $groupId = isset($input['group_id'])?$input['group_id']:0;
        //获取群员id
        $members = $input['members'];
        // $members = explode(',', $members);

        if($groupId==0)output_error('群ID错误！');
        $Group = model('Chatgroup');
        $groupInfo = $Group->getOneById($groupId);
        if(!$groupInfo)output_error('没有此群的信息，可能已经被群主解散！');
        if($groupInfo['group_owner_id'] != $member_id) output_error('你不是群主，没有权限删除群员！');
        $groupMembers = $Group->get_chatgroupmember_List(array('group_id'=>$groupId));
        if(!$groupMembers)output_error('此群聊已删除！');
        $where =array(
            'group_id' => $groupId,
            'member_id' => array('in',$members)
        );
        $result = $Group->chatgroupMembers_del($where);
        $RongCloud = new RongCloud();
        $result = $RongCloud->group()->quit($members, $groupId);
        $result = json_decode($result,TRUE);
        if ($result['code']==200) {
            $count = $Group->chatgroupMembers_count(array('group_id'=>$groupId));
            if($count<2){
                $Group->chatgroup_del($groupId);
                $Group->chatgroupMembers_del(array('group_id'=>$groupId));
                $RongCloud->group()->dismiss($member_id, $groupId);
            }
            output_data(array(
                'state' => 'true'
            ));
        }else{
            output_error('删除失败！');
        }
    }


    /**
     * 退出群聊
     */
    public function LeaveGroupChat(){
        $input = input();
        $member_id = $this->member_info['member_id'];
        $groupId = isset($input['group_id'])?$input['group_id']:0;
        if($groupId==0)output_error('群ID错误！');
        $Group = model('Chatgroup');
        $RongCloud = new RongCloud();
        $groupInfo = $Group->getOneById($groupId);
        if(!$groupInfo)output_error('没有此群的信息，可能已经被群主解散！');
        $is_exist = $Group->getChatmember(array('group_id'=>$groupId,'member_id'=>$member_id));
        if(!$is_exist)output_error('非法请求，你并不在此群内！');
        
        $del = $Group->chatgroupMembers_del(array('group_id'=>$groupId,'member_id'=>$member_id));
        //如果是群主退群
        if($del && $groupInfo['group_owner_id']==$member_id){
            $mber = $Group->getChatmember(array('group_id'=>$groupId));
            if($mber)$Group->chatgroup_set($groupId,'group_owner_id',$mber['member_id']);
        }
        $result = $RongCloud->group()->quit([$member_id], $groupId);
        $result = json_decode($result,TRUE);
        if ($result['code']==200) {
            $count = $Group->chatgroupMembers_count(array('group_id'=>$groupId));
            if($count<2){
                $Group->chatgroup_del($groupId);
                $Group->chatgroupMembers_del(array('group_id'=>$groupId));
                $RongCloud->group()->dismiss($member_id, $groupId);
            }
            output_data(array(
                'state' => 'true'
            ));
        }else{
            output_error('退出失败！');
        }
    }


    /**
     * 解散群组
     */
    public function DismissChatGroup(){
        $input = input();
        $member_id = $this->member_info['member_id'];
        $groupId = isset($input['group_id'])?$input['group_id']:0;
        if($groupId==0)output_error('群ID错误！');
        $Group = model('Chatgroup');
        $RongCloud = new RongCloud();
        $RongCloud->group()->dismiss($member_id, $groupId);
        $groupInfo = $Group->getOneById($groupId);
        if(!$groupInfo)output_error('没有此群的信息，可能已经被群主解散！');
        $is_exist = $Group->getChatmember(array('group_id'=>$groupId,'member_id'=>$member_id));
        if(!$is_exist)output_error('非法请求，你并不在此群内！');
        if($groupInfo['group_owner_id'] !=$member_id)output_error('只有群主才能解散群！');
        //删除群组
        $Group->chatgroup_del($groupId);
        //删除群员
        $Group->chatgroupMembers_del(array('group_id'=>$groupId));
        //删除融云群
        $RongCloud->group()->dismiss($member_id, $groupId);
        output_data(array(
                'state' => 'true'
            ));
    }
    





}                  