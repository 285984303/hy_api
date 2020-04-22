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
            <div class="col-md-12" style="padding: 0px;margin: 0px;margin-bottom: 10px">
                <form action="?" method="get">
                <div class="col-md-3" style="padding: 0px;margin: 0px">
                    <div class="input-group col-lg-10">
                        <span class="input-group-addon" id="sizing-addon2">预约日期</span>
                        <input type="text" name="date" value="{{ $options['date'] }}" id="start-date"
                               class="input-time layui-icon form-control" placeholder="请选择日期" aria-describedby="sizing-addon2">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group col-lg-10">
                        <span class="input-group-addon" id="sizing-addon2">预约科目</span>
                        <select
                                name="subject" id="training_model">
                            <option value="2" @if($options['subject']==2) selected @endif>
                                科目二
                            </option>
                            <option value="3"  @if($options['subject']==3) selected @endif>
                                科目三
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-1" style="text-align: left">
                    <input type="submit" class="searchBtn font16" value="查找" style="border: none;line-height: 30px"/>
                    {{--<button class="searchBtn font16">查找</button>--}}
                </div>
                </form>
            </div>
            <div class="col-md-12" style="clear:both;margin-top:5px;margin-bottom:5px">
                <div class="col-md">禁止预约
                    <i class="icon-circle2  text_color_CC3300"></i>
                </div>
                <div class="col-md">被预约
                    <i class="icon-circle2  text_color_00FF33 "></i>
                </div>
                <div class="col-md">可预约
                    <i class="icon-circle2  text_color_FF9933 "></i>
                </div>
            </div>
            <div class="table_w coach-schedule-content">
                <table>
                    <thead class="thead">
                    <tr class="hourTr" user_id="">
                        <th style="width: 100px;">教练</th>
                        <th style="width: 100px;">车辆</th>
                        @foreach($courselistinfo as $c)
                        <th>{{$c}}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody class="allValue overfllowDiv">
                    @if($courselist)
                    @foreach($courselist as $v)
                        <tr id="coachTime49056">
                            <td style="width:100px;line-height:40px;">{{ $v['coach_name'] }}</td>
                            <td style="width:100px;line-height:40px;">{{ $v['license_num'] }}</td>
                            @foreach($v['course_list'] as $v2)
                                @if($v2['status']=='DISABLE')
                                    <td style="color:white" prev="DISABLE" prevsubject="{{$v2['subject']}}" prevdate="{{$v2['date']}}" previd="{{$v2['id']}}" class="rgb-CC3300 detailTd">
                                @elseif($v2['status']=='ABLE')
                                    <td style="color:white" prev="ABLE" prevsubject="{{$v2['subject']}}" prevdate="{{$v2['date']}}" previd="{{$v2['id']}}" class="rgb-FF9933 detailTd">
                                @else
                                    <td style="color:white" prev="TAKEN" prevsubject="{{$v2['subject']}}" prevdate="{{$v2['date']}}" previd="{{$v2['id']}}" class="rgb-00FF33 detailTd">
                                @endif
                                        {{$v2['username']}}
                                   </td>
                            @endforeach
                        </tr>
                    @endforeach
                    @else
                        <tr>
                            <td colspan="7" style="height: 40px;color:#e74c3c; ">没有可预约数据！</td>
                        </tr>
                    @endif

                    </tbody>
                </table>
                <div class="nodata">数据正在加载中</div>
            </div>
        </div>
    </div>
    <input type="hidden" id="appoint-token" name="appoint-token" value="{{session('appoint-token')}}">
    <input type="hidden" id="username-token" name="appoint-token" value="{{session('username')}}">
@endsection
@section('script')
<script>
    $(function () {
        BUTTON=true;
        $(".detailTd").click(function () {
            if(!BUTTON){
                layer.msg('预约中,稍后在试!', {
                    time: 1000,
                    icon: 5
                });
                return false;
            }
            BUTTON=false;
            var __this = $(this);
            var isable = __this.attr('prev');
            if(isable!='ABLE'){
                if(isable=='TAKEN'){
                    var msg = "此节课已被预约!";
                }else{
                    var msg = "此节课无法预约!";
                }
                layer.msg(msg, {
                    time: 2000,
                    icon: 5
                });
                BUTTON=true;
                return false;
            }
            var id = __this.attr('previd');
            var token = $("#appoint-token").val();
            var date = __this.attr('prevdate');
            var subject = __this.attr('prevsubject');
            var appointment_type = 3;
            var ids = [];
            ids.push(id);
//            console.log(ids);
            $.ajax({
                url: '/api/small/doappointment',
                type: "POST",
                    dataType: "json",
                    data: {ids:ids,token:token,date:date,subject:subject,appointment_type:appointment_type},
                    success: function (data) {
                        if (data.result == "success") {
                            layer.msg('预约成功', {
                                time: 1000,
                                icon: 6
                            });
                            __this.removeClass('rgb-FF9933').addClass('rgb-00FF33');
                            __this.text($('#username-token').val());
                            __this.attr("prev",'TAKEN')
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
            BUTTON = true;
        });
    })
</script>
@endsection




