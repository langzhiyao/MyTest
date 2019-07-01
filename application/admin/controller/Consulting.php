<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Consulting extends AdminControl
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        Lang::load(APP_PATH . 'admin/lang/zh-cn/consulting.lang.php');
    }

    /**
     * 咨询管理
     */
    public function consulting()
    {
        $condition = array();
        if (request()->isPost()) {
            $member_name = trim(input('param.member_name'));
            if ($member_name != '') {
                $condition['member_name'] = array(
                    'like',
                    '%' . $member_name . '%'
                );
                $this->assign('member_name', $member_name);
            }
            $consult_content = trim(input('param.consult_content'));
            if ($consult_content != '') {
                $condition['consult_content'] = array(
                    'like',
                    '%' . $consult_content . '%'
                );
                $this->assign('consult_content', $consult_content);
            }
            $ctid = intval(input('param.ctid'));
            if ($ctid > 0) {
                $condition['ct_id'] = $ctid;
                $this->assign('ctid', $ctid);
            }
        }
        $model_consult = Model('consult');
        $consult_list = $model_consult->getConsultList($condition, '*');
        $this->assign('show_page', $model_consult->page_info->render());
        $this->assign('consult_list', $consult_list);


        // 咨询类型
        $consult_type = rkcache('consulttype', true);
        $this->assign('consult_type', $consult_type);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    public function delete()
    {
        if (empty(input('param.consult_id'))) {
            $this->error(lang('empty_error'));
        }
        $array_id = array();
        if (!empty(input('param.consult_id'))) {
            $array_id[] = intval(input('param.consult_id'));
        }
        if (!empty($_POST['consult_id'])) {
            $array_id = $_POST['consult_id'];
        }
        $consult = Model('consult');
        if ($consult->delConsult(array(
            'consult_id' => array(
                'in',
                $array_id
            )
        ))
        ) {
            $this->log('删除咨询' . '[ID:' . $array_id . ']', null);
            $this->success(lang('ds_common_del_succ'));
        } else {
            $this->error(lang('ds_common_del_fail'));
        }
    }

    /**
     * 咨询设置
     */
    public function setting()
    {
        $model_setting = Model('config');
        if (request()->isPost()) {
            $update_array = array();
            $update_array['consult_prompt'] = $_POST['consult_prompt'];
            $result = $model_setting->updateConfig($update_array);
            if ($result === true) {
                $this->log('编辑咨询文字提示', 1);
                $this->success(lang('ds_common_op_succ'));
            } else {
                $this->log('编辑咨询文字提示', 0);
                $this->error(lang('ds_common_op_fail'));
            }
        }
        $this->setAdminCurItem('setting');
        $setting_list = $model_setting->getListConfig();
        /* 所见即所得编辑器 */
        $this->assign('build_editor', buildEditor(array(
            'name' => 'consult_prompt',
            'content' => $setting_list['consult_prompt'],
        )));
        $this->assign('setting_list', $setting_list);

        return $this->fetch();
    }

    /**
     * 咨询类型列表
     */
    public function type_list()
    {
        $model_ct = Model('consulttype');
        if (request()->isPost()) {
            $ctid_array = $_POST['del_id'];
            if (!is_array($ctid_array)) {
                $this->error(lang('param_error'));
            }
            foreach ($ctid_array as $val) {
                if (!is_numeric($val)) {
                    $this->error(lang('param_error'));
                }
            }

            $result = $model_ct->delConsultType(array(
                'ct_id' => array(
                    'in',
                    $ctid_array
                )
            ));

            if ($result) {
                $this->log('删除咨询类型 ID:' . implode(',', $ctid_array), 1);
                $this->error(lang('ds_common_del_succ'));
            } else {
                $this->log('删除咨询类型 ID:' . implode(',', $ctid_array), 0);
                $this->error(lang('ds_common_del_fail'));
            }
        }
        $type_list = $model_ct->getConsultTypeList(array(), 'ct_id,ct_name,ct_sort');
        $this->assign('type_list', $type_list);
        $this->setAdminCurItem('type_list');
        return $this->fetch();
    }

    /**
     * 新增咨询类型
     */
    public function type_add()
    {
        if (request()->isPost()) {
            // 验证
            $obj_validate = new Validate();
            $data = [
                'ct_name' => $_POST["ct_name"],
                'ct_sort' => $_POST["ct_sort"],
            ];
            $rule = array(
                array(
                    'ct_name',
                    'require',
                    '请填写咨询类型名称'
                ),
                array(
                    'sort',
                    'require|Number',
                    '请正确填写咨询类型排序'
                ),
            );
            $error = $obj_validate->check($data, $rule);
            if ($error != '') {
                $this->error($obj_validate->getError());
            }
            $insert = array();
            $insert['ct_name'] = trim($_POST['ct_name']);
            $insert['ct_sort'] = intval($_POST['ct_sort']);
            $insert['ct_introduce'] = $_POST['ct_introduce'];
            $result = Model('consulttype')->addConsultType($insert);
            if ($result) {
                $this->log('新增咨询类型', 1);
                $this->error(lang('ds_common_save_succ'), url('consulting/type_list'));
            } else {
                $this->log('新增咨询类型', 0);
                $this->error(lang('ds_common_save_fail'));
            }
        }
        $ct_info = array(
            'ct_id' => '',
            'ct_name' => '',
            'ct_sort' => '',
            'ct_introduce' => '',
        );
        $this->assign('build_editor', buildEditor(array(
            'name' => 'ct_introduce',
        )));
        $this->assign('ct_info', $ct_info);
        $this->setAdminCurItem('type_add');
        return $this->fetch('type_edit');
    }

    /**
     * 编辑咨询类型
     */
    public function type_edit()
    {
        $model_ct = Model('consulttype');
        if (request()->isPost()) {
            // 验证
            $obj_validate = new Validate();
            $data = [
                'ct_name' => $_POST["ct_name"],
                'ct_sort' => $_POST["ct_sort"],
            ];
            $rule = array(
                array(
                    'ct_name',
                    'require',
                    '请填写咨询类型名称'
                ),
                array(
                    'sort',
                    'require|Number',
                    '请正确填写咨询类型排序'
                ),
            );
            $error = $obj_validate->check($data, $rule);
            if ($error != '') {
                $this->error($obj_validate->getError());
            }
            $where = array();
            $where['ct_id'] = intval($_POST['ct_id']);
            $update = array();
            $update['ct_name'] = trim($_POST['ct_name']);
            $update['ct_sort'] = intval($_POST['ct_sort']);
            $update['ct_introduce'] = $_POST['ct_introduce'];
            $result = $model_ct->editConsultType($where, $update);
            if ($result) {
                $this->log('编辑咨询类型 ID:' . $where['ct_id'], 1);
                $this->success(lang('ds_common_op_succ'), url('consulting/type_list'));
            } else {
                $this->log('编辑咨询类型 ID:' . $where['ct_id'], 0);
                $this->error(lang('ds_common_op_fail'));
            }
        }

        $ct_id = intval(input('param.ct_id'));
        if ($ct_id <= 0) {
            $this->error(lang('param_error'));
        }
        $ct_info = $model_ct->getConsultTypeInfo(array('ct_id' => $ct_id));
        $this->assign('build_editor', buildEditor(array(
            'name' => 'ct_introduce',
            'content' => $ct_info['ct_introduce'],
        )));
        $this->setAdminCurItem('type_list');
        $this->assign('ct_info', $ct_info);
        return $this->fetch();
    }

    /**
     * 删除咨询类型
     */
    public function type_del()
    {
        $ct_id = intval(input('param.ct_id'));
        if ($ct_id <= 0) {
            $this->error(lang('param_error'));
        }
        $result = Model('consulttype')->delConsultType(array('ct_id' => $ct_id));
        if ($result) {
            $this->log('删除咨询类型 ID:' . $ct_id, 1);
            $this->error(lang('ds_common_del_succ'));
        } else {
            $this->log('删除咨询类型 ID:' . $ct_id, 0);
            $this->error(lang('ds_common_del_fail'));
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList()
    {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '管理',
                'url' => url('consulting/consulting')
            ),
            array(
                'name' => 'setting',
                'text' => '设置',
                'url' => url('consulting/setting')
            ),
            array(
                'name' => 'type_list',
                'text' => '咨询类型',
                'url' => url('consulting/type_list')
            ),
            array(
                'name' => 'type_add',
                'text' => '新增类型',
                'url' => url('consulting/type_add')
            )
        );
        return $menu_array;
    }
}