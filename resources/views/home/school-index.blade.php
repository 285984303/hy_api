<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>报名驾校</title>
    <link rel="stylesheet" href="/css/reset.css" />
    <link rel="stylesheet" href="/css/school/school-index.css" />
    <link rel="stylesheet" href="/js/plugins/select2/select2.min.css">
    <link rel="stylesheet" href="/js/plugins/layer/skin/layer.css" />
    <link rel="stylesheet" type="text/css" href="/js/plugins/swiper/idangerous.swiper2.7.6.css"/>
    <link rel="stylesheet" type="text/css" href="/js/plugins/swiper/idangerous.swiper.3dflow.css"/>
</head>
<body>
<div class="school-head">
    <div class="head1">
        {{--<p class="left"><img src="/images/school-1.png"/><span>北京</span><a href="javascript:void(0);">[切换]</a></p>--}}
        <p class="right">
<?php
    if (auth()->user()) {
?>
    <a href="/home">个人中心</a>
    <a href="/home/logout">注销</a>
<?php
    } else {
?>
    <a href="/home/login?url=/home/sign_up">登录</a>
    <a href="/home/register?url=/home/sign_up">注册</a>
<?php
    }
    $school = \App\Models\Data\School::where('id',request('school_id',1))->first();
    $school->introduce;
    $school->aptidude;
    $products = \App\Models\Business\Product::getProductsValid($school->id);
?>
        </p>
    </div>
</div>
<div class="school-banner">
    <img src="/images/school-3.png"/>
</div>
<div class="school-main">
    <div class="school-bao">
        <div class="left"><img src="http://backend.dev.hongjitech.cn{{ $school->introduce->school_logo }}"/></div>
        <div class="mid">
            <h1 style="overflow: hidden;text-overflow:ellipsis;white-space: nowrap;">{{ $school->school_name }}</h1>
            <p class="p-one"><img src="/images/school-5.png"/><span>报名费</span><strong>{{ $products->min('register_fee') }}</strong>元起</p>
            <p><img src="/images/school-6.png"/><span>地址</span><strong>{{ $school->address }}</strong></p>
            <p><img src="/images/school-7.png"/><span>电话</span><strong>{{ $school->tel_phone }}</strong></p>
            <div>
                <div class="ban">
                    @foreach($products as $product)
                        <a onclick="choose( {!! $product->id !!}, this)" title="{{ $product->register_fee }}元 {{ $product->name }}">{{ $product->register_fee }}元 {{ $product->name }}</a>
                    @endforeach
                </div>
                <button class="school-bm" style="background-color: #cccccc;">立即报名</button>
            </div>
        </div>
        <div class="right"></div>
    </div>
    <div class="school-zhan">
        <ul>
            <li><img src="/images/school-8.png"/><p>先学后付  计时收费 </p></li>
            <li><img src="/images/school-9.png"/><p>免费班车  日夜接送 </p></li>
            <li><img src="/images/school-10.png"/><p>一人一车  四个自主 </p></li>
            <li><img src="/images/school-11.png"/><p>培训考试  同一场地 </p></li>
        </ul>
    </div>
    <div class="school-sub-bar">
        <ul>
            <li class="on"><a href="#1">驾校简介</a></li>
            <li><a href="#2">驾校风采</a></li>
            <li><a href="#4">费用说明</a></li>
            <li><a href="#5">联系方式</a></li>
        </ul>
    </div>
    <div class="school-content">
        <div id="1" class="div-one div">
            <h1><span class="te">简介</span></h1>
            <div><?= $school->introduce->introduce ?></div>
        </div>
        <div id="2" class="div">
            <h1><span>驾校风采</span></h1>
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    <?php
                    foreach ($school->aptidude->toArray() as $img) {
                        if (preg_match('/\.(jpg|png|bmp)\??/i',$img)) {
                    ?>
                    <div class="swiper-slide"><img src="http://backend.dev.hongjitech.cn<?= $img ?>"/></div>
                    <?php }} ?>
                </div>
                <div class="pagination"></div>
            </div>
        </div>
        <div id="4" class="div div-44">
            <h1><span>费用说明</span></h1>
            <?php foreach ($products as $product) { ?>
            <div class="up">
                <table border="1px" cellspacing="0" cellpadding="0" width="674px;">
                    <tr><td class="one"><?= $product->name ?></td><td class="two"><?= $product->register_fee ?>元</td></tr>
                    <tr><td class="one">套餐类型</td><td class="two"><?= $product->type ?></td></tr>
                    <tr><td class="one">欠费预约</td><td class="two"><?= $product->allow_arrears ? $product->arrears_hours.'课时' : '否' ?></td></tr>
                    <tr><td class="one">准驾车型</td><td class="two"><?= implode(',', $product->vehicle_types->pluck('name')->toArray()) ?></td></tr>
                </table>
                <p class="te-a"><a onclick="register(<?= $product->id ?>)">立即报名</a></p>
            </div>
            <?php } ?>
        </div>
        <div id="5" class="div div-55">
            <h1><span>联系方式</span></h1>
            <table border="" cellspacing="0" cellpadding="0" width="890px">
                <tr><td class="one">联系人</td><td class="two"><?= $school->contacts        ?></td></tr>
                <tr><td class="one">手机号码</td><td class="two"><?= $school->contact_phone ?></td></tr>
                <tr><td class="one">座机号码</td><td class="two"><?= $school->tel_phone     ?></td></tr>
                <tr><td class="one">传真号码</td><td class="two"><?= $school->fax           ?></td></tr>
                <tr><td class="one">联系地址</td><td class="two"><?= $school->address       ?></td></tr>
            </table>
        </div>
    </div>
</div>
<div class="school-footer">
    <p>copyright©2016<span>贵州宏济电子科技有限公司北京（分）公司</span></p>
</div>
<div id="lay-t" style="display: none;">
    {{ Form::open() }}
    <div><label for="">驾校</label><span>{{ $school->school_name }}</span></div>
    <div><label for="">班型</label><select name="product_id" id="product_id" style="width: 408px;height: 30px;">
            @foreach($products as $product)
                <option value="{{ $product->id }}">{{ $product->register_fee }}元 {{ $product->name }}</option>
            @endforeach
        </select></div>
    <div><label for="">姓名</label><input type="text" name="user_truename" value="{{ auth()->user()->user_truename??"" }}" placeholder="请填写您的真实姓名" /></div>
    <div><label for="">身份证号码</label><input type="text" name="id_card" value="{{ auth()->user()->id_card??"" }}" placeholder="请输入您的身份证号码" /></div>
    {{--<div><label for="">手机号码</label><input type="text" value="{{ auth()->user()->user_telphone }}" readonly placeholder="请输入您的手机号码" /></div>--}}
    <div><label for="">户籍地址</label><input type="text" name="id_card_address" value="{{ auth()->user()->id_card_address??"" }}" placeholder="请输入您的户籍地址(请与身份证一致)" /></div>
    <div class="spacil-d"><button type="submit" class="sure-bt">提交</button><a class="cancel-bt">取消</a></div>
    {{ Form::close() }}
</div>
</body>
<script src="/js/js/jquery/dist/jquery.js"></script>
<script src="/js/plugins/swiper/idangerous.swiper2.7.6.min.js"></script>
<script src="/js/plugins/swiper/idangerous.swiper.3dflow.js"></script>
<script src="/js/plugins/select2/select2.min.js"></script>
<script src="/js/plugins/layer/layer.js"></script>
<script>
    $(function(){
        var mySwiper = new Swiper('.swiper-container',{
            //其他设置
            autoplay : 5000,//可选选项，自动滑动
            loop : true,//可选选项，开启循环
            slidesPerView : 3,
            pagination : '.pagination',
            tdFlow: {
                rotate : 50,
                stretch :40,
                depth: 100,
                modifier : 1,
                shadows :false
            }
          
        });
        $(".sure-bt").on("click",function(){
        	layer.closeAll();
        });
        $(".cancel-bt").on("click",function(){
        	layer.closeAll();
        })
        $('select').select2();
    });

    // 错误信息
    @if($errors->any())
    var message = "";
    @foreach($errors->all() as $error)
            message = message+"{{ $error }}";
    @endforeach

    layer.confirm(message, {
    	btn: ['确认','取消']
       },function(){
         layer.msg('确认成功', {icon: 1});
        }, function(){
      }
    );
    @endif


    function register(id){
        // 是否需要先登录
        @if(auth()->user())
            layer.open({
                type: 1,
                closeBtn:1, //不显示关闭按钮
                shift: 2,
                title:false,
                shadeClose: true, //开启遮罩关闭
                //btn: [ '提交','取消'],
                area:['820px','520px'],
                content:  $('#lay-t')
            });
        @else
            layer.confirm('请先登录', {
                btn: ['登录','让我再想想'] //按钮
            }, function(){
                window.location.href =  "/home/login?url=/home/sign_up";
                return false;
            }, function(){
            });
        @endif
    }


    function choose(id, event) {
        $('.ban').children('a').css('background-color','#cccccc');
        $(event).css('background-color','#3384e4');
        var button = $('.school-bm');
        button.attr('onclick','register(' +id+ ')');
        button.css('background-color','#3384e4');
    }
</script>
</html>
