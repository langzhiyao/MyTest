<?php


$lang['limit_admin']			    = '管理员';
$lang['super_admin']			    = '超级管理员';
$lang['admin_no_role']			    = '暂无角色';
$lang['limit_gadmin']			    = '权限组';
$lang['admin_add_limit_admin']		= '添加管理员';
$lang['admin_add_limit_gadmin']		= '添加权限组';
$lang['admin_index_reset_password']		= '重置密码';
$lang['admin_index_status_on']		= '启用';
$lang['admin_index_status_off']		= '禁用';

$lang['gadmin_delete_before'] = '确认要删除该角色【';
$lang['gadmin_delete_after'] = '】吗，一旦删除将无法恢复，请慎重';
$lang['gadmin_index_delete'] = '请先清空权限组下的成员';

$lang['gadmin_no_perms'] = '您不具备删除权限，请联系管理员';
$lang['gadmin_delete_error'] = '删除失败，请联系管理员';
$lang['gadmin_delete_error_member'] = '删除失败，请先清空权限组下的成员';
$lang['gadmin_delete_error_td_member'] = '删除失败，该角色为特殊值，如需删除，请联系超级管理员操作';

/**
 * 操作列表
 */
$lang['admin_index_kl'] = '再考虑一下';
$lang['admin_index_title'] = '提示';
$lang['admin_index_qx'] = '已取消操作';

$lang['admin_status_close_true'] = '确认禁用';
$lang['admin_status_close_before'] = '确认要禁用该账号【';
$lang['admin_status_close_after'] = '】吗？禁用后该账号将无法继续使用？';
$lang['admin_status_close_success'] = '禁用成功';
$lang['admin_status_close_error'] = '禁用失败，请联系管理员操作';

$lang['admin_status_open_true'] = '确认启用';
$lang['admin_status_open_before'] = '确认要启用该账号【';
$lang['admin_status_open_after'] = '】吗？该账号已被禁用，启用后可以正常使用，请谨慎操作';
$lang['admin_status_open_success'] = '启用成功';
$lang['admin_status_open_error'] = '启用失败，请联系管理员操作';

$lang['admin_reset_pwd_true'] = '确认重置密码';
$lang['admin_reset_pwd_before'] = '确认要重置账号【';
$lang['admin_reset_pwd_after'] = '】密码吗？重置后该账号会收到系统短信发送的临时密码，用户凭临时密码即可登录。';
$lang['admin_reset_pwd_success'] = '密码已重置';
$lang['admin_reset_pwd_error'] = '密码重置失败，请联系超级管理员';

$lang['admin_delete_true'] = '确认删除';
$lang['admin_delete_before'] = '确认要删除账号【';
$lang['admin_delete_after'] = '】吗？删除后该账号将无法继续使用。';
$lang['admin_delete_success'] = '删除成功';
$lang['admin_delete_error'] = '删除失败，账号不存在或已删除';

$lang['admin_delete_role_true'] = '确认移除';
$lang['admin_delete_role_before'] = '确认要移除账号【';
$lang['admin_delete_role_after'] = '】吗？移除后该账号将不再具有此角色权限。';
$lang['admin_delete_role_success'] = '移除成功';
$lang['admin_delete_role_error'] = '移除失败，账号不存在或已移除';

/**
 * 管理员列表
 */
$lang['admin_index_not_allow_del']	= '该账号为系统管理员,不得删除';
$lang['admin_index_login_null']		= '此管理员未登录过';
$lang['admin_index_username']		= '账号';
$lang['admin_index_company']	    = '所属公司';
$lang['admin_index_department']	    = '所属部门';
$lang['admin_index_password']		= '密码';
$lang['admin_rpassword']			= '确认密码';
$lang['admin_index_truename']		= '真实姓名';
$lang['admin_index_email']			= '电子邮件';
$lang['admin_index_im']				= '即时通讯';
$lang['admin_index_last_login']		= '上次登录';
$lang['admin_index_login_times']	= '登录次数';
$lang['admin_index_sys_admin']		= '系统管理员';
$lang['admin_index_del_admin']		= '删除';
$lang['admin_index_delete_admin']		= '移除';
$lang['admin_index_sys_admin_no']	= '超级管理员不可编辑';
$lang['admin_index_description']	= '备注';
/**
 * 管理员添加
 */
$lang['admin_add_admin_not_exists']		= '该名称已存在';
$lang['admin_add_admin_phone_not_exists']		= '该手机号码已存在';
$lang['admin_edit_valid_phone']         	= '请您填写有效的手机号码';
$lang['admin_add_username_tip']			= '请输入账号';
$lang['admin_add_truename_tip']			= '请输入真实姓名';
$lang['admin_add_department_tip']		= '请输入所属部门';
$lang['admin_add_description_tip']		= '请输入备注说明';
$lang['admin_add_phone_tip']			= '请输入手机号码';
$lang['admin_add_password_tip']			= '请输入密码';
$lang['admin_add_gid_tip']				= '请选择一个权限组，如果还未设置，请马上设置';
$lang['admin_add_username_null']		= '账号不能为空';
$lang['admin_add_username_max']			= '账号长度为5-20';
$lang['admin_add_password_null']		= '密码不能为空';
$lang['admin_add_gid_null']				= '请选择一个权限组';
$lang['admin_add_company_id_null']		= '请选择一个所属公司';
$lang['admin_add_password_type']		= '密码为英文或数字';
$lang['admin_add_password_max']			= '密码长度为6-20';
$lang['admin_add_username_not_exists']	= '该名称不存在，请换一个';
$lang['admin_add_company_id_tip']	    = '请选择一个所属公司，如果还添加，请马上添加';
$lang['admin_add_company_chose']	    = '请选择所属公司';
$lang['admin_add_role_chose']	    = '请选择所属角色';
/**
 * 管理权限设置
 */
$lang['admin_set_admin_not_exists']		= '此管理员不存在';
$lang['admin_set_back_to_admin_list']	= '返回管理员列表';
$lang['admin_set_back_to_member_list']	= '返回会员列表';
$lang['admin_set_limt']					= '设置权限';
$lang['admin_set_system_login']			= '后台登录';
$lang['admin_set_website_manage']		= '网站管理';
$lang['admin_set_clear_cache']			= '清空缓存';
$lang['admin_set_operation']			= '运营管理';
$lang['admin_set_operation_ztc_class']	= '直通车管理';
$lang['admin_set_operation_gold_buy']	= '金币购买管理';
$lang['admin_set_operation_pointprod']	= '积分兑换管理';
/**
 * 管理员修改
 */
$lang['admin_edit_success']				= '更新成功';
$lang['admin_edit_fail']				= '更新失败';
$lang['admin_edit_repeat_error']		= '两次输入的密码不一致，请重新输入';
$lang['admin_edit_admin_error']			= '管理员信息错误';
$lang['admin_edit_admin_pw']			= '密码';
$lang['admin_edit_admin_pw2']			= '确认密码';
$lang['admin_edit_pwd_tip1']			= '不修改留空即可';


$lang['gadmin_name']				    = '权限组';
$lang['gadmin_del_confirm']				= '删除该组同时会清除该组内成员的所有权限，确认删除吗?';


?>
