<?php

namespace app\home\controller;

use think\Lang;

class Sellerevaluate extends BaseSeller {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/zh-cn/memberevaluate.lang.php');
    }

    /**
     * 评价列表
     */
    public function index() {
        $model_evaluate_goods = Model('evaluategoods');

        $condition = array();
        
        $goods_name = input('post.goods_name');
        if (!empty($goods_name)) {
            $condition['geval_goodsname'] = array('like', '%' . $goods_name . '%');
        }
        $member_name = input('post.member_name');
        if (!empty($member_name)) {
            $condition['geval_frommembername'] = array('like', '%' . $member_name . '%');
        }
        $condition['geval_storeid'] = session('store_id');
        $goodsevallist = $model_evaluate_goods->getEvaluateGoodsList($condition, 1, 'geval_id desc');

        $this->assign('show_page',$model_evaluate_goods->page_info->render());
        $this->assign('goodsevallist', $goodsevallist);

        /* 设置卖家当前菜单 */
        $this->setSellerCurMenu('sellerevaluate');
        /* 设置卖家当前栏目 */
        $this->setSellerCurItem('index');
        return $this->fetch($this->template_dir.'index');
    }

    /**
     * 解释来自买家的评价
     */
    public function explain_save() {
        $geval_id = intval($_POST['geval_id']);
        $geval_explain = trim($_POST['geval_explain']);
        //验证表单
        if (!$geval_explain) {
            $data['result'] = false;
            $data['message'] = '解释内容不能为空';
            echo json_encode($data);
            die;
        }
        $data = array();
        $data['result'] = true;

        $model_evaluate_goods = Model('evaluategoods');

        $evaluate_info = $model_evaluate_goods->getEvaluateGoodsInfoByID($geval_id, session('store_id'));
        if (empty($evaluate_info)) {
            $data['result'] = false;
            $data['message'] = lang('param_error');
            echo json_encode($data);
            die;
        }

        $update = array('geval_explain' => $geval_explain);
        $condition = array('geval_id' => $geval_id);
        $result = $model_evaluate_goods->editEvaluateGoods($update, $condition);

        if ($result) {
            $data['message'] = '解释成功';
        } else {
            $data['result'] = false;
            $data['message'] = '解释保存失败';
        }
        echo json_encode($data);
        die;
    }

    protected function getSellerItemList()
    {
        $menu_array=array(
            array('name'=>'index','text'=>'买家评价','url'=>'##')
        );
        return $menu_array;
    }

}

?>
