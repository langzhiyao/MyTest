<?php
namespace vomont\Core;

use vomont\Core\SDKConfig;

class CommandSDK
{

    const KEY = 12;
    const BASE = HTTP_URL;
    const Login = 110;//登录
    const EquipmentList = 133;//获取设备列表---
    const dayData = 134;//获取当天数据
    const currentData=136;//获取当前数据
    const dayDataType = 139;//获取类型信息
    const historyData = 138;//获取历史数据
    const videoList = 130;//获取播放资源列表-----
    const ChangePassword = 1376;//修改密码
    const DetailsIntercom = 115;//设备详情列表
    const AddIntercom = 116;//添加设备
    const ModifyIntercom = 117;//修改设备
    const DelIntercom = 118;//删除设备
    const IntercomLists = 119;//获取设备列表
    const getAllResources=130;//获取全部资源
    const ResourcesLists = 1280;//获取资源列表
    const ConfigResources = 1281;//配置资源
    const AddResources = 1285;//添加资源组
    const ModifyResources = 1286;//修改资源组
    const DelResources = 1287;//删除资源组
    const IntercomsLists = 1360;//获取设备组列表
    const AddIntercoms = 1361;//添加设备组
    const ModifyIntercoms = 1362;//修改设备组
    const DelIntercoms = 1363;//删除设备组
    const StorageList = 1364;//获取存储列表
    const AaaStorage = 1365;//添加存储
    const ModifyStorage = 1366;//修改存储
    const DelStorage = 1367;//删除存储
    const OrganizationList = 1368;//检索组织列表
    const AddOrganization = 1369;//增加组织
    const ModifyOrganization = 1370;//修改组织
    const DelOrganization = 1371;//删除组织
    const GetClientList = 1372;//检索用户.客户列表
    const AddUser = 1373;//增加用户.客户
    const ModifyUser = 1374;//修改用户.客户
    const DelUser = 1375;//删除用户.客户
    const ChangePswd = 1376;//修改密码
    const ResetPswd = 1377;//重置密码
    const GetEnterpriseList = 1378;//检索企业列表
    const GetUserVideoPermissionsList = 1379;//获取用户视频资源权限
    const SetUserVideoPermissions = 1380;//设置用户视频资源权限
    const GetUserInfo = 1381;//获取用户详情
    const addEnterprise = 1382;//新增企业
    const revStopEnterprise = 1385;//修改企业启用状态
    const getAuthorizationUser = 1386;//获取资源授权用户
    const ModifyAuthorizationUser = 1387;//修改资源授权用户信息
    const getAreaLists = 1389;//检索区域列表
    const AddArea = 1390;//增加区域列表
    const ModifyArea = 1391;//修改区域列表
    const DelArea = 1392;//删除区域列表
    const GetUserVideoPermissions = 1393;//获取单个用户视频资源权限
    const resourcesDetails = 1395;//获取客户开通详情
    const openService = 1396;//检索企业列表
    const createOrder = 1426;//创建新订单
    const renewal = 1427;//续费
    const upgradeServices = 1428;//升级服务
    const freeTrial = 1429;//免费试用
    const openRecord = 1430;//开通记录
    const GetResourceAllocation = 1397;//获取资源事件配置
    const SettingResourceAllocation = 1398;//设置资源事件配置
    const CancelResourceAllocation = 1399;//取消资源事件配置
    const getLogList = 848;//获取日志列表
    const AccountOverview = 1400;//账号总览
    const ModifyContact = 1401;//修改企业联系人
    const undoOrganizationUser = 1402;//撤销组织中用户
    const getSpecificResources = 1403;//获取特定资源
    const livestatus=120;//创建直播计划
    const liveend=122;//删除直播计划
    const Videotape=1421;//获取录像
    protected $key = null;
    const error_code = [   // 错误code
        'null'=>'服务器异常',
        '0'=>'操作成功',
        '101'=>'帐号不存在',
        '102'=>'重复登录',
        '103'=>'用户没有登录',
        '104'=>'设备不在线',
        '105'=>'无效的流媒体转发服务器',
        '106'=>'参数信息有误',
        '107'=>'版本信息过低，请升级版本',
        '108'=>'用户名重复',
        '109'=>'账号或密码错误',
        '111'=>'数据库操作失败',
        '112'=>'设备序列号重复',
        '113'=>'名称重复',
        '114'=>'超过限制个数',
        '115'=>'已存在事件配置',
        '116'=>'不存在事件配置',
        '117'=>'不支持',
        '118'=>'未获取到流通道资源',
        '119'=>'未获取到StreamSinker',
        '120'=>'未找到流服务器',
        '122'=>'未找到该账号',
        '123'=>'已有计划',
        '124'=>'未找到设备',
        '125'=>'未找到计划',
        '126'=>'未找到该用户',
        '127'=>'无效的签名信息',
        '128'=>'未找到服务id',
        '129'=>'权限不足',
        '130'=>'拥有子节点',
        '200'=>'数据库读写失败',
        '201'=>'未找到对应的部门',
        '202'=>'错误的部门数据',
        '203'=>'错误的企业数据',
        '300'=>'HTTP消息回复超时',
        '301'=>'HTTP消息回复JSON读取失败',
        '302'=>'错误的区域',
        '303'=>'该组织下还存在账号',
        '304'=>'数据一致',
        '306'=>'删除区域内有设备',
        '307'=>'删除区域内有用户',
        '308'=>'删除区域内有管理员',
        '309'=>'剩余可服务通道不足',
        '310'=>'企业账户已被禁用',
        '311'=>'web普通用户不能登录',
        '312'=>'用户名有特殊字符',
        '313'=>'必须提供组织ID',
        '314'=>'设备通道数错误',
        '315'=>'日志模板错误',
        '316'=>'组织名称重复',
        '317'=>'未找到流服务器',
        '318'=>'未找到通用服务器',
        '319'=>'服务开通金额不匹配',
        '320'=>'服务开通资源服务已过期',

    ];
    public function __construct()
    {
        $this->key = SDKConfig::KEY;;

    }
    public function getKey()
    {
        return $this->key;
    }

}