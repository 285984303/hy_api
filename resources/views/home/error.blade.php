@extends('home.base')

@section('title','错误')

@section('html_head')
    <style type="text/css">
        .error-main{
            min-height: 726px;
            border: 1px solid #ccc;
            border-top: none;
        }

        .error-submain{
            width: 600px;
            margin:0 auto;
            padding-top: 200px;
        }

        .error-submain img{
            display: block;
            float: left;
        }

        .error-submain p{
            font-size: 34px;
            color: red;
            float: left;
            padding: 10px 0 0 20px;
        }
    </style>
@endsection

@section('content')
    <div class="error-main">
        <div class="error-submain"><img src="/images/error-ee.png"/><p>{{ $error }}</p></div>
    </div>
@endsection

@section('script')
@endsection
