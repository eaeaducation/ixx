function validate(res) {
    if (res.length > 0) {
        $(".captcha").fadeOut(1000, function () {
            $(".code").fadeIn(100)
        });
    }
}

function alertMsg(msg, callback) {
    $("p.msg").html(msg);
    $(".msg-box").fadeIn(300, function () {
        setTimeout(function () {
            $(".msg-box").fadeOut(100);
            if (jQuery.isFunction(callback)) {
                callback()
            }
        }, 2000);
    })
}

$("button.reset").click(function (e) {
    if ($("input[name='luotest_response']").val() != '') {
        LUOCAPTCHA.reset();
        $(".code").fadeOut(1000, function () {
            $(".captcha").fadeIn(100);
        });
    }
});
$("button.submit").click(function (e) {
    var mobile = $("input[name=mobile]").val();
    var code = $("input[name=code]").val();
    var password = $("input[name=password]").val();
    var token = $("input[name=luotest_response]").val();
    if (token == '') {
        alertMsg("请先完成人机识别验证");
        return;
    }
    if (mobile == '') {
        alertMsg("请先输入手机号码");
        return;
    }
    if (password == '') {
        alertMsg("密码不能为空");
        return;
    }
    if (code == '') {
        alertMsg("验证码不能为空");
        return;
    }
    $.post('http://edu-api.test/register', {
        mobile: mobile,
        password: password,
        code: code,
    }, function (res) {
        if (!res.success) {
            alertMsg(res.msg);
            return;
        } else {
            alertMsg('注册成功!', goLogin)
        }
    })
});


function goLogin() {
    var mobile = $("input[name=mobile]").val();
    var password = $("input[name=password]").val();
    $.post('http://edu-api.test/login', {
        mobile: mobile,
        password: password
    }, function (res) {
        window.location.replace(app)
    })
}

var send = false;
var dcodeSecond = 60;
var codeSecond = 60;
$("span.sendCode").click(function () {
    if (send) return;
    var mobile = $("input[name=mobile]").val();
    var token = $("input[name=luotest_response]").val();
    if (token == '') {
        alertMsg("请点击重置验证<br>按钮重新验证")
        return;
    }
    if (mobile == '') {
        alertMsg("请先输入手机号码");
        return;
    }
    sendCode(mobile, token)
});
var countDown = null;
var countDownF = function () {
    if (codeSecond == 0) {
        $("span.sendCode").html("重新获取")
        codeSecond = dcodeSecond;
        send = false;
        clearInterval(countDown)
    } else {
        $("span.sendCode").html("(" + codeSecond + " s)");
        codeSecond--;
    }
};

function sendCode(mobile, token) {
    $.post('http://edu-api.test/luosimao', {mobile: mobile, token: token}, function (res) {
        if (!res.success) {
            if (res.code == 500) {
                alertMsg(res.msg, goLogin)
            } else {
                alertMsg(res.msg);
            }
        } else {
            send = true;
            countDown = setInterval(countDownF, 1000);
            alertMsg('验证码发送成功');
        }
    })
}