$(function() {
    $.showLoading();
    var myPlayer;
    var Time = 1;
    // 获取详情
    $.ajax({
        url: api + '/Teacherdetail/detail',
        type: 'POST',
        dataType: 'json',
        data: {
            key: user_token,
            member_id:user_member_id,
            t_id: GetPar("id")
        },
        success: function(response) {
            var image = '';
            if (response.result[0]['data']['t_picture'] == '') {
                image = response.result[0]['data']['t_videoimg'];
            } else {
                image = response.result[0]['data']['t_picture'];
            }
            $('#video_image').attr('src',image);
            if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) { //判断iPhone|iPad|iPod|iOS
                $('#video').html('<video id="video_true" controls="controls" controlslist ="nodownload"  src="'+response.result[0]['data']['t_url']+'"  width="750px" preload="none"  poster="'+image+'"></video>')
            } else  {
                var browser = getExplorerInfo();
                var minVersion = toNum(54.0);
                var maxVersion = toNum(58.0);
                var Version = toNum(browser.version);
                if(minVersion<Version && Version<maxVersion && browser.type == 'Chrome'){
                    $(document.body).append('<link rel="stylesheet" href="../content/style/video.css" type="text/css" />');
                    $('#video').html('<video id="video_true" controls="controls" src="'+response.result[0]['data']['t_url']+'"  width="750px" preload="none"  poster="'+image+'"></video>')
                }else{
                    $('#video').html('<video id="video_true" controls="controls" controlslist ="nodownload"  src="'+response.result[0]['data']['t_url']+'"  width="750px" preload="none"  poster="'+image+'"></video>')
                }
            }

            $('#related').html(HTML(response['result']));
            $.hideLoading();


        }
    })

    // 添加观看记录
    function addHistory() {
        $.ajax({
            url: api + '/teacherhistory/addhistory',
            type: 'POST',
            dataType: 'json',
            data: {
                key: user_token,
                member_id: user_member_id,
                tid: GetPar("id")
            },
            success: function(response) {
                if (response['code'] == 200) {
                    return true;
                } else {
                    return false;
                };
            }
        })
    }


    // 封装获取ID方法
    function GetPar(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) return decodeURIComponent(r[2]);
        return null;
    }

    // 收藏视频
    collectVideo = function(event) {
        if(!user_token){
         $.toast('请前往登陆','forbidden');return false;
        }
        $.ajax({
            url: api + '/teachercollect/collect',
            type: 'POST',
            dataType: 'json',
            data: {
                key: user_token,
                member_id: user_member_id,
                tid: $('#my-player').attr('data-id')
            },
            success: function(response) {
                if (response['code'] == 200) {
                    $('#qxCollect').show();
                    $('#collect').hide();
                    $.toast('收藏成功');
                } else {
                    $.toast(response['message'],'forbidden');
                }
            }
        })
    }

    qxCollectVideo = function(event) {
        if(!user_token){
            $.toast('请前往登陆','forbidden');return false;
        }
        $.ajax({
            url: api + '/teachercollect/collect',
            type: 'POST',
            dataType: 'json',
            data: {
                key: user_token,
                member_id: user_member_id,
                tid: $('#my-player').attr('data-id')
            },
            success: function(response) {
                if (response['code'] == 200) {
                    $('#qxCollect').hide();
                    $('#collect').show();
                    $.toast('取消收藏成功');
                } else {
                    $.toast(response['message'],'forbidden');
                }
            }
        })
    }
    // HTML模板
    function HTML(data) {
        var template = '';
        var List = '';
        var price = '';
        for (var i = 0; i < data[1]['lists'].length; i++) {
            var img = '';
            if (data[1]['lists'][i]['t_picture'] == '') {
                img = data[1]['lists'][i]['t_videoimg'];
            } else {
                img = data[1]['lists'][i]['t_picture'];
            }

            List += '<li class="related_list_li clearBoth">' +
                '<a href="details.html?id='+data[1]['lists'][i]['t_id']+'" >' +
                '<div class="img_wrap float_left">' +
                '<img src="' + img + '" alt="' + data[1]['lists'][i]['t_url'] + '">' +
                '</div>' +
                '<div class="content_wrap float_left">' +
                '<p class="title">' + data[1]['lists'][i]['t_title'] + '</p>';
                if(data[1]['lists'][i]['t_profile'] == ''){
                    List +='<p class="content">暂无简介</p>';
                }else{
                    List +='<p class="content">' + data[1]['lists'][i]['t_profile'] + '</p>';
                }
            List +=   '</div>' +
                '</a></li>';
        }
        if(data[0]['data']['myself'] == 0){
            if (data[0]['data']['t_price'] == 0 && data[0]['data']['buy'] == 0) {
                price = '免费观看';
            } else if(data[0]['data']['t_price'] != 0 && data[0]['data']['buy'] == 0){
                price = '￥ ' + data[0]['data']['t_price'] + ' 购买'
            } else if(data[0]['data']['t_price'] != 0 && data[0]['data']['buy'] != 0){
                price = '已购买'
            }
        }else{
            if (data[0]['data']['t_audit'] == 1) {
                price = '待审核';
            } else if(data[0]['data']['t_audit'] == 2 ){
                price = '审核失败';
            } else if(data[0]['data']['t_audit'] ==3){
                price = '审核通过';
            }

        }

        template = '<div class="related_wrap body_content" id="my-player" data-id="' + data[0]['data']['t_id'] + '">' +
            '<div class="related" style="height: 120px">' +
            '<div class="related1">' +
            '<h2 class="title">' + data[0]['data']['t_title'] + '</h2>' +
            '<p class="content" style="line-height: 40px;height: 40px;">作者：' + data[0]['data']['t_author'] + '</p>' +
            '</div>' +
            '<div class="related2">' ;
            if(data[0]['data']['myself'] == 0){
                if (data[0]['data']['t_price'] == 0 && data[0]['data']['buy'] == 0) {
                    template +='<button type="button" >' + price + '</button>'
                } else if(data[0]['data']['t_price'] != 0 && data[0]['data']['buy'] == 0){
                    template +='<button type="button" onclick="pay(' + data[0]['data']['t_id'] + ');">' + price + '</button>';
                } else if(data[0]['data']['t_price'] != 0 && data[0]['data']['buy'] != 0){
                    template +='<button type="button" >' + price + '</button>'
                }
            }else{
                template +='<button type="button" >' + price + '</button>'
            }
            template +=   '</div>' +
                '</div>' +
                '<div class="video_detail" onclick="msg_switch($(this))"><p>' + data[0]['data']['t_profile'] + '</p><a class="video_detail_icon" href="javascript:;"><i class="icon iconfont icon-iconfontjiantou"></i></a></div>'+
                '<div class="related_list" style="border-top: 1px solid #aaa;">' + '<p class="title_more ">更多课程</p>' + '<ul>' + List + '</ul>' + '</div>' + '</div>';
        return template;
    }

});
//跳转购买页面
function pay(t_id){
    if(user_token){
            location.href=http_url+"app/teachchild/pay.html?t_id="+t_id;
    }else{
        $.toast('请前往登陆','forbidden');
    }
}

function msg_switch(obj) {
    obj.toggleClass("open");
}


