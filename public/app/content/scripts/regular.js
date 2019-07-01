// var str = $('#numr').val().trim()

function test_paypwd(str) { //只能是数字
    if (str.length != 6) {
        reg = /^\d{6}$/
        if (!reg.test(str)) {
            $.toast('请输入6位数字作为支付密码。')
        }
    } else {
        $.toast('支付密码保存成功。')
    }
}

// function checsamepwd() { //判断密码是否一致
//     with(document.all) {
//         if (input1.value != input2.value) {
//             alert("密码不一致")
//             input1.value = "";
//             input2.value = "";
//         } else {
//             alert("密码一致");
//             document.forms[0].submit();
//         }
//     }
// }

var Tools = {
    // 是否为空
    IsNull: function(str) {
        return str == null || typeof(str) == "undefined" || str == "";
    },

    //判断是否是手机号
    IsPhoneNum: function(str) {
        var myreg = /^1\d{10}$/;
        return myreg.test(str);
    },

    //判断是否是邮箱地址
    IsEmail: function(str) {
        var myreg = /^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.[a-zA-Z0-9]{2,6}$/;
        return myreg.test(str);
    }
}