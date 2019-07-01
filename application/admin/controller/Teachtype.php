<?php

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Teachtype extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/teacher.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    /**
     * 分类管理
     */
    public function index() {
        if(session('admin_is_super') !=1 && !in_array(4,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $model_type = model('Teachtype');
        if (request()->isPost()) {
            //删除
            if ($_POST['submit_type'] == 'del') {
                $gcids = implode(',', $_POST['check_gc_id']);
                if (!empty($_POST['check_gc_id'])) {
                    if (!is_array($_POST['check_gc_id'])) {
                        $this->log(lang('ds_del,goods_class_index_class') . '[ID:' . $gcids . ']', 0);
                        $this->error(lang('ds_common_del_fail'));
                    }
                    $del_array = $model_type->delGoodsClassByGcIdString($gcids);
                    $this->log(lang('ds_del,goods_class_index_class') . '[ID:' . $gcids . ']', 1);
                    $this->success(lang('ds_common_del_succ'));
                } else {
                    $this->log(lang('ds_del,goods_class_index_class') . '[ID:' . $gcids . ']', 0);
                    $this->error(lang('ds_common_del_fail'));
                }
            }
        }

        //父ID
        $parent_id = input('param.gc_parent_id') ? intval(input('param.gc_parent_id')) : 0;

        //列表
        $tmp_list = $model_type->getTreeTypeList(4);
        $class_list = array();
        if (is_array($tmp_list)) {
            foreach ($tmp_list as $k => $v) {
                if ($v['gc_parent_id'] == $parent_id) {
                    //判断是否有子类
                    if (isset($tmp_list[$k + 1]['deep']) && $tmp_list[$k + 1]['deep'] > $v['deep']) {
                        $v['have_child'] = 1;
                    }
                    $class_list[] = $v;
                }
            }
        }
        if (input('param.ajax') == '1') {
            $output = json_encode($class_list);
            echo $output;
            exit;
        } else {
            $this->assign('class_list', $class_list);
            $this->setAdminCurItem('index');
            return $this->fetch();
        }
    }

    /**
     * 分类添加
     */
    public function type_class_add() {
        if(session('admin_is_super') !=1 && !in_array(1,$this->action )){
            $this->error(lang('ds_assign_right'));
        }
        $model_class = model('Teachtype');
        if (!request()->isPost()) {
            //父类列表，只取到第二级
            $parent_list = $model_class->getTreeTypeList(3);
            $gc_list = array();
            if (is_array($parent_list)) {
                foreach ($parent_list as $k => $v) {
                    $parent_list[$k]['gc_name'] = str_repeat("&nbsp;", $v['deep'] * 3) . $v['gc_name'];
                    if ($v['deep'] == 1)
                        $gc_list[$k] = $v;
                }
            }
            $this->assign('gc_list', $gc_list);
            //类型列表
            $model_type = Model('type');
            $type_list = $model_type->typeList(array(), '', 'type_id,type_name,class_id,class_name');
            $t_list = array();
            if (is_array($type_list) && !empty($type_list)) {
                foreach ($type_list as $k => $val) {
                    $t_list[$val['class_id']]['type'][$k] = $val;
                    $t_list[$val['class_id']]['name'] = $val['class_name'] == '' ? lang('ds_default') : $val['class_name'];
                }
            }

            ksort($t_list);

            $this->assign('type_list', $t_list);
            $this->assign('gc_parent_id', input('get.gc_parent_id'));
            $this->assign('parent_list', $parent_list);
            $this->setAdminCurItem('type_class_add');
            return $this->fetch();
        } else {

            $insert_array = array();
            $insert_array['gc_name'] = input('post.gc_name');
            $insert_array['type_id'] = intval(input('post.t_id'));
            $insert_array['type_name'] = trim(input('post.t_name'));
            $insert_array['gc_parent_id'] = intval(input('post.gc_parent_id'));
            $insert_array['commis_rate'] = intval(input('post.commis_rate'));
            $insert_array['gc_sort'] = intval(input('post.gc_sort'));
            $insert_array['gc_virtual'] = intval(input('post.gc_virtual'));
            $admininfo = $this->getAdminInfo();
            $insert_array['option_id'] = $admininfo['admin_id'];
            $insert_array['option_time'] = time();
            //验证数据  BEGIN
            $rule = [
                ['gc_name', 'require', '分类标题为必填'],
                ['gc_sort', 'between:0,255', '排序应该在0至255之间']
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($insert_array);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END

            $result = $model_class->addTypeClass($insert_array);
            if ($result) {
                if ($insert_array['gc_parent_id'] == 0) {
                    $upload_file = BASE_UPLOAD_PATH . DS . ATTACH_COMMON;
                    if (!empty($_FILES['pic']['name'])) {//上传图片
                        $file = request()->file('pic');
                        $result = $file->validate(['ext' => 'jpg,png,gif'])->move($upload_file, 'category-pic-' . $result . '.jpg');
                    }
                }
                $this->log(lang('ds_add').lang('goods_class_index_class') . '[' . $_POST['gc_name'] . ']', 1);
                $this->success(lang('ds_common_save_succs'), url('Admin/Teachtype/index'));
            } else {
                $this->log(lang('ds_add').lang('goods_class_index_class') . '[' . $_POST['gc_name'] . ']', 0);
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 编辑
     */
    public function type_class_edit() {
        $model_class = model('Teachtype');
        $gc_id = intval(input('param.gc_id'));

        if (!request()->isPost()) {
            //$class_array = $model_class->getGoodsClassInfoById($gc_id);
            $class_array = $model_class->getTeachInfoById($gc_id);

            if (empty($class_array)) {
                $this->error(lang('goods_class_batch_edit_paramerror'));
            }

            //类型列表
            $model_type = Model('type');
            $type_list = $model_type->typeList(array(), '', 'type_id,type_name,class_id,class_name');
            $t_list = array();
            if (is_array($type_list) && !empty($type_list)) {
                foreach ($type_list as $k => $val) {
                    $t_list[$val['class_id']]['type'][$k] = $val;
                    $t_list[$val['class_id']]['name'] = $val['class_name'] == '' ? lang('ds_default') : $val['class_name'];
                }
            }
            ksort($t_list);
            //父类列表，只取到第三级
            $parent_list = $model_class->getTreeTypeList(3);
            if (is_array($parent_list)) {
                foreach ($parent_list as $k => $v) {
                    $parent_list[$k]['gc_name'] = str_repeat("&nbsp;", $v['deep'] * 3) . $v['gc_name'];
                }
            }
            $this->assign('parent_list', $parent_list);
            // 一级分类列表
            $gc_list = model('Teachtype')->getGoodsClassListByParentId(0);
            $this->assign('gc_list', $gc_list);


            $pic_name = BASE_UPLOAD_PATH . '/' . ATTACH_COMMON . '/category-pic-' . $class_array['gc_id'] . '.jpg';
            if (file_exists($pic_name)) {
                $class_array['pic'] = UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/category-pic-' . $class_array['gc_id'] . '.jpg';
            }
            $this->assign('type_list', $t_list);
            $this->assign('class_array', $class_array);
            $this->setAdminCurItem('type_class_edit');
            return $this->fetch();
        } else {
            $update_array = array();
            $update_array['gc_name'] = input('post.gc_name');
            $update_array['type_id'] = intval(input('post.t_id'));
            $update_array['type_name'] = trim(input('post.t_name'));
            $update_array['commis_rate'] = intval(input('post.commis_rate'));
            $update_array['gc_sort'] = intval($_POST['gc_sort']);
            $update_array['gc_virtual'] = intval(input('post.gc_virtual'));
            $update_array['gc_parent_id'] = intval(input('post.gc_parent_id'));

            //验证数据  BEGIN
            $rule = [
                ['gc_name', 'require', '分类标题为必填'],
                ['gc_sort', 'between:0,255', '排序应该在0至255之间'],
                ['commis_rate', 'between:0,100', lang('goods_class_add_commis_rate_error')]
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($update_array);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END


            // 更新分类信息
            $where = array('gc_id' => $gc_id);
            $result = $model_class->editTypeClass($update_array, $where);
            if (!$result) {
                $this->log(lang('ds_edit').lang('goods_class_index_class') . '[' . $_POST['gc_name'] . ']', 0);
                $this->error(lang('goods_class_batch_edit_fail'));
            }

            if (!empty($_FILES['pic']['name'])) {//上传图片
                $upload_file = BASE_UPLOAD_PATH . DS . ATTACH_COMMON;
                if (!empty($_FILES['pic']['name'])) {//上传图片
                    $file = request()->file('pic');
                    $file->validate(['ext' => 'jpg,png,gif'])->move($upload_file, 'category-pic-' . $gc_id . '.jpg');
                }
            }

            // 检测是否需要关联自己操作，统一查询子分类
            if ($_POST['t_commis_rate'] == '1' || $_POST['t_associated'] == '1' || $_POST['t_gc_virtual'] == '1') {
                $gc_id_list = $model_class->getChildClass($gc_id);
                $gc_ids = array();
                if (is_array($gc_id_list) && !empty($gc_id_list)) {
                    foreach ($gc_id_list as $val) {
                        $gc_ids[] = $val['gc_id'];
                    }
                }
            }

            // 更新该分类下子分类的所有分佣比例
            if ($_POST['t_commis_rate'] == '1' && !empty($gc_ids)) {
                $model_class->editGoodsClass(array('commis_rate' => $update_array['commis_rate']), array('gc_id' => array('in', $gc_ids)));
            }

            // 更新该分类下子分类的所有类型
            if ($_POST['t_associated'] == '1' && !empty($gc_ids)) {
                $where = array();
                $where['gc_id'] = array('in', $gc_ids);
                $update = array();
                $update['type_id'] = intval($_POST['t_id']);
                $update['type_name'] = trim($_POST['t_name']);
                $model_class->editGoodsClass($update, $where);
            }


            $this->log(lang('ds_edit').lang('goods_class_index_class') . '[' . $_POST['gc_name'] . ']', 1);
            $this->success(lang('goods_class_batch_edit_ok'), url('Admin/Teachtype/index'));
        }
    }

    /**
     * 删除分类
     */
    public function type_class_del() {

        $model_class = model('Teachtype');
        $gc_id = intval(input('param.gc_id'));
        if ($gc_id > 0) {
            //删除分类
            $model_class->delTypeClassByGcIdString($gc_id);
            $this->log(lang('ds_del,goods_class_index_class') . '[ID:' . $gc_id . ']', 1);
            $this->success(lang('ds_common_del_succ'), url('Admin/Teachtype/index'));
        } else {
            $this->log(lang('ds_del,goods_class_index_class') . '[ID:' . $gc_id . ']', 0);
            $this->error(lang('ds_common_del_fail'), url('Admin/Teachtype/index'));
        }
    }

    /**
     * tag列表
     */
    public function tag() {

        /**
         * 处理商品分类
         */
        $choose_gcid = ($t = intval(input('param.choose_gcid'))) > 0 ? $t : 0;
        $gccache_arr = model('Teachtype')->getGoodsclassCache($choose_gcid, 3);
        $this->assign('gc_json', json_encode($gccache_arr['showclass']));
        $this->assign('gc_choose_json', json_encode($gccache_arr['choose_gcid']));

        $model_class_tag = Model('goodsclasstag');

        if (!request()->isPost()) {
            $where = array();
            if ($choose_gcid > 0) {
                $where['gc_id_' . ($gccache_arr['showclass'][$choose_gcid]['depth'])] = $choose_gcid;
            }
            $tag_list = $model_class_tag->getTagList($where, 10);
            $this->assign('tag_list', $tag_list);
            $this->assign('page', $model_class_tag->page_info->render());
            $this->setAdminCurItem('tag');
            return $this->fetch('goods_class_tag');
        } else {
            //删除
            if ($_POST['submit_type'] == 'del') {
                if (is_array($_POST['tag_id']) && !empty($_POST['tag_id'])) {
                    //删除TAG
                    $model_class_tag->delTagByIds(implode(',', $_POST['tag_id']));
                    $this->log(lang('ds_del') . 'tag[ID:' . implode(',', $_POST['tag_id']) . ']', 1);
                    $this->success(lang('ds_common_del_succ'));
                } else {
                    $this->log(lang('ds_del') . 'tag', 0);
                    $this->error(lang('ds_common_del_fail'));
                }
            }
        }
    }

    /**
     * 重置TAG
     */
    public function tag_reset() {
        //实例化模型
        $model_class = model('Teachtype');
        $model_class_tag = Model('goodsclasstag');

        //清空TAG
        $return = $model_class_tag->clearTag();
        if (!$return) {
            $this->error(lang('goods_class_reset_tag_fail'), url('Admin/Goodsclass/tag'));
        }

        //商品分类
        $goods_class = $model_class->getTreeTypeList(3);
        //格式化分类。组成三维数组
        if (is_array($goods_class) and !empty($goods_class)) {
            $goods_class_array = array();
            foreach ($goods_class as $val) {
                //一级分类
                if ($val['gc_parent_id'] == 0) {
                    $goods_class_array[$val['gc_id']]['gc_name'] = $val['gc_name'];
                    $goods_class_array[$val['gc_id']]['gc_id'] = $val['gc_id'];
                    $goods_class_array[$val['gc_id']]['type_id'] = $val['type_id'];
                } else {
                    //二级分类
                    if (isset($goods_class_array[$val['gc_parent_id']])) {
                        $goods_class_array[$val['gc_parent_id']]['sub_class'][$val['gc_id']]['gc_name'] = $val['gc_name'];
                        $goods_class_array[$val['gc_parent_id']]['sub_class'][$val['gc_id']]['gc_id'] = $val['gc_id'];
                        $goods_class_array[$val['gc_parent_id']]['sub_class'][$val['gc_id']]['gc_parent_id'] = $val['gc_parent_id'];
                        $goods_class_array[$val['gc_parent_id']]['sub_class'][$val['gc_id']]['type_id'] = $val['type_id'];
                    } else {
                        foreach ($goods_class_array as $v) {
                            //三级分类
                            if (isset($v['sub_class'][$val['gc_parent_id']])) {
                                $goods_class_array[$v['sub_class'][$val['gc_parent_id']]['gc_parent_id']]['sub_class'][$val['gc_parent_id']]['sub_class'][$val['gc_id']]['gc_name'] = $val['gc_name'];
                                $goods_class_array[$v['sub_class'][$val['gc_parent_id']]['gc_parent_id']]['sub_class'][$val['gc_parent_id']]['sub_class'][$val['gc_id']]['gc_id'] = $val['gc_id'];
                                $goods_class_array[$v['sub_class'][$val['gc_parent_id']]['gc_parent_id']]['sub_class'][$val['gc_parent_id']]['sub_class'][$val['gc_id']]['type_id'] = $val['type_id'];
                            }
                        }
                    }
                }
            }

            $return = $model_class_tag->tagAdd($goods_class_array);

            if ($return) {
                $this->log(lang('ds_reset') . 'tag', 1);
                $this->success(lang('ds_common_op_succ'), url('Admin/Goodsclass/tag'));
            } else {
                $this->log(lang('ds_reset') . 'tag', 0);
                $this->error(lang('ds_common_op_fail'), url('Admin/Goodsclass/tag'));
            }
        } else {
            $this->log(lang('ds_reset') . 'tag', 0);
            $this->error(lang('goods_class_reset_tag_fail_no_class'), url('Admin/Goodsclass/tag'));
        }
    }

    /**
     * 更新TAG名称
     */
    public function tag_update() {
        $model_class = model('Teachtype');
        $model_class_tag = Model('goodsclasstag');

        //需要更新的TAG列表
        $tag_list = $model_class_tag->getTagList(array(), '', 'gc_tag_id,gc_id_1,gc_id_2,gc_id_3');
        if (is_array($tag_list) && !empty($tag_list)) {
            foreach ($tag_list as $val) {
                //查询分类信息
                $in_gc_id = array();
                if ($val['gc_id_1'] != '0') {
                    $in_gc_id[] = $val['gc_id_1'];
                }
                if ($val['gc_id_2'] != '0') {
                    $in_gc_id[] = $val['gc_id_2'];
                }
                if ($val['gc_id_3'] != '0') {
                    $in_gc_id[] = $val['gc_id_3'];
                }
                $gc_list = $model_class->getGoodsClassListByIds($in_gc_id);

                //更新TAG信息
                $update_tag = array();
                if (isset($gc_list['0']['gc_id']) && $gc_list['0']['gc_id'] != '0') {
                    $update_tag['gc_id_1'] = $gc_list['0']['gc_id'];
                    $update_tag['gc_tag_name'] .= $gc_list['0']['gc_name'];
                }
                if (isset($gc_list['1']['gc_id']) && $gc_list['1']['gc_id'] != '0') {
                    $update_tag['gc_id_2'] = $gc_list['1']['gc_id'];
                    $update_tag['gc_tag_name'] .= "&nbsp;&gt;&nbsp;" . $gc_list['1']['gc_name'];
                }
                if (isset($gc_list['2']['gc_id']) && $gc_list['2']['gc_id'] != '0') {
                    $update_tag['gc_id_3'] = $gc_list['2']['gc_id'];
                    $update_tag['gc_tag_name'] .= "&nbsp;&gt;&nbsp;" . $gc_list['2']['gc_name'];
                }
                unset($gc_list);
                $update_tag['gc_tag_id'] = $val['gc_tag_id'];
                $return = $model_class_tag->updateTag($update_tag);
                if (!$return) {
                    $this->log(lang('ds_update') . 'tag', 0);
                    $this->error(lang('ds_common_op_fail'), 'Admin/Goodsclass/tag');
                }
            }
            $this->log(lang('ds_update') . 'tag', 1);
            $this->success(lang('ds_common_op_succ'), 'Admin/Goodsclass/tag');
        } else {
            $this->log(lang('ds_update') . 'tag', 0);
            $this->error(lang('goods_class_update_tag_fail_no_class'), 'Admin/Goodsclass/tag');
        }
    }

    /**
     * 删除TAG
     */
    public function tag_del() {
        $id = intval($_GET['tag_id']);
        $model_class_tag = Model('goods_class_tag');
        if ($id > 0) {
            /**
             * 删除TAG
             */
            $model_class_tag->delTagByIds($id);
            $this->log(lang('ds_del') . 'tag[ID:' . $id . ']', 1);
            $this->success(lang('ds_common_op_succ'));
        } else {
            $this->log(lang('ds_del') . 'tag[ID:' . $id . ']', 0);
            $this->error(lang('ds_common_op_fail'));
        }
    }

    /**
     * 分类导航 v3-b12
     */
    public function nav_edit() {
        $gc_id = input('param.gc_id');
        $model_goods = model('Teachtype');
        $class_info = $model_goods->getGoodsClassInfoById($gc_id);
        $model_class_nav = Model('goodsclassnav');
        $nav_info = $model_class_nav->getGoodsClassNavInfoByGcId($gc_id);

        if (request()->isPost()) {
            $update = array();
            $update['gc_id'] = $gc_id;
            $update['cn_alias'] = $_POST['cn_alias'];
            if (isset($_POST['class_id'])&&is_array($_POST['class_id'])) {
                $update['cn_classids'] = implode(',', $_POST['class_id']);
            }
            if (isset($_POST['brand_id'])&&is_array($_POST['brand_id'])) {
                $update['cn_brandids'] = implode(',', $_POST['brand_id']);
            }
            $update['cn_adv1_link'] = $_POST['cn_adv1_link'];
            $update['cn_adv2_link'] = $_POST['cn_adv2_link'];
            if (!empty($_FILES['pic']['name'])) {//上传图片
                $upload=request()->file('pic');
                @unlink(BASE_UPLOAD_PATH. '/' . ATTACH_GOODS_CLASS . '/' . $nav_info['cn_pic']);
                $dir_name=BASE_UPLOAD_PATH. '/' . ATTACH_GOODS_CLASS.'/';
                $file_name = date('YmdHis') . rand(10000, 99999);
                $result = $upload->rule('uniqid')->validate(['ext' => 'jpg,png,gif.jpeg'])->move($dir_name, $file_name);

                $update['cn_pic'] = $result->getFilename();
            }
            if (!empty($_FILES['adv1']['name'])) {//上传广告图片
                @unlink(BASE_UPLOAD_PATH. '/' . ATTACH_GOODS_CLASS . '/' . $nav_info['cn_adv1']);
                $upload=request()->file('adv1');
                $dir_name=BASE_UPLOAD_PATH. '/' . ATTACH_GOODS_CLASS.'/';
                $file_name = date('YmdHis') . rand(10000, 99999);
                $result = $upload->rule('uniqid')->validate(['ext' => 'jpg,png,gif.jpeg'])->move($dir_name, $file_name);
                $update['cn_adv1'] = $result->getFilename();
            }
            if (!empty($_FILES['adv2']['name'])) {//上传广告图片
                @unlink(BASE_UPLOAD_PATH.'/' . ATTACH_GOODS_CLASS . '/' . $nav_info['cn_adv2']);
                $upload=request()->file('adv2');
                $dir_name=BASE_UPLOAD_PATH. '/' . ATTACH_GOODS_CLASS.'/';
                $file_name = date('YmdHis') . rand(10000, 99999);
                $result = $upload->rule('uniqid')->validate(['ext' => 'jpg,png,gif.jpeg'])->move($dir_name, $file_name);
                $update['cn_adv2'] = $result->getFilename();
            }
            if (empty($nav_info)) {
                $result = $model_class_nav->addGoodsClassNav($update);
            } else {
                $result = $model_class_nav->editGoodsClassNav($update, $gc_id);
            }
            if ($result) {
                $this->log('编辑分类导航，' . $class_info['gc_name'], 1);
                $this->success('编辑成功');
            } else {
                $this->log('编辑分类导航，' . $class_info['gc_name'], 0);
                $this->success('编辑成功');
            }
        }

        $pic_name = BASE_UPLOAD_PATH. '/' . ATTACH_GOODS_CLASS . '/' . $nav_info['cn_pic'];
        if (file_exists($pic_name)) {
            $nav_info['cn_pic'] = UPLOAD_SITE_URL . '/' . ATTACH_GOODS_CLASS . '/' . $nav_info['cn_pic'];
        }
        $pic_name = BASE_UPLOAD_PATH. '/' . ATTACH_GOODS_CLASS . '/' . $nav_info['cn_adv1'];
        if (file_exists($pic_name)) {
            $nav_info['cn_adv1'] = UPLOAD_SITE_URL . '/' . ATTACH_GOODS_CLASS . '/' . $nav_info['cn_adv1'];
        }
        $pic_name = BASE_UPLOAD_PATH. '/' . ATTACH_GOODS_CLASS . '/' . $nav_info['cn_adv2'];
        if (file_exists($pic_name)) {
            $nav_info['cn_adv2'] = UPLOAD_SITE_URL . '/' . ATTACH_GOODS_CLASS . '/' . $nav_info['cn_adv2'];
        }
        $nav_info['cn_classids'] = isset($nav_info['cn_classids'])?explode(',', $nav_info['cn_classids']):'';
        $nav_info['cn_brandids'] = isset($nav_info['cn_brandids'])?explode(',', $nav_info['cn_brandids']):'';

        $this->assign('nav_info', $nav_info);
        $this->assign('class_info', $class_info);
        // 一级分类列表
        $gc_list = $model_goods->getGoodsClassListByParentId(0);
        $this->assign('gc_list', $gc_list);

        // 全部三级分类
        $third_class = $model_goods->getChildClassByFirstId($gc_id);
        $this->assign('third_class', $third_class);

        // 品牌列表
        $model_brand = Model('brand');
        $brand_list = $model_brand->getBrandPassedList(array());

        $b_list = array();
        if (is_array($brand_list) && !empty($brand_list)) {
            foreach ($brand_list as $k => $val) {
                $b_list[$val['gc_id']]['brand'][$k] = $val;
                $b_list[$val['gc_id']]['name'] = $val['brand_class'] == '' ? lang('ds_default') : $val['brand_class'];
            }
        }
        ksort($b_list);
        $this->assign('brand_list', $b_list);

        return $this->fetch('nav_edit');
    }

    /**
     * ajax操作
     */
    public function ajax() {
        $branch = input('param.branch');

        switch ($branch) {
            /**
             * 更新分类
             */
            case 'goods_class_name':
                $model_class = model('Teachtype');
                $class_array = $model_class->getGoodsClassInfoById(intval($_GET['id']));

                $condition['gc_name'] = trim($_GET['value']);
                $condition['gc_parent_id'] = $class_array['gc_parent_id'];
                $condition['gc_id'] = array('neq' => intval($_GET['id']));
                $class_list = $model_class->getGoodsClassList($condition);
                if (empty($class_list)) {
                    $where = array('gc_id' => intval($_GET['id']));
                    $update_array = array();
                    $update_array['gc_name'] = trim($_GET['value']);
                    $model_class->editGoodsClass($update_array, $where);
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
            /**
             * 分类 排序 显示 设置
             */
            case 'goods_class_sort':
            case 'goods_class_show':
            case 'goods_class_index_show':
                $model_class = model('Teachtype');
                $where = array('gc_id' => intval($_GET['id']));
                $update_array = array();
                $update_array[$_GET['column']] = $_GET['value'];
                $model_class->editGoodsClass($update_array, $where);
                echo 'true';
                exit;
                break;
            /**
             * 添加、修改操作中 检测类别名称是否有重复
             */
            case 'check_class_name':
                $model_class = model('Teachtype');
                $condition['gc_name'] = trim(input('get.gc_name'));
                $condition['gc_parent_id'] = intval(input('get.gc_parent_id'));
                $condition['gc_id'] = array('neq', intval(input('get.gc_id')));
                $class_list = $model_class->getGoodsClassList($condition);
                if (empty($class_list)) {
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
            /**
             * TAG值编辑
             */
            case 'goods_class_tag_value':
                $model_class_tag = Model('goods_class_tag');
                $update_array = array();
                $update_array['gc_tag_id'] = intval($_GET['id']);
                /**
                 * 转码  防止GBK下用中文逗号截取不正确
                 */
                $comma = '，';
                $update_array[$_GET['column']] = trim(str_replace($comma, ',', $_GET['value']));
                $model_class_tag->updateTag($update_array);
                echo 'true';
                exit;
                break;
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
                'url' => url('Admin/Teachtype/index')
            ),
        );
        if(session('admin_is_super') ==1 || in_array(1,$this->action )){
            if (request()->action() == 'type_class_add' || request()->action() == 'index') {
                $menu_array[] = array(
                    'name' => 'type_class_add',
                    'text' => '新增',
                    'url' => url('Admin/Teachtype/type_class_add')
                );
            }
        }
        if (request()->action() == 'type_class_edit') {
            $menu_array[] = array(
                'name' => 'type_class_edit',
                'text' => '编辑',
                'url' => url('Admin/Teachtype/type_class_edit')
            );
        }
        return $menu_array;
    }

}

?>
