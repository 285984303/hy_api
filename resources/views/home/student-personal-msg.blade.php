@extends('home.base')
@section('title','个人信息')
@section('html_head')
    <link rel="stylesheet" href="{{ asset('css/student-personal-msg.css') }}"/>
    <style type="text/css">
       .panel{
         min-width: 1033px;
       }
    </style>
@endsection
@section('content')
        <!-- Page header -->
    <div class="page-header page-header-default">
        <div class="breadcrumb-line">
            <ul class="breadcrumb">
                <i class="icon-home2 color-999"></i>
                <li><a href="javascript:;">当前功能</a></li>
                <li><a href="javascript:;">个人信息</a></li>
            </ul>
        </div>
    </div>
    <!-- /page header -->
    <div class="content">
        <div class="panel panel-flat">
            <!-- Title with left icon -->
            <div class="panel panel-white">
                <div class="panel-heading">
                    <h6 class="panel-title"><i class="icon-user position-left"></i> 个人信息</h6>
                    <div class="heading-elements">
                        <a class="modify-btn text_color_5897FB">修改</a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="title-down">
                        <dl>
                            <dt><img src="{{ $user->user_img or asset('images/wait-img.png') }}">
                            <p style="text-align: center;">编号:<span>{{ $user->student_id }}</span></p></dt>
                            <dd class="dd-first"><label for="">姓名：</label>{{ $user->user_truename }}
                                <span>({{ $user->getGender() }})</span></dd>
                            <dd><label for="">身份证号：</label>{{ $user->id_card }}</dd>
                            <dd><label for="">户口所在地：</label><span
                                        title="{{ $user->id_card_address }}">{{ $user->id_card_address }}</span>
                            </dd>
                            <dd><label for="">现居住地址：</label><span
                                        title="{{ $user->xz_address }}">{{ $user->xz_address }}</span>
                            </dd>
                        </dl>
                        <?php
                        $relation = (new \App\Models\Business\UserProduct())->where('user_id', $user->id)
//                                               ->where('status', \App\Models\Business\UserProduct::STATUS_USING)
                                ->first(); ?>

                        <div class="left">
                            <p><label for="">期数：</label>{{ isset($relation->installments)?$relation->installments:'' }}</p>
                            <p><label for="">邮箱：</label>{{ $user->user_email }}</p>
                            <p><label for="">状态：</label>{{ $relation ? $relation->getStatus() : '暂未报名' }}</p>
                        </div>
                        <div class="mid">
                            <p><label for="">电话：</label>{{ $user->user_telphone }}</p>
                            <p><label for="">套餐类型：</label>{{ $product ? $product->name : '暂无' }}</p>
                            <p><label for="">报考类型：</label>{{ $product ? $product->licence_type->name:'暂无' }} </p>
                        </div>
                        <div class="right">
                            <p><label for="">报名时间：</label><span>{{ $relation ? $relation->start_date :'暂无' }}</span></p>
                            <p style="margin-top: 10px;line-height: 34px;text-align: left;color: #000"><label
                                        for="">备用电话：</label><span>{{ $user->alternate_user_telephone }}</span></p>
                            <p style="line-height: 34px;text-align: left;color: #000"><label
                                        for="">报名来源：</label><span>{{ $relation ? $relation->students_sources:"" }}</span></p>
                            <p style="line-height: 34px;text-align: left;color: #000"><label
                                        for="">技能证时间：</label><span>{{ $relation ? $relation->skill_time:'' }}</span></p>
                        </div>
                    </div>

                </div>
            </div>
            @if($product)
                    <!-- Title with left icon -->
            <div class="panel panel-white">
                <div class="panel-heading">
                    <h6 class="panel-title"><i class=" icon-loop3 position-left"></i> 考证进度</h6>
                    <div class="heading-elements">
                        <a href="{{ url('home/appointment') }}" class="text_color_5897FB">
                            预约训练
                        </a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="plan-img">
                        @if($user->status=='在读')
                            @if(empty($user->subject_1))
                                <img src="{{ asset('images/apply-img2.png') }}" alt=""/>
                            @elseif(empty($user->subject_2) && empty($user->subject_3))
                                <img src="{{ asset('images/apply-img3.png') }}" alt=""/>
                            @elseif(empty($user->subject_2) && !empty($user->subject_3))
                                <img src="{{ asset('images/apply-img8.png') }}" alt=""/>
                            @elseif(empty($user->subject_4))
                                <img src="{{ asset('images/apply-img5.png') }}" alt=""/>
                            @else
                                <img src="{{ asset('images/apply-img6.png') }}" alt=""/>
                            @endif
                        @elseif($user->status=='结业')
                            <img src="{{ asset('images/apply-img7.png') }}" alt=""/>
                        @else
                            <img src="{{ asset('images/apply-img1.png') }}" alt=""/>
                        @endif
                        <ul class="plan-status">
                            <li class="status-1">
                                <span>报考驾校</span>
                                <a href="javascript:void(0);" class="status-red" style="padding-left: 3%;">
                                    已完成</a>
                            </li>
                            <li class="status-2">
                                <span style="padding-left: 22%;">受理</span>
                                <a style="padding-left: 20%;" href="javascript:void(0);"
                                   class="status-red">@if($user->status=='在读') 已完成 @else 未完成 @endif</a>
                            </li>
                            <li class="status-3">
                                <span style="padding-left: 30%;">科目一考试</span>
                                <a style="padding-left: 30%;" href="javascript:void(0);" class="status-no"><b
                                            class="status-green">@if(empty($user->subject_1))
                                            未开始    @else {{ $user->subject_1 }}分钟 @endif</b></a>
                            </li>
                            <li class="status-4">
                                <span style="padding-left: 40%;">科目二考试</span>
                                <a style="padding-left: 40%;" href="javascript:void(0);" class="status-no"><b
                                            class="status-green">@if(empty($user->subject_2))
                                            未开始    @else {{ $user->subject_2 }}分钟 @endif</b></a>
                            </li>
                            <li class="status-5">
                                <span style="padding-left: 50%;">科目三考试</span>
                                <a style="padding-left: 50%;" href="javascript:void(0);" class="status-no"><b
                                            class="status-green">@if(empty($user->subject_3))
                                            未开始    @else {{ $user->subject_3 }}分钟 @endif</b></a>
                            </li>
                            <li class="status-6">
                                <span style="padding-left: 51%;">科目四考试</span>
                                <a style="padding-left: 60%;" href="javascript:void(0);"
                                   class="status-no"><b class="status-green">@if(empty($user->subject_4))
                                            未开始    @else {{ $user->subject_4 }}分钟 @endif</b></a>
                            </li>
                            <li class="status-7">
                                <span style="padding-left: 58%;">我的驾照</span>
                                <a style="padding-left: 58%;" href="javascript:void(0);"></a>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
            <!-- Inline list -->
            <div class="panel panel-white">
                <div class="panel-heading">
                    <h6 class="panel-title"><i class="icon-bookmark4 position-left"></i>阶段节点</h6>
                </div>

                <div class="panel-body">
                    <div class="tale-main">
                        @if($stages)
                            <ul class="submain_up_title" style="margin-bottom: 0;padding-left: 0;">
                                @foreach($stages as $key=>$stage)
                                    @if($key<1)
                                        <li class="personal_on">{{ $stage['name'] }}</li>
                                    @else
                                        <li>{{ $stage['name'] }}</li>
                                    @endif
                                @endforeach
                            </ul>
                            <div class="tab-content">
                                @foreach($stages as $key=>$stage)
                                    <table width="98%" border="" cellspacing="0" cellpadding="0"
                                           class="submain_main_tab"
                                           @unless($key<1)
                                           style="display: none;"
                                            @endunless>
                                        <tr>
                                            <th width="10%">序号</th>
                                            <th width="64%">节点</th>
                                            <th width="20%">时间</th>
                                        </tr>
                                        @forelse($stage as $node_name=>$node)
                                            @if(is_numeric($node_name))
                                                <tr>
                                                    <td>{{ $node_name }}</td>
                                                    <td>{{ $node->content }}</td>
                                                    <td>{{ $node->created_at }}</td>
                                                </tr>
                                            @endif
                                        @empty
                                        @endforelse
                                    </table>
                                @endforeach
                            </div>
                        @else
                            暂无数据
                        @endif
                    </div>

                </div>
            </div>
            <!-- /inline list -->
            @endif
        </div>
        </div>

    <div style="display:none" id='alertmsg'>
        @include("home.student-alert-msg")
    </div>
    <script>
        $(function () {
            $(".submain_up_title>li").on("click", function () {
                var i = $(this).index();
                $(this).addClass("personal_on").siblings().removeClass("personal_on");
                $(".submain_main_tab").eq(i).show().siblings().hide();
            });
            $(".modify-btn").click(function () {
                layer.open({
//                    btn: ['保存', '取消'], //按钮
                    type: 1,
                    id: "form",
                    area: ['700px', 'auto'], //宽高
                    skin: 'layui-layer-demo', //样式类名
                    closeBtn: 1, //不显示关闭按钮
                    shift: 2,
                    title: "修改个人信息",
                    shadeClose: true, //开启遮罩关闭
                    content: $('#alertmsg').html(),
                    success: function () {
                        var $idCard = $('#form').find("#idCard");
                        var $email = $('#form').find("#email");

                        //foucs事件
                        $idCard.focus(function () {
                            $idCard.next("p").remove();
                        });
                        //foucs事件
                        $email.focus(function () {
                            $email.next("p").remove();
                        })
                    },
                    yes: function (index, layero) {
                        var $idCard = $('#form').find("#idCard");
                        var $email = $('#form').find("#email");
                        event.preventDefault();
                        var idTip = '<p style="color: red;width: 300px;margin-left: 131px;margin-top: 10px;">身份证号码格式有误!</p>';
                        var emailTip = '<p style="color: red;width: 300px;margin-left: 131px;margin-top: 10px;">邮箱格式有误!</p>';
                        //身份证正则表达式
                        var idReg = /^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/;
                        //邮箱正则表达式
                        var emailReg = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;


                        var idResult = idReg.test($idCard.val());
                        var emailResult = emailReg.test($email.val());
                        //确保提示信息不会被重复增加!
                        $idCard.next("p").remove();
                        $email.next("p").remove();

                        if (idResult == true && emailResult == true) {
                            return true;
                        } else if (idResult == false && emailResult == true) {
                            $(".idDiv").append(idTip);
                            event.preventDefault();
                        } else if (idResult == true && emailResult == false) {
                            $(".emailDiv").append(emailTip);
                            event.preventDefault();
                        } else if (idResult == false && emailResult == false) {
                            $(".emailDiv").append(emailTip);
                            $(".idDiv").append(idTip);
                            event.preventDefault();
                        }
                        $.post('/home/user/update', $('form.wrap').serialize(), function (data) {
                            if (data.result == 'success') {
                                window.location.reload();
                                layer.closeAll();
                                layer.msg('保存成功:' + data.err_message, {time: 2000, icon: 6});
                            } else {
                                layer.msg('保存失败:' + data.err_message, {time: 2000, icon: 5});
                            }
                        });
                        layer.closeAll(); //如果设定了yes回调，需进行手工关闭
                    }
                });
            });
        })
    </script>
@endsection
