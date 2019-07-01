$(function() {

    var Arr = [];

    changeState = function(event, el) {
        Amount();
    }

    // 商品++
    add = function(event) {
        var ID = event.target.dataset.id;
        var stock = parseInt(event.target.dataset.stock);
        var number = parseInt($('.number' + ID).html());
        number++;
        if (number >= stock) {
            $.toast("只有这么多了！", "text");
            number = stock;
        }
        $('.number' + ID).html(number);
        $('.check' + ID).attr('checked', 'checked');
        Amount();
    }

    // 商品--
    less = function(event) {
        var ID = event.target.dataset.id;
        var number = parseInt($('.number' + ID).html());
        number--;
        if (number <= 1) {
            $.toast("不能再减了！", "text");
            number = 1;
        }
        $('.number' + ID).html(number);
        Amount();
    }

    function Amount() {
        var check = $('.weui-check');
        var price = 0;
        var amount = 0;
        Arr = [];
        for (var i = 0; i < check.length; i++) {
            if (check[i].checked) {
                Arr.push(check[i].dataset.id);
                price += $('.price' + check[i].dataset.id).html() * $('.number' + check[i].dataset.id).html();
                amount += 1;
            }
        }
        $('.amount').html(amount);
        $('.total_price').html(price);
    }

    // 显示删除操作
    $('.cart_edit').on('click', function(event) {
        if (event.target.dataset.select == 'false') {
            event.target.dataset.select = 'true';
            $('.Delete').show();
        } else {
            event.target.dataset.select = 'false';
            $('.Delete').hide();
        }
    })

    // 删除
    deleteComm = function() {
        if (Arr.length == 0) {
            $.alert("请选择需要删除的商品！");
        } else {
            $.confirm("确定要删除选中的商品吗？", function() {
                for (var i = 0; i < Arr.length; i++) {
                    $('.delete' + Arr[i]).remove();
                }
                $('.shop_coom_wrap').each(function(index, el) {
                    if ($(this).children('.comm_list_wrap').children('.comm_wrap').length == 0) {
                        $(this).remove();
                    }
                });
                $('.Delete').hide();
                $('.cart_edit').get(0).dataset.select = 'false';
                Amount();
            }, function() {
                return false;
            })
        }
    }

})