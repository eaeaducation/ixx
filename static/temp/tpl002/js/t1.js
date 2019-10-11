/**
 * Created by Administrator on 2018/6/2.
 */

layui.use(['form', 'layedit', 'laydate'], function () {
    var form = layui.form
        , layer = layui.layer
        , layedit = layui.layedit
        , laydate = layui.laydate;
    //自定义验证规则
    form.verify({
        pass: [/(.+){6,12}$/, '密码必须6到12位']
        , content: function (value) {
            layedit.sync(editIndex);
        }
        // ,vercode: [/(.+){4}$/, '验证码必须填写']
    });
    //监听提交
    form.on('submit(sub)', function (data) {
        var url="/api/v1/Shows/register";
        $.post(url, data.field, function (res) {
            if (res.success) {
                layer.alert(res.msg, {
                    icon: 6,
                    skin: 'layui-layer-lan' //样式类名
                    , closeBtn: 0
                }, function (index) {
                    layer.close(index);
                    window.location.href = '{:url("showtpl/activeBack")}?actid='+actid+'&uid='+res.data.id;
                });
            } else {
                layer.msg(res.msg, {icon: 5, time: 5000, shade: 0.2});
            }
        });
        return false;
    });
});
