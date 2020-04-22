@extends('home.base')

@section('title','我的学费')

@section('html_head')
    <link rel="stylesheet" href="{{ asset('css/student-paying.css') }}"/>
    @endsection

    @section('content')
            <!-- Page header -->
    <div class="page-header page-header-default">
        <div class="breadcrumb-line">
            <ul class="breadcrumb">
                <i class="icon-home2 color-999"></i>
                <li><a href="javascript:;">当前功能</a></li>
                <li><a href="javascript:;">我的学费</a></li>
            </ul>
        </div>
    </div>
    <!-- /page header -->
    <div class="content">
        <div class="panel panel-flat">
            <!-- Inline list -->
            <div class="panel panel-white">
                <div class="panel-heading">
                    <h6 class="panel-title"><i class="icon-clipboard3  position-left"></i>学费详情</h6>
                </div>

                <div class="panel-body">
                    <ul class="student-paying-title">
                        <li class="title1">
                            <img src="{{ asset('images/student-paying-qb.png') }}"/>
                            <p>交费总额：<span>{{ $total_fee }}</span>元</p>
                        </li>
                        <li class="title2">
                            <a href="{{ url('/home/user/arrears_info') }}">
                                <p>欠费金额：<span>{{ $arrears_fee??"0" }}</span>元</br><i>（请及时交费以免影响预约学车）</i></p>
                            </a>
                        </li>
                        <li class="title2">
                            <p>用户余额：<span>{{ auth()->user()->balance }}</span>元</br><i>（用户充值余额）</i></p>
                        </li>
                        <li class="title3">
                            <a href="#" class="payingbtn"><img
                                        src="{{ asset('images/student-paying-paybtn.jpg') }}"/></a>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- Inline list -->
            <div class="panel panel-white">
                <div class="panel-heading">
                    <h6 class="panel-title"><i class="icon-list position-left"></i>交费明细</h6>
                </div>

                <div class="panel-body">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr class="bg-mainColor">
                            <td width="20%">交费日期</td>
                            <td width="20%">费用类型</td>
                            <td width="20%">应收金额（元）</td>
                            <td width="20%">实收金额（元）</td>
                            <td width="20%">支付方式</td>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($charges as $charge)
                            <tr>
                                <td>{{ $charge->pay_time }}</td>
                                <td>{{ $charge->income_type_detail->name }}</td>
                                <td>{{ $charge->money }}</td>
                                <td>{{ $charge->pay_money }}</td>
                                <td>{{ $charge->pay_type_detail->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">暂无数据</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @include('home.page',['paginator'=>$charges])

        </div>
    </div>
@endsection

@section('script')
@endsection
