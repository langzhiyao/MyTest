<?php
/**
 * 商品管理
 */
namespace app\admin\controller;

use think\View;
use think\Url;
use think\Lang;
use think\Request;
use think\Db;
use think\Validate;

class Goods extends AdminControl
{
    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/goods.lang.php');
        //获取当前角色对当前子目录的权限
        $class_name=explode('\\',__CLASS__);
        $class_name = strtolower(end($class_name));
        $perm_id = $this->get_permid($class_name);
        $this->action = $action = $this->get_role_perms(session('admin_gid') ,$perm_id);
        $this->assign('action',$action);
    }

    public function index()
    {
        if(session('admin_is_super') !=1 && !in_array('4',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        //待添加筛选条件
        $condition=array();
        $goods_name= input('goods_name');
        $type=input('param.type')?input('param.type'):'index';
        if(!empty($goods_name)){
            $condition['goods_name'] = array('like', '%'. "$goods_name" .'%');
        }
        if($type == 'lockup'){
            //下架商品
            $condition['goods_state']   = 10;
            $condition['goods_verify']  = 1;
        }
        if ($type== 'waitverify'){
            //等待审核商品
            $condition['goods_verify']  = array('neq',1);
        }

        $goods_list = db('goodscommon')->where($condition)->paginate(10,false,['query' => request()->param()]);

        $this->assign('goods_list', $goods_list);
        //计算商品库存
        $goods_storage = $this->goods_storage($goods_list);
        //halt($goods_storage);
        $this->assign('storage', $goods_storage);
        $page = $goods_list->render();
        $this->assign('page', $page);
        $this->assign('type',$type);
        $this->setAdminCurItem($type);
        return $this->fetch();
    }
    /**
     * 计算商品库存
     */
    public function goods_storage($goods_list)
    {
        // 计算库存
        $storage_array=array();
        if (!empty($goods_list)) {
            foreach ($goods_list as $value) {
                $storage_array[$value['goods_commonid']]['goods_storage'] = db('goods')->where('goods_commonid',$value['goods_commonid'])->sum('goods_storage');
                $storage_array[$value['goods_commonid']][]=db('goods')->where('goods_commonid',$value['goods_commonid'])->field('goods_id')->find();
            }
            return $storage_array;
        } else {
            return false;
        }
    }
    
    
    //ajax获取同一个commonid下面的商品信息
    public function ajax_goodslist()
    {
        $common_id = $_GET['commonid'];
        if (empty($common_id)) {
            $this->error(lang('empty_error'));
        }
        $map['goods_commonid'] = $common_id;
        $common_info = db('goodscommon')->where($map)->field('spec_name')->find();
        $goods_list = db('goods')->where($map)->select();
        //halt($goods_list);
        $spec_name = array_values((array)unserialize($common_info['spec_name']));
        foreach ($goods_list as $key => $val) {
            $goods_spec = array_values((array)unserialize($val['goods_spec']));
            $spec_array = array();
            foreach ($goods_spec as $k => $v) {
                $spec_array[] = '<div class="goods_spec">' . $spec_name[$k].':' . '<em title="' . $v . '">' . $v . '</em>' . '</div>';
            }
            $goods_list[$key]['goods_image'] = cthumb($val['goods_image']);
            $goods_list[$key]['goods_spec'] = implode('', $spec_array);
            $goods_list[$key]['url'] = url('Home/Goods/index', array('goods_id' => $val['goods_id']));
        }
        return json_encode($goods_list);
    }

    /**
     * 违规下架
     */
    public function goods_lockup() {
        if(session('admin_is_super') !=1 && !in_array('16',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        if (request()->isPost()) {
            $commonids = $_POST['commonids'];
            $commonid_array = explode(',', $commonids);
            foreach ($commonid_array as $value) {
                if (!is_numeric($value)) {
                    showDialog(lang('ds_common_op_fail'), 'reload');
                }
            }
            $update = array();
            $update['goods_stateremark'] = trim($_POST['close_reason']);

            $where = array();
            $where['goods_commonid'] = array('in', $commonid_array);

            Model('goods')->editProducesLockUp($update, $where);
            showDialog(lang('ds_common_op_succ'), 'reload', 'succ');
        }
        $this->assign('commonids', input('commonid'));
        echo $this->fetch('close_remark');
    }

    /**
     * 删除商品
     */
    public function goods_del() {
        if(session('admin_is_super') !=1 && !in_array('2',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        $common_id = intval(input('common_id'));
        if ($common_id <= 0) {
            $this->error(lang('ds_common_op_fail'));
        }
        Model('goods')->delGoodsAll(array('goods_commonid' => $common_id));
        $this->success(lang('ds_common_op_succ'));
    }

    /**
     * 审核商品
     */
    public function goods_verify(){
        if(session('admin_is_super') !=1 && !in_array('15',$this->action)){
            $this->error(lang('ds_assign_right'));
        }
        if (request()->isPost()) {
            $commonids = $_POST['commonids'];
            $commonid_array = explode(',', $commonids);
            foreach ($commonid_array as $value) {
                if (!is_numeric($value)) {
                    showDialog(lang('ds_common_op_fail'), 'reload');
                }
            }
            $update2 = array();
            $update2['goods_verify'] = intval($_POST['verify_state']);

            $update1 = array();
            $update1['goods_verifyremark'] = trim($_POST['verify_reason']);
            $update1 = array_merge($update1, $update2);
            $where = array();
            $where['goods_commonid'] = array('in', $commonid_array);

            $model_goods = Model('goods');
            if (intval($_POST['verify_state']) == 0) {
                $model_goods->editProducesVerifyFail($where, $update1, $update2);
            } else {
                $model_goods->editProduces($where, $update1, $update2);
            }
            showDialog(lang('ds_common_op_succ'), 'reload', 'succ');
        }
        $this->assign('commonids', input('commonid'));
        echo $this->fetch('verify_remark');
    }
    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '所有商品',
                'url' => url('Admin/Goods/index')
            ),
            array(
                'name' => 'lockup',
                'text' => '下架商品',
                'url' => url('Admin/Goods/index',['type'=>'lockup'])
            ),
            array(
                'name' => 'waitverify',
                'text' => '待审核',
                'url' => url('Admin/Goods/index',['type'=>'waitverify'])
            ),
        );
        return $menu_array;
    }
    
}
?>
