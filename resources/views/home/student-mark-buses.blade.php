@extends('home.base')

@section('title','班车预约')

@section('html_head')
    <link rel="stylesheet" href="{{ asset('css/student-shuttle.css') }}"/>
    <style type="text/css">
      .panel{
        min-width: 1033px!important;
      }
    </style>
@endsection

@section('content')
<!-- Page header -->
    <div class="page-header page-header-default">
        <div class="breadcrumb-line">
            <ul class="breadcrumb">
                <i class="icon-home2 color-999"></i>
                <li><a href="javascript:;">当前功能</a></li>
                <li><a href="javascript:;">班车预约</a></li>
            </ul>
        </div>
    </div>
<!-- /page header -->
<div class="content">
  <div class="panel panel-flat">
    <div class="student-shuttle-main">
        <div class="panel panel-white">
		<div class="panel-heading">
			<h6 class="panel-title"><i class="icon-clipboard3 position-left"></i>预约申请</h6>
                    <div class="heading-elements">
                        <a class="modify-btn text_color_5897FB"></a>
                    </div>
		</div>
	</div>
        <div class="head"><img src="{{ asset('images/mark-shuttle.png') }}"/></div>
        <div class="big">
            <ul class="head-row">
                <li style="width: 22%;">班车路线</li>
                <li style="width: 26%;">车牌号</li>
                <li style="width: 51%;">途径站</li>
            </ul>
            @foreach($buses as $bus)
                <ul class="row" id='bus-{{ $bus->id }}'>
                    <li style="width: 22%;">{{ $bus->bus_line }}</li>
                    <li style="width: 26%;">{{ $bus->vehicle->car_num }}</li>
                    <li style="width: 51%;">{{ implode(' | ', array_keys($bus->stations)) }}</li>
                </ul>
                <div class="small" style="display: none;">
                    <ul class="head-row1" style="height: 36px;">
                        @foreach($bus->stations as $station=>$times)
                            <li style="width: {{ floor(1/count($bus->stations)*100) }}%;">{{ $station }}</li>
                        @endforeach
                    </ul>
                    <?php $line_num = count(array_first($bus->stations)) ?>
                    @for($i=0;$i<$line_num;$i++)
                        <ul class="head-row1  xu-z" style="height: 36px;"
                            onclick="change_bus({{ $bus->id }},'{{ array_first($bus->stations)[$i] }}',$(this))">
                            @foreach($bus->stations as $times)
                                <li class="time" style="width: {{ floor(1/count($bus->stations)*100) }}%;">
                                    <span>{{ $times[$i] }}</span></li>
                            @endforeach
                        </ul>
                    @endfor
                </div>
            @endforeach
        </div>
        <div class="foot-btn">
            <a href="javascript:history.back()"><img src="{{ asset('images/shang.png') }}"/></a>
            <a class="submit"><img src="{{ asset('images/next-btn.png') }}"/></a>
        </div>
    </div>
  </div>
</div>
@endsection

@section('script')
    <script>
        $(function () {
            $(".row>li").click(function () {
                $(".small").hide();
                $(this).parents(".row").next(".small").show();
            });
        });
        var bus_id = 0;
        var bus_time = '';
        function change_bus(id, time, row) {
            row.parent().children('.xu-z').each(function () {
                $(this).css('background-color', '#fff');
            });
            //return bus_id=id;
            if (bus_id == id && time == bus_time) {
                bus_id = 0;
            } else {
                bus_id = id;
                bus_time = time;
                row.css('background-color', '#76d4fe');
            }
        }
        $(".submit").click(function () {
            // 弹窗提示详情
            layer.confirm('确定预约此班车吗？', {
                btn: ['确认', '取消'], //按钮
                title: '班车预约'
            }, function () {
                if (!bus_id) {
                    window.location.href = '/home/appointment';
                } else {
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        data: {
                            bus_id: bus_id,
                            time: bus_time,
                            date: getUrlParam('date'),
                            _token: '{{ csrf_token() }}'
                        },
                        url: "/home/appointment/bus",
                        success: function (data) {
                            if (data.result == "success") {
                                layer.msg('预约班车成功!', {time: 2000, icon: 6}, function () {
                                    window.location.href = '/home/appointment';
                                });
                            } else {
                                layer.msg('预约班车失败!' + data.err_message, {time: 2000, icon: 5});
                            }
                        },
                        error: function () {
                            layer.msg('预约班车失败:' + data.err_message, {time: 2000, icon: 5});
                        }
                    });
                }

            }, function () {

            });
        });
        function getUrlParam(name) {
            var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
            if (results != null) {
                return decodeURIComponent(results[1]) || '';
            }
            return null;
        }
    </script>
@endsection
