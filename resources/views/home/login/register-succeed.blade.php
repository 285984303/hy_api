<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>注册成功</title>
    {!! HTML::style('css/reset.css') !!}
    {!! HTML::style('css/student-register.css') !!}
</head>
<body>
<div class="reg-main">
    <img class="logo-image" src="{!! asset('images/login-logo.png') !!}" alt="" />
    <img style="margin:44px 0 0 270px;" src="{!! asset('images/student-reg-over.png') !!}" alt="" />
    <div style="margin:44px 0 0 285px;">
        <a href="{{ URL::to('home/user') }}"><input type="image" src="{!! asset('images/login-btn11.png') !!}" /></a>
    </div>
</div>
<img class="login-footer" src="{!! asset('images/footer-image.png') !!}"/>
</body>
</html>
