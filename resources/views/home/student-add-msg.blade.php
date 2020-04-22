<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>信息完善</title>
    {!! HTML::style('css/reset.css') !!}
    {!! HTML::style('css/student-register.css') !!}
    {!! HTML::style('js/plugins/Validform-master/css/style.css') !!}
    {!! HTML::style('js/plugins/select2/select2.min.css') !!}
</head>
<body>
<div class="reg-main">
    <img class="logo-image" src="{!! asset('images/login-logo.png') !!}" alt="" />
    <form class="reg-form" action="{{ url('home/editUser') }}" method="post">
        <div class="input-one-add" >
            <label for="" >姓名：</label><span><input type="text" name="user_truename"  placeholder=""/></span>
        </div>
        <div class="input-two-add">
            <label for="" >性别：</label><input type="radio" name="user_sex" value="1"><span>男</span> <input type="radio" name="user_sex" value="2"><span>女</span>
        </div>
        <div class="input-one-add">
            <label for="" >身份证号码：</label><span><input type="text" name="id_card"  placeholder=""/></span>
        </div>
        <div class="input-three-add">
            <label for="" >户口所在地：</label>
            <span class="input-prov1"><select name="old_province_id" class="input-prov" onchange="change_area(this)"><option value="">省</option>
                @foreach($provinces as $province)
                <option value="{{ $province->code }}">{{ $province->name }}</option>
                @endforeach
            </select></span>
            <span class="input-prov1"><select name="old_city_id" class="input-prov" onchange="change_area(this)"><option value="">市</option></select></span>
			<span class="input-city1"><select name="old_area_id" class="input-city" onchange="change_area(this)"><option value="">区/县</option></select></span>

        </div>
        <div class="input-three-add">
            <label for="" >现居住地址：</label>
            <span class="input-prov1"><select name="new_province_id" class="input-prov" onchange="change_area(this)"><option value="">省</option>
                @foreach($provinces as $province)
                        <option value="{{ $province->code }}">{{ $province->name }}</option>
                @endforeach
            </select></span>
            <span class="input-prov1"><select name="new_city_id" class="input-prov" onchange="change_area(this)"><option value="">市</option></select></span>
			<span class="input-city1"><select name="new_area_id" class="input-city" onchange="change_area(this)"><option value="">区/县</option></select></span>
        </div>
        <div class="input-one-add">
            <label for="" >详细地址：</label><span><input type="text" name="user_address"  placeholder=""/></span>
        </div>
        <div class="input-one-add">
            <label for="" >邮箱：</label><span><input type="text" name="user_email"  placeholder=""/></span>
        </div>
        <div class="input-five-add">
            <a href="{{ URL::to('/home') }}"><input type="image" src="{!! asset('images/add-msg-btn.png') !!}"/></a>
            <a><input type="image" src="{!! asset('images/add-msg-btn1.png') !!}" name="" id="" value="" /></a>
        </div>

    </form>

</div>
<img class="login-footer" src="{!! asset('images/footer-image.png') !!}"/>
</body>

<script type="text/javascript" src="{{ asset("js/js/jquery/dist/jquery.js") }}" ></script>
<script type="text/javascript">
function change_area(pro){
    $.ajax({
        type:'get',
        url:"{{ url('home/user/change_area') }}",
        data:{'id':$(pro).val()},
        success:function(msg){
            $(pro).parent().next().children().eq(0).empty();
            $(pro).parent().next().children().eq(0).append(msg);
        }
    });
}
</script>
</html>
