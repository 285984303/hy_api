@extends('home.base')

@section('title','预约学车')

@section('html_head')

@endsection

@section('content')
<div class="page-header page-header-default">
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <i class="icon-home2 color-999"></i>
            <li><a href="javascript:;">当前功能</a></li>
            <li><a href="javascript:;">预约学车</a></li>
        </ul>
        <ul class="breadcrumb-elements">
            <li>
                <a class="right_button_style width100"
                   href="{{ url('home/appointment/types') }}" target="_blank">预约学车</a>
            </li>

        </ul>
        </div>
    </div>
<div class="content">
    <div class="panel panel-flat">
        <div class="head_bg_color table_width row ">
            <form class="form-horizontal " action="{{ url()->current() }}" method="get">
                <div class="col-md-10">
                    <div class="col-md-12">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-lg-3 control-label min_width85">教练:</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control"
                                           name="admin_name" value="{{ $options['admin_name'] }}"/>

                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-lg-3 control-label min_width85">课程名称:</label>
                                <div class="col-lg-9">
                                    <select name="appointment_type_id">
                                        <option value="">全部</option>

                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-lg-3 control-label min_width85">日期:</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control laydate-icon"
                                           id="born" value="{{ $options['appointment_date'] }}"
                                           onclick="laydate({istime: false, format: 'YYYY-MM-DD'})"
                                           name="appointment_date"/>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-offset-1 col-md-1">
                    <button class="head_btn_bg_color log-searchBtn" type="submit">查找
                    </button>
                </div>
            </form>

        </div>
        <div class="table_responsive">
            <table class="table table-striped table-bordered table-hover table_width">
                <thead>
                <tr class="bg-mainColor">
                    <th width="6%">序号</th>
                    <th width="8%">教练</th>
                    <th width="10%">教练车</th>
                    <th width="10%">课程名称</th>
                    <th width="10%">日期</th>
                    <th width="10%">时间段</th>
                    <th width="10%">开始时间</th>
                    <th width="10%">结束时间</th>
                    <th width="8%">操作人</th>
                    <th width="16%">状态</th>
                </tr>
                </thead>
                <tbody>

                </tbody>

            </table>

        </div>
        @if($appoint_list)
            @include('home.page',['paginator'=>$appoint_list])
        @endif
    </div>
</div>
<!-- /page header -->

@endsection

@section('script')
    <script>
        $(function () {
            !function () {
                laydate({elem: '#born'});
            }();
            $('select').selectpicker();
            $(".cancel").on("click", function () {
                var appoint_id = $(this).attr('appoint_id');
                console.log(appoint_id);
                layer.open({
                    type: 1,
                    title: false,
                    closeBtn: 2, //不显示关闭按钮
                    shift: 2,
                    area: ['486px', '228px'],
                    shadeClose: true, //开启遮罩关闭
                    content: '<p class="cacel-txt"><span>取消本次预约？</span></p><input class="sure-btn1"  type="image" src="../images/sure-btn.png" onclick="cancel_appoint(' + appoint_id + ')">'
                });
                $(".sure-btn1").on("click", function () {
                    layer.closeAll();
                });
            });

        });
        function cancel_appoint(appoint) {
            $.ajax({
                type: 'post',
                url: "{{ url('home/appointment/cancel') }}",
                data: {'appoint_id': appoint},
                success: function (msg) {
                    if (msg.result == 'success') {
                        layer.msg('取消成功!', {time: 2000, icon: 6}, function () {
                            layer.closeAll();
                            window.location.reload();
                        });
                    } else {
                        layer.msg('取消失败：' + data.err_message, {time: 2000, icon: 5});
                    }
                }
            });
        }
    </script>
@endsection
