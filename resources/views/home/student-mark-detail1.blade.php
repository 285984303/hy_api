@extends('home.base')

@section('title','预约申请详情')

@section('html_head')
    <link rel="stylesheet" href="{{ asset('css/student-mark-coah.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/student-mark-succ.css') }}"/>
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
                <p class="first-wrap"><span>预约类型:<b>{{ session('appointment_info.appointment_type_name') }}</b></span>
                </p>
                <p class="sec-wrap"><span>预约教练:<b>{{ session('appointment_info.coach_name') }}</b></span></p>
                <p class="th-wrap"><span>预约车辆:<b>{{ session('appointment_info.car_num') }}</b></span></p>
                <p class="fo-wrap"><span>预约时段:
                <b>{{ session('appointment_info.date') }}<s class="time-pic">（
                        @foreach(session('appointment_info.hours') as $hour)
                            {{ $hour->start_time }}-{{ $hour->finish_time }}
                        @endforeach
                        ）</s></b><br/>
            </span></p>
                <p class="fi-wrap"><span>预约班车:<b>{{ $bus->bus_line }}</b></span></p>
            </div>
        </div>
    </div>
    <div class="foot-btn">
        <div class="foot-sub">
            <a href="javascript:history.back(-1);"><img src="{{ asset('images/shang.png') }}"/></a>
            <a href="javascript:submit()"><img  src="{{ asset('images/sure-btn.png') }}"/></a>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript" src="{{ asset('js/js/jquery/dist/jquery.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/nav_side.js') }}"></script>
    <script>
        function submit() {
            var url = '/home/coach/coachsubmit';
            var form = $("<form></form>");
            form.attr('action', url);
            form.attr('method', 'post');
            form.attr('target', '_self');
            var input = $("<input type='hidden' name='bus_id' />");
            input.attr('value', "{{ $bus->id }}");
            form.append(input);

            input = $("<input type='hidden' name='bus_time' />");
            input.attr('value', "{{ $bus_time }}");
            form.append(input);


            form.appendTo("body");
            form.css('display', 'none');
            form.submit();
        }
    </script>
@endsection
