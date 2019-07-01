function get_sms_captcha(type) {
    if ($("#sms_mobile").val().length == 11) {
        var ajaxurl = SITE_URL+'/index.php/Home/Connectsms/get_captcha.html?type=' + type;
        ajaxurl += '&sms_mobile=' + $('#sms_mobile').val();
        $.ajax({
            type: "GET",
            url: ajaxurl,
            async: false,
            success: function(rs) {
                if (rs == 'true') {
                    showSucc('短信动态码已发出');
                } else {
                    showError(rs);
                }
            }
        });
    }else{
        showError('请先输入正确手机号');
    }
}
function check_captcha() {
    if ($("#sms_mobile").val().length == 11 && $("#sms_captcha").val().length == 6) {
        var ajaxurl = 'index.php?act=connect_sms&op=check_captcha';
        ajaxurl += '&sms_captcha=' + $('#sms_captcha').val() + '&sms_mobile=' + $('#sms_mobile').val();
        $.ajax({
            type: "GET",
            url: ajaxurl,
            async: false,
            success: function(rs) {
                if (rs == 'true') {
                    $.getScript('index.php?act=connect_sms&op=register' + '&sms_mobile=' + $('#sms_mobile').val());
                    $("#register_sms_form").show();
                    $("#post_form").hide();
                } else {
                    showError(rs);
                }
            }
        });
    }
}