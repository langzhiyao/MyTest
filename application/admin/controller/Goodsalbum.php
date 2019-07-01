<?php

/*
 * 空间管理
 */

namespace app\admin\controller;

use think\Lang;
use think\Validate;

class Goodsalbum extends AdminControl {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/zh-cn/album.lang.php');
    }

    /**
     * 相册列表
     */
    public function index() {
        if (request()->isPost()) {
            if (is_array($_POST['aclass_id'])) {
                foreach ($_POST['aclass_id'] as $k => $v) {
                    if (!is_numeric($v)) {
                        unset($_POST['aclass_id'][$k]);
                    }
                }
            }
            if (!empty($_POST['aclass_id'])) {
                $pic = db('albumpic')->field('apic_cover')->where(array('aclass_id' => array('in', $_POST['aclass_id'])))->select();
                if (is_array($pic)) {
                    foreach ($pic as $v) {
                        $this->del_file($v['apic_cover']);
                    }
                }
                db('albumpic')->where(array('aclass_id' => array('in', $_POST['aclass_id'])))->delete();
                db('albumclass')->where(array('aclass_id' => array('in', $_POST['aclass_id'])))->delete();
                $this->log(lang('ds_del,g_album_one') . '[ID:' . implode(',', $_POST['aclass_id']) . ']', 1);
                $this->success(lang('ds_common_del_succ'));
            }
        }
        $condiiton = array();
        $store_name = '';
        if (is_numeric(input('param.keyword'))) {
            $condiiton['store.store_id'] = input('param.keyword');
            $store_name = db('store')->getfby_store_id(input('param.keyword'), 'store_name');
        } elseif (!empty(input('param.keyword'))) {
            $store_name = input('param.keyword');
            $store_id = db('store')->getfby_store_name(input('param.keyword'), 'store_id');
            if (is_numeric($store_id)) {
                $condiiton['store.store_id'] = $store_id;
            } else {
                $condiiton['store.store_id'] = 0;
            }
        }

        $list = db('albumclass')->alias('a')->where($condiiton)->join('__STORE__ s', 'a.store_id=s.store_id', 'LEFT')->field('a.*,s.store_name')->paginate(10,false,['query' => request()->param()]);

        $this->assign('page', $list->render());

        if (is_array($list) && !empty($list)) {
            foreach ($list as $v) {
                $class[] = $v['aclass_id'];
            }
            $where = "array('aclass_id'=>array('in',implode(',',$class)))";
        } else {
            $where = '1=1';
        }
        $count = db('albumpic')->field('aclass_id,count(*) as pcount')->group('aclass_id')->where($where)->select();

        if (is_array($count)) {
            foreach ($count as $v) {
                $pic_count[$v['aclass_id']] = $v['pcount'];
            }
        }
        $this->assign('pic_count', $pic_count);
        $this->assign('list', $list);
        $this->assign('store_name', $store_name);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    /**
     * 图片列表
     */
    public function pic_list() {
        $condiiton = array();
        $store_name = '';
        if (is_numeric(input('param.keyword'))) {
            $condiiton['store_id'] = input('param.keyword');
            $store_name = db('store')->getfby_store_id(input('param.keyword'), 'store_name');
        } elseif ((input('param.keyword'))) {
            $store_name = input('param.keyword');
            $store_id = db('store')->getfby_store_name(input('param.keyword'), 'store_id');
            if (is_numeric($store_id)) {
                $condiiton['store_id'] = $store_id;
            } else {
                $condiiton['store_id'] = 0;
            }
        } elseif (is_numeric(input('param.aclass_id'))) {
            $condiiton['aclass_id'] = input('param.aclass_id');
        }
        $list = db('albumpic')->where($condiiton)->order('apic_id desc')->paginate(10,false,['query' => request()->param()]);
        //halt($list);
        $show_page = $list->render();
        $this->assign('page', $show_page);
        $this->assign('list', $list);
        $this->assign('store_name', $store_name);
        $this->setAdminCurItem('pic_list');
        return $this->fetch();
    }

    /**
     * 删除相册
     */
    public function aclass_del() {
        $aclass_id = intval(input('param.aclass_id'));
        if (!is_numeric($aclass_id)) {
            $this->error(lang('param_error'));
        }
        $pic = db('albumpic')->field('apic_cover')->where(array('aclass_id' => $aclass_id))->select();
        if (is_array($pic)) {
            foreach ($pic as $v) {
                $this->del_file($v['apic_cover']);
            }
        }
        db('albumpic')->where(array('aclass_id' => $aclass_id))->delete();
        db('albumclass')->where(array('aclass_id' => $aclass_id))->delete();
        $this->log(lang('ds_del') . lang('g_album_one') . '[ID:' . intval(input('param.aclass_id')) . ']', 1);
        $this->success(lang('ds_common_del_succ'));
    }

    /**
     * 删除一张图片及其对应记录
     *
     */
    public function del_album_pic() {
        list($apic_id, $filename) = @explode('|', input('param.key'));
        if (!is_numeric($apic_id) || empty($filename))
            exit('0');
        $this->del_file($filename);
        db('albumpic')->where(array('apic_id' => $apic_id))->delete();
        $this->log(lang('ds_del') . lang('g_album_pic_one') . '[ID:' . $apic_id . ']', 1);
        exit('1');
    }

    /**
     * 删除多张图片
     *
     */
    public function del_more_pic() {
        $list = db('albumpic')->where(array('apic_id' => array('in', $_POST['delbox'])))->select();
        if (is_array($list)) {
            foreach ($list as $v) {
                $this->del_file($v['apic_cover']);
            }
        }
        db('albumpic')->where(array('apic_id' => array('in', $_POST['delbox'])))->delete();
        $this->log(lang('ds_del') . lang('g_album_pic_one') . '[ID:' . implode(',', $_POST['delbox']) . ']', 1);
        redirect('Goodsalbum/pic_list');
    }

    /**
     * 删除图片文件
     *
     */
    private function del_file($filename) {
        //取店铺ID
        if (preg_match('/^(\d+_)/', $filename)) {
            $store_id = substr($filename, 0, strpos($filename, '_'));
        } else {
            //$store_id = db('albumpic')->getfby_apic_cover($filename,'store_id');
            $store_id = db('albumpic')->field('store_id')->where('apic_cover', $filename)->select();
        }

        $path = config('url_domain_root') . $filename;

        $ext = strrchr($path, '.');
        $type = array('_tiny', '_small', '_mid', '_max', '_240x240');
        foreach ($type as $v) {
            if (is_file($fpath = $path . $v . $ext)) {
                unlink($fpath);
            }
        }
        if (is_file($path))
            unlink($path);
    }

    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '相册列表',
                'url' => url('Goodsalbum/index')
            ),
            array(
                'name' => 'pic_list',
                'text' => '图片列表',
                'url' => url('Goodsalbum/pic_list')
            ),
        );
        return $menu_array;
    }

}

?>
