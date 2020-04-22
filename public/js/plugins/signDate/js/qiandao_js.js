$(function () {
    var signFun = function () {

        var dateArray = [1, 2, 4, 6]; // 假设已经签到的

        var $dateBox = $("#js-qiandao-list"),
            $currentDate = $(".current-date"),
            $qiandaoBnt = $("#js-just-qiandao"),
            $dayTime = $("#dayTime"),
            $yearTime = $("#yearTime"),
        $weekTime = $("#weekTime"),
        _html = '',
            _handle = true,
            myDate = new Date();
        var week = myDate.getDay();
        //获取当前第几周
        var getMonthWeek = function (a, b, c) {
            /*
             a = d = 当前日期
             b = 6 - w = 当前周的还有几天过完(不算今天)
             a + b 的和在除以7 就是当天是当前月份的第几周
             */
            var date = new Date(a, parseInt(b) - 1, c), w = date.getDay(), d = date.getDate();
            return Math.ceil(
                (d + 6 - w) / 7
            );
        };
        switch (week){
            case 0:
                $weekTime.text("星期日"+" 第"+getMonthWeek(myDate.getYear(),myDate.getMonth()+1,myDate.getDate())+"周");
                break;
            case 1:
                $weekTime.text("星期一"+" 第"+getMonthWeek(myDate.getYear(),myDate.getMonth()+1,myDate.getDate())+"周");
                break;
            case 2:
                $weekTime.text("星期二"+" 第"+getMonthWeek(myDate.getYear(),myDate.getMonth()+1,myDate.getDate())+"周");
                break;
            case 3:
                $weekTime.text("星期三"+" 第"+getMonthWeek(myDate.getYear(),myDate.getMonth()+1,myDate.getDate())+"周");
                break;
            case 4:
                $weekTime.text("星期四"+" 第"+getMonthWeek(myDate.getYear(),myDate.getMonth()+1,myDate.getDate())+"周");
                break;
            case 5:
                $weekTime.text("星期五"+" 第"+getMonthWeek(myDate.getYear(),myDate.getMonth()+1,myDate.getDate())+"周");
                break;
            case 6:
                $weekTime.text("星期六"+" 第"+getMonthWeek(myDate.getYear(),myDate.getMonth()+1,myDate.getDate())+"周");
                break;
        }

        $currentDate.text(myDate.getFullYear() + '年' + parseInt(myDate.getMonth() + 1) + '月' + myDate.getDate() + '日');
        $dayTime.text(myDate.getDate());
        $yearTime.text(myDate.getFullYear() + '年' + parseInt(myDate.getMonth() + 1) + '月');

        var monthFirst = new Date(myDate.getFullYear(), parseInt(myDate.getMonth()), 1).getDay();

        var d = new Date(myDate.getFullYear(), parseInt(myDate.getMonth() + 1), 0);
        var totalDay = d.getDate(); //获取当前月的天数

        for (var i = 0; i < 42; i++) {
            _html += ' <li><div class="qiandao-icon"></div></li>'
        }
        $dateBox.html(_html) //生成日历网格

        var $dateLi = $dateBox.find("li");
        for (var i = 0; i < totalDay; i++) {
            $dateLi.eq(i + monthFirst).addClass("date" + parseInt(i + 1));
            for (var j = 0; j < dateArray.length; j++) {
                if (i == dateArray[j]) {
                    $dateLi.eq(i + monthFirst).addClass("qiandao");
                }
            }
        } //生成当月的日历且含已签到

        $(".date" + myDate.getDate()).addClass('able-qiandao');

        //$dateBox.on("click", "li", function() {
        //        if ($(this).hasClass('able-qiandao') && _handle) {
        //            $(this).addClass('qiandao');
        //            qiandaoFun();
        //        }
        //    }) //签到
        //
        //$qiandaoBnt.on("click", function() {
        //    if (_handle) {
        //        qiandaoFun();
        //    }
        //}); //签到

        function qiandaoFun() {
            $qiandaoBnt.addClass('actived');
            qianDao();
            _handle = false;
        }

        function qianDao() {
            $(".date" + myDate.getDate()).addClass('qiandao');
        }
    }();


});
