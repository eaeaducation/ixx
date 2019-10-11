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
                $(document).ready(function () {
                    tradePay(res.data.json.tradeNo);
                });

                function ready(callback) {
                    if (window.AlipayJSBridge) {
                        callback && callback();
                    } else {
                        document.addEventListener('AlipayJSBridgeReady', callback, false);
                    }
                }

                function tradePay(tradeNO) {
                    ready(function () {
                        AlipayJSBridge.call("tradePay", {
                            tradeNO: tradeNO
                        }, function (data) {
                            if ("9000" == data.resultCode) {
                                $.alert("支付成功", function () {
                                    window.location.replace(paySuccessUrl + '?oid=' + res.data.oid);
                                });
                            } else {
                                $.alert("支付失败", function () {
                                    window.location.replace(payFailUrl + '?msg=支付失败');
                                });
                            }
                        });
                    });
                }
            } else {
                $.alert(res.msg)
            }
        });
    });
});