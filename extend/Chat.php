<?php

class Chat
{

    public static function getChatHtml(){
        $web_html = '';

            $avatar = getMemberAvatar(session('avatar'));
            $store_avatar = getStoreLogo(session('store_avatar'));

            $app_url = BASE_SITE_URL;
            $chat_url = CHAT_SITE_URL;
            $node_url = config('node_site_url');
            $shop_url = SHOP_SITE_URL;
            $goods_id = intval(input('goods_id'));
            $member_id=session('member_id');
            $member_name=session('member_name');
            $store_id=session('store_id');
            $store_name=session('store_name');
            $seller_smt_limits=session('seller_smt_limits');
            $seller_id=session('seller_id');
            $seller_name=session('seller_name');
            $seller_is_admin=session('seller_is_admin');
            $con=request()->controller();
            $act=request()->action();

            $web_html = <<<EOT
			<link href="{$chat_url}/css/chat.css" rel="stylesheet" type="text/css">
			<div style="clear: both;"></div>
			<div id="web_chat_dialog" style="display: none;float:right;">
					</div>
			<a id="chat_login" href="javascript:void(0)" style="display: none;"></a>
			<script type="text/javascript">
			var APP_SITE_URL = '{$app_url}';
                    var CHAT_SITE_URL = '{$chat_url}';
                    var SHOP_SITE_URL = '{$shop_url}';
                    var connect_url = "{$node_url}";
                    var layout ="{$con}";

                   
                    var act_op = "{$con}_{$act}";
                    var chat_goods_id = "{$goods_id}";
                    var user = {};

                    user['u_id'] = "{$member_id}";
                    user['u_name'] = "{$member_name}";
                    user['s_id'] = "{$store_id}";
                    user['s_name'] = "{$store_name}";
                    user['s_avatar'] = "{$store_avatar}";
                    user['avatar'] = "{$avatar}";

                    $("#chat_login").nc_login({
                    
                    });
                    </script>
EOT;

                $web_html .= '<link href="' . BASE_SITE_URL . 'static/plugins/perfect-scrollbar.min.css" rel="stylesheet" type="text/css">';
                $web_html .= '<script type="text/javascript" src="'.BASE_SITE_URL . 'static/plugins/perfect-scrollbar.min.js"></script>';
                $web_html .= '<script type="text/javascript" src="'.BASE_SITE_URL . 'static/plugins/jquery.mousewheel.js"></script>';

            $web_html .= '<script type="text/javascript" src="'.BASE_SITE_URL . 'static/plugins/jquery.charCount.js" charset="utf-8"></script>';
            $web_html .= '<script type="text/javascript" src="'.BASE_SITE_URL . 'static/plugins/jquery.smilies.js" charset="utf-8"></script>';
            $web_html .= '<script type="text/javascript" src="'.BASE_SITE_URL . 'static/chat/js/user.js" charset="utf-8"></script>';


if($con =='store') {
    $web_html .= '<script type="text/javascript" src="' . BASE_SITE_URL . 'static/chat/js/store.js" charset="utf-8"></script>';
    $seller_smt_limits = '';
    if ($seller_smt_limits && is_array($seller_smt_limits)) {
        $seller_smt_limits = implode(',', $seller_smt_limits);
    }
    $web_html .= <<<EOT
					<script type="text/javascript">
					user['seller_id'] = "{$seller_id}";
					user['seller_name'] = "{$seller_name}";
					user['seller_is_admin'] = "{$seller_is_admin}";
					var smt_limits = "{$seller_smt_limits}";
					</script>
EOT;
}

        return $web_html;
    }
}