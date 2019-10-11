// JavaScript Document
// $(function(){
//
// });

var lottery={
    index:0,	//当前转动到哪个位置
    count:9,	//总共有多少个位置
    timer:0,	//setTimeout的ID，用clearTimeout清除
    speed:200,	//初始转动速度
    times:0,	//转动次数
    cycle:21,	//转动基本次数：即至少需要转动多少次再进入抽奖环节
    prize:4,	//中奖位置
    init:function(id){
        if ($("#"+id).find(".lottery-unit").length>0) {
            $lottery = $("#"+id);
            $units = $lottery.find(".lottery-unit");
            this.obj = $lottery;
            this.count = $units.length;
            $lottery.find(".lottery-unit-"+this.index).addClass("active");
        };
    },
    roll:function(){
        var index = this.index;
        var count = this.count;
        var lottery = this.obj;
        $(lottery).find(".lottery-unit-"+index).removeClass("active");
        index += 1;
        if (index>count){
            index = 0;
        };
        $(lottery).find(".lottery-unit-"+index).addClass("active");
        this.index=index;
        return false;
    },
    stop:function(index){
        this.prize=index;
        return false;
    }
};

function roll(){
    lottery.times += 1;
    lottery.roll();
    if (lottery.times > lottery.cycle+10 && lottery.prize==lottery.index) {
        $('#giftname').html(p);
        $('#giftimg').attr('src',n);
        showgiftbox();
        //alert(lottery.prize+' / '+lottery.index);
        clearTimeout(lottery.timer);
        //lottery.prize=4;
        lottery.times=0;
        click=false;
    }else{
        if (lottery.times<lottery.cycle) {
            lottery.speed -= 10;
        }else if(lottery.times==lottery.cycle) {
            //var index = Math.random()*(lottery.count)|0;
            //lottery.prize = index;
        }else{
            if (lottery.times > lottery.cycle+10 && ((lottery.prize==0 && lottery.index==7) || lottery.prize==lottery.index+1)) {
                lottery.speed += 110;
            }else{
                lottery.speed += 20;
            }
        }
        if (lottery.speed<40) {
            lottery.speed=40;
        };
        //console.log(lottery.times+'^^^^^^'+lottery.speed+'^^^^^^^'+lottery.prize);
        lottery.timer = setTimeout(roll,lottery.speed);
    }
    return false;
}
var click = false;
var n = 8;
var p = '';
$(function(){
    $('#start').click(function(){
        if(click){ return false; }
        $.getJSON('./js/pagedata.json', function (data) {
            //给所有页面设置logo
            var res = data.data;
            var lastres = prizeRand(res);

            function prizeRand(oArr) {
                var sum = 0;    // 总和
                var rand = 0;   // 每次循环产生的随机数
                var result = 0; // 返回的对象的key

                for (var i in oArr) {
                    sum += eval(oArr[i].probaility.value);
                }
                // 思路就是如果设置的数落在随机数内，则返回，否则减去本次的数
                for (var i in oArr) {
                    rand = Math.floor(Math.random()*sum + 1);
                    if (eval(oArr[i].probaility.value) >= rand) {
                        result = i;
                        break;
                    } else {
                        sum -= eval(oArr[i].probaility.value);
                    }
                }
                return result;
            }
            var s = '';
            if (lastres == 3){
                s = 7;
            }else if(lastres == 5){
                s = 3;
            }else if(lastres == 7){
                s = 5;
            }else {
                s = lastres;
            }
            p = res[lastres].reward.value;
            n = res[lastres].background.value;
            lottery.init('lottery');
            lottery.speed=100;
            lottery.prize=s;
            click=true;
            roll();
        });
    });

    $('.getgift').click(function(){
        if($(this).hasClass('notread')){
            $('.rightnow').hide();
            $(this).removeClass('notread');
            $('.giftfm').removeClass('hidden');
        }else{
            hidegiftbox();
        }
    });
    //关闭
    $('.closegift').click(function(){
        $('.rightnow').show();
        hidegiftbox();
    });
    //规则
    $('.showrolebox').click(function(){
        showrolebox();
    });
    $('.closerolebox').click(function(){
        hiderolebox();
    });
});
function showgiftbox(){
    $('.giftfm').addClass('hidden');
    $('.getgift').addClass('notread');
    $('.cover').fadeIn(500,function(){
        $('.giftout,.giftbg,.giftclose').show();
    });
}
function hidegiftbox(){
    $('.giftout,.giftbg,.giftclose').hide(function(){
        $('.cover').fadeOut();
    });
}
function showrolebox(){
    $('.cover').fadeIn();
    $('.rolebox').show();
}
function hiderolebox(){
    $('.rolebox').hide(function(){
        $('.cover').fadeOut();
    });
}