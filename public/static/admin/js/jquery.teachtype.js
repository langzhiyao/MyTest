$(document).ready(function() {
    //列表下拉 v3-b12
    $('img[nc_type="flex"]').click(function() {
        var status = $(this).attr('status');
        if (status == 'open') {
            var pr = $(this).parent('td').parent('tr');
            var id = $(this).attr('fieldid');
            var obj = $(this);
            $(this).attr('status', 'none');
            //ajax
            $.ajax({
                url: SITE_URL + 'index.php/Admin/Teachtype/index.html?ajax=1&gc_parent_id=' + id,
                dataType: 'json',
                success: function(data) {
                    var src = '';
                    var admin_is_super = $('#admin_is_super').val();
                    var adminAction = $('#adminAction').val();
                    adminAction = $.parseJSON(adminAction);
                    console.info(adminAction);
                    console.info(isInArray(adminAction,'1'));
                    var addsign = "1";
                    var delsign = "2";
                    var editsign = "3";
                    for (var i = 0; i < data.length; i++) {
                        var tmp_vertline = "<img class='preimg' src='" + SITE_URL + "/static/admin/images/treetable/vertline.gif'/>";
                        src += "<tr class='" + pr.attr('class') + " row" + id + "'>";
                        src += "<td class='w36'><input type='checkbox' name='check_gc_id[]' value='" + data[i].gc_id + "' class='checkitem'>";
                        //图片
                        if (data[i].have_child == 1) {
                            src += " <img fieldid='" + data[i].gc_id + "' status='open' nc_type='flex' src='" + SITE_URL + "/static/admin/images/treetable/tv-expandable.gif' />";
                        } else {
                            src += " <img fieldid='" + data[i].gc_id + "' status='none' nc_type='flex' src='" + SITE_URL + "/static/admin/images/treetable/tv-item.gif' />";
                        }
                        src += "</td><td class='w48 sort'>";
                        //排序
                        src += " <span title='可编辑下级分类排序' ajax_branch='goods_class_sort' datatype='number' fieldid='" + data[i].gc_id + "' fieldname='gc_sort' nc_type='inline_edit' class='editable tooltip'>" + data[i].gc_sort + "</span></td>";
                        //名称
                        src += "<td class='w50pre name'>";


                        for (var tmp_i = 1; tmp_i < (data[i].deep - 1); tmp_i++) {
                            src += tmp_vertline;
                        }
                        if (data[i].have_child == 1) {
                            src += " <img fieldid='" + data[i].gc_id + "' status='open' nc_type='flex' src='" + SITE_URL + "/static/admin/images/treetable/tv-item1.gif' />";
                        } else {
                            src += " <img fieldid='" + data[i].gc_id + "' status='none' nc_type='flex' src='" + SITE_URL + "/static/admin/images/treetable/tv-expandable1.gif' />";
                        }
                        src += " <span title='可编辑下级分类名称' required='1' fieldid='" + data[i].gc_id + "' ajax_branch='goods_class_name' fieldname='gc_name' nc_type='inline_edit' class='editable tooltip'>" + data[i].gc_name + "</span>";
                        //新增下级
                        if(admin_is_super == 1 || isInArray(adminAction,addsign)) {
                            if (data[i].deep < 4) {
                                src += "<a class='btn-add-nofloat marginleft' href='" + SITE_URL + "index.php/Admin/Teachtype/type_class_add.html?gc_parent_id=" + data[i].gc_id + "'><span>新增下级</span></a>";
                            }
                        }
                        src += "</td>";
                        //是否显示
                        /*src += "<td class='power-onoff'>";
                         if(data[i].gc_show == 0){
                         src += "<a href='JavaScript:void(0);' class='tooltip disabled' fieldvalue='0' fieldid='"+data[i].gc_id+"' ajax_branch='goods_class_show' fieldname='gc_show' nc_type='inline_edit'><img src='"+ADMIN_TEMPLATES_URL+"/images/transparent.gif'></a>";
                         }else{
                         src += "<a href='JavaScript:void(0);' class='tooltip enabled' fieldvalue='1' fieldid='"+data[i].gc_id+"' ajax_branch='goods_class_show' fieldname='gc_show' nc_type='inline_edit'><img src='"+ADMIN_TEMPLATES_URL+"/images/transparent.gif'></a>";
                         }
                         src += "</td>";
                         */
                        //类型
                        src += "<td>" + data[i].type_name + "</td>";
                        //操作
                        src += "<td class='w84'>";
                        if(admin_is_super == 1 || isInArray(adminAction,editsign)){
                            src += "<a href='"+SITE_URL+"index.php/Admin/Teachtype/type_class_edit.html?gc_id=" + data[i].gc_id + "'>编辑</a>";
                        }
                        if(admin_is_super == 1 || isInArray(adminAction,editsign)&&isInArray(adminAction,delsign)){
                            src += " | <a href=\"javascript:if(confirm('删除该分类将会同时删除该分类的所有下级分类，您确定要删除吗'))window.location = '"+SITE_URL+"index.php/Admin/Teachtype/type_class_del?gc_id=" + data[i].gc_id + "';\">删除</a>";
                        }else if(admin_is_super == 1 || isInArray(adminAction,delsign)){
                            src += "<a href=\"javascript:if(confirm('删除该分类将会同时删除该分类的所有下级分类，您确定要删除吗'))window.location = '"+SITE_URL+"index.php/Admin/Teachtype/type_class_del?gc_id=" + data[i].gc_id + "';\">删除</a>";
                        }
                        src += "</td>";
                        src += "</tr>";
                    }
                    //插入
                    pr.after(src);
                    obj.attr('status', 'close');
                    obj.attr('src', obj.attr('src').replace("tv-expandable", "tv-collapsable"));
                    $('img[nc_type="flex"]').unbind('click');
                    $('span[nc_type="inline_edit"]').unbind('click');
                    //重现初始化页面
                    $.getScript(SITE_URL + "static/plugins/jquery.edit.js");
                    $.getScript(SITE_URL + "static/admin/js/jquery.teachtype.js");
//                    $.getScript(SITE_URL + "/js/admincp.js");
                },
                error: function() {
                    alert('获取信息失败');
                }
            });
        }
        if (status == 'close') {
            $(".row" + $(this).attr('fieldid')).remove();
            $(this).attr('src', $(this).attr('src').replace("tv-collapsable", "tv-expandable"));
            $(this).attr('status', 'open');
        }
        function isInArray(arr,value){
            for(var i = 0; i < arr.length; i++){
                if(value === arr[i]){
                    return true;
                }
            }
            return false;
        }
    })
});