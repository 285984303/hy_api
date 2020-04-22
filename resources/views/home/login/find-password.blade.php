<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7"/>
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>56驾考</title>
    {!! HTML::style('css/reset.css') !!}
    {!! HTML::style('css/student-find.css') !!}
</head>
<body>

<div class="login-main">
    <img class="logo-image" src="{!! asset('images/login-logo.png') !!}" alt="" />
    <form class="login-form find-form" action="{{ URL::to('home/setNewPassword') }}" method="post">
        <div class="find-one">
            <label for="">手机号码：</label><span><input type="text" name="user_telphone" value="{{ $mobile or '' }}" onblur="mobileCode()"/></span><font id="info" style="float: left;color: red;padding-left: 154px;"></font>
        </div>
        <div class="find-two">
            <label for="">短信验证码：</label><span><input type="text" name="captcha"/></span>
            <button type="button" id="Submit">点击获取</button>
        </div>
        <div class="find-one">
            <label for="">新密码：</label><span><input type="password" name="password"/></span>
        </div>
        <div class="find-one">
            <label for="">验证新密码：</label><span><input type="password" name="password_confirmation"/></span>
        </div>
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="find-four">
            <input type="image" src="{!! asset('images/find-next.png') !!}"/>
        </div>
    </form>
</div>

<img class="login-footer" src="{!! asset('images/footer-image.png') !!}"/>
</body>
{!! HTML::script('js/js/jquery/dist/jquery.min.js') !!}
<script>

    $('form').submit(function (event) {
        event.preventDefault();
        //todo 验证表单
        $.post('/home/findPassword', $('form').serialize(), function (data) {
            if (data.result == 'success') {
                layer.msg('设置新密码成功!', {time: 2000, icon: 6});
                window.location.href = '/home';
            } else {
                layer.msg('操作失败！', {time: 2000, icon: 5});
            }
        });
    });

    var test = {
        node:null,
        count:60,
        start:function(){
            console.log(this.count);
            if(this.count > 0){
                this.node.html(this.count--+"秒后重发");
                var _this = this;
                setTimeout(function(){
                    _this.start();
                },1000);
            }else{
                //this.node.attr("disabled",true);
                this.node.html("再次发送");
                this.count = 60;
                rebind();
            }
        },
        //初始化
        init:function(node){
            this.node = node;
            //this.node.attr("disabled",true);
            this.start();
        }
    };
    var btn = $('#Submit');

    function rebind() {
        btn.click(function(){
            if (mobileCode() == true){
            $.get("{{ URL::to('home/captcha') }}/" + $("input[name='user_telphone']").val(),function(data){});
            test.init(btn);
            btn.unbind();
            }
           
        });
        //$(this).removeAttr('disabled');
    }
    rebind();

    /*手机号验证*/
    function mobileCode(){
        var mobile = $("input[name='user_telphone']").val();
        var phone_reg = /^0{0,1}(13[0-9]|15[0-9]|18[0-9]|14[0-9]|17[0-9])[0-9]{8}$/;
        if(mobile == ""){
            $("#info").html("请填写手机号");
            return false;
        }else{
            if(!phone_reg.test(mobile)){
                $("#info").html("请正确填写手机号");
                return false;
            }else{
                $("#info").html("√");
                return true;
            }
        }
    }

</script>
</html>
