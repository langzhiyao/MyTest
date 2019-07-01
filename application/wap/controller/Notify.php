<?php

namespace app\wap\controller;

use think\captcha\Captcha;

class Notify {


    /**
     * app消息通知
     */
    public function notify() {
        $model_notify = Model('document');
        $logindata = $model_notify->getList();

        foreach ($logindata as $k=>$v){
            $logindata[$k]['doc_time'] = date("Y-m-d H:i:s",$v['doc_time']);
        }

        if($logindata){
            output_data($logindata);
        }else{
            output_error('操作有误');
        }


    }

    /**
     * app消息通知详情
     * id 消息id
     */
    public function detail() {
        $id  = intval(input('post.doc_id'));
        if (empty($id)) {
            output_error('参数有误');
        }
        $model_notify = Model('document');
        $logindata = $model_notify->getOneById($id);

        $logindata['doc_time'] = date("Y-m-d H:i:s",$logindata);


        if($logindata){
            output_data($logindata);
        }else{
            output_data(array());
        }


    }



}