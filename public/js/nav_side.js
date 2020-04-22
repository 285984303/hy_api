$(function(){
	var currentUrl = window.location.href;
	if (currentUrl.indexOf("admin") > -1) {
		var urlArr = currentUrl.split("admin/");
		if (!urlArr[1]) {
			currentUrl = urlArr[0] + '/school/student/create';
			return;
		}
		//针对url特别优化
		if (urlArr[1].indexOf('school/product/create') > -1) {
			currentUrl = urlArr[0] + 'admin/school/setting';
		}
		//针对url特别优化
		if (urlArr[1].indexOf('school/student/edit') > -1) {
			currentUrl = urlArr[0] + 'admin/school/student/list';
		}
		//针对url特别优化
		if (urlArr[1].indexOf('admins/comment/detail') > -1) {
			currentUrl = urlArr[0] + 'admin/admins/coach/comment';
		}
		//针对url特别优化
		if (urlArr[1].indexOf('admins/modify') > -1) {
			currentUrl = urlArr[0] + 'admin/admins/list';
		}
		if (urlArr[1].indexOf('admins/create') > -1) {
			currentUrl = urlArr[0] + 'admin/admins/list';
		}
		if (urlArr[1].indexOf('role/list') > -1 || urlArr[1].indexOf('permission/list') > -1) {
			currentUrl = urlArr[0] + 'admin/member/list';
		}
		if (urlArr[1].indexOf('finance/wages/setting') > -1) {
			currentUrl = urlArr[0] + 'admin/finance/wages';
		}

		if (urlArr[1].indexOf('bus/setting') > -1) {
			currentUrl = urlArr[0] + 'admin/bus/appointment';
		}
		if (urlArr[1].indexOf('setting/hours') > -1) {
			currentUrl = urlArr[0] + 'admin/school/student/schedule_list';
		}

		if ((urlArr[1].indexOf("supervison/training") > -1) || (urlArr[1].indexOf("/supervison/teaching_img") > -1)) {
			currentUrl = urlArr[0] + 'admin/statistics/appointments';
		}

        if (urlArr[1].indexOf('statistics/appointments/change_list') > -1 || urlArr[1].indexOf('statistics/appointments/list') > -1) {
            currentUrl = urlArr[0] + 'admin/statistics/appointments/list';
        }
        // //训练情况
        if (urlArr[1].indexOf('statistics/appointments/cache') > -1 || urlArr[1].indexOf('supervison/teaching_img') > -1 || urlArr[1].indexOf('supervison/teaching_log') > -1) {
            currentUrl = urlArr[0] + 'admin/statistics/appointments/cache';
        }

		if (urlArr[1].indexOf("term/info") > -1) {
			currentUrl = urlArr[0] + 'admin/term';
		}
	}

	var $ulTitle = $(".navigation-main>li>ul>li");
	for(var i = 0;i < $ulTitle.length;i++){
		if (currentUrl.indexOf($($ulTitle[i]).find("a").attr("href")) > -1) {
			$($ulTitle[i]).parents('li').addClass("active");
			$($ulTitle[i]).addClass("active");
		}
	}
	//$(".laydate-icon").val(laydate.now());
	//点击选择侧边栏
	$ulTitle.on("click", function () {
		$(this).siblings().removeClass("active");
		$(this).parents('li').siblings().removeClass("active");
		$(this).parents('li').addClass("active");
		$(this).addClass("active");
	});
	$.fn.selectpicker.defaults = {
		noneSelectedText: '没有选中任何项',
		noneResultsText: '没有找到匹配项',
		countSelectedText: '选中{1}中的{0}项',
		maxOptionsText: ['超出限制 (最多选择{n}项)', '组选择超出限制(最多选择{n}组)'],
		multipleSeparator: ', ',
		selectAllText: '全选',
		deselectAllText: '取消全选'
	};
	function startLoading() {
		/**启动加载效果**/
		//需要动态创建load效果的DIV
		var objdiv = document.createElement("div");
		objdiv.id = "loader_container";
		objdiv.innerHTML = '<div  class="page-spinner-bar"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>';

		//设置背景效果的宽高
		var _scrollWidth = window.screen.width;
		var _scrollHeight = window.screen.height;
		objdiv.style.width = _scrollWidth + "px";
		objdiv.style.height = _scrollHeight + "px";
		document.body.appendChild(objdiv);
	}

	//$("select").selectpicker();
	function stopLoading() {
		/**停止加载效果**/
		var objdiv = document.getElementById("loader_container");
		if (objdiv != null) {
			document.body.removeChild(objdiv);
		}
	}
	$(document).ajaxStart(function () {
		startLoading();
	});
	$(document).ajaxStop(function () {
		stopLoading();
	});


});
function validateForm(form) {
    return form.validate({
        errorElement: "em",
        errorPlacement: function (error, element) {
            // Add the `help-block` class to the error element
            error.addClass("help-block");

            if (element.prop("type") === "checkbox") {
                error.insertAfter(element.parent("label"));
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function (element, errorClass, validClass) {
            $(element).parent().addClass("has-error").removeClass("has-success");
            $(element).next("span").addClass("glyphicon-remove").removeClass("glyphicon-ok");
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).parent().addClass("has-success").removeClass("has-error");
            $(element).next("span").addClass("glyphicon-ok").removeClass("glyphicon-remove");
        }
    }).form();
}
