<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="pragma" content="no-cache">
    <title>科锐特- @yield('title','首页')</title>
    <link href="/assets/css/bootstrap.css" rel="stylesheet" type="text/css">
    <link href="/assets/css/components.css" rel="stylesheet" type="text/css">
    <link href="/assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
    <link href="/assets/css/bootstrap.css" rel="stylesheet" type="text/css">
    <link href="/assets/css/core.css" rel="stylesheet" type="text/css">
    <link href="/assets/css/colors.css" rel="stylesheet" type="text/css">
    @section('html_head')
    @show
    <style>
        body{
            overflow: hidden;
        }
    </style>
</head>
<body>
<div class="page-container">
    <!-- Page content -->
    <div class="page-content">
        @section('nav')
                <!-- Main sidebar -->
          <nav class="sidebar sidebar-main" style="height: 3000px;">
            <div class="sidebar-content">
                <!-- Main navigation -->
                <div class="sidebar-category sidebar-category-visible">
                    <div class="category-content no-padding">

                        <ul class="navigation navigation-main navigation-accordion" style="padding-top: 0px">

                            <!-- Main -->
                            <li class="navigation-header" style="background-color: #00274e;height: 60px;">
                                <img src="/images/coach-logo.png" alt="" style="height:40px;margin-top: 3px;">
                                <i class="icon-menu" title="Main pages"></i>
                            </li>
                            <li class="active">
                                <a href="javascript:;">
                                    <i class="icon-vcard"></i>
                                    <span>个人中心</span>
                                </a>
                                <ul>

                                    <li class="{{ Request::getPathinfo() == '/appointment'?'active':'' }}">
                                        <a href="{{ url('appointment') }}">
                                            <span>训练预约</span></a>
                                    </li>
                                    <li class="{{ Request::getPathinfo() == '/cancleappointment'?'active':'' }}">
                                        <a href="{{ url('cancleappointment') }}">
                                            <span>取消预约</span>
                                        </a>
                                    </li>
                                    <li class="{{ Request::getPathinfo() == '/appointmentrecord'?'active':'' }}">
                                        <a href="{{ url('appointmentrecord') }}">
                                            <span>预约记录</span></a>
                                    </li>


                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- /main navigation -->

            </div>
          </nav>
        <!-- /main sidebar -->
        <!-- Main content -->

        @show

        <div class="content-wrapper">
            <div class="page-header page-header-default">
                <div class="breadcrumb-line">
                    <ul class="breadcrumb">
                        {{--<i class="icon-home2 color-999"></i>--}}
                        {{--<li><a href="javascript:;">当前功能</a></li>--}}
                        <li class="padding-left"><a href="javascript:;">{{$catname}}</a></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right ">
                        <li><a href="javascript:;" style="color: #666;padding-top: 15px">欢迎您:  <b>{{session('username')}}</b></a></li>
                        <li><a href="{{ url('loginout') }}" style="font-size:14px;padding-top: 15px">[退出]</a></li>
                    </ul>
                </div>
            </div>
            <div class="lodding" style="width: 50px;margin: 0 auto;margin-top:200px; ">
                <img src="/images/loader5.gif">
            </div>
            <div class="show-main-content" style="display: none;padding: 0px;margin: 0px;margin-top: -20px">
            @section('content')
            @show
            </div>
        </div>

    </div>
</div>
</body>
<script type="text/javascript" src="/js/js/jquery/dist/jquery.js"></script>
<script type="text/javascript" src="/assets/js/core/libraries/bootstrap.min.js"></script>
<script src="/assets/js/plugins/forms/selects/bootstrap_select.min.js"></script>
<script src="/js/laydate/laydate.js"></script>
<script src="/js/plugins/layer/layer.js"></script>
@section('script')
@show
<script>
    $(function(){
       /* var screen_height = $(window).height()-100;
        $(".content > .panel").css("min-height",screen_height+"px");*/
        $('select').selectpicker();
        $(".lodding").hide();
        $(".show-main-content").fadeIn();
        laydate.render({
            elem: '#start-date'
        });
    })
</script>
</html>
