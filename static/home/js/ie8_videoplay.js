/**
 * videoplay
 */
 
$(function(){
	if ($('.video-module').length > 0) {
		autoPlay('video-container', 'video-nav', 'video-clear', 1000, 3000);
	}
});


function autoPlay(container, nav, clear, animateTime, infiniteTime){
	var init=0;
	var move;
	var containerElem = $('[node-type="'+container+'"]');
	var navElem = $('[node-type="'+nav+'"]');
	var clearElem = $('[node-type="'+clear+'"]');
	var len=parseInt(containerElem.find('.picbox').length);
		
	// 点击播放
	navElem.children('li').hover(function(){
		init = $(this).index();
		$(this).addClass("active").siblings().removeClass("active");
		containerElem.find('.picbox').eq(init).fadeIn(animateTime, function(){
			$(this).addClass("active");
		}).siblings().fadeOut(animateTime).removeClass("active");
	});
	navElem.children('li').click(function(){
		init = $(this).index();
		$(this).addClass("active").siblings().removeClass("active");
		containerElem.find('.picbox').eq(init).fadeIn(animateTime, function(){
			$(this).addClass("active");
		}).siblings().fadeOut(animateTime).removeClass("active");
	});
	
	
	// 自动轮播
	var auto = function(){
		init < navElem.children('li').length -1 ? init++ : init = 0;
		navElem.children('li').eq(init).click();
		move = setTimeout(auto,infiniteTime);
	}
	
	// 鼠标放上清除自动轮播
	clearElem.hover(function(){
		clearTimeout(move);
	},function(){
		move = setTimeout(auto,infiniteTime);
	});
	move = setTimeout(auto,infiniteTime);
}