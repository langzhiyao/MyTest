//v3-b11
$(function() {
    var article_id = GetQueryString('article_id')

    if (article_id == '') {
        window.location.href = WapSiteUrl + '/index.html';
        return;
    }
    else {
        $.ajax({
            url: ApiUrl + "/Article/article_show.html",
            type: 'get',
            data: {article_id: article_id},
            jsonp: 'callback',
            dataType: 'jsonp',
            success: function(result) {
                var data = result.result;
                var html = template.render('article', data);
                $("#article-content").html(html);
                $(".article-content").html(data.article_content);
            }
        });
    }
});