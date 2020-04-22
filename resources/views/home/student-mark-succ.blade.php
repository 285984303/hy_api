@extends('home.base')

@section('title','预约申请成功')

@section('html_head')
    <link rel="stylesheet" href="{{ asset('css/student-mark-coah.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/student-mark-succ.css') }}"/>
@endsection

@section('content')
    <div class="student-mark-succ-main" style="min-height: 500px;">
        <h1>
            <span></span>
            <b>预约申请</b>
        </h1>
        <div class="succ-submain">
            <p class="first-row"><span>恭喜您！预约成功！</span></p>
            <div class="wrap">
                <p class="first-wrap">
                    <span>预约类型:<b>{{ $insert_data->first()->type->name }}</b></span>
                </p>
                <p class="sec-wrap">
                    <span>预约教练:<b>{{ $insert_data->first()->admin->name }}</b></span>
                </p>
                <p class="th-wrap"><span>预约车辆:<b>{{ $insert_data->first()->admin->vehicle ? $insert_data->first()->admin->vehicle->car_num : '未分配车辆' }}</b></span></p>
                <p class="fo-wrap"><span>预约时段:
                    <b>{{ $insert_data->first()->date }}<s class="time-pic">（
                        @foreach($insert_data as $appointment)
                            {{ substr($appointment->hour->start_time,0,5) }}-{{ substr($appointment->hour->finish_time,0,5) }}
                        @endforeach）</s></b><br/>
                </span></p>
                <p class="fi-wrap">
                    <span>预约班车:<b>@if($insert_data[0]['bus_id']!=0){{ $insert_data->first()->bus->bus_line }} {{ $insert_data->first()->bus_time }}发车@else
                                自驾 @endif</b></span></p>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript" src="{{ asset('js/js/jquery/dist/jquery.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/nav_side.js') }}"></script>
@endsection
