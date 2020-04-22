/**
 * Created by qiyq on 2016/12/14.
 */
$(function () {
    var currentUrl = window.location.href;
    var urlArr = currentUrl.split("home");
    //针对url特别优化
    if (urlArr[1].indexOf('/appointment/coaches') > -1) {
        currentUrl = urlArr[0] + 'home/appointment/types';
    }
    if ((urlArr[1].indexOf("/supervison/training") > -1) || (urlArr[1].indexOf("/supervison/teaching_img") > -1)) {
        currentUrl = urlArr[0] + 'admin/statistics/appointments';
    }
    if (urlArr[1].indexOf("/term/info") > -1) {
        currentUrl = urlArr[0] + 'admin/term';
    }
    var $ulTitle = $(".navigation-main>li>ul>li");
    for (var i = 0; i < $ulTitle.length; i++) {
        if (currentUrl.indexOf($($ulTitle[i]).find("a").attr("href")) > -1) {
            $ulTitle.removeClass("active");
            $(".navigation-main>li").removeClass("active");
            $($ulTitle[i]).parents('li').addClass("active");
            $($ulTitle[i]).addClass("active");
        }
    }
    //点击选择侧边栏
    $ulTitle.on("click", function () {
        $(this).siblings().removeClass("active");
        $(this).parents('li').siblings().removeClass("active");
        $(this).parents('li').addClass("active");
        $(this).addClass("active");
    });
});