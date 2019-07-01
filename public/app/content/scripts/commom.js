// foot定位底部函数
function footerfixed() {
    $("#footer").css("position", "static");
    var bodyheitht = $(document.body).height();
    var windowheight = $(window).height();
    if (bodyheitht < windowheight) {
        $("#footer").css({
            "position": "fixed",
            "bottom": 0
        });
    } else {
        $("#footer").css({
            "position": "static",
            "bottom": "atuo"
        });
    }
}
$(function() {
    // foot定位底部
    footerfixed();
    $(window).resize(function() {
        footerfixed();
    });

    //启用fastclick
    // FastClick.attach(document.body);


})

//返回函数
function historyback() {
    if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) { //判断iPhone|iPad|iPod|iOS
        // window.webkit.messageHandlers.backClick();
        // window.webkit.messageHandlers.backClick.postMessage();
        window.webkit.messageHandlers.backClick.postMessage('back!!');
    } else if (/(Android)/i.test(navigator.userAgent)) { //判断Android
        Android.backToApp();
    } else { //pc
        window.history.back(-1);
    };
}

function layout(){
    if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) { //判断iPhone|iPad|iPod|iOS
        window.webkit.messageHandlers.outClick.postMessage('back!!');
    } else if (/(Android)/i.test(navigator.userAgent)) { //判断Android
        Android.outClick();
    } else { //pc
    };
}

//赛心情发生

function send_mood(){
    if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) { //判断iPhone|iPad|iPod|iOS
        window.webkit.messageHandlers.sendClick.postMessage('back!!');
    } else if (/(Android)/i.test(navigator.userAgent)) { //判断Android
        Android.sendClick();
    } else { //pc
    };
}

function GetQueryString(name)
{
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if(r!=null)return  unescape(r[2]); return null;
}

//刷新页面

function reLoad(){
    location.reload()
}

//教孩视频详情
function videoClick(id){
    var url = http_url+"app/teachchild/details.html?id="+id;
    // location.href = url;
    if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) { //判断iPhone|iPad|iPod|iOS
        window.webkit.messageHandlers.videoClick.postMessage(url);
    } else if (/(Android)/i.test(navigator.userAgent)) { //判断Android
        Android.videoClick(url);
    } else { //pc
    };
}

//跳视频首页
function videoList() {
    if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) { //判断iPhone|iPad|iPod|iOS
        // window.webkit.messageHandlers.backClick();
        // window.webkit.messageHandlers.backClick.postMessage();
        window.webkit.messageHandlers.videoListClick.postMessage('back!!');
    } else if (/(Android)/i.test(navigator.userAgent)) { //判断Android
        Android.videoListClick();
    } else { //pc
    };
}

//跳上传视频页面
function videoUpload() {
    if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) { //判断iPhone|iPad|iPod|iOS
        // window.webkit.messageHandlers.backClick();
        // window.webkit.messageHandlers.backClick.postMessage();
        window.webkit.messageHandlers.videoUploadClick.postMessage('back!!');
    } else if (/(Android)/i.test(navigator.userAgent)) { //判断Android
        Android.videoUploadClick();
    } else { //pc
    };
}

//视频下架提示
function forbidden(){
    $.toast('该视频已过期且下架，无法观看','forbidden');
}

function ago_back(){
    ago = GetQueryString('ago');
    if(ago==null || ago == 'undefined'){
        historyback();
    }else{
        window.history.back(-1);
    }

}
function GetQueryString(name)
{
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if(r!=null)return  unescape(r[2]); return null;
}

function getExplorerInfo() {
    var explorer = window.navigator.userAgent.toLowerCase();
    //ie 
    if (explorer.indexOf("msie") >= 0) {
        var ver = explorer.match(/msie ([\d.]+)/)[1];
        return { type: "IE", version: ver };
    }
    //firefox 
    else if (explorer.indexOf("firefox") >= 0) {
        var ver = explorer.match(/firefox\/([\d.]+)/)[1];
        return { type: "Firefox", version: ver };
    }
    //Chrome
    else if (explorer.indexOf("chrome") >= 0) {
        var ver = explorer.match(/chrome\/([\d.]+)/)[1];
        return { type: "Chrome", version: ver };
    }
    //Opera
    else if (explorer.indexOf("opera") >= 0) {
        var ver = explorer.match(/opera.([\d.]+)/)[1];
        return { type: "Opera", version: ver };
    }
    //Safari
    else if (explorer.indexOf("Safari") >= 0) {
        var ver = explorer.match(/version\/([\d.]+)/)[1];
        return { type: "Safari", version: ver };
    }
}
//计算版本号大小,转化大小
function toNum(a){
    var a=a.toString();
    var c=a.split('.');
    var num_place=["","0","00","000","0000"],r=num_place.reverse();
    for (var i=0;i<c.length;i++){
        var len=c[i].length;
        c[i]=r[len]+c[i];
    }
    var res= c.join('');
    return res;
}


