@extends('home.base')

@section('title','教练详情')

@section('html_head')
<link rel="stylesheet" href="{{ asset('css/student-details.css') }}" />
<style type="text/css">
	.panel{
		min-width: 1033px!important;
	}
</style>
@endsection

@section('content')
<div class="content">
<div class="panel panel-flat">
<div class="student-details-main">
	<div class="panel panel-white">
		<div class="panel-heading">
			<h6 class="panel-title"><i class="icon-car position-left"></i> 预约学车</h6>
                    <div class="heading-elements">
                        <a class="modify-btn text_color_5897FB"></a>
                    </div>
		</div>
	</div>
	<div class="panel-body">
    <div class="student-details-submain">
        <div class="list-con-one">
            <dl class="con-left">
                <dt><img src="{{ asset('images/wait-img.png') }}"/></dt>
                <dd class="one">教练姓名：{{ $coach->admin_name }}
                    @if($coach->is_follow)
                    <a href="javascript:void(0);" class="cancel" style="background:#ff9933" admin_id="{{ $coach->id }}">已关注</a>
                    @else
                    <a href="javascript:void(0);" class="attention" admin_id="{{ $coach->id }}">关注</a>
                    @endif
                </dd>
                <dd class="two">
                    <span>驾照日期：{{ $coach->getlicense_date }}</span>
                </dd>

                <dd class="three"><span>准教车型：<b>@forelse($coach->licenceTypes as $licencetype)
                {{ $licencetype->name }} @empty 暂无 @endforelse &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></span><span>入职日期:{{ date('Y-m-d',strtotime($coach->created_at)) }}</span></dd>
            </dl>
            <div class="con-subright">
                <div class="con-mid">
                    <p class="p_one">合格率：<span>{{ $qualified_rate }}%</span></p>
                    <p class="p_two">预约率：<span>{{ $appoint_rate }}%</span></p>
                </div>
                <div class="con-submid">
                    <p>满意度：<span>{{ $analysis['avg'] }}分</span></p>
                </div>
                <div class="con-right">
                    <a href="{{ url('/home/appointment/types') }}"><input type="image" src="{{ asset('images/list-btn.png') }}" name="" id="" value="" /></a>
                </div>
            </div>
            <p class="con-more">准教类型：@forelse($coach->appointment_list as $appointment){{ $appointment->name }}&nbsp;&nbsp; @empty 暂无数据 @endforelse</p>
            <p class="con-more">使用车辆：@unless($coach->vehicle_detail) 暂无 @else {{ $coach->vehicle_detail->car_num }} @endunless (@forelse($coach->vehicle_list as $vehicle){{ $vehicle->name }} &nbsp;&nbsp; @empty 暂无数据 @endforelse)</p>
        </div>
    </div>
  </div>
  <div class="panel panel-white">
		<div class="panel-heading">
			<h6 class="panel-title"><i class="icon-compose position-left"></i> 学员评价</h6>
                    <div class="heading-elements">
                        <a class="modify-btn text_color_5897FB"></a>
                    </div>
		</div>
	</div>
<div class="panel-body">
    <div class="student-details-submain">
        <div class="start-one">
            <div class="start-one-left">
                <p class="p-big"><span class="span-big">{{ $analysis['avg'] }}</span>分</p>
                <p><img src="{{ asset('images/pj_'.ceil($analysis['avg']).'.png') }}" alt="" /></p>
                <p class="p-small">共<span>{{ $analysis['count'] }}</span>人评价</p>
            </div>
            <div class="start-one-mid">
                <p>服务态度：<strong><img src="{{ asset('images/pj_'.ceil($analysis['attitude']).'.png') }}" alt="" /></strong><span>{{ $analysis['attitude'] }}分</span></p>
                <p>教学质量：<strong><img src="{{ asset('images/pj_'.ceil($analysis['teach']).'.png') }}" alt="" /></strong><span>{{ $analysis['teach'] }}分</span></p>
                <p>行为规范：<strong><img src="{{ asset('images/pj_'.ceil($analysis['behavior']).'.png') }}" alt="" /></strong><span>{{ $analysis['behavior'] }}分</span></p>
                <p>廉洁教学：<strong><img src="{{ asset('images/pj_'.ceil($analysis['quality']).'.png') }}" alt="" /></strong><span>{{ $analysis['quality'] }}分</span></p>
            </div>
            <div class="start-one-right">
                <div class="right-wrap">
                    <span>5分</span><div class="big"><div class="small" style="width: {{ $analysis['recommend_ratio'] }}%"></div></div><b>{{ $analysis['recommend_count'] }}人</b>
                </div>
                <div class="right-wrap">
                    <span>4分</span><div class="big"><div class="small" style="width: {{ $analysis['satisfactoryer_ratio'] }}%"></div></div><b>{{ $analysis['satisfactoryer_count'] }}人</b>
                </div>
                <div class="right-wrap">
                    <span>3分</span><div class="big"><div class="small" style="width: {{ $analysis['satisfactory_ratio'] }}%"></div></div><b>{{ $analysis['satisfactory_count'] }}人</b>
                </div>
                <div class="right-wrap">
                    <span>2分</span><div class="big"><div class="small" style="width: {{ $analysis['indifferent_ratio'] }}%"></div></div><b>{{ $analysis['indifferent_count'] }}人</b>
                </div>
                <div class="right-wrap">
                    <span>1分</span><div class="big"><div class="small" style="width: {{ $analysis['poor_ratio'] }}%"></div></div><b>{{ $analysis['poor_count'] }}人</b>
                </div>
            </div>
        </div>
        @forelse($comments as $comment)
        <div class="start-two">
            <dl>
                <dt><img src="{{ $comment->user_info->user_img or asset('images/delt-img.png') }}"/></dt>
                <dd>{{ $comment->user_info->user_truename }}</dd>
            </dl>
            <div>
                <p class="p-first">
                    <a href="javascript:void(0);">总体评价：{{ $comment->general }}</a>
                    <a href="javascript:void(0);">服务质量：<img src="{{ asset("images/pj_{$comment->attitude}.png") }}" alt="" /></a>
                    <a href="javascript:void(0);">教学质量：<img src="{{ asset("images/pj_{$comment->teach}.png") }}" alt="" /></a>
                    <a href="javascript:void(0);">行为规范：<img src="{{ asset("images/pj_{$comment->behavior}.png") }}" alt="" /></a>
                    <a href="javascript:void(0);">廉洁教学：<img src="{{ asset("images/pj_{$comment->quality}.png") }}" alt="" /></a>
                </p>
                <p class="p-sec">{{ $comment->content }}</p>
            </div>
        </div>
        @empty
        暂无数据
        @endforelse
    </div>
   </div>
</div>
</div>
</div>
@endsection

@section('script')
    <script>
    $(function(){
        reBind();
    })
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
