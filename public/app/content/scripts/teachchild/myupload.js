$(function() {

    var Page = 1;

    let Params = {
        key: user_token,
        member_id: user_member_id,
        status: 1,
        page: 1

    };

    // 默认加载审核中的内容
    getList(1);

    // 分页
    var loading = false; //状态标记
    $(document.body).infinite().on("infinite", function(event) {
        if (loading) return;
        loading = true;
        setTimeout(function() {
            Page = Page + 1;
            Params['page'] = Page;
            getPage(Params);
            loading = false;
        }, 20)
    });

    // 下拉刷新
    $('.content').pullToRefresh(function() {
        $('.content').infinite();
        setTimeout(function() {
            $('.content').pullToRefreshDone(); // 重置下拉刷新
        }, 1500);
        Page = 1;
        var params = Params;
        params['page'] = 1;
        GetKejian(params);
    });
    // 获取课件接口
    function GetKejian(params) {
        $.ajax({
            url: api + '/teacherchild/myUpload.html',
            type: 'POST',
            dataType: 'json',
            data: params,
            success: function(response) {
                if (response['code'] == 200) {
                    if (response['result'].length == 0) {
                        $('.main_content').html('<div class="weui-loadmore weui-loadmore_line"><span class="weui-loadmore__tips">暂无数据</span></div>')
                    } else {
                        $('.main_content').html(HTML(response['result']))
                    }
                } else {
                    $.toast(response['message'], 'forbidden');
                    return false;
                };
            }
        })
    }

    // 选项卡切换
    Tabs = function(type, el) {
        if (!$(el).hasClass('action')) {
            $('.myupload_tabs_wrap span').removeClass('action');
            $(el).addClass('action');
            getList(type)
        }
    }

    // 获取数据
    function getList(type) {
        let params = {
            key: user_token,
            member_id: user_member_id,
            status: type,
            page: 1
        }
        Params = params;
        $.ajax({
            url: api + '/teacherchild/myUpload.html',
            type: 'POST',
            dataType: 'json',
            data: params,
            success: function(response) {
                if (response['code'] == 200) {
                    if (response['result'].length == 0) {
                        $('.main_content').html('<div class="weui-loadmore weui-loadmore_line"><span class="weui-loadmore__tips">暂无数据</span></div>')
                    } else {
                        $('.main_content').html(HTML(response['result']))
                    }
                } else {
                    $.toast(response['message'], 'forbidden');
                    return false;
                };
            }
        })
    }

    function getPage(params) {
        $('.weui-loadmore').show();
        $.ajax({
            url: api + '/teacherchild/myUpload.html',
            type: 'POST',
            data: params,
            dataType: "json",
            success: function(response) {
                if (response['code'] == 200) {
                    if (response['result'].length == 0) {
                        var is_data = $('.main_content>.weui-loadmore').hasClass('weui-loadmore_line');
                        if(!is_data){
                            $('.weui-footer').show();
                        }
                        $('.weui-loadmore').hide();
                        $(document.body).destroyInfinite();
                    } else {
                        $('.weui-loadmore').hide();
                        $('.main_content').append(HTML(response['result']))
                    }
                } else {
                    $('.weui-footer').show();
                    $('.weui-loadmore').hide();
                    $(document.body).destroyInfinite();
                    $.toast(response['message'], 'forbidden');
                    return false;
                };
            }
        });
    }

    // HTML模板
    function HTML(data) {
        var template = '';
        var img = '';
        for (var i = 0; i < data.length; i++) {
            template += '<div class="content_wrap" onclick="videoClick(' + data[i]['t_id'] + ')">';
            template += '<div class="img_wrap">' ;
            if (data[i]['t_audit'] == 1) {
                img = '../content/images/teachchild/1.png'; //审核中
                template += '<img class="img_top" src="' + img + '">';
            } else if (data[i]['t_audit'] == 2) {
                img = '../content/images/teachchild/2.png'; //失败
                template += '<img class="img_top" src="' + img + '">';
            } else if (data[i]['t_audit'] == 3 && data[i]['t_price'] == 0) {
                img = '../content/images/teachchild/3.png'; //通过免费
            } else if (data[i]['t_audit'] == 3 && data[i]['t_price'] != 0) {
                img = '../content/images/teachchild/4.png'; //通过付费
            } else if (data[i]['t_audit'] == 4) {
                img = '../content/images/teachchild/5.png'; //下架
                template += '<img class="img_top" src="' + img + '">';
            }

            template +=    '<img src="' + data[i]['t_videoimg'] + '" alt="' + data[i]['t_url'] + '">' +
                '</div>' +
                '<h3 class="title">' + data[i]['t_title'] + '</h3>' +
                '<p class="cont">' + data[i]['t_profile'] + '</p>' +
                '</div>';
        }
        return template;
    }

})