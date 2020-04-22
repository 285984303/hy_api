@extends('home.base')

@section('title','待评价')

@section('html_head')
<link rel="stylesheet" href="{{ asset('css/student-wait.css') }}" />
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
                <li><a href="javascript:;">待评价</a></li>
            </ul>
        </div>
    </div>
<!-- /page header -->
<div class="content">
<div class="panel panel-flat">
<div class="student-wait-main">
    <div class="panel panel-white">
		<div class="panel-heading">
			<h6 class="panel-title"><i class="icon-compose position-left"></i> 待评价</h6>
                    <div class="heading-elements">
                        <a class="modify-btn text_color_5897FB"></a>
                    </div>
		</div>
	</div>
    @forelse($not_comment as $comment)
    <div class="con-one">
        <dl>
            <dt><img src="{{ $comment['admin_thumb'] or asset('images/wait-img.png') }}"/></dt>
            <dd>教练姓名：{{ $comment['admin_name'] }}</dd>
            <dd>教练车号：{{ $comment['vehicle_code'] }}</dd>
            <dd>训练类型：{{ $comment['type_name'] }}</dd>
        </dl>
        <div class="con-right" >
            <p>我的总体评价：</p>
            <div class="evaluation"><a href="javascript:void(0)" class="eva-one">差</a><a href="javascript:void(0)" class="eva-two">一般</a><a href="javascript:void(0)" class="eva-two">满意</a><a href="javascript:void(0)" class="eva-three">很满意</a><a href="javascript:void(0)" class="eva-four">强烈推荐</a><span>(请选择评价)</span></div>
        </div>

        <div class="con-right1" style="display: none;">
            <p class="row-one">评价成功!</p>
            <p class="row-two">我的总体评价:<span class="ass-w">满意</span></p>
            <p class="row-three">教练很负责任，每一步操作都很仔细认真的教我，而且我错了也没有凶我，非常棒的教练！</p>
        </div>
    </div>

    <div class="con-two" style="display: none;">
        <div class="left">
            <div class="one"><label for="">服务质量:</label><span class="starAll" data-score=''></span></div>
            <div class="two"><label for="">教学质量:</label><span class="starAll" data-score=''></span></div>
            <div class="three"><label for="">行为规范:</label><span class="starAll" data-score=''></span></div>
            <div class="four"><label for="">廉洁教学:</label><span class="starAll" data-score=''></span></div>
        </div>
        <div class="mid">
            <textarea class="txt-con" name="" rows="" cols=""></textarea>
        </div>
        <div class="right">
            <input type="image" class="fabiao" src="{{ asset('images/fabiao.png') }}" name="" id="" value="{{ $comment['id'] }}" />
        </div>
    </div>
        @empty
        <p style="width: 100px;margin: 200px auto;font-size: 20px;">暂无数据</p>
    @endforelse
    @include('home.page',['paginator'=>$not_comment])
@endsection
</div>
</div>
</div>

@section('script')
<script src="{{ asset('js/plugins/star/jquery.raty.js') }}"></script>
<script>
    $(function(){
        $(".evaluation>a").on("click",function(){
            var color=['#99cc99','#999966','#ff99ff','#ff6633','#ff0000'];
            var index=$(this).index();
            var val=$(this).html();
            $(this).css("background-color",color[index]).siblings().css("background","");
            $(this).parents(".con-one").next(".con-two").show();
            $(".fabiao").on("click",function(){
                var one=$(this);
                $.ajax({
                    type: 'post',
                    url : "{{ url('home/comment/addComment') }}",
                    data: {
                        'appointment_id':$(this).val(),
                        'general':index+1,
                        'attitude':$(this).parent().siblings("div.left").find('span.starAll').eq(0).attr('data-score'),
                        'quality':$(this).parent().siblings("div.left").find('span.starAll').eq(1).attr('data-score'),
                        'behavior':$(this).parent().siblings("div.left").find('span.starAll').eq(2).attr('data-score'),
                        'teach':$(this).parent().siblings("div.left").find('span.starAll').eq(3).attr('data-score'),
                        'content':$(this).parent().siblings('div.mid').children().val()
                    },
                    success:function(msg){
                        if(msg.status=='success'){
                            one.parents('.con-two').prev(".con-one").find(".con-right").hide();
                            one.parents('div.con-two').prev().find('span.ass-w').html(msg.general);
                            one.parents('div.con-two').prev().find('p.row-three').html(msg.content);
                            one.parents('div.con-two').prev().find('div.con-right1').show();
                            one.parents(".con-two").hide();
                        }
                    }
                });

            })
            /*$(this).parents('.con-right').hide();
            $(this).parents('.con-right').siblings('.con-right1').show();
            $(".ass-w").html(val);*/
        });
        $('.starAll').each(function(){
            $(this).raty({
                click:function(score){
                    return $(this).attr('data-score',score);
                }
            });
        });
        // $('.starAll').eq(0).raty({
        //     click:function(score){
        //         return $(this).attr('data-score',score);
        //     }
                // });
    })
</script>
@endsection
