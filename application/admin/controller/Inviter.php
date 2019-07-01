<?php
/**
 * 运营设置
 */

namespace app\admin\controller;


use think\Lang;
use think\Validate;
class Inviter extends AdminControl
{
public function _initialize()
{
    parent::_initialize(); // TODO: Change the autogenerated stub
    Lang::load(APP_PATH.'admin/lang/zh-cn/inviter.lang.php');
}

    /**
     * 基本设置
     */
    public function setting(){
        $model_setting = Model('config');
        if (request()->isPost()){
            $upload_file = BASE_UPLOAD_PATH . DS . ATTACH_COMMON;
            if (!empty($_FILES['inviter_back']['name'])) {
                $file = request()->file('inviter_back');
                $info = $file->validate(['ext'=>'jpg,png,gif'])->move($upload_file, 'inviter_back');
                if ($info) {
                    $upload['inviter_back'] = $info->getFilename();
                } else {
                    // 上传失败获取错误信息
                    $this->error($file->getError());
                }
            }
            if (!empty($upload['inviter_back'])) {
                $update_array['inviter_back'] = $upload['inviter_back'];
            }
            $update_array['inviter_ratio_1']=floatval($_POST['inviter_ratio_1']);
            $update_array['inviter_ratio_2']=floatval($_POST['inviter_ratio_2']);
            $update_array['inviter_ratio_3']=floatval($_POST['inviter_ratio_3']);
            $result = $model_setting->updateConfig($update_array);
            if ($result) {
                dkcache('config');
                $this->log(lang('ds_inviter_set'),1);
                $this->success('修改成功', 'Inviter/setting');
            }else{
                $this->log(lang('ds_inviter_set'),0);
            }
        }
        $list_setting = $model_setting->getListConfig();
        //halt($list_setting);
        $this->assign('list_setting',$list_setting);
        $this->setAdminCurItem('index');
        return $this->fetch('index');
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '推广设置',
                'url' => url('Inviter/setting')
            ),
        );
        return $menu_array;
    }
}