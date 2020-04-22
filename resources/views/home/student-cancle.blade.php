@extends('home.base')

@section('title','预约学车')

@section('html_head')
    {!! HTML::style('css/student-mark-coah.css') !!}
    {!! HTML::style('css/admin_style.css') !!}
    {!! HTML::style('css/administration/admin-coach-schedule.css') !!}
@endsection


@section('content')
    <div class="content">
        <div class="panel panel-flat">

            <div class="table_w coach-schedule-content">
                <table>
                    <thead class="thead">
                    <tr class="hourTr" user_id="">
                        <th>预约教练</th>
                        <th>车牌号码</th>
                        <th>预约科目</th>
                        <th>训练日期</th>
                        <th>训练时段</th>
                        <th>预约人员</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($listinfo)
                    @foreach($listinfo as $c)
                        <tr id="coachTime_{{$c['id']}}">
                            <td style="height: 40px;">{{ $c['coach_name'] }}</td>
                            <td>{{ $c['license_num'] }}</td>
                            <td>{{ $c['subject'] }}</td>
                            <td>{{ $c['date'] }}</td>
                            <td>{{ $c['training_time'] }}</td>
                            <td>{{session('username')}}</td>
                            <td style="color: #00aa00;cursor: pointer;" previnfo="{{ $c['date'] }} {{ $c['training_time'] }}" class="cancle-button" previd="{{$c['id']}}">取消</td>
                        </tr>
                    @endforeach
                        @else
                        <tr>
                            <td colspan="7" style="height: 40px;color:#e74c3c; ">没有数据！ <a href="/appointment">去预约?</a></td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <input type="hidden" id="appoint-token" name="appoint-token" value="{{session('appoint-token')}}">
@endsection

@section('script')
    <script>
        $(function () {
            $(".cancle-button").click(function () {
                var __this = $(this);
                var id = __this.attr('previd');
                var tipmsg = __this.attr('previnfo');
                var token = $("#appoint-token").val();
                layer.confirm('确定取消 '+tipmsg+" 的课时吗？", function(index){
                    layer.close(index);
                    $.ajax({
                        url: '/api/small/docancle',
                        type: "POST",
                        dataType: "json",
                        data: {id:id,token:token},
                        success: function (data) {
                            if (data.result == "success") {
                                layer.msg('取消成功', {
                                    time: 1000,
                                    icon: 6
                                });
                                __this.parent().remove();
                            }else{
                                var code = data.code;
                                layer.msg(data.msg, {
                                    time: 2000,
                                    icon: 5
                                },function () {
                                    if(code==400){
                                        window.location.href="/loginout";
                                    }
                                });
                            }
                        }
                    });
                });
            });
        })
    </script>
@endsection





