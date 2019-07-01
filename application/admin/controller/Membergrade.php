<?php
namespace app\admin\controller;

use think\Lang;
use think\Validate;
use think\Db;
use think\view;


class Membergrade extends AdminControl
{
    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/Membergrade.lang.php');
    }

    public function index()
    {
        $model_config = model('config');
        $list_config = $model_config->getListConfig();
        if (!(request()->isPost())) {
            $mg_arr = array();
            $mg_list = $list_config['member_grade'] ? unserialize($list_config['member_grade']) : array();

            foreach ($mg_list as $k => $v) {
                $mg_arr[$k]['exppoints'] = intval($v['exppoints']);
            }
            $this->assign('mg', $mg_arr);
            $this->setAdminCurItem('index');
            return $this->fetch();
        } else {
            $update_arr = array();
            $mg_arr = array();

            foreach (input('post.exppoints/a') as $k => $v) {
                $mg_arr[$k]['level'] = $k;
                $mg_arr[$k]['level_name'] = 'V' . $k;
                //所需经验值
                $mg_arr[$k]['exppoints'] = intval($v);

            }
            $update_arr['member_grade'] = serialize($mg_arr);
        }
        if ($update_arr) {
            $result = $model_config->updateConfig($update_arr);
        }
        if ($result) {
            $this->success(lang('ds_common_op_succ'), 'Membergrade/index');
        } else {
            $this->error(lang('error'));
        }
    }
    
    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '管理',
                'url' => url('Admin/Membergrade/index')
            )
        );
        return $menu_array;
    }
}