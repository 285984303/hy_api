@extends('home.base')

@section('title','预约学车')

@section('html_head')
	{!! HTML::style('css/student-mark-coah.css') !!}
	{!! HTML::style('css/admin_style.css') !!}
	{!! HTML::style('css/administration/admin-coach-schedule.css') !!}
@endsection


@section('content')
	<div class="content">
		<div class="panel panel-flat">
			<div class="col-md-12" style="padding: 0px;margin: 0px;margin-bottom: 10px">
				<form action="?" method="get">
					<div class="col-md-3" style="padding: 0px;margin: 0px">
						<div class="input-group col-lg-10">
							<span class="input-group-addon" id="sizing-addon2">预约日期</span>
							<input type="text" name="date" value="{{ $options['date'] }}" id="start-date"
								   class="input-time layui-icon form-control" placeholder="请选择日期" aria-describedby="sizing-addon2">
						</div>
					</div>
					<div class="col-md-3">
						<div class="input-group col-lg-10">
							<span class="input-group-addon" id="sizing-addon2">预约科目</span>
							<select
									name="subject" id="training_model">
								<option value="" @if($options['subject']=='') selected @endif>
									全部科目
								</option>
								<option value="2" @if($options['subject']==2) selected @endif>
									科目二
								</option>
								<option value="3"  @if($options['subject']==3) selected @endif>
									科目三
								</option>
							</select>
						</div>
					</div>
					<div class="col-md-3">
						<div class="input-group col-lg-10">
							<span class="input-group-addon" id="sizing-addon2">课时状态</span>
							<select name="namestatus">
								<option value="" @if($options['namestatus']=='') selected @endif>
									全部
								</option>
								<option value="TAKEN"  @if($options['namestatus']=='TAKEN') selected @endif>
									待开始
								</option>
								<option value="DONE" @if($options['namestatus']=='DONE') selected @endif>
									已完成
								</option>
								<option value="CANCELED" @if($options['namestatus']=='CANCELED') selected @endif>
									已取消
								</option>
								<option value="BROKEN" @if($options['namestatus']=='BROKEN') selected @endif>
									已违约
								</option>
							</select>
						</div>
					</div>
					<div class="col-md-1" style="text-align: left">
						<input type="submit" class="searchBtn font16" value="查找" style="border: none;line-height: 30px"/>
						{{--<button class="searchBtn font16">查找</button>--}}
					</div>
				</form>
			</div>
			<div class="table_w coach-schedule-content">
				<table>
					<thead class="thead">
					<tr class="hourTr" user_id="">
						<th>预约教练</th>
						<th>车牌号码</th>
						<th>预约科目</th>
						<th>训练日期</th>
						<th>训练时段</th>
						<th style="width: 160px">签到时间</th>
						<th style="width: 160px">签退时间</th>
						<th>预约人员</th>
						<th>课时状态</th>
					</tr>
					</thead>
					<tbody>
					@foreach($listinfo as $v)
						<tr id="coachTime49095">
							<td style="height: 40px;">{{ $v->admin->admin_name }}</td>
							<td>{{ $v->vehicle->car_num }}</td>
							<td>{{ $v->subject }}</td>
							<td>{{ $v->date }}</td>
							<td>{{ $v->training_time }}</td>
							<td style="width: 160px">{{ $v->sign_in_time }}</td>
							<td style="width: 160px">{{ $v->sign_out_time }}</td>
							<td>{{session('username')}}</td>
							<td>
								@if($v->status=='BROKEN')
									<label style="color:red;font-size: 12px">已违约</label>
								@elseif($v->status=='TAKEN')
									<label style="color:#96ef77;font-size: 12px">待开始</label>
								@elseif($v->status=='DONE')
									<label style="color:#00aa00;font-size: 12px">已完成</label>
								@elseif($v->status=='CANCELED')
									<label style="color:#c5573a;font-size: 12px">已取消</label>
									@endif
							</td>
						</tr>
					@endforeach

					</tbody>
				</table>
			</div>
			@include('home.page',['paginator'=>$listinfo])
		</div>

	</div>
@endsection




