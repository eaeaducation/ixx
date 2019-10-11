$(".video-nav li").hover(function(){
	$(this).addClass("active").siblings("").removeClass("active");
})
$(".subnav li").hover(function(){
})

$(".side ul li").hover(function(){
		$(this).find(".sidebox").stop().animate({"width":"154px"},200).css({"opacity":"1","filter":"Alpha(opacity=100)","background":"#ae1c1c"})	
},function(){
	$(this).find(".sidebox").stop().animate({"width":"54px"},200).css({"opacity":"0.8","filter":"Alpha(opacity=80)","background":"#000"})	
});
$(".weixin").hover(function(){
	$(this).find(".wx_erm").show();	
},function(){
	$(this).find(".wx_erm").hide();	
});



function goTop(){
	$('html,body').animate({'scrollTop':0},300);
}
