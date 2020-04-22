<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7"/>
    <title>56驾考</title>
    {!! HTML::style('css/reset.css') !!}
    {!! HTML::style('css/student-find.css') !!}
</head>
<body>

<div class="login-main">
    <img class="logo-image" src="{!! asset('images/login-logo.png') !!}" alt="" />
    <form class="login-form find-form" action="{{ URL::to('home/setNewPassword') }}" method="post">
        <div class="find-one">
            <label for="">原密码：</label><span><input type="password" name="old_password" /></span>
        </div>
        <div class="find-one">
            <label for="">设置新密码：</label><span><input type="password" name="new_password" /></span>
        </div>
        <div class="find-one">
            <label for="">新密码确认：</label><span><input type="password" name="new_password_confirmation" /></span>
        </div>
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="_method" value="put">
        <div class="find-sure">
            <input type="image" src="{!! asset('images/find-sure-btn.png') !!}" name="" id="" value="" />
        </div>
    </form>

</div>

<img class="login-footer" src="{!! asset('images/footer-image.png') !!}"/>
</body>
<script src="/js/js/jquery/dist/jquery.min.js"></script>
<script>
$('form').submit(function (event) {
    event.preventDefault();
    $.post('/home/resetPassword',$('form').serialize(),function (data) {
        if (data.result == 'success') {
            layer.msg('修改成功!', {time: 2000, icon: 6});
            window.location.href = '/home';
        } else {
            layer.msg('修改失败：' + data.err_message, {time: 2000, icon: 5});

        }
    });
});
</script>
</html>
