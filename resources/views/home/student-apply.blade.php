@extends('home.base')

@section('title','预约学车')

@section('html_head')
    <link rel="stylesheet" href="{{ asset('css/student-apply.css') }}"/>
    <style type="text/css">
      .panel{
      	min-width: 1033px;
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
                <li><a href="javascript:;">预约学车</a></li>
            </ul>
        </div>
    </div>
    <div class="content">
        <div class="panel panel-flat">
            <div class="panel panel-white">
                <div class="panel-heading">
                    <h6 class="panel-title"><i class="icon-clipboard3  position-left"></i>预约申请</h6>
                </div>

                <div class=" panel-body student-apply-main">
                    <img class="tempo" src="{{ asset('images/apply-img.png') }}"/>
                    <p>我的驾考进度：</p>
                    <div class="apply-tempo">
                        @if($user->status=='在读')
                            @if(empty($user->subject_1))
                                <img src="{{ asset('images/apply-img2.png') }}" alt=""/>
                            @elseif(empty($user->subject_2) && empty($user->subject_3))
                                <img src="{{ asset('images/apply-img3.png') }}" alt=""/>
                            @elseif(empty($user->subject_2) && !empty($user->subject_3))
                                <img src="{{ asset('images/apply-img8.png') }}" alt=""/>
                            @elseif(empty($user->subject_4))
                                <img src="{{ asset('images/apply-img5.png') }}" alt=""/>
                            @else
                                <img src="{{ asset('images/apply-img6.png') }}" alt=""/>
                            @endif
                        @elseif($user->status=='结业')
                            <img src="{{ asset('images/apply-img7.png') }}" alt=""/>
                        @else
                            <img src="{{ asset('images/apply-img1.png') }}" alt=""/>
                        @endif
                        <ul class="plan-status">
                            <li class="status-1">
                                <span>报考驾校</span>
                                <a href="javascript:void(0);" class="status-red" style="padding-left: 3%;">已完成</a>
                            </li>
                            <li class="status-2">
                                <span style="padding-left: 22%;">受理</span>
                                <a style="padding-left: 20%;" href="javascript:void(0);"
                                   class="status-no">@if($user->status=='在读') 已完成 @else 未完成 @endif</a>
                            </li>
                            <li class="status-3">
                                <span style="padding-left: 30%;">科目一考试</span>
                                <a style="padding-left: 30%;" href="javascript:void(0);" class="status-no"><b
                                            class="status-green">@if(empty($user->subject_1))
                                            未开始    @else {{ $user->subject_1 }}分钟 @endif</b></a>
                            </li>
                            <li class="status-4">
                                <span style="padding-left: 40%;">科目二考试</span>
                                <a style="padding-left: 40%;" href="javascript:void(0);" class="status-no"><b
                                            class="status-green">@if(empty($user->subject_2))
                                            未开始    @else {{ $user->subject_2 }}分钟 @endif</b></a>
                            </li>
                            <li class="status-5">
                                <span style="padding-left: 44%;">科目三考试</span>
                                <a style="padding-left: 50%;" href="javascript:void(0);" class="status-no"><b
                                            class="status-green">@if(empty($user->subject_3))
                                            未开始    @else {{ $user->subject_3 }}分钟 @endif</b></a>
                            </li>
                            <li class="status-6">
                                <span style="padding-left: 44%;">科目四考试</span>
                                <a style="padding-left: 60%;" href="javascript:void(0);"
                                   class="status-no"><b class="status-green">@if(empty($user->subject_4))
                                            未开始    @else {{ $user->subject_4 }}分钟 @endif</b></a>
                            </li>
                            <li class="status-7">
                                <span style="padding-left: 55%;">我的驾照</span>
                                <a style="padding-left: 58%;" href="javascript:void(0);"></a>
                            </li>
                        </ul>
                    </div>
                    <div class="choose-teach">
                        <form id="change_type"
                              action="{{ '/home/appointment/coach'. (request('coach_id') ? '?coach_id='.request('coach_id') : 'es') }}"
                              method="get">
                            @foreach($types as $key=>$type)
                                @if($key%3==0 && $key==0)
                                    <div class="choose-teach-one"><span>请选择您要预约的课程：</span>
                                        @elseif($key%3==0 && $key>0)
                                            <div class="choose-teach-two"><span></span>
                                                @endif

                                                @if ($type->disabled)
                                                    <a href="javascript:void(0);" class="check"
                                                       style="cursor:not-allowed;"
                                                       disabled="true">{{ $type->name }}</a>
                                                @else
                                                    <a href="javascript:void(0);" class="check"
                                                       onclick='change_id({{ $type->id }},$(this))'>{{ $type->name }}</a>
                                                @endif

                                                @if($key%3==2)
                                            </div>
                                        @endif
                                        @endforeach
                                        @if(request('coach_id'))
                                            <input type="hidden" name="coach_id" value="{{ request('coach_id') }}">
                                        @endif
                                        <input type="hidden" name="appointment_type_id" id="type_id">
                                    </div>
                        </form>
                    </div>
                    <div class="apply-btn">
                        <div class="apply-btn-box">
                            <a href="javascript:window.history.back();" class="margin-right-20">
                                <img src="/images/shang.png"/></a>
                            <a href="javascript:change_type.submit()" id="sub-btn">
                                <img src="/images/next-btn.png"/></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
@endsection

@section('script')
    <script>
        $(function () {
            for (var i = 0; i < $("a.check").length; i++) {
                var a = $("a.check").eq(i).attr("disabled");
                if (!a) {
                    $("a.check").eq(i).css("background", "#00CCFF").trigger("click");
                    $("#sub-btn").attr("href", "javascript:change_type.submit()");
                    return false;
                } else {
                    $("#sub-btn").attr("href", "#");
                }
            }
            $("a.check").on("click", function () {
                var a = $(this).attr("disabled");
                if (!a) {
                    $("#sub-btn").attr("href", "javascript:change_type.submit()");
                } else {
                    $("#sub-btn").attr("href", "#");
                }
            })
        });
        function change_id(id, row) {
            $('#type_id').val(id);
            row.parents('div.choose-teach').find('a.check').css("background", "#CCC");
            row.css("background", "#00CCFF");
        }
    </script>
@endsection
