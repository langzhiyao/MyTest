$(function()
{
    //给图标的加减号添加展开收缩行为
    $('img[ectype="flex"]').click(function(){
        var status = $(this).attr("status");
        var id = $(this).attr("fieldid");
        //状态是加号的事件
        if(status == 'open')
        {
            var src = $(this).attr('src');
            var pr = $(this).parent('td').parent('tr');
            //如果已经请求过的数据再次请求时只显示改变状态，不再去请求
            /*if($("."+id).length > 0)
            {
                $("."+id).show();
                $(this).attr('src',src.replace("tv-expandable","tv-collapsable"));
                $(this).attr('status','close');
                return;
            }*/
            $.get(AJAX_URL_ARTICLECATEGORY, {id: id}, function(data){
                if(data)
                {
                    var str = "";
                    var res = eval('('+data+')');
                    for(var i = 0; i < res.length; i++)
                    {
                        var src = "";
                        var if_show = "";
                        //给每一个异步取出的数据添加伸缩图标后者无状态图标
                        if(res[i].switchs)
                        {
                           src =  "<img src='"+SITE_URL+"static/admin/images/treetable/tv-expandable.gif' ectype='flex' status='open' fieldid="+res[i].gc_id+" onclick='secajax($(this))'>";
                        }
                        else
                        {
                           src =  "<img src='"+SITE_URL+"static/admin/images/treetable/tv-item.gif' fieldid='"+res[i].gc_id+"'>";
                        }
                        //给每一个取出的数据添加是否显示标志

                        //构造每一个tr组成的字符串，标语添加
                        str += "<tr class='row" + id + "'>" +
                            "<td><input type='checkbox' class='checkitem' value='" + res[i].gc_id + "' /></td>" +
                                "<td><img class='preimg' src='"+SITE_URL+"static/admin/images/treetable/vertline.gif'/>" + src + "<span class='node_name editable' ectype='inline_edit' fieldname='gc_name' fieldid='" + res[i].gc_id + "' required='1'>" + res[i].gc_name + "</span></td>" +
                                "<td><span class='editable' ectype='inline_edit' fieldname='gc_sort' fieldid='" + res[i].gc_id + "' datatype='number'>" + res[i].gc_sort + "</span></td>" +
                                "<td><span><a href='"+SITE_URL+"index.php/admin/Articlecategory/edit/ac_id/" + res[i].gc_id + ".html'>编辑</a>  | <a href='javascript:if(confirm(\"确认删除?\"))window.location=\""+SITE_URL+"index.php/admin/Articlecategory/drop/ac_id/" + res[i].gc_id + ".html\";'>删除</a> | <a href='"+SITE_URL+"index.php/admin/Articlecategory/add/ac_id/" + res[i].gc_id + ".html'>添加子分类</a> </span></td></tr>";
                    }
                    //将组成的字符串添加到点击对象后面
                    pr.after(str);
                    //解除行间编辑的绑定事件
                    $('span[ectype="inline_edit"]').unbind('click');
                    //重现初始化页面
                    $.getScript(SITE_URL+"/includes/libraries/javascript/inline_edit.js");
                }
            });
            $(this).attr('src',src.replace("tv-expandable","tv-collapsable"));
            $(this).attr('status','close');
        }
        //状态是减号的事件
        if(status == "close")
        {
            var src = $(this).attr('src');
            $('.row'+id).hide();
            $(this).attr('src',src.replace("tv-collapsable","tv-expandable"));
            $(this).attr('status','open');
        }
    });
});
//异步请求回来的数据的再次添加异步伸缩行为
function secajax(ob)
{
    var status = $(ob).attr("status");
    var id = $(ob).attr("fieldid");
    var src = $(ob).attr('src');
        if(status == 'open')
        {
            var src = $(ob).attr('src');
            var pr  = $(ob).parent('td').parent('tr');
            var pid = pr.attr('class');
            var sr  = pr.clone();
            var td2 = sr.find("td:eq(1)");
            td2.prepend("<img class='preimg' src='"+SITE_URL+"static/admin/images/treetable/vertline.gif'/>")
                            .children('span')
                            .remove().end()
                            .find("img[ectype=flex]").remove();
            var td2html = td2.html();

            $.get(AJAX_URL_GOODSCATEGORY, {id: id}, function(data){
                if(data)
                {
                    var str = '';
                    var res = eval('('+data+')');
                    for(var i = 0; i < res.length; i++)
                    {

                        var add_child = '';
                        if(res[i].switchs)
                        {
                           src =  "<img src='"+SITE_URL+"static/admin/images/treetable/tv-expandable.gif' ectype='flex' status='open' fieldid="+res[i].gc_id+
                           " onclick='secajax($(this))'><span class='node_name editable' ectype='inline_edit' fieldname='gc_name' fieldid='"+res[i].gc_id+"' required='1'>"+res[i].gc_name+"</span>";

                        }
                        else
                        {
                           src =  "<img src='"+SITE_URL+"static/admin/images/treetable/tv-item.gif' fieldid='"+res[i].gc_id+"'><span class='node_name editable' ectype='inline_edit' fieldname='gc_name' fieldid='"+res[i].gc_id+"' required='1'>"+res[i].gc_name+"</span>";
                        }
                        if(res[i].add_child)
                        {
                            add_child =  " | <a href='"+SITE_URL+"index.php/admin/Articlecategory/add/ac_id/" + res[i].gc_id + ".html'>添加子分类</a>";
                        }
                        var itd2 = td2html+src;

                    str += "<tr class='" + pid + " row" + id + "'><td class='align_center w30'><input type='checkbox' class='checkitem' value='" + res[i].gc_id + "' /></td>" +
                            "<td class='node' width='50%'>" + itd2 + "</td>" +
                            "<td class='align_center'><span class='editable' ectype='inline_edit' fieldname='gc_sort' fieldid='" + res[i].gc_id + "' datatype='number'>" + res[i].gc_sort + "</span></td>" +
                            "<td><span><a href='"+SITE_URL+"index.php/admin/Articlecategory/edit/ac_id/" + res[i].gc_id + ".html'>编辑</a>  | <a href='javascript:if(confirm(\"确认删除\"))window.location=\""+SITE_URL+"index.php/admin/Articlecategory/drop/ac_id/" + res[i].gc_id + ".html\";'>删除</a> | " + add_child + " </span></td></tr>";
                    }
                    pr.after(str);
                    $('span[ectype="inline_edit"]').unbind('click');
                    $.getScript(SITE_URL+"/includes/libraries/javascript/inline_edit.js");
                }
            });
            $(ob).attr('src',src.replace("tv-expandable","tv-collapsable"));
            $(ob).attr('status','close');
        }
        if(status == "close")
        {
            $('.row' + id).hide();
            $(ob).attr('src',src.replace("tv-collapsable","tv-expandable"));
            $(ob).attr('status','open');
        }
}