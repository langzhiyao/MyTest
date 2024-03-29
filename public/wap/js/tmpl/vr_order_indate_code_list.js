$(function() {
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/tmpl/member/index.html";
        return
    }
    var r = getQueryString("order_id");
    var t = function(e) {
        if (typeof e != "object")
            return !e;
        for (var r in e)
            return false;
        return true
    };
    $.ajax({
        type: "post",
        url: ApiUrl + "/Membervrorder/indate_code_list.html",
        data: {
            key: e,
            order_id: r
        },
        dataType: "json",
        success: function(e) {
            checkLogin(e.login);
            var r = e && e.result || {};
            if (t(r)) {
                r = {}
            }
            if (t(r.code_list)) {
                r.err = r.message || "暂无可用的兑换码列表"
            }
            template.helper("toDateString", function(e) {
                var r = new Date(parseInt(e) * 1e3);
                var t = "";
                t += r.getFullYear() + "年";
                t += r.getMonth() + 1 + "月";
                t += r.getDate() + "日 ";
                t += r.getHours() + ":";
                t += r.getMinutes();
                return t
            });
            var n = template.render("order-indatecode-tmpl", r);
            $("#order-indatecode").html(n)
        }
    })
});