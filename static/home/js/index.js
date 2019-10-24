/**
 * Main Javascript
 */

//
////检查滚动位置和导航添加/删除背景
//function checkScrollPosition() {
//if($(window).scrollTop() > 70) {
//    $(".M-header").show();
//} else {
//    $(".M-header").hide();
//}
//} 
////判断浏览器高度；当高于50像素时 添加scroll，否则减掉srcoll
//
//$(window).on("scroll", function() {
//  checkScrollPosition();    
//});


$(function() {
    initPage();
});

$(window).resize(function() {
    initPage();
});
$('.owl-clients-v1').owlCarousel({
    items: 7,
    autoPlay: 5000,
    itemsDesktop: [1000, 7],
    itemsDesktopSmall: [900, 4],
    itemsTablet: [600, 3],
    itemsMobile: [300, 2]
});
// Init Page
function initPage() {
    var w = $(window).width();

    // 二级页面图片切换
    $('.M2 .picbox .pic-number a').click(function() {
        var paged_elem = $(this).parent();
        var i = $(this).index();
        if ($(this).parent().prev('.pic-wrapper').children('.pic').eq(i).hasClass('white')) {
            paged_elem.addClass('white');
        } else {
            paged_elem.removeClass('white');
        }
        $(this).addClass('active').siblings().removeClass('active');
        $(this).parent().prev('.pic-wrapper').children('.pic').eq(i).fadeIn(500, function() {
            $(this).addClass('active');
        }).siblings().fadeOut(500, function() {
            $(this).removeClass('active');
        });
    });

    // 自动播放第一组
    var init1 = 0;
    var auto1 = function() {
            init1 < $('.design-2 .pic-number').children('a').length - 1 ? init1++ : init1 = 0;
            $('.design-2 .pic-number').children('a').eq(init1).click();
            move1 = setTimeout(auto1, 3000);
        }
        // 停止第一组自动播放
    $('.design-2 .pic-number').hover(function() {
        clearTimeout(move1);
    }, function() {
        move1 = setTimeout(auto1, 3000);
    });
    // 触发第一组
    $('.design-2').hover(function() {
        move1 = setTimeout(auto1, 3000);
    }, function() {
        clearTimeout(move1);
    });

    // 自动播放第二组
    var init2 = 0;
    var auto2 = function() {
            init2 < $('.design-3 .pic-number').children('a').length - 1 ? init2++ : init2 = 0;
            $('.design-3 .pic-number').children('a').eq(init2).click();
            move2 = setTimeout(auto2, 3000);
        }
        // 停止第二组自动播放
    $('.design-3 .pic-number').hover(function() {
        clearTimeout(move2);
    }, function() {
        move2 = setTimeout(auto2, 3000);
    });
    // 触发第二组
    $('.design-3').hover(function() {
        move2 = setTimeout(auto2, 3000);
    }, function() {
        clearTimeout(move2);
    });

    // 自动播放第三组
    var init3 = 0;
    var auto3 = function() {
            init3 < $('.design-4 .pic-number').children('a').length - 1 ? init3++ : init3 = 0;
            $('.design-4 .pic-number').children('a').eq(init3).click();
            move3 = setTimeout(auto3, 3000);
        }
        // 停止第三组自动播放
    $('.design-4 .pic-number').hover(function() {
        clearTimeout(move3);
    }, function() {
        move3 = setTimeout(auto3, 3000);
    });
    // 触发第三组
    $('.design-4').hover(function() {
        move3 = setTimeout(auto3, 3000);
    }, function() {
        clearTimeout(move3);
    });

    // 自动播放第四组
    var init4 = 0;
    var auto4 = function() {
            init4 < $('.design-5 .pic-number').children('a').length - 1 ? init4++ : init4 = 0;
            $('.design-5 .pic-number').children('a').eq(init4).click();
            move4 = setTimeout(auto4, 3000);
        }
        // 停止第四组自动播放
    $('.design-5 .pic-number').hover(function() {
        clearTimeout(move4);
    }, function() {
        move4 = setTimeout(auto4, 3000);
    });
    // 触发第四组
    $('.design-5').hover(function() {
        move4 = setTimeout(auto4, 3000);
    }, function() {
        clearTimeout(move4);
    });

    // 自动播放第五组
    var init5 = 0;
    var auto5 = function() {
            init5 < $('.design-6 .pic-number').children('a').length - 1 ? init5++ : init5 = 0;
            $('.design-6 .pic-number').children('a').eq(init5).click();
            move5 = setTimeout(auto5, 3000);
        }
        // 停止第五组自动播放
    $('.design-6 .pic-number').hover(function() {
        clearTimeout(move5);
    }, function() {
        move5 = setTimeout(auto5, 3000);
    });
    // 触发第五组
    $('.design-6').hover(function() {
        move5 = setTimeout(auto5, 3000);
    }, function() {
        clearTimeout(move5);
    });

    // M3系统切换选项卡
    $('.intro-6 .tab a').click(function() {
        if ($(this).hasClass('security')) {
            $('.intro-6').addClass('security');
        } else {
            $('.intro-6').removeClass('security');
        }
        $(this).addClass('active').siblings().removeClass('active');
    });

    // subnav
    var isHover1;
    var isHover2;
    var isHover3;
    $('.nav ul li.product').hover(function() {
        isHover1 = false;
        $('.product-subnav').show();
    }, function() {
        setTimeout(function() {
            if (!isHover1) {
                $('.product-subnav').hide();
            }
        }, 300);
    });
    $('.product-subnav').hover(function() {
        isHover1 = true;
        $('.product-subnav').show();
    }, function() {
        setTimeout(function() {
            if (isHover1) {
                $('.product-subnav').hide();
            }
        }, 300);
    });

    $('.nav ul li.brand').hover(function() {
        isHover2 = false;
        $('.brand-subnav').show();
    }, function() {
        setTimeout(function() {
            if (!isHover2) {
                $('.brand-subnav').hide();
            }
        }, 300);
    });
    $('.brand-subnav').hover(function() {
        isHover2 = true;
        $('.brand-subnav').show();
    }, function() {
        setTimeout(function() {
            if (isHover2) {
                $('.brand-subnav').hide();
            }
        }, 300);
    });

    $('.nav ul li.custom').hover(function() {
        isHover3 = false;
        $('.custom-subnav').show();
    }, function() {
        setTimeout(function() {
            if (!isHover3) {
                $('.custom-subnav').hide();
            }
        }, 300);
    });
    $('.custom-subnav').hover(function() {
        isHover3 = true;
        $('.custom-subnav').show();
    }, function() {
        setTimeout(function() {
            if (isHover3) {
                $('.custom-subnav').hide();
            }
        }, 300);
    });

    // home
    $(".feature-content ul li gray").hide();
    $('.feature-content ul li a').hover(function() {
        $(this).find(".gray").stop().fadeTo(500, 0.5)
        $(this).find(".content").stop().animate({
            bottom: '0'
        }, 500)

    }, function() {
        $(this).find(".gray").stop().fadeTo(500, 0)
        $(this).find(".content").stop().animate({
            bottom: '-200'
        }, 300)
        $(this).find(".content").animate({
                bottom: '-200'
            }, fast)
            // $(this).find(".content").addClass('active').siblings().removeClass('active');
    });

    // brand topic pic
    $('[node-type="screen-pic"]').width(w);
    $('[node-type="screen-pic"]').height(parseInt(w * 74 / 168));

    // 零售店
    $('.shop-btn').click(function() {
        $('body,html').animate({ scrollTop: 300 }, 500);
        $(this).toggleClass('active');
        $('.store-body').toggle();
    });

    // 高清相册
    $('.pic-play').click(function() {
        $('.pic-box').show();
        $('.gray-bg').show();
        $('.gray-bg').one('click', function() {
            $('.pic-box').hide();
            $('.gray-bg').hide();
        });
    });
    $('.pic-box .close').click(function() {
        $('.pic-box').hide();
        $('.gray-bg').hide();
    });
    var picLen = $('.pic-body ul li').length;
    $('.left-arrow, .right-arrow').click(function() {
        num = parseInt($(this).attr('number'));
        if ($(this).hasClass('left-arrow')) {
            if (num == 0) {
                num = picLen - 1;
            } else {
                num--;
            }
            $('.pic-body').find('li').eq(num).fadeIn(500, function() {
                $(this).addClass("active");
            }).siblings().fadeOut(500).removeClass("active");
        } else {
            if (num == picLen - 1) {
                num = 0;
            } else {
                num++;
            }
            $('.pic-body').find('li').eq(num).fadeIn(500, function() {
                $(this).addClass("active");
            }).siblings().fadeOut(500).removeClass("active");
        }
        $('.left-arrow, .right-arrow').attr('number', num);
    });

    // 私人订制页选项卡
    $('.custom-tab a').click(function() {
        var i = $(this).index();
        var scrollTop;
        switch (i) {
            case 0:
                scrollTop = 1455;
                break;
            case 1:
                scrollTop = 2050;
                break;
            case 2:
                scrollTop = 3843;
                break;
        }
        $("html, body").animate({
            scrollTop: scrollTop
        }, 500);
    });

    // 风尚版手机切换
    $('.fashion-10 .tab a').click(function() {
        var i = $(this).index();
        $(this).addClass('active').siblings().removeClass('active');
        $('.fashion-10 .tab-content .mobile').eq(i).addClass('active').siblings().removeClass('active');
    });

    // 巅峰系列手机切换
    $('.peak-1 .tab a').click(function() {
        var i = $(this).index();
        $(this).addClass('active').siblings().removeClass('active');
        if (i == 1) {
            $('.M3').addClass('two');
        } else {
            $('.M3').removeClass('two');
        }
        $('.peak-10 .tab a').eq(i).addClass('active').siblings().removeClass('active');
    });
    $('.peak-10 .tab a').click(function() {
        var i = $(this).index();
        $(this).addClass('active').siblings().removeClass('active');
        if (i == 1) {
            $('.M3').addClass('two');
        } else {
            $('.M3').removeClass('two');
        }
        $('.peak-1 .tab a').eq(i).addClass('active').siblings().removeClass('active');
    });

    // 右侧底部工具栏
    $('.tools-bar .customer').hover(function() {
        $('.tools-bar .nav-content').toggle();
    });
    $('.gotop').click(function() {
        $("html,body").animate({ scrollTop: 0 }, 500);
    });
}