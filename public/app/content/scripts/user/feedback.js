
$(function () {
    var key = getCookie('key');
	if (!key) {
		doLogin();
        return false;
    }

    $('#show-alert').click(function(event) {
    	var sizeStatistics = $('#sizeStatistics').val();
    	var phone = $('#phone').val()
    	if (!phone || !sizeStatistics) {alert('意见反馈和手机号码不能为空');return false;}
    	var myreg = /(^1[3|4|5|7|8]\d{9}$)|(^09\d{8}$)/;
 		if(!myreg.test(phone)){alert('请填写正确的手机号码！');return false;}
 		$.ajax({
 			url: ApiUrl + '/Feedback/feed_back',
 			type: 'POST',
 			dataType: 'json',
 			data: {param1: 'value1'},
 		})
 		.done(function(sb) {
 			console.log(sb);
 		})
 		
 		

    });
})