//var basepath = "https://www.*****.cn/cjq/gua";
var basepath = "http://192.168.1.100:8085/portal-bos";
//转盘抽奖  /cjqh5/act/bigTurntable/doPrize.action   loginName
//中奖用户 /cjqh5/act/bigTurntable/getPrizeWall.action
//兑奖记录/cjqh5/act/bigTurntable/getPrizeRecord.action   loginName
// 倒计时
var interval = 1000;


//超级返现规则
$("#gz-b").on('click', function () {
    $(".zz").show();
    $(".cjfx").show();
});
$(".cjgz-c").on('click', function () {
    $(".zz").hide();
    $(".cjfx").hide();
});
//大转盘规则
$("#look-gz").on('click', function () {
    $(".zz").show();
    $(".zpgz").show();
});
$(".cjgz-c").on('click', function () {
    $(".zz").hide();
    $(".zpgz").hide();
});
//中奖纪录
$("#zjjl").on('click', function () {
    $(".zz").show();
    $(".zj").show();
});
$(".cjgz-c").on('click', function () {
    $(".zz").hide();
    $(".zj").hide();
});
//无次数弹框
$(".cjgz-c").on('click', function () {
    $(".wcs").hide();
    $(".zz").hide();
});

//获取参数
function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return null;
}

var login = getQueryString("login");
var loginName = getQueryString("loginName");
var isapp = getQueryString("isapp");
var memberId = getQueryString("memberId");

//是否在APP&是否登录 
if (isapp == 1) {
    if (login == 1) {
        $("#tzbtn").attr("href", "cjq:terminal");
    } else {
        $("#tzbtn").attr('href', 'cjq:login');
    }
} else {
    $("#share").hide();
    //$("#tzbtn").attr("href", "https://*****.html");
}

// 滚动-start
// 左边
var speedi = 80;
var colee2 = document.getElementById("colee2");
var colee1 = document.getElementById("colee1");
var colee = document.getElementById("colee");
colee2.innerHTML = colee1.innerHTML;

//克隆colee1为colee2
function Marquee1() {
    //当滚动至colee1与colee2交界时
    if (colee2.offsetTop - colee.scrollTop <= 0) {
        colee.scrollTop -= colee1.offsetHeight; //colee跳到最顶端
    } else {
        colee.scrollTop++
    }
}

var MyMar1 = setInterval(Marquee1, speedi) //设置定时器
// 右边
var coleer2 = document.getElementById("coleer2");
var coleer1 = document.getElementById("coleer1");
var coleer = document.getElementById("coleer");
coleer2.innerHTML = coleer1.innerHTML; //克隆colee1为colee2
function Marqueer1() {
    //当滚动至colee1与colee2交界时
    if (coleer2.offsetTop - coleer.scrollTop <= 0) {
        coleer.scrollTop -= coleer1.offsetHeight; //colee跳到最顶端
    } else {
        coleer.scrollTop++
    }
}

var MyMarr1 = setInterval(Marqueer1, speedi) //设置定时器
// console.log(num+"  "+arr[num])
// 滚动-end
// 中奖用户
jp = {
    '1': ["0", "0.1%加息券"],
    '2': ["1", "0.2%加息券"],
    '3': ["2", "0.3%加息券"],
    '4': ["3", "谢谢参与"],
    '5': ["4", "Iphone8"],
    '6': ["5", "0.5元"],
    '7': ["6", "0.1元"],
    '8': ["7", "10元"],
};
//抽奖代码
$(function () {
    var $btn = $('.g-lottery-img'); // 旋转的div
    var cishu = 4; //初始次数，由后台传入
    $('#cishu').html(cishu); //显示还剩下多少次抽奖机会
    var isture = 0; //是否正在抽奖
    var clickfunc = function () {
        $.getJSON("data.json", function (data) {
            var prize_arr = new Array();
            var arr = new Array();
            prize_arr[0] = data.data[0][0];
            prize_arr[1] = data.data[0][1];
            prize_arr[2] = data.data[0][2];
            prize_arr[3] = data.data[0][3];
            prize_arr[4] = data.data[0][4];
            prize_arr[5] = data.data[0][5];
            prize_arr[6] = data.data[0][6];
            prize_arr[7] = data.data[0][7];
            for (var i = 0; i < prize_arr.length; i++) {
                arr[prize_arr[i]['id']] = prize_arr[i]['v'];
            }
            var rid = prizeRand(arr);//根据概率获取奖项id
            var res = prize_arr[rid - 1]; //中奖项
            var prize = res['id'];
            switch (prize) {
                case 1:
                    rotateFunc(1, 25, '双季丰0.1%加息红包1');
                    break;
                case 2:
                    rotateFunc(2, 70, '双季丰满减红包10元2');
                    break;
                case  3:
                    rotateFunc(3, 115, '1元现金红包3');
                    break;
                case  4:
                    rotateFunc(4, 160, '财金币20枚4');
                    break;
                case 5:
                    rotateFunc(5, 203, '20元现金红包5');
                    break;
                case  6:
                    rotateFunc(6, 245, '双季丰0.5%加息红包(6)');
                    break;
                case 7:
                    rotateFunc(7, 290, '双季丰11满减红包50元(7)');
                    break;
                case 8:
                    rotateFunc(8, 340, '5元现金红包');
                    break;
            }
        });
        //         $.ajax({
        //             type: 'POST',
        //             url: 'cj',
        //             dataType: 'json',
        //             cache: false,
        //             error: function() {
        //                 alert('Sorry，出错了！');
        //                 return false;
        //             },
        //             success: function(json) {
        //             	console.log(json);
        //             	// 返回的中奖id
        //                 var prize = json.prize;
        //                 switch (prize) {
        //                     case '一等奖':
        //                         rotateFunc(1, 25, '双季丰0.1%加息红包1');
        //                         break;
        //                     case  '二等奖':
        //                         rotateFunc(2, 70, '双季丰满减红包10元2');
        //                         break;
        //                     case  '三等奖':
        //                         rotateFunc(3, 115, '1元现金红包3');
        //                         break;
        //                     case  '四等奖':
        //                         rotateFunc(4, 160, '财金币20枚4');
        //                         break;
        //                     case  '五等奖':
        //                         rotateFunc(5, 203, '20元现金红包5');
        //                         break;
        //                     case  '六等奖':
        //                         rotateFunc(6, 245, '双季丰0.5%加息红包(6)');
        //                         break;
        //                     case  '七等奖':
        //                         rotateFunc(7, 290, '双季丰11满减红包50元(7)');
        //                         break;
        //                     case '八等奖':
        //                         rotateFunc(8, 340, '5元现金红包');
        //                         break;
        //                 }
        // 		}
        // 	});
    };
    $(".zhizhen").click(function () {
        //判断是否投资然后是fou抽奖======================
        var touzi = "没投资11";
        if (touzi == "没投资") {
            $(".zz").show();
            $(".today").show();
            $(".cjgz-c").on('click', function () {
                $(".zz").hide();
                $(".today").hide();
            });
            $(".ok-img").on('click', function () {
                $(".zz").hide();
                $(".today").hide();
            });
        } else {
            $(".zz").hide()
            $(".today").hide();
            if (isture) return; // 如果在执行就退出
            isture = true; // 标志为 在执行
            if (cishu <= 0) { //当抽奖次数为0的时候执行
                $(".zz").show();
                $(".wcs").show();
                $(".ok-img").on('click', function () {
                    $(".wcs").hide();
                    $(".zz").hide();
                });
                // alert("没有次数了");
                $('#cishu').html(0); //次数显示为0
                isture = false;
            } else { //还有次数就执行
                cishu = cishu - 1; //执行转盘了则次数减1
                if (cishu <= 0) {
                    cishu = 0;
                }
                $('#cishu').html(cishu);
                clickfunc();
            }
        }
    });
    // 计算概率
    var prizeRand = function (arr) {
        var sum = 0;    // 总和
        var rand = 0;   // 每次循环产生的随机数
        var result = 0; // 返回的对象的key
        // 计算总和
        for (var i in arr) {
            sum += arr[i];
        }
        // 如果设置的数落在随机数内，则返回，否则减去本次的数
        for (var i in arr) {
            rand = Math.floor(Math.random() * sum + 1);
            if (arr[i] >= rand) {
                result = i;
                break;
            } else {
                sum -= arr[i];
            }
        }
        console.log(result);
        return result;
    };
    //抽奖执行
    var rotateFunc = function (awards, angle, text) {
        isture = true;
        $btn.stopRotate();
        $btn.rotate({
            angle: 0, //旋转的角度数
            duration: 4000, //旋转时间
            animateTo: angle + 1440, //给定的角度,让它根据得出来的结果加上1440度旋转
            callback: function () {
                isture = false; // 标志为 执行完毕
                // alert(text);
                $(".texts").html("恭喜您，已获得<br>" + text + "");
                console.log(text)
                $(".zz").show();
                $(".jl-tk").show();
                $(".cjgz-c").on('click', function () {
                    $(".zz").hide();
                    $(".jl-tk").hide();
                });
                $(".ok-img").on('click', function () {
                    $(".zz").hide();
                    $(".jl-tk").hide();
                    $(".ok-img").hide();
                    window.location.href = "http://edu-project.test/pop/pop/index";
                });
            }
        });
    };
});