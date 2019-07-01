$(function () {
    //自定义radio样式
    $(".cb-enable").click(function () {
        var parent = $(this).parents('.onoff');
        $('.cb-disable', parent).removeClass('selected');
        $(this).addClass('selected');
        $('.checkbox', parent).attr('checked', true);
    });
    $(".cb-disable").click(function () {
        var parent = $(this).parents('.onoff');
        $('.cb-enable', parent).removeClass('selected');
        $(this).addClass('selected');
        $('.checkbox', parent).attr('checked', false);
    });
    if ($(window).width() < 768) {

        $("#navbar_moblie").click(function() {
            if ($("#main_navbar").css("display") == "none") {
                $("#main_navbar").slideDown(200);
            } else {
                $("#main_navbar").slideUp(200);
            }
        });

        $(".main_navbar li dd a").click(function() {
            $("#main_navbar").slideUp(200);
        });


        $("#membercenternav_bar").hover(function() {
            $("#main_navbar").slideUp(200);
            $("#membercenternav").slideDown("200");
        }, function() {
            $("#membercenternav").slideUp("200");
        })

    }
});


$(function () {
    // 显示隐藏预览图 start
    $('.show_image').hover(
            function () {
                $(this).next().css('display', 'block');
            },
            function () {
                $(this).next().css('display', 'none');
            }
    );

    // 全选 start
    $('.checkall').click(function(){
        var _self = this.checked;
        $('.checkitem').each(function(){
            $(this).prop('checked', _self);
        });
        $('.checkall').prop('checked', _self);
    });

    // 表格鼠标悬停变色 start
    $("tbody tr").hover(
            function () {
                $(this).css({background: "#FBFBFB"});
            },
            function () {
                $(this).css({background: "#FFF"});
            });

    // 可编辑列（input）变色
    $('.editable').hover(
            function () {
                $(this).removeClass('editable').addClass('editable2');
            },
            function () {
                $(this).removeClass('editable2').addClass('editable');
            }
    );

    // 提示操作 展开与隐藏
    $("#checkZoom").click(function () {
        $(this).next("ul").toggle(800);
        $(this).find(".arrow").toggleClass("up");
    });

    // 可编辑列（area）变色
    $('.editable-tarea').hover(
            function () {
                $(this).removeClass('editable-tarea').addClass('editable-tarea2');
            },
            function () {
                $(this).removeClass('editable-tarea2').addClass('editable-tarea');
            }
    );

});

function DrawImage(ImgD,FitWidth,FitHeight){
    var image=new Image();
    image.src=ImgD.src;
    if(image.width>0 && image.height>0)
    {
        if(image.width/image.height>= FitWidth/FitHeight)
        {
            if(image.width>FitWidth)
            {
                ImgD.width=FitWidth;
                ImgD.height=(image.height*FitWidth)/image.width;
            }
            else
            {
                ImgD.width=image.width;
                ImgD.height=image.height;
            }
        }
        else
        {
            if(image.height>FitHeight)
            {
                ImgD.height=FitHeight;
                ImgD.width=(image.width*FitHeight)/image.height;
            }
            else
            {
                ImgD.width=image.width;
                ImgD.height=image.height;
            }
        }
    }
}