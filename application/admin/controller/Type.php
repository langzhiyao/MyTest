<?php

/*
 * 类型管理
 */

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Type extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/type.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    public function index() {
        if(session('admin_is_super') !=1 && !in_array('4',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $type_list = db('type')->paginate(10,false,['query' => request()->param()]);
        // 获取分页显示
        $page = $type_list->render();
        $this->assign('type_list', $type_list);
        $this->assign('page', $page);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    /*
     * 新增类型
     */

    public function type_add() {
        if(session('admin_is_super') !=1 && !in_array('1',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        if (!(request()->isPost())) {
            $type = [
                'type_name' => '',
                'type_sort' => 0,
                'class_id' => 0,
                'class_name' => '',
            ];
            $this->assign('type', $type);
            //设置类型关联的分类
            $gc_list = model('goodsclass')->getGoodsClassListByParentId(0);
            $this->assign('gc_list', $gc_list);
            //获取所有品牌
            $brand_list = db('brand')->select();
            $b_list = array();
            if (is_array($brand_list) && !empty($brand_list)) {
                foreach ($brand_list as $k => $val) {
                    $b_list[$val['gc_id']]['brand'][$k] = $val;
                    $b_list[$val['gc_id']]['name'] = $val['brand_class'];
                }
            }
            ksort($b_list);
            $this->assign('brand_list', $b_list);
            //根据相同分类检索出对应的规格

            $spec_list = db('spec')->select();
            $s_list = array();
            if (is_array($spec_list) && !empty($spec_list)) {
                foreach ($spec_list as $k => $val) {
                    $s_list[$val['gc_id']]['spec'][$k] = $val;
                    $s_list[$val['gc_id']]['name'] = $val['gc_name'];
                }
            }
            ksort($s_list);
            $this->assign('spec_list', $s_list);
            $this->setAdminCurItem('type_add');
            return $this->fetch('type_form');
        } else {


            $data = array(
                'type_name' => input('post.type_name'),
                'type_sort' => input('post.type_sort'),
                'class_id' => input('post.class_id'),
                'class_name' => input('post.class_name'),
            );
            //验证数据  BEGIN
            $rule = [
                ['type_name', 'require', '类别名称为必填'],
                ['type_sort', 'require|number', '类别排序为必填|类别排序必须为数字'],
                ['class_id', 'require|number', '分类为必填|分类ID必须为数字'],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($data);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END
            //添加类型
            $type_id = db('type')->insertGetId($data);
            if (empty($type_id)) {
                $this->error('添加失败');
            }

            //添加类型与品牌对应
            if (!empty($_POST['brand_id'])) {
                $brand_array = $_POST['brand_id'];
                if (is_array($brand_array)) {
                    foreach ($brand_array as $brand_id) {
                        $typebrand[] = array('type_id' => $type_id, 'brand_id' => $brand_id);
                    }
                    db('typebrand')->insertAll($typebrand);
                }
            }
            //添加类型与规格对应
            if (!empty($_POST['spec_id'])) {
                $spec_array = $_POST['spec_id'];
                if (is_array($spec_array)) {
                    foreach ($spec_array as $sp_id) {
                        $typespec[] = array('type_id' => $type_id, 'sp_id' => $sp_id);
                    }
                    db('typespec')->insertAll($typespec);
                }
            }

            //添加类型属性
            if (!empty($_POST['at_value'])) {
                $attribute_array = $_POST['at_value'];
                foreach ($attribute_array as $v) {
                    if ($v['value'] != '') {
                        //属性值
                        //添加属性
                        $attr_array = array();
                        $attr_array['attr_name'] = $v['name'];
                        $attr_array['attr_value'] = $v['value'];
                        $attr_array['type_id'] = $type_id;
                        $attr_array['attr_sort'] = $v['sort'];
                        $attr_array['attr_show'] = $v['show'] == "on" ? 1 : 0;
                        $attr_id = db('attribute')->insertGetId($attr_array);
                        if (!$attr_id) {
                            $this->error("添加属性失败");
                        }
                        //添加属性值
                        $attr_value = explode(',', $v['value']);
                        if (!empty($attr_value)) {
                            $attr_array = array();
                            foreach ($attr_value as $val) {
                                $tpl_array = array();
                                $tpl_array['attr_value_name'] = $val;
                                $tpl_array['attr_id'] = $attr_id;
                                $tpl_array['type_id'] = $type_id;
                                $tpl_array['attr_value_sort'] = 0;
                                $attr_array[] = $tpl_array;
                            }
                            $return = db('attributevalue')->insertAll($attr_array);
                            if (!$return) {
                                $this->error("添加属性失败");
                            }
                        }
                    }
                }
            }
            $this->success(lang('ds_common_op_succ'), 'Type/index');
        }
    }

    public function type_edit() {
        if(session('admin_is_super') !=1 && !in_array('3',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $type_id = input('param.type_id');
        if (empty($type_id)) {
            $this->error(lang('empty_error'));
        }
        if (!(request()->isPost())) {
            $type = db('type')->where(array('type_id' => $type_id))->find();
            $this->assign('type', $type);
            //设置类型关联的分类
            $gc_list = model('goodsclass')->getGoodsClassListByParentId(0);
            $this->assign('gc_list', $gc_list);
            //获取所有品牌
            $brand_list = db('brand')->select();
            $b_list = array();
            if (is_array($brand_list) && !empty($brand_list)) {
                foreach ($brand_list as $k => $val) {
                    $b_list[$val['gc_id']]['brand'][$k] = $val;
                    $b_list[$val['gc_id']]['name'] = $val['brand_class'];
                }
            }
            ksort($b_list);
            $this->assign('brand_list', $b_list);
            //类型与品牌关联列表
            $brand_related = db('typebrand')->where(array('type_id' => $type_id))->select();
            $b_related = array();
            if (is_array($brand_related) && !empty($brand_related)) {
                foreach ($brand_related as $val) {
                    $b_related[] = $val['brand_id'];
                }
            }
            unset($brand_related);
            $this->assign('brang_related', $b_related);
            //根据相同分类检索出对应的规格
            $spec_list = db('spec')->select();
            $s_list = array();
            if (is_array($spec_list) && !empty($spec_list)) {
                foreach ($spec_list as $k => $val) {
                    $s_list[$val['gc_id']]['spec'][$k] = $val;
                    $s_list[$val['gc_id']]['name'] = $val['gc_name'];
                }
            }
            ksort($s_list);
            $this->assign('spec_list', $s_list);
            //规格关联列表
            $spec_related = db('typespec')->where(array('type_id' => $type_id))->select();
            $sp_related = array();
            if (is_array($spec_related) && !empty($spec_related)) {
                foreach ($spec_related as $val) {
                    $sp_related[] = $val['sp_id'];
                }
            }
            unset($spec_related);
            $this->assign('spec_related', $sp_related);
            //属性
            $attr_list = db('attribute')->where(array('type_id' => $type_id))->select();
            $this->assign('attr_list', $attr_list);
            $this->setAdminCurItem('type_edit');
            return $this->fetch();
        } else {

            $data = array(
                'type_name' => input('post.type_name'),
                'type_sort' => input('post.type_sort'),
                'class_id' => input('post.class_id'),
                'class_name' => input('post.class_name'),
            );
            //验证数据  BEGIN
            $rule = [
                ['type_name', 'require', '类别名称为必填'],
                ['type_sort', 'require|number', '类别排序为必填|类别排序必须为数字'],
                ['class_id', 'require|number', '分类为必填|分类ID必须为数字'],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($data);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END
            //更新前删除对应类型与品牌关联
            db('typebrand')->where(array('type_id' => $type_id))->delete();
            //添加类型与品牌对应
            if (!empty($_POST['brand_id'])) {
                $brand_array = $_POST['brand_id'];
                if (is_array($brand_array)) {
                    foreach ($brand_array as $brand_id) {
                        $typebrand[] = array('type_id' => $type_id, 'brand_id' => $brand_id);
                    }
                    db('typebrand')->insertAll($typebrand);
                }
            }
            //添加类型与规格对应
            //更新前删除对应类型与规格关联
            db('typespec')->where(array('type_id' => $type_id))->delete();
            if (!empty($_POST['spec_id'])) {
                $spec_array = $_POST['spec_id'];
                if (is_array($spec_array)) {
                    foreach ($spec_array as $sp_id) {
                        $typespec[] = array('type_id' => $type_id, 'sp_id' => $sp_id);
                    }
                    db('typespec')->insertAll($typespec);
                }
            }

            //添加类型属性
            if (!empty($_POST['at_value'])) {
                $attribute_array = $_POST['at_value'];
                foreach ($attribute_array as $v) {

                    // 要删除的属性id
                    $del_array = array();
                    if (!empty($_POST['a_del'])) {
                        $del_array = $_POST['a_del'];
                    }

                    if (isset($v['attr_id']) && !in_array($v['attr_id'], $del_array)) {
                        //原属性修改
                        $attr_array = array();
                        $attr_array['attr_name'] = $v['name'];
                        $attr_array['type_id'] = $type_id;
                        $attr_array['attr_sort'] = $v['sort'];
                        $attr_array['attr_show'] = $v['show'] == "on" ? 1 : 0;

                        $return = db('attribute')->where(array('type_id' => $type_id, 'attr_id' => intval($v['attr_id'])))->update($attr_array);
//                        if (!$return) {
//                            $this->error("更新类型属性失败");
//                        }
                    } else if (!isset($v['form_submit'])) {
                        //添加新属性
                        if ($v['value'] != '') {
                            //属性值
                            //添加属性
                            $attr_array = array();
                            $attr_array['attr_name'] = $v['name'];
                            $attr_array['attr_value'] = $v['value'];
                            $attr_array['type_id'] = $type_id;
                            $attr_array['attr_sort'] = $v['sort'];
                            $attr_array['attr_show'] = $v['show'] == "on" ? 1 : 0;
                            $attr_id = db('attribute')->insertGetId($attr_array);
                            if (!$attr_id) {
                                $this->error("添加属性失败");
                            }
                            //添加属性值
                            $attr_value = explode(',', $v['value']);
                            if (!empty($attr_value)) {
                                $attr_array = array();
                                foreach ($attr_value as $val) {
                                    $tpl_array = array();
                                    $tpl_array['attr_value_name'] = $val;
                                    $tpl_array['attr_id'] = $attr_id;
                                    $tpl_array['type_id'] = $type_id;
                                    $tpl_array['attr_value_sort'] = 0;
                                    $attr_array[] = $tpl_array;
                                }
                                $return = db('attributevalue')->insertAll($attr_array);
                                if (!$return) {
                                    $this->error("添加属性值失败");
                                }
                            }
                        }
                    }
                }

                // 删除属性
                $del_array = array();
                if (!empty($_POST['a_del'])) {
                    $del_array = $_POST['a_del'];
                    foreach ($del_array as $key => $del_id) {
                        db('attribute')->where(array('attr_id' => $del_id))->delete();
                        db('attributevalue')->where(array('attr_id' => $del_id))->delete();
                    }
                }

                //更新属性信息
                $type_array = array();
                $type_array['type_name'] = trim($_POST['type_name']);
                $type_array['type_sort'] = trim($_POST['type_sort']);
                $type_array['class_id'] = $_POST['class_id'];
                $type_array['class_name'] = $_POST['class_name'];
                $return = db('type')->where(array('type_id' => $type_id))->update($type_array);
            }
            $this->success(lang('ds_common_op_succ'), 'Type/index');
        }
    }

    public function attr_edit() {
        $attr_id = input('param.attr_id');
        if (empty($attr_id)) {
            $this->error(lang('empty_error'));
        }
        if (!(request()->isPost())) {
            $attribute = db('attribute')->where(array('attr_id' => $attr_id))->find();
            $this->assign('attribute', $attribute);
            $attributevalue_list = db('attributevalue')->where(array('attr_id' => $attr_id))->select();
            $this->assign('attributevalue_list', $attributevalue_list);
            $this->setAdminCurItem('attr_edit');
            return $this->fetch();
        } else {
            $data = array(
                'attr_name' => input('post.attr_name'),
                'type_id' => input('post.type_id'),
                'attr_show' => input('post.attr_show'),
                'attr_sort' => input('post.attr_sort'),
            );
            //验证数据  BEGIN
            $rule = [
                ['attr_name', 'require', '属性名称为必填'],
                ['type_id', 'require|number', '类型ID为必填|类型ID必须为数字'],
                ['attr_show', 'require|number', '属性是否显示为必填|属性是否显示必须为数字'],
                ['attr_sort', 'require|number', '属性排序为必填|属性排序必须为数字'],
            ];
            $validate = new Validate($rule);
            $validate_result = $validate->check($data);
            if (!$validate_result) {
                $this->error($validate->getError());
            }
            //验证数据  END
            $del_array = array();
            if (!empty($_POST['attr_del'])) {
                $del_array = $_POST['attr_del'];
            }
            //更新属性值表
            $attr_value = $_POST['attr_value'];
            $attr_array = array();
            // 要删除的属性值id
            $del_array = array();
            if (!empty($_POST['attr_del'])) {
                $del_array = $_POST['attr_del'];
            }
            if (!empty($attr_value) && is_array($attr_value)) {
                foreach ($attr_value as $key => $val) {

                    if (isset($val['form_submit']) && !in_array(intval($key), $del_array)) {  // 属性已修改
                        $update = array();
                        $update['attr_value_name'] = $val['name'];
                        $update['attr_value_sort'] = intval($val['sort']);
                        db('attributevalue')->where(array('attr_value_id' => intval($key)))->update($update);

                        $attr_array[] = $val['name'];
                    } else if (!isset($val['form_submit'])) {

                        $insert = array();
                        $insert['attr_value_name'] = $val['name'];
                        $insert['attr_id'] = $attr_id;
                        $insert['type_id'] = input('post.type_id');
                        $insert['attr_value_sort'] = intval($val['sort']);
                        db('attributevalue')->insert($insert);

                        $attr_array[] = $val['name'];
                    }
                }
                // 删除属性
                if ($del_array) {
                    foreach ($del_array as $key => $value) {
                        db('attributevalue')->delete($value);
                    }
                }
            }

            //更新属性
            $data['attr_value'] = implode(',', $attr_array);
            db('attribute')->where(array('attr_id' => $attr_id))->update($data);
            // 不需要返回
            $this->success(lang('ds_common_op_succ'), 'Type/index');
        }
    }

    /*
     * 删除类型
     */

    public function type_drop() {
        if(session('admin_is_super') !=1 && !in_array('2',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $type_id = input('param.type_id');
        if (empty($type_id)) {
            $this->error(lang('empty_error'));
        }

        //更新前删除对应类型与品牌关联
        db('typebrand')->where(array('type_id' => $type_id))->delete();
        //更新前删除对应类型与规格关联
        db('typespec')->where(array('type_id' => $type_id))->delete();
        //删除属性值
        db('attribute')->where(array('type_id' => $type_id))->delete();
        //删除属性
        db('attributevalue')->where(array('type_id' => $type_id))->delete();
        //删除类型
        db('type')->where(array('type_id' => $type_id))->delete();

        $this->success(lang('ds_common_op_succ'), 'Type/index');
    }
    
    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '管理',
                'url' => url('Admin/Type/index')
            ),
        );
        if(session('admin_is_super') ==1 || in_array('1',$this->action)){
            if (request()->action() == 'type_add' || request()->action() == 'index') {
                $menu_array[] = array(
                    'name' => 'type_add',
                    'text' => '新增类型',
                    'url' => url('Admin/Type/type_add')
                );
            }
        }
        if(session('admin_is_super') ==1 || in_array('3',$this->action)){
            if (request()->action() == 'type_edit') {
                $menu_array[] = array(
                    'name' => 'type_edit',
                    'text' => '编辑类型',
                    'url' => url('Admin/Member/type_edit')
                );
            }
        }


        if (request()->action() == 'attr_edit') {
            $menu_array[] = array(
                'name' => 'type_edit',
                'text' => '编辑属性',
                'url' => url('Admin/Member/attr_edit')
            );
        }
        return $menu_array;
    }
}

?>
