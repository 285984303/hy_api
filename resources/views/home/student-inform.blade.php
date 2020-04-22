@extends('home.base')

@section('title','学员通知提醒')

@section('html_head')
    <link rel="stylesheet" href="{{ asset('css/student-inform.css') }}"/>
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
                <li><a href="javascript:;">通知提醒</a></li>
            </ul>
        </div>
    </div>
    <!-- /page header -->
    <div class="content">
        <div class="panel panel-flat">
            <div class="student-inform-main">
                <div class="coach-coach-main">
                    <div class="coach-backlog-head margin-bottom-20">
                        <ul class="tab">
                            <li class="li-active">全部消息</li>
                            <li>未读消息</li>
                        </ul>
                        <input type="image" class="right clearAll" src="{{ asset('images/coach-backlog-btn.png') }}"/>
                    </div>
                    <div class="coach-backlog-submain ">
                        <table border="1px solid #0099cc" id="all" width="100%" cellspacing="0" cellpadding="0"
                               class="table-con">
                            @foreach($list as $value)
                                <tr class="tr-on" onclick="read($(this),'{{ $value->id }}')">
                                    <td width="80%" class="msg">
                                        {{--&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[{{ $value->type }}]--}}
                                        {{ $value->title }},<a>查看详情>> </a></td>
                                    <td width="20%" class="date"><span>{{ $value->created_at }}</span><b class="del-btn"
                                                                                                         not_id="{{ $value->id }}"></b>
                                    </td>
                                </tr>
                                <tr class="detail">
                                    <td colspan="2">
                                        <div>{!! $value->content !!}</div>
                                    </td>
                                </tr>
                            @endforeach
                            @if(!$list->count())
                                <tr>
                                    <td colspan="2">您没有此类消息</td>
                                </tr>
                            @endif
                        </table>
                        <table style="display: none;" id="unread" border="1px solid #0099cc" width="100%"
                               cellspacing="0" cellpadding="0" class="table-con">
                            @foreach($not_read_list as $value)
                                <tr class="tr-on" onclick="read($(this),'{{ $value->id }}')">
                                    <td width="80%" class="msg">
                                        {{--&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[{{ $value->type }}]--}}
                                        {{ $value->title }},<a>查看详情>> </a></td>
                                    <td width="20%" class="date"><span>{{ $value->created_at }}</span><b class="del-btn"
                                                                                                         not_id="{{ $value->id }}"></b>
                                    </td>
                                </tr>
                                <tr class="detail">
                                    <td colspan="2">
                                        <div>{!! $value->content !!}</div>
                                    </td>
                                </tr>
                            @endforeach
                            @if(!$not_read_list->count())
                                <tr>
                                    <td colspan="2">您没有此类消息</td>
                                </tr>
                            @endif

                        </table>
                    </div>
                </div>
            </div>

            @include('home.page',['paginator'=>$list])

        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $('.detail').hide();
        /**删除所用行*/
	$(".clearAll").on("click",function(){
			layer.open({
						  type: 1,
						  title:false,
						  closeBtn: 2, //不显示关闭按钮
						  shift: 2,
						  area: ['486px', '228px'],
						  shadeClose: true, //开启遮罩关闭
						  content: '<p class="cacel-txt"><span>清空所有消息？</span></p><input class="sure-btn1" onclick="deleteAll()" type="image" src="../../images/sure-btn.png">'
						});
			$(".sure-btn1").on("click",function(){
				$(".table-con").find("tr").remove();
				layer.closeAll();
			})
	});

        $('.del-btn').click(function () {
            var one = $(this);
            $.ajax({
                type: 'post',
                url: "{{ url('home/user/del_msg') }}",
                data: {id: one.attr('not_id')}
            })
        });
        $(".tab li").click(function () {
            var $this = $(this);
            if ($this.text() == "全部消息") {
                $("#unread").hide();
                $("#all").show();
                $this.addClass("li-active").siblings().removeClass("li-active");
            } else {
                $("#all").hide();
                $("#unread").show();
                $this.addClass("li-active").siblings().removeClass("li-active");
            }
        });
        function read(row, id) {
            $('.detail').hide();
            row.next().show();
            console.log(row.next());
            $.get('/home/user/message/'+id,{},function (data) {
                if (data.result == 'success') {
                } else {
                    layer.msg('请生成电子签章数据：' + data.err_message, {time: 2000, icon: 5});
                }
            });
        }
    </script>
@endsection
