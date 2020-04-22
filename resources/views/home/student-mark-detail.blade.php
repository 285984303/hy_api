@extends('home.base')

@section('title','预约教练')

@section('html_head')
    <link rel="stylesheet" href="/css/student-mark-coah.css" />
    <link rel="stylesheet" href="/css/student-mark-succ.css" />
@endsection

@section('content')
<div class="student-mark-succ-main" style="min-height: 500px;">
    <h1>
        <span>
        </span>
        <b>预约申请详情</b>
        <a href="javascript:void(0);">返回</a>
    </h1>
    <div class="succ-submain">
        <div class="wrap">
            <p class="first-wrap"><span>预约类型:<b>{{ $appointment_type->name }}</b></span></p>
            <p class="sec-wrap"><span>预约教练:<b>{{ $coach->admin_name }}</b></span></p>
            <p class="th-wrap"><span>预约车辆:<b>{{ $coach->vehicle->car_num }}</b></span></p>
            <p class="fo-wrap"><span>预约时段:<b>{{ $date }}<s class="time-pic">（@foreach($hours as $hour) {{ substr($hour->start_time,0,5) }}-{{ substr($hour->finish_time,0,5) }} @endforeach）</s></b><br/></span></p>
            <p class="fi-wrap"><span>预约班车:<b>{{ is_object($bus) ? $bus->bus_line : '自驾' }} : {{ $bus_time }} 发车</b></span></p>
        </div>
    </div>
</div>
<div class="foot-btn">
    <div class="foot-sub">
        <a href="javascript:history.back();"><img src="/images/shang.png" /></a>
        <a href="javascript:submit()"><img src="/images/sure-btn.png" /></a>
    </div>
</div>
@endsection

@section('script')
    {{ HTML::script('js/js/jquery/dist/jquery.js') }}
    {{ HTML::script('js/nav_side.js') }}
    <script>
        function submit() {
            var url = '/home/appointment/submit';
            var form = $("<form></form>");
            form.attr('action', url);
            form.attr('method', 'post');
            form.attr('target', '_self');

            form.append($("<input type='hidden' name='coach_id' value='{{ $coach->id }}' />"));
            @foreach($hour_ids as $id)
            form.append($("<input type='hidden' name='hour_ids[]' value='{{ $id }}' />"));
            @endforeach
            form.append($("<input type='hidden' name='type_id' value='{{ $appointment_type->id }}'/>"));
            form.append($("<input type='hidden' name='date' value='{{ $date }}' />"));
            form.append($("<input type='hidden' name='bus_id' value='{{ is_object($bus) ? $bus->id : 0 }}' />"));
            form.append($("<input type='hidden' name='bus_time' value='{{ $bus_time }}' />"));

            form.appendTo("body");
            form.css('display', 'none');
            form.submit();
        }
    </script>
@endsection
