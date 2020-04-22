@extends('home.base')

@section('title','教练列表页')

@section('html_head')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student-list.css') }}"/>
    <link rel="stylesheet" href="{{ asset('js/plugins/dataPicker/need/laydate.css') }}"/>
    <style type="text/css">
       .panel{
          min-width: 1023px!important;
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
                <li><a href="javascript:;">教练列表</a></li>
            </ul>
        </div>
    </div>
    <!-- /page header -->
    <div class="content">
        <div class="panel panel-flat">
            <form class="" action="{{ url()->current() }}" method="get">
                <div class="head">
                  <div class="row">
                    <div class="col-md-10">
                       <div class="col-lg-4">
                         <div class="col-lg-3">
                           <label for="" class="control-label">姓名：</label>
                         </div>
                         <div class="col-lg-9">
                           <input class="head-one form-control" type="text" name='admin_name' value="{{ $options['admin_name'] }}"/>
                         </div>
                       </div>
                       <div class="col-lg-4">
                        <div class="col-lg-3">
                           <label for="">性别：</label>
                        </div>
                        <div class="col-lg-9">
                            <select name="gender" class="head-one">
                              <option value="">全部</option>
                              <option value="1" @if($options['gender']==1) selected @endif>
                            男
                              </option>
                              <option value="2" @if($options['gender']==2) selected @endif>
                            女
                              </option>
                          </select>
                        </div>
                       </div>
                       <div class="col-lg-4">
                        <div class="col-lg-3">
                           <label for="">课程名称：</label>
                        </div>
                        <div class="col-lg-9">
                        	<select name="appointment_type_id" class="head-sel">
                              <option value="">全部</option>
                              @forelse($types as $type)
                              <option value="{{ $type->id }}" @if($options['appointment_type_id']== $type->id) selected @endif>
                                {{ $type->name }}
                               </option>
                              @empty
                              <option value="0">
                                暂无数据
                              </option>
                            @endforelse
                         </select>
                        </div>
                       </div>
                    </div>
                    <div class="col-md-2">
                       <input type="image" class="head-two" src="{{ asset('images/search-btn.png') }}" name="" id=""value=""/>
                    </div>
                    <!-- <label for="">日期:</label>
                    <input class="head-sel laydate-icon" type="text" id="born" value="" onclick="laydate({istime: false, format: 'YYYY-MM-DD'})"/> -->
                     </div>
                </div>
            </form>
            @forelse($coachList as $coach)
                <div class="list-con-one">
                    <dl class="con-left">
                        <dt><img src="{{ $coach->admin_thumb or asset('images/wait-img.png') }}"/></dt>
                        <dd class="one">教练姓名：{{ $coach->admin_name }}<span class="sex">(@if($coach->gender==1)男@else
                                    女@endif</span>
                            @if($coach->is_follow)
                                <a href="javascript:void(0);" class="cancel" style="background:#ff9933"
                                   admin_id="{{ $coach->id }}">已关注</a>
                            @else
                                <a href="javascript:void(0);" class="attention" admin_id="{{ $coach->id }}">关注</a>
                            @endif
                        </dd>
                        <dd class="two"><span>驾照日期：{{ $coach->getlicense_date }}</span></dd>
                        <dd class="three"><span>准教车型：<b>
                                    @forelse($coach->licenceTypes as $licencetype)
                                        {{ $licencetype->name }} @empty 暂无 @endforelse &nbsp;&nbsp;</b></span><span>入职日期：{{ $coach->contract_start }}</span>
                        </dd>
                    </dl>
                    <div class="con-subright">
                        <div class="con-mid">
                            <p class="p_one">合格率：<span>{{ round($coach->qualified_rate(),3)*100 }}%</span></p>
                            <p class="p_two">预约率：<span>{{ round($coach->appoint_rate(),3)*100 }}%</span></p>
                        </div>
                        <div class="con-submid">
                            <p>满意度：<span>{{ $coach->analysis['avg'] }}分</span></p>
                        </div>
                        <div class="con-right">
                            <div class="con-right22">
                                <a href="{{ url('home/appointment/types?coach_id='.$coach->id) }}"
                                   style="display: block;height: 36px;background: #FF9933;color: white;line-height: 36px;border-radius: 8px;">我要预约 </a>
                                <a class="view" href="{{ url('home/coach/detail') }}/{{ $coach->id }}">查看评价</a>
                            </div>
                        </div>
                    </div>
                    <p class="con-more">准教类型：@forelse($coach->appointment_list as $type){{ $type->name }}
                        &nbsp;&nbsp; @empty 暂无 @endforelse</p>
                    <p class="con-more">使用车辆：@unless($coach->vehicle_detail)
                            暂无 @else {{ $coach->vehicle_detail->car_num }} @endunless
                            （@forelse($coach->vehicle_list as $type){{ $type->name }} &nbsp;&nbsp; @empty
                                暂无 @endforelse） </p>
                </div>
            @empty
                暂无数据
            @endforelse
            @include('home.page',['paginator'=>$coachList])
            @endsection
        </div>

    </div>


@section('script')
    <script>
        $(function () {
            $('select').selectpicker();
            reBind();
        });
        function reBind(){
            $(".attention").on("click", function () {
                var one = $(this);
                one.unbind();
                $.ajax({
                    type: 'post',
                    url: "{{ url('home/coach/follow') }}",
                    data: {admin_id: one.attr('admin_id'), status: 'add'},
                    success: function (msg) {
                        if (msg == 'success') {
                            one.html('已关注').css("background", "#ff9933");
                            one.attr('class', 'cancel');
                            reBind();
                        }
                    }
                });
            });
            $(".cancel").on("click", function () {
                var one = $(this);
                one.unbind();
                $.ajax({
                    type: 'post',
                    url: "{{ url('home/coach/follow') }}",
                    data: {admin_id: one.attr('admin_id'), status: 'delete'},
                    success: function (msg) {
                        if (msg == 'success') {
                            one.html('关注').css("background", "#00ccff");
                            one.attr('class', 'attention');
                            reBind();
                        }
                    }
                });
            });
        }
    </script>
@endsection
