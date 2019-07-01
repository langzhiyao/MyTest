<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Express extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/express.lang.php');
    }

    public function index() {
        $letter = input('get.letter');
        $condition = array();
        if (preg_match('/^[A-Z]$/', $letter)) {
            $condition['e_letter'] = $letter;
        }
        $express_list = db('express')->where($condition)->order('e_order,e_state desc,id')->paginate(10,false,['query' => request()->param()]);
        $this->assign('page', $express_list->render());
        $this->assign('express_list', $express_list);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    /**
     * ajax操作
     */
    public function ajax() {
        switch ($_GET['branch']) {
            case 'state':
                $model_brand = Model('express');
                $update_array = array();
                $update_array['id'] = intval($_GET['id']);
                $update_array[$_GET['column']] = trim($_GET['value']);
                $model_brand->update($update_array);
                dkcache('express');
                $this->log(lang('ds_edit').lang('express_name').lang('express_state') . '[ID:' . intval($_GET['id']) . ']', 1);
                echo 'true';
                exit;
                break;
            case 'order':
                $_GET['value'] = $_GET['value'] == 0 ? 2 : 1;
                $model_brand = Model('express');
                $update_array = array();
                $update_array['id'] = intval($_GET['id']);
                $update_array[$_GET['column']] = trim($_GET['value']);
                $model_brand->update($update_array);
                dkcache('express');
                $this->log(lang('ds_edit').lang('express_name').lang('express_state') . '[ID:' . intval($_GET['id']) . ']', 1);
                echo 'true';
                exit;
                break;
            case 'e_zt_state':
                $model_brand = Model('express');
                $update_array = array();
                $update_array['id'] = intval($_GET['id']);
                $update_array[$_GET['column']] = trim($_GET['value']);
                $model_brand->update($update_array);
                dkcache('express');
                $this->log(lang('ds_edit').lang('express_name').lang('express_state') . '[ID:' . intval($_GET['id']) . ']', 1);
                echo 'true';
                exit;
                break;
        }
        dkcache('express');
    }

}

?>
