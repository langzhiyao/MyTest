$(function() {
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/tmpl/member/index.html";
        return
    }
    $.ajax({type: "get", url: ApiUrl + "/Memberaccount/modify_paypwd_step4.html", data: {key: e}, dataType: "json", success: function(e) {
            if (e.code != 200) {
                errorTipsShow("<p>权限不足或操作超时</p>");
                setTimeout("location.href = WapSiteUrl+'/tmpl/member/member_paypwd_step1.html'", 2e3)
            }
        }});
    $.sValid.init({rules: {password: {required: true, minlength: 6, maxlength: 20}, password1: {required: true, equalTo: "#password"}}, messages: {password: {required: "请填写支付密码", minlength: "请正确填写支付密码", maxlength: "请正确填写支付密码"}, password1: {required: "请填写确认密码", equalTo: "两次密码输入不一致"}}, callback: function(e, r, a) {
            if (e.length > 0) {
                var t = "";
                $.map(r, function(e, r) {
                    t += "<p>" + e + "</p>"
                });
                errorTipsShow(t)
            } else {
                errorTipsHide()
            }
        }});
    $("#nextform").click(function() {
        if (!$(this).parent().hasClass("ok")) {
            return false
        }
        if ($.sValid()) {
            var r = $.trim($("#password").val());
            var a = $.trim($("#password1").val());
            $.ajax({type: "post", url: ApiUrl + "/Memberaccount/modify_paypwd_step5.html", data: {key: e, password: r, password1: a}, dataType: "json", success: function(e) {
                    if (e.code == 200) {
                        $.sDialog({skin: "block", content: "支付密码设置成功", okBtn: false, cancelBtn: false});
                        setTimeout("location.href = WapSiteUrl+'/tmpl/member/member_account.html'", 2e3)
                    } else {
                        errorTipsShow("<p>" + e.message + "</p>")
                    }
                }})
        }
    })
});