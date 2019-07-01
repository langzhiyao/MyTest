<?php

namespace app\home\controller;

use think\Lang;
use think\Validate;

class Sellerplate extends BaseSeller {

    public function index() {
        $this->plate_list();
    }

    /**
     * 关联版式列表
     */
    public function plate_list() {
        // 版式列表
        $where = array();
        $where['store_id'] = session('store_id');
        $p_name = trim(input('get.p_name'));
        if ($p_name != '') {
            $where['plate_name'] = array('like', '%' . $p_name . '%');
        }
        $p_position = trim(input('get.p_position'));
        if (in_array($p_position, array('0', '1'))) {
            $where['plate_position'] = $p_position;
        }
        $store_plate = Model('storeplate');
        $plate_list = $store_plate->getStorePlateList($where, '*', 10);
        $this->assign('show_page', $store_plate->page_info->render());
        $this->assign('plate_list', $plate_list);
        $this->assign('position', array(0 => '底部', 1 => '顶部'));

        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('sellerplate');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem('plate_list');
        echo $this->fetch($this->template_dir . 'plate_list');
        exit;
    }

    /**
     * 关联版式添加
     */
    public function plate_add() {
        if (!request()->isPost()) {
            $plate_info = array(
                'plate_name' => '',
                'plate_position' => '',
                'plate_content' => '',
            );
            $this->assign('plate_info', $plate_info);
            // 是否能使用编辑器
            if (checkPlatformStore()) { // 平台店铺可以使用编辑器
                $editor_multimedia = true;
            } else {    // 三方店铺需要
                $editor_multimedia = false;
                if ($this->store_grade['sg_function'] == 'editor_multimedia') {
                    $editor_multimedia = true;
                }
            }
            $this->assign('editor_multimedia', $editor_multimedia);
            /* 设置卖家当前菜单 */
            $this->setSellerCurMenu('sellerplate');
            /* 设置卖家当前栏目 */
            $this->setSellerCurItem('plate_add');
            return $this->fetch($this->template_dir . 'plate_add');
        } else {
            
            $insert = array();
            $insert['plate_name'] = $_POST['p_name'];
            $insert['plate_position'] = $_POST['p_position'];
            $insert['plate_content'] = $_POST['p_content'];
            $insert['store_id'] = session('store_id');

            //验证数据  BEGIN
            $rule = [
                ['plate_name', 'require', '请填写版式名称'],
                ['plate_content', 'require', '请填写版式内容'],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($insert);
            if (!$validate_result) {
                showDialog(lang('error') . $validate->getError(), url('Home/Sellerplate/index'));
            }
            //验证数据  END

            $result = Model('storeplate')->addStorePlate($insert);
            if ($result) {
                showDialog(lang('ds_common_op_succ'), url('Home/Sellerplate/index'), 'succ');
            } else {
                showDialog(lang('ds_common_op_fail'), url('Home/Sellerplate/index'));
            }
        }
    }

    /**
     * 关联版式编辑
     */
    public function plate_edit() {
        
        $plate_id = intval(input('param.p_id'));
        if ($plate_id <= 0) {
            $this->error(lang('wrong_argument'));
        }
        if (!request()->isPost()) {

            
            $plate_info = Model('storeplate')->getStorePlateInfo(array('plate_id' => $plate_id, 'store_id' => session('store_id')));
            $this->assign('plate_info', $plate_info);

            /* 设置卖家当前菜单 */
            $this->setSellerCurMenu('sellerplate');
            /* 设置卖家当前栏目 */
            $this->setSellerCurItem('plate_edit');
            return $this->fetch($this->template_dir .'plate_add');
        } else {

            $update = array();
            $update['plate_name'] = $_POST['p_name'];
            $update['plate_position'] = $_POST['p_position'];
            $update['plate_content'] = $_POST['p_content'];

            //验证数据  BEGIN
            $rule = [
                ['plate_name', 'require', '请填写版式名称'],
                ['plate_content', 'require', '请填写版式内容'],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($update);
            if (!$validate_result) {
                showDialog(lang('error') . $validate->getError(), url('Home/Sellerplate/index'));
            }
            //验证数据  END
            
            $where = array();
            $where['plate_id'] = $plate_id;
            $where['store_id'] = session('store_id');
            $result = Model('storeplate')->editStorePlate($update, $where);
            if ($result) {
                showDialog(lang('ds_common_op_succ'), url('Home/Sellerplate/index'), 'succ');
            } else {
                showDialog(lang('ds_common_op_fail'), url('Home/Sellerplate/index'));
            }
        }
    }

    /**
     * 删除关联版式
     */
    public function drop_plate() {
        $plate_id = input('param.p_id');
        if (!preg_match('/^[\d,]+$/i', $plate_id)) {
            showDialog(lang('wrong_argument'), '', 'error');
        }
        $plateid_array = explode(',', $plate_id);
        $return = Model('storeplate')->delStorePlate(array('plate_id' => array('in', $plateid_array), 'store_id' => session('store_id')));
        if ($return) {
            showDialog(lang('ds_common_del_succ'), 'reload', 'succ');
        } else {
            showDialog(lang('ds_common_del_fail'), '', 'error');
        }
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_type 导航类型
     * @param string $menu_key 当前导航的menu_key
     * @return
     */
    function getSellerItemList() {
        $item_list = array(
            array(
                'name' => 'plate_list',
                'text' => '版式列表',
                'url' => url('Home/Sellerplate/plate_list'),
            ),
        );
        if (request()->action() == 'plate_add') {
            $item_list[] = array(
                'name' => 'plate_add',
                'text' => '添加版式',
                'url' => url('Home/Sellerplate/plate_add'),
            );
        }

        if (request()->action() == 'plate_edit') {
            $item_list[] = array(
                'name' => 'plate_add',
                'text' => '添加版式',
                'url' => url('Home/Sellerplate/plate_add'),
            );
            $item_list[] = array(
                'name' => 'plate_edit',
                'text' => '编辑版式',
                'url' => url('Home/Sellerplate/plate_edit'),
            );
        }
        return $item_list;
    }

}

?>
