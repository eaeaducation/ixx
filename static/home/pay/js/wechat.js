$(function () {
    $(".next").click(function (e) {
        var total_money = $('[name="total_money"]').val();
        var name = $('[name="name"]').val();
        var mobile = $('[name="mobile"]').val();

        if (name === '') {
            $.toast('姓名不能为空', 'text');
            return false;
        }
        if (mobile === '') {
            $.toast('联系电话不能为空', 'text');
            return false;
        }
        if (total_money === '') {
            $.toast('消费金额不能为空', 'text');
            return false;
        }

        var data = $("form").serialize();
        $.showLoading();
        $.post(url, data, function (res) {
            $.hideLoading();
            if (res.code == 1) {
                function onBridgeReady() {
                    WeixinJSBridge.invoke(
                        'getBrandWCPayRequest', res.data.json,
                        function (s) {
                            if (s.err_msg == "get_brand_wcpay_request:ok") {
                                $.alert('支付成功!', function () {
                                    window.location.replace(paySuccessUrl + '?oid=' + res.data.oid);
                                })
                            } else {
                                $.alert('支付失败', function () {
                                    window.location.replace(payFailUrl + '?msg=支付失败');
                                })
                            }
                        });
                }

                if (typeof WeixinJSBridge == "undefined") {
                    if (document.addEventListener) {
                        document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
                    } else if (document.attachEvent) {
                        document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
                        document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
                    }
                } else {
                    onBridgeReady();
                }
            } else {
                $.alert(res.msg)
            }
        });
    });
});