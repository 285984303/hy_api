@extends('home.base')

@section('title','预约申请')

@section('html_head')
    <link rel="stylesheet" href="{{ asset('css/student-mark-coah.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/student-mark-time.css') }}"/>
@endsection

@section('content')
<!-- Page header -->
    <div class="page-header page-header-default">
        <div class="breadcrumb-line">
            <ul class="breadcrumb">
                <i class="icon-home2 color-999"></i>
                <li><a href="javascript:;">当前功能</a></li>
                <li><a href="javascript:;">时段预约</a></li>
            </ul>
        </div>
    </div>
<!-- /page header -->
<div class="content">
<div class="panel panel-flat">
    <div class="student-mark-time-main">
         <div class="panel panel-white">
		<div class="panel-heading">
			<h6 class="panel-title"><i class="icon-database-time2 position-left"></i>时段预约</h6>
                    <div class="heading-elements">
                        <a class="modify-btn text_color_5897FB"></a>
                    </div>
		</div>
	</div>
        <div class="head">
            <img src="{{ asset('images/mark-time.png') }}" alt=""/>
        </div>
        <div class="coach-schedule-content">
            <table class="coach-schedule-content-table">
                <thead>
                <tr class="hourTr">
                    <th style="width: 100px;min-width: 100px">日期</th>
                    <th style="width: 100px;min-width: 100px">车辆</th>
                </tr>
                </thead>
                <tbody class="allValue"></tbody>
            </table>
        </div>
    </div>
    <div class="foot-btn">
        <div class="foot-sub">
            <a href="javascript:history.back()"><img src="{{ asset('images/shang.png') }}"/></a>
            <a onclick="submit()"><img src="{{ asset('images/next-btn.png') }}"/></a>
        </div>
    </div>
  </div>
</div>
@endsection

@section('script')
    <script>
        function getUrlParam(name) {
            var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
            if (results != null) {
                return decodeURIComponent(results[1]) || '';
            }
            return null;
        }
        function getTimestamp(time) {
            var times = time.split(':');
            var date = new Date();
            date.setHours(times[0]);
            date.setMinutes(times[1]);
            return date.getTime();
        }
        var timeIdArr = [];


        // 预约
        function submit() {
            // 弹窗提示详情
            var htmlIncome = '<div class="modalIncome">' +
                    '<div>' +
                    '<label>教练姓名:</label><span  id="incomeName"></span></div>' +
                    '<div>' +
                    '<label>车牌号码:</label><span  id="carNo"></span></div>' +
                    '<div class="timeLine">' +
                    '<label>时间段:</label></div></div>';

            layer.open({
                type: 1,
                id: 'incomeModal',
                btn: ['确认', '取消'],
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                shift: 2,
                area: ['760px', '400px'],
                title: ['教练预约'],
                shadeClose: true, //开启遮罩关闭
                content: htmlIncome,
                success: function () {
                    function getTime(id, index) {
                        $.ajax({
                            type: "GET",
                            dataType: "json",
                            url: "/home/appointment/detail/" + id,
                            success: function (data) {
                                if (data.result == "success") {
                                    $(".modalIncome .timeLine").append('<span>' + data.data.start_time + ' - ' + data.data.finish_time + '</span>');
                                    if (index == 0) {
                                        $("#incomeName").text(data.data.admin.admin_name);
                                        if (data.data.vehicle) {
                                            $("#carNo").text(data.data.vehicle.car_num);
                                        }
                                    }
                                }
                            }
                        });
                    }

                    for (var i in timeIdArr) {
                        getTime(timeIdArr[i], i);
                    }
                },
                yes: function (index) {
                    $.ajax({
                        {{--layer.confirm('确定预约此时段吗？', {--}}

                        type: "POST",
                        dataType: "json",
                        data: {
                            type_id: getUrlParam('appointment_type_id'),
                            ids: timeIdArr,
                            _token: '{{ csrf_token() }}'
                        },
                        url: "/home/appointment/submit",
                        success: function (data) {
                            if (data.result == "success") {
                                layer.closeAll();
                                layer.msg('预约教练成功!', {time: 2000, icon: 6}, function () {
                                    window.location.href = '/home/appointment/buses?date=' + $("#born").val();
                                });
                            } else {
                                layer.msg('预约教练失败!' + data.err_message, {time: 2000, icon: 5});
                            }
                        },
                        error: function (data) {
                            layer.msg('预约教练失败:' + data.err_message, {time: 2000, icon: 5});
                        }
                    });

                }
            });

        }


        function addTime(timeId, that) {
            for (var i in timeIdArr) {
                if (timeId == timeIdArr[i]) {
                    $(that).removeClass("green");
                    timeIdArr.splice(i, 1);
                    return;
                }
            }
            timeIdArr.push(timeId);
            $(that).addClass("green");
        }
        // 排课数据
        function getData() {
            var coach_id = getUrlParam('coach_id');
            var myDate = new Date();
            var currentDate = myDate.getFullYear() + '-' + (myDate.getMonth() + 1) + '-' + myDate.getDate();
            $.ajax({
                url: '/home/appointments/coach/' + coach_id,
                type: "GET",
                dataType: "json",
                success: function (data) {
                    if (data.result == "success") {
                        //获取时间戳
                        var timestamp = Date.parse(currentDate.replace(/-/g, "/"));
                        var coachInfo = data.data.appointments;
                        for (var i = 0; i < coachInfo.length; i++) {
                            var time = timestamp + 24 * 60 * 60 * 1000 * i;
                            var $date = new Date(time);
                            var month = $date.getMonth() + 1;
                            var day = $date.getDate();
                            var date = month + '月' + day + '日';
                            var idFlag = "coachTime" + i;

                            var htmlTemp = '<tr><td style="width:100px;line-height:30px;">' + date + '</td>' +
                                    '<td style="width:100px;line-height:30px;">' + data.data.coach.vehicle.car_num + '</td>' +
                                    '<td  class="deleteHtml">' +
                                    '<ul id="' + idFlag + '"></ul></td></tr>';
                            $(".allValue").append(htmlTemp);
                            var arr = coachInfo[i];
                            for (var j in arr) {
                                var color = '';
                                if (arr[j].status == "ABLE") {
                                    if (arr[j].is_valid == "T") {
                                        color = "rgb-FF9933";
                                    } else {
                                        color = "rgb-5897FB";
                                    }
                                } else if (arr[j].status == "DISABLE") {
                                    color = "rgb-CC3300";
                                } else {
                                    color = "rgb-00FF33";
                                }
                                var margin = '';

                                if (j == 0) {
                                    margin = (getTimestamp(arr[j].start_time) - getTimestamp("07:00")) / 60000 * minWidth;
                                } else {
                                    if (getTimestamp(arr[j].start_time) >= getTimestamp(arr[j - 1].finish_time)) {
                                        margin = (getTimestamp(arr[j].start_time) - getTimestamp(arr[j - 1].finish_time)) / 60000 * minWidth
                                                + (getTimestamp(arr[j].start_time) - getTimestamp(arr[j - 1].finish_time)) / 3600000;
                                    }
                                }
                                var width = (getTimestamp(arr[j].finish_time) - getTimestamp(arr[j].start_time)) / 60000 * minWidth;
                                var schedule = (arr[j].status == "ABLE" || arr[j].status == "DISABLE") ? "1" : "0";
                                var html = '<li class="' + color + '" style="margin-left:' + margin + 'px;width:' + width + 'px;' +
                                        'height:30px;border-right:1px solid #ccc;" ' +
                                        'onclick="addTime(' + arr[j].id + ',this)" ' +
                                        'isSchedule = "' + schedule + '"  isOpen = "false">' +
                                        '<input type="hidden" value="' + arr[j].id + '" ></li>';
                                $('#coachTime' + i).append(html);
                            }


                        }
                    }
                }
            });

        }
        $(function () {
            $(".hourTr").append(' <th colspan="16" class="coach-table"> ' +
                    '<ul class="timeLine"> </ul> </th>');
            var liHtml = '';
            var $timeLine = $(".timeLine");
            var ulWidth = $timeLine.width() - 50;
            minWidth = (ulWidth / (16 * 60)).toFixed(2);
            var diffWidth = 60 * minWidth;

            for (var i = 0; i < 15; i++) {
                if (i == 0) {
                    liHtml = '<li style="width:' + diffWidth + 'px">' +
                            '<span class="pull-left">07:00</span>' +
                            '<span class="pull-right">0' + (i + 8) + ':00</span></li>';
                } else if (i < 2) {
                    liHtml = '<li style="width:' + diffWidth + 'px">' +
                            '<span class="pull-right">0' + (i + 8) + ':00</span>' +
                            '</li>';
                } else {
                    liHtml = '<li style="width:' + diffWidth + 'px">' +
                            '<span class="pull-right">' + (i + 8) + ':00</span>' +
                            '</li>';
                }
                $timeLine.append(liHtml);
            }

            var coachId = [];


            //获取小时
            $.get('/home/appointment/hours', {}, function (data) {
                if (data.result == "success") {
                    for (var i in data.data) {
                        var tempSTime = data.data[i].start_time.split(":");
                        var l_index = tempSTime[0] - 7;
                        $(".timeLine>li:eq(" + l_index + ")").addClass("ul_green");
                    }
                    getData();
                }
            });
        });

    </script>
@endsection
