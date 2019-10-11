function getRequest() {
    var url = window.location.search; //获取url中"?"符后的字串
    var theRequest = new Object();
    if (url.indexOf("?") != -1) {
        var str = url.substr(1);
        strs = str.split("&");
        for (var i = 0; i < strs.length; i++) {

            theRequest[strs[i].split("=")[0]] = decodeURI(strs[i].split("=")[1]);

        }
    }
    return theRequest;
}

$(function () {
    FastClick.attach(document.body);
    $("button[name=submit]").on('click', function () {
        var mobile = $("input[name=mobile]")
        var password = $("input[name=password]")
        if (mobile.val() !== undefined && mobile.val() == '') {
            $.toptip('请输入手机号码');
            return false;
        }
        if (!/^(0|86|17951)?(13[0-9]|15[012356789]|166|17[3678]|18[0-9]|14[57])[0-9]{8}$/.test(mobile.val())) {
            $.toptip('手机号码格式不正确');
            return false;
        }
        if (password.val() !== undefined && password.val() == '') {
            $.toptip('请输入密码');
            return false;
        }
        if (password.val().length < 6) {
            $.toptip('密码应不少于6位');
            return false;
        }
        $.toptip('success', 3000, 'success')
    });
});