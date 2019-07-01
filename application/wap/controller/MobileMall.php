<?php

namespace app\wap\controller;

class MobileMall extends MobileHome {

    public function _initialize() {
        parent::_initialize();
        if(!config('site_state')) {
            output_error(config('closed_reason'), array('login' => '0'),400);
        }
    }

    protected function getMemberIdIfExists() {
        $key = input('post.key');
        if (empty($key)) {
            $key = input('get.key');
        }

        $model_mb_user_token = Model('mbusertoken');
        $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
        if (empty($mb_user_token_info)) {
            return 0;
        }

        return $mb_user_token_info['member_id'];
    }

}

?>

