<?php

/**
 * 抢购管理
 */

namespace app\admin\controller;

use think\Lang;

class Groupbuy extends AdminControl {

    public function _initialize() {
        parent::_initialize(); // TODO: Change the autogenerated stub
        Lang::load(APP_PATH . 'admin/lang/zh-cn/groupbuy.lang.php');
    }

    /**
     * 进行中抢购列表，只可推荐
     *
     */
    public function index() {
        $model_groupbuy = Model('groupbuy');

        $condition = array();
        if (!empty(input('param.groupbuy_name'))) {
            $condition['groupbuy_name'] = array('like', '%' . input('param.groupbuy_name') . '%');
        }
        if ((input('param.store_name'))) {
            $condition['store_name'] = array('like', '%' . input('param.store_name') . '%');
        }
        if ((input('param.groupbuy_state'))) {
            $condition['state'] = input('param.groupbuy_state');
        }
        $groupbuy_list = $model_groupbuy->getGroupbuyExtendList($condition, 10);
        $this->assign('groupbuy_list', $groupbuy_list);
        $this->assign('show_page', $model_groupbuy->page_info->render());
        $this->assign('groupbuy_state_array', $model_groupbuy->getGroupbuyStateArray());

        $this->setAdminCurItem('index');

        // 输出自营店铺IDS
        $this->assign('flippedOwnShopIds', array_flip(Model('store')->getOwnShopIds()));
        $this->assign('flippedOwnShopIds', '');
        return $this->fetch();
    }

    /**
     * 审核通过
     */
    public function groupbuy_review_pass() {
        $groupbuy_id = intval($_POST['groupbuy_id']);

        $model_groupbuy = Model('groupbuy');
        $result = $model_groupbuy->reviewPassGroupbuy($groupbuy_id);
        if ($result) {
            $this->log('通过抢购活动申请，抢购编号' . $groupbuy_id, null);
            // 添加队列
            $groupbuy_info = $model_groupbuy->getGroupbuyInfo(array('groupbuy_id' => $groupbuy_id));
            $this->addcron(array(
                'exetime' => $groupbuy_info['start_time'], 'exeid' => $groupbuy_info['goods_commonid'],
                'type' => 5
            ));
            $this->addcron(array(
                'exetime' => $groupbuy_info['end_time'], 'exeid' => $groupbuy_info['goods_commonid'],
                'type' => 6
            ));
            $this->success(lang('ds_common_op_succ'), 'Groupbuy/index');
        } else {
            $this->error(lang('ds_common_op_fail'));
        }
    }

    /**
     * 审核失败
     */
    public function groupbuy_review_fail() {
        $groupbuy_id = intval($_POST['groupbuy_id']);

        $model_groupbuy = Model('groupbuy');
        $result = $model_groupbuy->reviewFailGroupbuy($groupbuy_id);
        if ($result) {
            $this->log('拒绝抢购活动申请，抢购编号' . $groupbuy_id, null);
            $this->success(lang('ds_common_op_succ'), 'Groupbuy/index');
        } else {
            $this->error(lang('ds_common_op_fail'));
        }
    }

    /**
     * 取消
     */
    public function groupbuy_cancel() {
        $groupbuy_id = intval($_POST['groupbuy_id']);

        $model_groupbuy = Model('groupbuy');
        $result = $model_groupbuy->cancelGroupbuy($groupbuy_id);
        if ($result) {
            $this->log('取消抢购活动，抢购编号' . $groupbuy_id, null);
            $this->success(lang('ds_common_op_succ'), 'Groupbuy/index');
        } else {
            $this->error(lang('ds_common_op_fail'));
        }
    }

    /**
     * 删除
     */
    public function groupbuy_del() {
        $groupbuy_id = intval($_POST['groupbuy_id']);

        $model_groupbuy = Model('groupbuy');
        $result = $model_groupbuy->delGroupbuy(array('groupbuy_id' => $groupbuy_id));
        if ($result) {
            $this->log('删除抢购活动，抢购编号' . $groupbuy_id, null);
            $this->success(lang('ds_common_op_succ'), 'Groupbuy/index');
        } else {
            $this->error(lang('ds_common_op_fail'));
        }
    }

    /**
     * ajax修改抢购信息
     */
    public function ajax() {

        $result = true;
        $update_array = array();
        $where_array = array();

        switch (input('param.branch')) {
            case 'class_sort':
                $model = Model('groupbuyclass');
                $update_array['sort'] = input('param.value');
                $where_array['class_id'] = input('param.id');
                $result = $model->update($update_array, $where_array);
                // 删除抢购分类缓存
                Model('groupbuy')->dropCachedData('groupbuy_classes');
                break;
            case 'class_name':
                $model = Model('groupbuyclass');
                $update_array['class_name'] = input('param.value');
                $where_array['class_id'] = input('param.id');
                $result = $model->update($update_array, $where_array);
                // 删除抢购分类缓存
                Model('groupbuy')->dropCachedData('groupbuy_classes');
                $this->log(lang('groupbuy_class_edit_success') . '[ID:' . input('param.id') . ']', null);
                break;
            case 'recommended':
                $model = Model('groupbuy');
                $update_array['recommended'] = input('param.value');
                $where_array['groupbuy_id'] = input('param.id');
                $result = $model->editGroupbuy($update_array, $where_array);
                break;
        }
        if ($result) {
            echo 'true';
            exit;
        } else {
            echo 'false';
            exit;
        }
    }

    /**
     * 套餐管理
     * */
    public function groupbuy_quota() {
        $model_groupbuy_quota = Model('groupbuyquota');

        $condition = array();
        $condition['store_name'] = array('like', '%' . input('param.store_name') . '%');
        $list = $model_groupbuy_quota->getGroupbuyQuotaList($condition, 10, 'end_time desc');
        $this->assign('list', $list);
        $this->assign('show_page', $model_groupbuy_quota->page_info->render());

        $this->setAdminCurItem('groupbuy_quota');
        return $this->fetch();
    }

    /**
     * 抢购类别列表
     */
    public function class_list() {

        $model_groupbuy_class = Model('groupbuyclass');
        $param = array();
        $param['order'] = 'sort asc';
        $groupbuy_class_list = $model_groupbuy_class->getTreeList($param);
        //halt($groupbuy_class_list);
        $this->setAdminCurItem('class_list');
        $this->assign('list', $groupbuy_class_list);
        return $this->fetch();
    }

    /**
     * 添加抢购分类页面
     */
    public function class_add() {

        $model_groupbuy_class = Model('groupbuyclass');
        $param = array();
        $param['order'] = 'sort asc';
        $param['class_parent_id'] = 0;
        $groupbuy_class_list = $model_groupbuy_class->getList($param);
        $this->assign('list', $groupbuy_class_list);

        $this->setAdminCurItem('class_add');
        $this->assign('parent_id', input('param.parent_id'));
        return $this->fetch();
    }

    /**
     * 保存添加的抢购类别
     */
    public function class_save() {

        $class_id = intval($_POST['class_id']);
        $param = array();
        $param['class_name'] = trim($_POST['input_class_name']);
        if (empty($param['class_name'])) {
            $this->error(lang('class_name_error'), '');
        }
        $param['sort'] = intval($_POST['input_sort']);
        $param['class_parent_id'] = intval($_POST['input_parent_id']);

        $model_groupbuy_class = Model('groupbuyclass');

        // 删除抢购分类缓存
        Model('groupbuy')->dropCachedData('groupbuy_classes');

        if (empty($class_id)) {
            //新增
            if ($model_groupbuy_class->saveinfo($param)) {
                $this->log(lang('groupbuy_class_add_success') . '[ID:' . $class_id . ']', null);
                $this->success(lang('groupbuy_class_add_success'), url('groupbuy/class_list'));
            } else {
                $this->error(lang('groupbuy_class_add_fail'), url('groupbuy/class_list'));
            }
        } else {
            //编辑
            if ($model_groupbuy_class->updateinfo($param, array('class_id' => $class_id))) {
                $this->log(lang('groupbuy_class_edit_success') . '[ID:' . $class_id . ']', null);
                $this->success(lang('groupbuy_class_edit_success'), url('groupbuy/class_list'));
            } else {
                $this->error(lang('groupbuy_class_edit_fail'), url('groupbuy/class_list'));
            }
        }
    }

    /**
     * 删除抢购类别
     */
    public function class_drop() {

        $class_id = trim($_POST['class_id']);
        if (empty($class_id)) {
            $this->error(lang('param_error'), '');
        }

        $model_groupbuy_class = Model('groupbuyclass');
        //获得所有下级类别编号
        $all_class_id = $model_groupbuy_class->getAllClassId(explode(',', $class_id));
        $param = array();
        $param['in_class_id'] = implode(',', $all_class_id);
        if ($model_groupbuy_class->drop($param)) {
            // 删除抢购分类缓存
            Model('groupbuy')->dropCachedData('groupbuy_classes');

            $this->log(lang('groupbuy_class_drop_success') . '[ID:' . $param['in_class_id'] . ']', null);
            $this->error(lang('groupbuy_class_drop_success'), 'groupbuy/index');
        } else {
            $this->error(lang('groupbuy_class_drop_fail'));
        }
    }

    /**
     * 抢购价格区间列表
     */
    public function price_list() {

        $model = Model('groupbuypricerange');
        $groupbuy_price_list = $model->getList();
        $this->assign('list', $groupbuy_price_list);

        $this->setAdminCurItem('price_list');
        return $this->fetch();
    }

    /**
     * 添加抢购价格区间页面
     */
    public function price_add() {
        $price_info = [
            'range_id' => '', 'range_name' => '', 'range_start' => '', 'range_end' => '',
        ];
        $this->assign('price_info', $price_info);
        $this->setAdminCurItem('price_add');
        return $this->fetch();
    }

    /**
     * 编辑抢购价格区间页面
     */
    public function price_edit() {

        $range_id = intval(input('param.range_id'));
        if (empty($range_id)) {
            $this->error(lang('param_error'), '');
        }

        $model = Model('groupbuypricerange');

        $price_info = $model->getOne($range_id);
        if (empty($price_info)) {
            $this->error(lang('param_error'), '');
        }
        $this->assign('price_info', $price_info);

        $this->setAdminCurItem('price_edit');
        return $this->fetch('price_add');
    }

    /**
     * 保存添加的抢购价格区间
     */
    public function price_save() {

        $range_id = intval($_POST['range_id']);
        $param = array();
        $param['range_name'] = trim($_POST['range_name']);
        if (empty($param['range_name'])) {
            $this->error(lang('range_name_error'), '');
        }
        $param['range_start'] = intval($_POST['range_start']);
        $param['range_end'] = intval($_POST['range_end']);

        $model = Model('groupbuypricerange');

        if (empty($range_id)) {
            //新增
            if ($model->save($param)) {
                dkcache('groupbuy_price');
                $this->log(lang('groupbuy_price_range_add_success') . '[' . $_POST['range_name'] . ']', null);
                $this->error(lang('groupbuy_price_range_add_success'), url('groupbuy/price_list'));
            } else {
                $this->error(lang('groupbuy_price_range_add_fail'), url('groupbuy/price_list'));
            }
        } else {
            //编辑
            if ($model->update($param, array('range_id' => $range_id))) {
                dkcache('groupbuy_price');
                $this->log(lang('groupbuy_price_range_edit_success') . '[' . $_POST['range_name'] . ']', null);
                $this->error(lang('groupbuy_price_range_edit_success'), url('groupbuy/price_list'));
            } else {
                $this->error(lang('groupbuy_price_range_edit_fail'), url('groupbuy/price_list'));
            }
        }
    }

    /**
     * 删除抢购价格区间
     */
    public function price_drop() {

        $range_id = trim($_POST['range_id']);
        if (empty($range_id)) {
            $this->error(lang('param_error'), '');
        }

        $model = Model('groupbuypricerange');
        $param = array();
        $param['in_range_id'] = "'" . implode("','", explode(',', $range_id)) . "'";
        if ($model->drop($param)) {
            dkcache('groupbuy_price');
            $this->log(lang('groupbuy_price_range_drop_success') . '[ID:' . $range_id . ']', null);
            $this->error(lang('groupbuy_price_range_drop_success'));
        } else {
            $this->error(lang('groupbuy_price_range_drop_fail'));
        }
    }

    /**
     * 设置
     * */
    public function groupbuy_setting() {

        $model_setting = Model('config');
        $setting = $model_setting->GetListConfig();
        $this->assign('setting', $setting);
        $this->setAdminCurItem('groupbuy_setting');
        return $this->fetch();
    }

    public function groupbuy_setting_save() {
        $groupbuy_price = intval($_POST['groupbuy_price']);
        if ($groupbuy_price < 0) {
            $groupbuy_price = 0;
        }

        $groupbuy_review_day = intval($_POST['groupbuy_review_day']);
        if ($groupbuy_review_day < 0) {
            $groupbuy_review_day = 0;
        }

        $model_setting = Model('config');
        $update_array = array();
        $update_array['groupbuy_price'] = $groupbuy_price;
        $update_array['groupbuy_review_day'] = $groupbuy_review_day;
        $result = $model_setting->updateConfig($update_array);
        if ($result) {
            $this->log('修改抢购套餐价格为' . $groupbuy_price . '元');
            $this->error(lang('ds_common_op_succ'));
        } else {
            $this->error(lang('ds_common_op_fail'));
        }
    }

    /**
     * 幻灯片设置
     */
    public function slider() {
        $model_setting = Model('config');
        if (request()->isPost()) {
            $update = array();
            $fprefix = 'admin/groupbuy';
            $upload_file = BASE_UPLOAD_PATH . DS . $fprefix;
            if (!empty($_FILES['live_pic1']['name'])) {
                $file = request()->file('live_pic1');
                $info = $file->validate(['ext' => 'jpg,png,gif'])->move($upload_file);
                if ($info) {
                    $update['live_pic1'] = $info->getSaveName();
                } else {
                    $this->error($file->getError());
                }
            }

            if (!empty($_POST['live_link1'])) {
                $update['live_link1'] = $_POST['live_link1'];
            }

            if (!empty($_FILES['live_pic2']['name'])) {
                $file = request()->file('live_pic2');
                $info = $file->validate(['ext' => 'jpg,png,gif'])->move($upload_file);
                if ($info) {
                    $update['live_pic2'] = $info->getSaveName();
                } else {
                    $this->error($file->getError());
                }
            }

            if (!empty($_POST['live_link2'])) {
                $update['live_link2'] = $_POST['live_link2'];
            }

            if (!empty($_FILES['live_pic3']['name'])) {
                $file = request()->file('live_pic3');
                $info = $file->validate(['ext' => 'jpg,png,gif'])->move($upload_file);
                if ($info) {
                    $update['live_pic3'] = $info->getSaveName();
                } else {
                    $this->error($file->getError());
                }
            }

            if (!empty($_POST['live_link3'])) {
                $update['live_link3'] = $_POST['live_link3'];
            }

            if (!empty($_FILES['live_pic4']['name'])) {
                $file = request()->file('live_pic4');
                $info = $file->validate(['ext' => 'jpg,png,gif'])->move($upload_file);
                if ($info) {
                    $update['live_pic4'] = $info->getSaveName();
                } else {
                    $this->error($file->getError());
                }
            }

            if (!empty($_POST['live_link4'])) {
                $update['live_link4'] = $_POST['live_link4'];
            }

            $list_setting = $model_setting->getListConfig();
            $result = $model_setting->updateConfig($update);
            if ($result) {
                if ($list_setting['live_pic1'] != '' && isset($update['live_pic1'])) {
                    @unlink($upload_file . DS . $list_setting['live_pic1']);
                }

                if ($list_setting['live_pic2'] != '' && isset($update['live_pic2'])) {
                    @unlink($upload_file . DS . $list_setting['live_pic2']);
                }

                if ($list_setting['live_pic3'] != '' && isset($update['live_pic3'])) {
                    @unlink($upload_file . DS . $list_setting['live_pic3']);
                }

                if ($list_setting['live_pic4'] != '' && isset($update['live_pic4'])) {
                    @unlink($upload_file . $list_setting['live_pic4']);
                }

                dkcache('config');
                $this->log('修改抢购幻灯片设置', 1);
                $this->error('编辑成功');
            } else {
                $this->error('编辑失败');
            }
        }

        $list_setting = $model_setting->getListConfig();
        $this->assign('list_setting', $list_setting);
        $this->setAdminCurItem('slider');
        return $this->fetch();
    }

    /**
     * 幻灯片清空
     */
    public function slider_clear() {
        $model_setting = Model('config');
        $update = array();
        $update['live_pic1'] = '';
        $update['live_link1'] = '';
        $update['live_pic2'] = '';
        $update['live_link2'] = '';
        $update['live_pic3'] = '';
        $update['live_link3'] = '';
        $update['live_pic4'] = '';
        $update['live_link4'] = '';
        $res = $model_setting->updateConfig($update);
        if ($res) {
            dkcache('config');
            $this->log('清空抢购幻灯片设置', 1);
            echo json_encode(array('result' => 'true'));
        } else {
            echo json_encode(array('result' => 'false'));
        }
        exit;
    }

    /**
     * 页面内导航菜单
     *
     * @param string $menu_key 当前导航的menu_key
     * @param array $array 附加菜单
     * @return
     */
    protected function getAdminItemList() {

        $menu_array = array(
            array(
                'name' => 'index', 'text' => '抢购活动', 'url' => url('groupbuy/index')
            ), array(
                'name' => 'groupbuy_quota', 'text' => '套餐管理', 'url' => url('groupbuy/groupbuy_quota')
            ), array(
                'name' => 'class_list', 'text' => lang('groupbuy_class_list'), 'url' => url('groupbuy/class_list')
            ), array(
                'name' => 'price_list', 'text' => lang('groupbuy_price_list'), 'url' => url('groupbuy/price_list')
            ), array(
                'name' => 'groupbuy_setting', 'text' => '设置', 'url' => url('groupbuy/groupbuy_setting')
            ), array(
                'name' => 'slider', 'text' => '幻灯片管理', 'url' => url('groupbuy/slider')
            ),
        );
        if (request()->action() == 'class_add') {
            $menu_array[] = array(
                'name' => 'class_add', 'text' => lang('groupbuy_class_add'), 'url' => url('groupbuy/class_add')
            );
        }
        if (request()->action() == 'price_add') {
            $menu_array[] = array(
                'name' => 'price_add', 'text' => lang('groupbuy_price_add'), 'url' => url('groupbuy/price_add')
            );
        }
        if (request()->action() == 'price_edit') {
            $menu_array[] = array(
                'name' => 'price_edit', 'text' => lang('groupbuy_price_edit'), 'url' => url('groupbuy/price_edit')
            );
        }
        return $menu_array;
    }

}