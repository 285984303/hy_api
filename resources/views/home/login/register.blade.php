<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>学员注册</title>
    {!! HTML::style('css/reset.css') !!}
    {!! HTML::style('css/student-register.css') !!}
    <link rel="stylesheet" href="{{ asset('js/plugins/Validform-master/css/style.css') }} " />
</head>
<body>
<div class="reg-main">
    <img class="logo-image" src="{!! asset('images/login-logo.png') !!}" alt="" />
    <form class="reg-form" action="{{ URL::to('home/register') }}" method="post">
        <div class="input-one top" >
            <label for="" >手机号码：</label><span><input type="text" name="user_telphone" id="mobile" onblur="mobileCode()"  placeholder=""/></span><font style="float: left;color: red;
    padding-left: 166px;" id="info"></font>
        </div>
        <div class="input-two top">
            <label for="" >短信验证码：</label><span><input type="text" name="captcha"  placeholder="" /></span><button type="button" href="#" id="Submit">点击获取</button>
        </div>
        <div class="input-three top">
            <label for="" >设置密码：</label><span><input type="password" name="password" class="psdCode" onblur="pwdCode()"  placeholder=""/></span><font style="float: left;color: red;
    padding-left: 166px;" class="psd_code"></font>
        </div>
        <div class="input-four top">
            <label for="" >密码确认：</label><span><input type="password" name="password_confirmation" class="confirm_password" onblur="confirm()"  placeholder=""/></span><font style="float: left;color: red;
    padding-left: 166px;"class="psd_true"></font>
        </div>
        <p>我同意<a href="#">《56驾考用户协议》</a></p>
        {{ csrf_field() }}
        <div class="input-five top">
            <input type="image" src="{!! asset('images/reg-btn.png') !!}" />
        </div>
        <div class="login-forget"><a href="#">已有账号？</a><a href="/home">立即登录</a></div>
    </form>

</div>
<img class="login-footer" src="{!! asset('images/footer-image.png') !!}"/>
</body>
{!! HTML::script('js/js/jquery/dist/jquery.js') !!}
<script>
    var test = {
        node:null,
        count:60,
        start:function(){
            console.log(this.count);
            if(this.count > 0){
                this.node.innerHTML = this.count--+"S后重发";
                var _this = this;
                setTimeout(function(){
                    _this.start();
                },1000);
            }else{
                this.node.innerHTML = "再次发送";
                this.count = 60;
                $("#Submit").click(function(){
                    if (!mobileCode() || $("#info").html() != "√")
                       return;
                    $("#Submit").unbind();
                    test.init(btn);
                });
            }
        },
        //初始化
        init:function(node){
            var url = "{{ URL::to('home/captcha') }}";
            $.get(url + "/"+$("#mobile").val(),function(data){});
            this.node = node;
            delete node.onclick;
            this.start();
        }
    };
    var btn = document.getElementById("Submit");
    $("#Submit").click(function(){
        if (!mobileCode() || $("#info").html() != "√")
           return;
        $("#Submit").unbind();
        test.init(btn);
    });
    /*手机号验证*/
   var current_mobile = '';
    function mobileCode(){
        var mobile = $("#mobile").val();
        var phone_reg = /^0{0,1}(13[0-9]|15[0-9]|18[0-9]|14[0-9]|17[0-9])[0-9]{8}$/;
        if(mobile == ""){
            $("#info").html("请填写手机号");
            return false;
        } else {
            if(!phone_reg.test(mobile)){
                $("#info").html("请正确填写手机号");
                return false;
            } else {
                if (current_mobile == mobile) return true;
                var xmlHttp = new XMLHttpRequest();
                xmlHttp.open('GET', "/home/CheckTel/"+mobile, false);
                xmlHttp.send(null);

                var data = JSON.parse(xmlHttp.responseText);

                current_mobile = mobile;
                if(data.result == 'success'){
                    $("#info").html("√");
                    return true;
                }else{
                    $("#info").html("已被注册");
                    return false;
                }
            }
        }
    }
    /*密码非空验证*/
    function pwdCode(){
        var password = $(".psdCode").val();
        var psd_reg = /^[A-Za-z0-9]+$/;
        if(password=="") {
            $(".psd_code").html("密码不能为空");
            return false;
        } else {
            if(!psd_reg.test(password)||password.length<6||password.length>8){
                $(".psd_code").html("字母数字6-18位");
                return false;
            }else{
                $(".psd_code").html("√");
                return true;
            }

        }

    }
    /*确认密码验证*/
    function confirm(){
        var password = $(".psdCode").val();
        var confirm_password = $(".confirm_password").val();
        if(confirm_password=="") {
            $(".psd_true").html("不能为空");
            return false;
        } else {
            if(confirm_password != password){
                $(".psd_true").html("两次输入不一样");
                return false;
            }else{
                $(".psd_true").html("√");
                return true;
            }
        }
    }

    $("form").submit(function(event) {
        event.preventDefault();
        if (mobileCode() == false || pwdCode() == false || confirm()==false ) {
            event.preventDefault();
            return false;
        }
        $.post('/home/register',$('form').serialize(),function(data){
            if (data.result == 'success') {
                layer.msg('注册成功!', {time: 2000, icon: 6});
                window.location.href = '/home';
            } else {
                layer.msg('注册失败：' + data.err_message, {time: 2000, icon: 5});
            }
        });

    });
</script>
</html>
