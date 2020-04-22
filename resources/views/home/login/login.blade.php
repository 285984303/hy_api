<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>趁年轻</title>
    {!! HTML::style('css/reset.css') !!}
    {!! HTML::style('css/student-login.css') !!}
</head>
<body>
<div style="height: 5%;clear: both;"></div>
<div class="login-main">
    <div class="user-top-logo">
        <img class="logo-image" src="{!! asset('images/bg.png') !!}" alt=""/>
        <a href="http://www.beian.miit.gov.cn">豫ICP备18009642号</a>
    </div>
    <div class="user-login-form" style="display: none;">
        <form class="login-form" method="post" action="/dologin">
            <span id="use_yan" style="display: block;"></span>
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
            <div class="user-form-input">
                <input type="text" name="user_telphone" class="user-login-input" id="login-user-input" placeholder="请输入手机号"/>
                <span id="use_tel" style="display: block;"></span>
            </div>
            <div class="user-form-input msgcode">
                <div class="input-left">
                    <input type="text" name="msgcode" class="user-login-input" id="login-auth-input" placeholder="请输入验证码"/>
                </div>
                <div class="input-right">
                    <button type="button" class="user-login-input checkCode" >获取验证码</button>
                </div>
            </div>
            <div class="user-form-input password">
                <input type="password" name="password" class="user-login-input" id="password" placeholder="请输入密码"/>
            </div>
            <div class="user-form-submit">
                <input class="btn-login" id="login-submit" type="button" value="登    录"/>
                <div class="login_method">
                    <span class="msgcode_login">验证码登录</span>
                    <span class="password_login">密码登录</span>
                </div>
            </div>
        </form>

    </div>
</div>
{{--<div class="user_bottom">
<img class="login-footer" src="{!! asset('images/footer-image.png') !!}"/>
</div>--}}
</body>

<script src="/js/js/jquery/dist/jquery.min.js"></script>
<script>
    $(function () {
        /*默认验证码登录*/
        var login_method = "msgcode_login";
        /*密码登录*/
        $(".password_login").on("click",function(){
            $(".msgcode").hide();
            $(".password_login").hide();

            $(".msgcode_login").show();
            $(".password").show();
            login_method = "password_login";
        });
        /*验证码登录*/
        $(".msgcode_login").on("click",function(){
            $(".msgcode_login").hide();
            $(".password").hide();

            $(".msgcode").show();
            $(".password_login").show();
            login_method = "msgcode_login";
        });

        /*验证码*/
        function re_captcha() {
            var url = "{{ URL::to('api/small/sendmsg') }}";
            $.get(url + "/?phone="+$("#login-user-input").val(),function(data){
            });
        }
        $("#login-submit").click(function (event) {
            event.preventDefault();
            if(login_method == "msgcode_login"){
                data = {
                    phone:$("#login-user-input").val(),
                    msgcode:$("#login-auth-input").val()
                };
                $.post('{{ URL::to('api/small/login') }}',data,function (data) {
                    if (data.result == 'success') {
                        window.location.href = "{{ URL::to('appointment') }}";
                        return;
                    } else {
                        //alert(data.err_message);
                        $("#use_yan").html("<font color='red'>"+data.msg+"</font>");
                    }
                });
            }else if(login_method == "password_login"){
                data = {
                    phone:$("#login-user-input").val(),
                    password:$("#password").val()
                };
                $.post('{{ URL::to('api/small/login') }}',data,function (data) {
                    if (data.result == 'success') {
                        window.location.href = "{{ URL::to('appointment') }}";
                        return;
                    } else {
                        //alert(data.err_message);
                        $("#use_yan").html("<font color='red'>"+data.msg+"</font>");
                    }
                });
            };


        });


        var $checkCode = $(".checkCode");
        var t = 59;
        var timeCount;
        $checkCode.click(function () {
            if($("#login-user-input").val() ==''){
                $("#use_yan").html("<font color='red'>请输入手机号！</font>");
                return false;
            }else{
                $("#use_yan").html("");
            }
            re_captcha();
            $checkCode.attr('disabled', 'disabled');
            $checkCode.text("60s后重新获取");
            function count() {
                $checkCode.text(t + "s后重新获取");
                if (t > 0) {
                    t--;
                } else {
                    clearInterval(timeCount);
                    $checkCode.text("发送验证码");
                    $checkCode.attr('disabled', false);
                    t = 59;
                }
            }
            timeCount = setInterval(count, 1000);

        });
    })
</script>
</html>
