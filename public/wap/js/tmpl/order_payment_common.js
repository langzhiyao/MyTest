var key = getCookie("key");
var password, rcb_pay, pd_pay, payment_code;
function toPay(a, e, p) {
    $.ajax({
        type: "post",
        url: ApiUrl + "/" + e + "/" + p+".html",
        data: {
            key: key,
            pay_sn: a
        },
        dataType: "json",
        success: function(p) {
            checkLogin(p.login);
            if (p.code==100) {
                $.sDialog({
                    skin: "red",
                    content: p.message,
                    okBtn: false,
                    cancelBtn: false
                });
                return false
            }
            $.animationUp({
                valve: "",
                scroll: ""
            });
            $("#onlineTotal").html(p.result.pay_info.pay_amount);
            if (!p.result.pay_info.member_paypwd) {
                $("#wrapperPaymentPassword").find(".input-box-help").show();
            }
            var s = false;
            if (parseFloat(p.result.pay_info.payed_amount) <= 0) {
                if (parseFloat(p.result.pay_info.member_available_pd) == 0 && parseFloat(p.result.pay_info.member_available_rcb) == 0) {
                    $("#internalPay").hide()
                } else {
                    $("#internalPay").show();
                    if (parseFloat(p.result.pay_info.member_available_rcb) != 0) {
                        $("#wrapperUseRCBpay").show();
                        $("#availableRcBalance").html(parseFloat(p.result.pay_info.member_available_rcb).toFixed(2))
                    } else {
                        $("#wrapperUseRCBpay").hide()
                    }
                    if (parseFloat(p.result.pay_info.member_available_pd) != 0) {
                        $("#wrapperUsePDpy").show();
                        $("#availablePredeposit").html(parseFloat(p.result.pay_info.member_available_pd).toFixed(2))
                    } else {
                        $("#wrapperUsePDpy").hide()
                    }
                }
            } else {
                $("#internalPay").hide()
            }
            password = "null";
            $("#paymentPassword").on("change",
            function() {
                password = $(this).val();
            });
            rcb_pay = 0;
            $("#useRCBpay").click(function() {
                if ($(this).prop("checked")) {
                    s = true;
                    $("#wrapperPaymentPassword").show();
                    rcb_pay = 1
                } else {
                    if (pd_pay == 1) {
                        s = true;
                        $("#wrapperPaymentPassword").show()
                    } else {
                        s = false;
                        $("#wrapperPaymentPassword").hide()
                    }
                    rcb_pay = 0
                }
            });
            pd_pay = 0;
            $("#usePDpy").click(function() {
                if ($(this).prop("checked")) {
                    s = true;
                    $("#wrapperPaymentPassword").show();
                    pd_pay = 1
                } else {
                    if (rcb_pay == 1) {
                        s = true;
                        $("#wrapperPaymentPassword").show()
                    } else {
                        s = false;
                        $("#wrapperPaymentPassword").hide()
                    }
                    pd_pay = 0
                }
            });
            payment_code = "";
            if (!$.isEmptyObject(p.result.pay_info.payment_list)) {
                var t = false;
                var r = false;
                var n = navigator.userAgent.match(/MicroMessenger\/(\d+)\./);
                if (parseInt(n && n[1] || 0) >= 5) {
                    t = true
                } else {
                    r = true
                }

                for (var o = 0; o < p.result.pay_info.payment_list.length; o++) {
                    var i = p.result.pay_info.payment_list[o].payment_code;
                   /* if(i == "alipay" ){
                        $("#" + i).parents("label").show();
                    }
                    if(i == "wxpay_jsapi" ){
                        $("#" + i).parents("label").show();
                    }
                    if (i == "alipay"  && r) {
                        if (payment_code == "") {
                            payment_code = i;
                            $("#" + i).attr("checked", true).parents("label").addClass("checked")
                        }
                    }
                    if (i == "wxpay_jsapi"  && t) {
                        if (payment_code == "") {
                            payment_code = i;
                            $("#" + i).attr("checked", true).parents("label").addClass("checked")
                        }
                    }*/
                    if(i == "alipay_app" ){
                        $("#" + i).parents("label").show();
                    }
                    if(i == "wxpay_app" ){
                        $("#" + i).parents("label").show();
                    }
                    if (i == "alipay_app"  && r) {
                        if (payment_code == "") {
                            payment_code = i;
                            $("#" + i).attr("checked", true).parents("label").addClass("checked").siblings().removeClass('checked');
                        }
                    }
                    if (i == "wxpay_app"  && t) {
                        if (payment_code == "") {
                            payment_code = i;
                            $("#" + i).attr("checked", true).parents("label").addClass("checked").siblings().removeClass('checked');
                        }
                    }
                }
            }
           /* $("#alipay").click(function() {
                payment_code = "alipay";
            });
            $("#wxpay_jsapi").click(function() {
                    payment_code = "wxpay_jsapi";
            });*/
            $("#alipay_app").click(function() {
                payment_code = "alipay_app";
            });
            $("#wxpay_app").click(function() {
                payment_code = "wxpay_app";
            });
            $("#toPay").click(function() {
                if (payment_code == "") {
                    $.sDialog({
                        skin: "red",
                        content: "请选择支付方式",
                        okBtn: false,
                        cancelBtn: false
                    });
                    return false
                }
                if (s) {
                    if (password == "") {
                        $.sDialog({
                            skin: "red",
                            content: "请填写支付密码",
                            okBtn: false,
                            cancelBtn: false
                        });
                        return false
                    }
                    $.ajax({
                        type: "post",
                        url: ApiUrl + "/Memberbuy/check_pd_pwd.html",
                        dataType: "json",
                        data: {
                            key: key,
                            password: password
                        },
                        success: function(p) {
                            if (p.code==100) {
                                $.sDialog({
                                    skin: "red",
                                    content: p.message,
                                    okBtn: false,
                                    cancelBtn: false
                                });
                                return false;
                            }
                            // goToPayment(a, e == "memberbuy" ? "pay_new": "vr_pay_new")
                            goToPayment(a, e == "memberbuy" ? "orderpay_app": "orderpay_app_vr")
                        }
                    })
                } else {
                        // goToPayment(a, e == "memberbuy" ? "pay_new": "vr_pay_new")
                        goToPayment(a, e == "memberbuy" ? "orderpay_app": "orderpay_app_vr")
                }
            })
        }
    })
}
function goToPayment(a, e) {
    // location.href = ApiUrl + "/Memberpayment/" + e + "/key/" + key + "/pay_sn/" + a + "/password/" + password + "/rcb_pay/" + rcb_pay + "/pd_pay/" + pd_pay + "/payment_code/" + payment_code;
    $.ajax({
        type:'POST',
        url:ApiUrl + "/Memberpayment/" + e + "/key/" + key + "/pay_sn/" + a + "/password/" + password + "/rcb_pay/" + rcb_pay + "/pd_pay/" + pd_pay + "/payment_code/" + payment_code,
        data:{},
        dataType: "json",
        success: function(response){
            if (response['code'] == 200) {
                var mbs = response.result;
                // 微信支付
                if (payment_code == 'wxpay_app') {
                    if (mbs.result_code == 'SUCCESS') {
                        if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) { //判断iPhone|iPad|iPod|iOS
                            window.webkit.messageHandlers.wxpayClick.postMessage(mbs);
                        } else if (/(Android)/i.test(navigator.userAgent)) { //判断Android
                            mbs  = mbs['prepay_id']+','+mbs['nonce_str']+','+mbs['timestamp']+','+mbs['sign']+','+mbs['orderSn'];
                            Android.wxpayClick(mbs);
                        } else { //pc
                        }
                        ;
                    } else {

                    }
                }
                //支付宝支付
                if (payment_code == 'alipay_app') {
                    if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) { //判断iPhone|iPad|iPod|iOS
                        window.webkit.messageHandlers.aliapyClick.postMessage(mbs);
                    } else if (/(Android)/i.test(navigator.userAgent)) { //判断Android
                        mbs  = mbs['content']+','+mbs['orderSn'];
                        Android.aliapyClick(mbs);
                    } else { //pc
                    }
                    ;
                }
            } else if (response['code'] == 400) {
                alert('请先前往登陆');
                return false;
            } else {
                alert(response['message']);
                return false;
            }
        }
    })

}