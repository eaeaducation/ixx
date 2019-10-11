$('body').on('click', '.type_item', function () {
    var index = $(this).val(); //选择添加问题的类型
    if (index == "-1") {
        return;
    }
    var movie_box = '<div isover="no" class="movie_box"></div>';
    var Grade = $(".yd_box").find(".movie_box").length + 1;

    if (index == '1' || index == '2' || index == '3' || index == '4') {
        var wjdc_list = '<ul class="wjdc_list"></ul>'; //问答 单选 多选
        var danxuan = "";
        if (index == "1") {
            movie_box = '<div isover="no" class="movie_box"></div>';
            danxuan = '【单选】';
        } else if (index == "2") {
            movie_box = '<div isover="no"  class="movie_box"></div>';
            danxuan = '【多选】';
        } else if (index == "3") {
            movie_box = '<div isover="no"  class="movie_box"></div>';
            danxuan = '【单行问答】';
        } else if (index == "4") {
            movie_box = '<div isover="no"  class="movie_box"></div>';
            danxuan = '【多行问答】';
        }
        wjdc_list = $(wjdc_list).append(' <li><div class="tm_btitlt"><i class="nmb">' + Grade + '</i>. <i class="btwenzi">请编辑问题？</i><span class="tip_wz">' + danxuan + '</span></div>' + '' +
            '&nbsp;&nbsp;<input class="isbt" style="vertical-align: middle;" type="checkbox"  /><font color="red"><small>必填</small></font></li> ');
        if (index == "3") {
            wjdc_list = $(wjdc_list).append('<li><input name="input[' + Grade + '][text][]" class="layui-input layui-input-inline input_wenbk btwen_text btwen_text_dx" ></input></li>');
        }
        if (index == "4") {
            wjdc_list = $(wjdc_list).append('<li><textarea name="input[' + Grade + '][text][]" class="layui-textarea input_wenbk btwen_text btwen_text_dx" ></textarea></li>');
        }
        movie_box = $(movie_box).append(wjdc_list);
        movie_box = $(movie_box).append('<div class="dx_box" data-t="' + index + '"></div>');
    }


    $.msg.tips("成功创建" + danxuan + "题", 1);
    $(".yd_box").append(movie_box);
});

//鼠标移上去显示按钮
$("body").on('mouseenter', '.movie_box', function () {
    var html_cz = "<div class='kzqy_czbut'><a href='javascript:void(0)' class='layui-btn layui-btn-xs layui-btn-normal sy'>上移</a><a href='javascript:void(0)'  class='layui-btn layui-btn-xs layui-btn-normal xy'>下移</a><a href='javascript:void(0)'  class='layui-btn layui-btn-xs layui-btn-normal bianji'>编辑</a><a href='javascript:void(0)' class='layui-btn layui-btn-xs layui-btn-normal del' >删除</a></div>"
    $(this).css({
        "border": "1px solid #0099ff"
    });
    $(this).children(".wjdc_list").after(html_cz);
});
$("body").on('mouseleave', '.movie_box', function () {
    $(this).css({
        "border": "1px solid #fff"
    });
    $(this).children(".kzqy_czbut").remove();
});


//下移
$('body').on("click", ".xy", function () {
    //文字的长度
    var leng = $(".yd_box").children(".movie_box").length;
    var dqgs = $(this).parent(".kzqy_czbut").parent(".movie_box").index();
    if (dqgs < leng - 1) {
        var czxx = $(this).parent(".kzqy_czbut").parent(".movie_box");
        var xyghtml = czxx.next().html();
        var syghtml = czxx.html();
        czxx.next().html(syghtml);
        czxx.html(xyghtml);
        //序号
        czxx.children(".wjdc_list").find(".nmb").text(dqgs + 1);
        czxx.next().children(".wjdc_list").find(".nmb").text(dqgs + 2);
    } else {
        $.msg.tips("到底了");
    }
});
//上移
$('body').on("click", ".sy", function () {
    //文字的长度
    var leng = $(".yd_box").children(".movie_box").length;
    var dqgs = $(this).parent(".kzqy_czbut").parent(".movie_box").index();
    if (dqgs > 0) {
        var czxx = $(this).parent(".kzqy_czbut").parent(".movie_box");
        var xyghtml = czxx.prev().html();
        var syghtml = czxx.html();
        czxx.prev().html(syghtml);
        czxx.html(xyghtml);
        //序号
        czxx.children(".wjdc_list").find(".nmb").text(dqgs + 1);
        czxx.prev().children(".wjdc_list").find(".nmb").text(dqgs);

    } else {
        $.msg.tips("到头了");
    }
});
//删除
$('body').on("click", ".del", function () {
    var czxx = $(this).parent(".kzqy_czbut").parent(".movie_box");
    czxx.remove();
    var xh_num = 1;
    //重新编号
    $('.yd_box').find(".movie_box").each(function () {
        var cc = $(this).find(".nmb").html(xh_num);
        xh_num++;
    });
});

//编辑
$('body').on("click", ".bianji", function () {
    //编辑的时候禁止其他操作
    $(this).siblings().hide();
    //$(this).parent(".kzqy_czbut").parent(".movie_box").unbind("hover");
    var dxtm = $(".dxuan").html();
    var duoxtm = $(".duoxuan").html();
    var tktm = $(".tktm").html();
    var jztm = $(".jztm").html();
    //接受编辑内容的容器
    var dx_rq = $(this).parent(".kzqy_czbut").parent(".movie_box").find(".dx_box");
    var title = dx_rq.attr("data-t");
    //$.msg.tips(title);
    //题目选项的个数
    var timlrxm = $(this).parent(".kzqy_czbut").parent(".movie_box").children(".wjdc_list").children("li").length;

    //单选题目
    if (title == 1) {
        dx_rq.show().html(dxtm);
        //模具题目选项的个数
        var bjxm_length = dx_rq.find(".title_itram").children(".kzjxx_iteam").length;
        var dxtxx_html = dx_rq.find(".title_itram").children(".kzjxx_iteam").html();
        //添加选项题目
        for (var i_tmxx = bjxm_length; i_tmxx < timlrxm - 1; i_tmxx++) {
            dx_rq.find(".title_itram").append("<div class='kzjxx_iteam'>" + dxtxx_html + "</div>");
        }
        //赋值文本框
        //题目标题
        var texte_bt_val = $(this).parent(".kzqy_czbut").parent(".movie_box").find(".wjdc_list").children("li").eq(0).find(".tm_btitlt").children(".btwenzi").text();
        dx_rq.find(".btwen_text").val(texte_bt_val);
        //遍历题目项目的文字
        var bjjs = 0;
        $(this).parent(".kzqy_czbut").parent(".movie_box").find(".wjdc_list").children("li").each(function () {
            //可选框框
            var ktksfcz = $(this).find("input").hasClass("wenb_input");
            if (ktksfcz) {
                var jsxz_kk = $(this).index();
                dx_rq.find(".title_itram").children(".kzjxx_iteam").eq(jsxz_kk - 1).find("label").remove();
            }
            //题目选项
            var texte_val = $(this).find("span").text();
            dx_rq.find(".title_itram").children(".kzjxx_iteam").eq(bjjs - 1).find(".input_wenbk").val(texte_val);
            bjjs++

        });
    }
    //多选题目
    if (title == 2) {
        dx_rq.show().html(duoxtm);
        //模具题目选项的个数
        var bjxm_length = dx_rq.find(".title_itram").children(".kzjxx_iteam").length;
        var dxtxx_html = dx_rq.find(".title_itram").children(".kzjxx_iteam").html();
        //添加选项题目
        for (var i_tmxx = bjxm_length; i_tmxx < timlrxm - 1; i_tmxx++) {
            dx_rq.find(".title_itram").append("<div class='kzjxx_iteam'>" + dxtxx_html + "</div>");
            //$.msg.tips(i_tmxx);
        }
        //赋值文本框
        //题目标题
        var texte_bt_val = $(this).parent(".kzqy_czbut").parent(".movie_box").find(".wjdc_list").children("li").eq(0).find(".tm_btitlt").children(".btwenzi").text();
        dx_rq.find(".btwen_text").val(texte_bt_val);
        //遍历题目项目的文字
        var bjjs = 0;
        $(this).parent(".kzqy_czbut").parent(".movie_box").find(".wjdc_list").children("li").each(function () {
            //可选框框
            var ktksfcz = $(this).find("input").hasClass("wenb_input");
            if (ktksfcz) {
                var jsxz_kk = $(this).index();
                dx_rq.find(".title_itram").children(".kzjxx_iteam").eq(jsxz_kk - 1).find("label").remove();
            }
            //题目选项
            var texte_val = $(this).find("span").text();
            dx_rq.find(".title_itram").children(".kzjxx_iteam").eq(bjjs - 1).find(".input_wenbk").val(texte_val);
            bjjs++

        });
    }

    //填空题目 单行文本
    if (title == 3) {
        dx_rq.show().html(tktm);
        //赋值文本框
        //题目标题
        var texte_bt_val = $(this).parent(".kzqy_czbut").parent(".movie_box").find(".wjdc_list").children("li").eq(0).find(".tm_btitlt").children(".btwenzi").text();
        dx_rq.find(".btwen_text").val(texte_bt_val);
    }

    //填空题目 多行文本
    if (title == 4) {
        dx_rq.show().html(tktm);
        //赋值文本框
        //题目标题
        var texte_bt_val = $(this).parent(".kzqy_czbut").parent(".movie_box").find(".wjdc_list").children("li").eq(0).find(".tm_btitlt").children(".btwenzi").text();
        dx_rq.find(".btwen_text").val(texte_bt_val);
    }

});

//增加选项
$('body').on("click", ".zjxx", function () {
    var zjxx_html = $(this).prev(".title_itram").children(".kzjxx_iteam").html();
    $(this).prev(".title_itram").append("<div class='kzjxx_iteam'>" + zjxx_html + "</div>");
});

//删除一行
$('body').on("click", ".del_xm", function () {
    //获取编辑题目的个数
    var zuxxs_num = $(this).parent(".kzjxx_iteam").parent(".title_itram").children(".kzjxx_iteam").length;
    if (zuxxs_num > 1) {
        $(this).parent(".kzjxx_iteam").remove();
    } else {
        $.msg.tips("确认删除？");
    }
});
//取消编辑
$('body').on("click", ".dx_box .qxbj_but", function () {
    $(this).parent(".bjqxwc_box").parent(".dx_box").empty().hide();
    $(".movie_box").css({
        "border": "1px solid #fff"
    });
    $(".kzqy_czbut").remove();
    //
});
// body...
//完成编辑（编辑）
$('body').on("click", ".swcbj_but", function () {
    var jcxxxx = $(this).parent(".bjqxwc_box").parent(".dx_box"); //编辑题目区
    var querstionType = jcxxxx.attr("data-t"); //获取题目类型
    var Grade = $(this).parent(".bjqxwc_box").parent(".dx_box").parent(".movie_box").find(".nmb").html();
    var finishBox = $("#finishFrom");
    switch (querstionType) {
        case "1": //单选
        case "2": //多选
            //编辑题目选项的个数
            var bjtm_xm_length = jcxxxx.find(".title_itram").children(".kzjxx_iteam").length; //编辑选项的 选项个数
            var xmtit_length = jcxxxx.parent(".movie_box").children(".wjdc_list").children("li").length - 1; //题目选择的个数

            //赋值文本框
            //题目标题
            var texte_bt_val_bj = jcxxxx.find(".btwen_text").val(); //获取问题题目
            jcxxxx.parent(".movie_box").children(".wjdc_list").children("li").eq(0).find(".tm_btitlt").children(".btwenzi").text(texte_bt_val_bj); //将修改过的问题题目展示

            //删除选项
            for (var toljs = xmtit_length; toljs > 0; toljs--) {
                jcxxxx.parent(".movie_box").children(".wjdc_list").children("li").eq(toljs).remove();
            }
            //遍历题目项目的文字
            var bjjs_bj = 0;
            jcxxxx.children(".title_itram").children(".kzjxx_iteam").each(function () {
                //题目选项
                var texte_val_bj = $(this).find(".input_wenbk").val(); //获取填写信息
                var inputType = 'radio';
                //jcxxxx.parent(".movie_box").children(".wjdc_list").children("li").eq(bjjs_bj + 1).find("span").text(texte_val_bj);
                if (querstionType == "2") {
                    inputType = 'checkbox';
                }
                var li = '<li><label class="think-radio"><input name="input[' + Grade + '][' + inputType + '][]" type="' + inputType + '" value=""><span>' + texte_val_bj + '</span></label></li>';
                jcxxxx.parent(".movie_box").children(".wjdc_list").append(li);

                bjjs_bj++
                //可填空
                var kxtk_yf = $(this).find(".fxk").is(':checked');
                if (kxtk_yf) {
                    //第几个被勾选
                    var jsxz = $(this).index();
                    //$.msg.tips(jsxz);
                    jcxxxx.parent(".movie_box").children(".wjdc_list").children("li").eq(jsxz + 1).find("span").after("<input name='' type='text' class='wenb_input'>");
                }
            });
            //完成编辑加一个状态
            $(this).parent(".bjqxwc_box").parent(".dx_box").parent(".movie_box").attr('isover', "yes");
            break;
        case "4":
            //完成编辑加一个状态
            $(this).parent(".bjqxwc_box").parent(".dx_box").parent(".movie_box").attr('isover', "yes");
            var texte_bt_val_bj = jcxxxx.find(".btwen_text").val(); //获取问题题目
            jcxxxx.parent(".movie_box").children(".wjdc_list").children("li").eq(0).find(".tm_btitlt").children(".btwenzi").text(texte_bt_val_bj); //将修改过的问题题目展示
            break;

        case "3":
            //完成编辑加一个状态
            $(this).parent(".bjqxwc_box").parent(".dx_box").parent(".movie_box").attr('isover', "yes");
            var texte_bt_val_bj = jcxxxx.find(".btwen_text").val(); //获取问题题目
            jcxxxx.parent(".movie_box").children(".wjdc_list").children("li").eq(0).find(".tm_btitlt").children(".btwenzi").text(texte_bt_val_bj); //将修改过的问题题目展示
            break;
    }
    //清除
    $(this).parent(".bjqxwc_box").parent(".dx_box").empty().hide();
});

