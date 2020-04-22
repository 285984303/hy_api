<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <style>
        .wrap{
            width: 470px;
            margin: 0 auto;
            margin-top: 30px;
        }
        .wrap div{
            margin-bottom: 20px;
        }
        .wrap label{
            display: inline-block;
            width: 120px;
            height: 30px;
            line-height: 30px;
            text-align: right;
            margin-right: 10px;
            font-size: 16px;
        }
        .wrap .row-two input{
            margin-left: 10px;
        }
        .wrap .row-two span{
            font-size: 18px;
            color:#8a8a8a;
        }
        .wrap .row-four select{
            width: 90px;
            height: 36px;
            line-height: 36px;
            margin-right: 15px;
            border: 1px solid #ddd;
        }
        .wrap .row-five select{
            width: 94px;
            height: 36px;
            line-height: 36px;
            margin-right: 15px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .input input{
            width: 320px;
            height: 36px;
            line-height: 36px;
            display: inline;
        }
        .wrap .row-eight{
            width: 160px;
            margin: 0 auto;
        }

        .wrap .su-btn {
            background: url('/images/sure11.png');
            width: 160px;
            height: 42px;
        }
    </style>
    <body>
        <form class="wrap" action="{{ url("home/user/update") }}" method="post">
            @unless($product)
            <div class="row-first input"><label for="">姓名:</label>
                <input type="text" name="user_truename" value="{{ $user->user_truename }}" /></div>
            <div class="row-two">
                <label for="">性别:</label>
                <input type="radio" name="user_sex" value="1" @if($user->user_sex=='1')checked @endif/><span>男</span>
                <input type="radio" name="user_sex" value="2" @if($user->user_sex=='2')checked @endif/><span>女</span>
            </div>
            <div class="row-three input idDiv">
                <label for="">身份证号:</label>
                <input type="text" name="id_card" id="idCard" value="{{ $user->id_card }}"/>
            </div>
            <div class="row-four">
                <label for="">户口所在地:</label>
                <input type="text" name="id_card_address" value="{{ $user->id_card_address }}">
            </div>
            @endunless
            <div class="row-five">
                <label for="">现居住地址:</label>
                <select name="new_province_id" class="input-prov" onchange="change_area(this)">
                    <option value='0'>省</option>
                    @foreach($provinces as $province)
                    <option value="{{ $province->code }}" @if($user->new_province_id == $province->code) selected @endif>{{ $province->name }}</option>
                    @endforeach
                </select>
                <select name="new_city_id" class="input-prov" onchange="change_area(this)">
                    <option value='0'>市</option>
                    @foreach($new_cities as $city)
                    <option value="{{ $city->code }}" @if($user->new_city_id == $city->code) selected @endif>{{ $city->name }}</option>
                    @endforeach
                </select>
                <select name="new_area_id" class="input-city" onchange="change_area(this)">
                    <option value='0'>县/区</option>
                    @foreach($new_areaes as $area)
                    <option value="{{ $area->code }}" @if($user->new_area_id == $area->code) selected @endif>{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="row-six input">
                <label for=""></label>
                <input type="text" name="user_address" class="form-control" value="{{ $user->user_address }}"/>
            </div>
            <div class="row-seven input emailDiv">
                <label for="">邮箱:</label>
                <input type="text" name="user_email" class="form-control" id="email" value="{{ $user->user_email }}"/>
                <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                <input type="hidden" name="_method" value="put"/>

            </div>
            <div class="row-eight">
                <button class="su-btn" type="submit" value=""></button>
            </div>
        </form>
    </body>
    <script type="text/javascript" src="{{ asset("js/js/jquery/dist/jquery.js") }}" ></script>
    <script src="{{ asset("js/plugins/cascade/script.js") }}"></script>
    <script>
        function change_area(pro){
            if (!$(pro).val())
                    return;
            $.get("{{ url('home/user/change_area') }}", {'id':$(pro).val()}, function(msg){
                $(pro).next().html(msg).change();
            });
        }
    </script>
</html>
