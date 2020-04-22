<?php

namespace App\Http\Controllers\Api\Article;

use App\Models\Appointment\Appointment;
use App\Models\Vehicle\Vehicle;
use App\Models\Vehicle\VehicleType;
use Illuminate\Http\Request;
use App\Models\Admin\CoachStatistics;
use App\Models\Data\Statistics;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class CourseController extends BaseController
{
    private $_school_id;
    private $_admin_id;
    /*
     * @Des：析构函数
     * */
    public function __construct()
    {
        parent::__construct();
        $parms = array(
            'openid' => $this->_openid,
            'token' => $this->_token,
            'admin_id' => session('admin_id')
        );
        if (!$this->checkIsLogin($parms)) {
            echo $this->notLoginInfo();
            exit;
        }
        //接收参数检测
        $info = $this->checkParms(request()->all());
        if ($info) {
            echo $this->notPassParms($info);
            exit;
        }
        $this->_admin_id = session('admin_id');
        $this->_school_id = session('school_id');
    }

    /*
     * @Des:  教练扫码签到签退
     * @Parms: $classid 课时id
     * */
    public function scancode(){
        try {
            $classid = request('classid');
            $termphone = request('termphone');
            if(!$classid){
                throw new \Exception('课程数据异常无法扫码!');
            }
            if(!$termphone){
                throw new \Exception('设备数据异常无法扫码!');
            }
            $info = Appointment::find($classid);
            if(!$info){
                //记录数据库日志 暂时省略
                throw new \Exception('不存在此课程数据扫码异常!');
            }
            if($info->admin_id !=$this->_admin_id){
                throw new \Exception('此课程不属于您的预约课程无法扫码!');
            }
            $classendtime = strtotime($info->date ." ". $info->finish_time) + 3600; //课时结束2小时候不允许扫码
            if($classendtime < time()){
                throw new \Exception('课时已结束无法扫码!');
            }
            //生成队列消息
            $data = array(
                'classid' => $classid,
                'termphone' => $termphone,
                'type'    => 2,
                'logintype'=> request('logintype'),
            );
            $sendinfo = json_encode($data);
            $hmkey = "COACHLOGIN_".$termphone."_".request('logintype')."_".$classid;
            $result = Redis::HMSET($hmkey,$data);
            Redis::expire($hmkey,7200); //设置过期时间
            if(!$result){
                throw new \Exception('异常错误!');
            }
            Redis::Lpush("USERNEEDSENDLIST",$sendinfo);

            return response()->json(['result' => 'success', 'code' => 200]);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail', 'code' => $e->getCode(), 'msg' => $e->getMessage()]);
        }
    }

    /*
     * @Des:  教练预约课程信息
     * @Parms:
     * */
    public function coachappointment(){
        $nowdate = date("Y-m-d"); //当前日期
        $nextdate = date("Y-m-d",strtotime("+1 day")); //下一天
        $enddate = date("Y-m-d",strtotime("+2 day")); //结束日期
        $options = array(
            'startdate' =>$nowdate,
            'enddate'   =>$enddate
        );
        $listinfo = Appointment::getAppointmentInfo($this->_admin_id,$options);
        $Response = array();
        $arrdate = array($nowdate,$nextdate,$enddate);
        foreach ($arrdate as $k=>$v){
            $Response[$k]['date'] = $v;
            $Response[$k]['course_list'] = array();
            foreach ($listinfo as $k2=>$v2){
                if($v2->date == $v){
                    if($v2->status == 'ABLE' && $v2->is_valid == 'F'){
                        $status = 'NOOPEN';
                    }else{
                        $status = $v2->status;
                    }
                    //是否有预约
                    $name = '';
                    $phone='';
                    $state='';
                    if($v2->user_id){
                        $phone = $v2->user->user_telphone;
                        if($status == 'BROKEN'){
                            $name = $v2->user->user_truename;
                            $state = "违约";
//                            $content = $v2->user->user_truename."--".$v2->user->user_telphone."[违约]";
                        }else{
                            $name = $v2->user->user_truename;
//                            $content = $v2->user->user_truename."--".$v2->user->user_telphone;
                        }

                    }else{
                        if($status == 'NOOPEN'){
                            $state  = "未放课";
                        }else if($status == 'DISABLE'){
                            $state  = "禁止预约";
                        }else{
                            $state  = "无预约";
                        }
                    }

                    $Response[$k]['course_list'][] = array(
                        'id'=>$v2->id,
                        'status'=>$status,
//                        'content'=>$content,
                        'state' => $state,
                        'name'  => $name,
//                        'phone' => $phone,
                        'phone' => substr($phone,0,1)."*******".substr($phone,-3),
                        'time'=>date("H:i",strtotime($v2->start_time))."-".date("H:i",strtotime($v2->finish_time)),
                        'carnum' => $v2->vehicle->car_num,
                        'ischange' => $v2->is_change
                    );
                }
            }
        }
        return response()->json(['result' => 'success', 'code' => 200,'data'=>$Response]);
    }
    /*
     * @Des:预约列表
     * @Request URL: 域名/api/small/appointmentlist
     * @Request Method:POST
     * @Parms:$date    可选 默认为全部日期
     * */
    public function appointmentlist()
    {
        
        try {
            $cartypelist = array(
                '1' => '旗舰版',
                '2' => '智能版',
                '3' => '普通版',
                '4' => '模拟器',
            );
            $data = request()->all();
            /*if($data['coach']==0 || $data['coach'] ==''){
                $coach_id = '';
            }*/
            $options = array(
                'date' => request('date') ? request('date') : date("Y-m-d"), //默认当天
                'coach' => $data['coach']==0?'':$data['coach'],
                'subject' => $data['subject'] ? $data['subject'] : 2, //默认科目二
                'school_id' => $this->_school_id
            );
            
            $Response = array();
            //$limitdate = $this->setappointtime();
            //if ($options['date'] >= $limitdate) { //只能选择大于等于当前日期的预约课程列表
                /*if (date("H:i", time()) >= '21:00') {
                    $nextdate = date("Y-m-d", strtotime("+1 day"));
                    if($options['date'] == $nextdate){
                        return response()->json(['result' => 'success', 'code' => 200, 'data' => $Response]);
                    }
                }*/
                //获取学员可预约车辆列表
                $carinfo = $this->getAppointCarList($options['subject']);
                if ($carinfo) {
                    $options['carlist'] = $carinfo;
                }


                //取得可预约教练组 没有分组默认是全部教练 -1 是没有分组
                /* if($this->_coach_group !=-1){
                    $admins = Admin::getCoachGroupList($this->_school_id,$this->_coach_group);
                    $options['coach_group_admins'] = $admins;
                }*/
//                $options['coach_group_admins'] = $options['coach'];

                $listinfo = Appointment::getCoachClass(options_filter($options), array('id', 'admin_id', 'vehicle_id', 'course_id'));
//                exit;
                //组装返回页面数据
//                $Cache = array();
//                $Cache_Key = 'COACH_INFO_' . $options['subject'] . "_" . $this->_user_id . "_" . $options['date'];
                //取得课时时段费用
//                $coursecost = $this->getPackagesTimeCharge();
                foreach ($listinfo as $k => $v) {
                    $Cache[$k]['id'] = $v->admin->id;
                    $Cache[$k]['coach_name'] = $v->admin->admin_name;
                    $Response[$k]['car_type'] = $v->vehicle->vehicle_type->name;
                    $Response[$k]['type'] = $cartypelist[$v->vehicle->vehicle_type->vehicle_version_id];
                    $Response[$k]['coach_name'] = $v->admin->admin_name;
                    $Response[$k]['coach_id'] = $v->admin->id;
                    $Response[$k]['license_num'] = $v->vehicle->car_num;
                    
                    $Response[$k]['course_list'] = array();
                    foreach ($v->appointments as $k2 => $v2) {
                        $Response[$k]['course_list'][$k2]['id'] = $v2->id;
                        $Response[$k]['course_list'][$k2]['time'] = date("H:i", strtotime($v2->start_time)) . "-" . date("H:i", strtotime($v2->finish_time));
                        /*if ($this->isHolidayinfo($options['date']) == 'holiday_fee'){
                            $Response[$k]['course_list'][$k2]['cost'] = $coursecost[$v->vehicle->vehicle_type->id][$v2->course_id]['holiday_price'];
                        } else {
                            $Response[$k]['course_list'][$k2]['cost'] = $coursecost[$v->vehicle->vehicle_type->id][$v2->course_id]['work_price'];
                        }*/

                        if($v2->is_valid !='T'){
                            unset($Response[$k]['course_list'][$k2]);
                        }
                    }
                    $Response[$k]['free_course_num'] = count($Response[$k]['course_list']);
                    //清除0节课的教练
                    if ($Response[$k]['free_course_num'] == 0) {
                        unset($Response[$k]);
                    }
                }
                /*if (!$options['coach']) { //不存在教练ID时 写入缓存
                    Redis::setex($Cache_Key, 60, json_encode($Cache));
                }*/
           // }
            return response()->json(['result' => 'success', 'code' => 200, 'data' => $Response]);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail', 'code' => $e->getCode(), 'msg' => $e->getMessage()]);
        }

    }



    /*
     * @Des:  处理展示提示内容
     * @Parms: $array
     * */
    public function initStatusContent($arr= array()){

    }

    /*
     * @Des:时间点限制
     * */
    public function setappointtime()
    {
        //根据驾校业务 不同 设置不同的预约时间点  赞略...
        //如果时间点 大于晚上10点
        if (date("H:i", time()) >= '21:00') {
            $date = date("Y-m-d", strtotime("+2 day"));
        } else {
            $date = date("Y-m-d", strtotime("+1 day"));
        }
        return $date;
    }
    /*
     * @Des:获取教练
     * */
    public function getcoach()
    {
        try {
            $data = request()->all();
            $options = array(
                'date' => $data['date'] ? $data['date'] : date('Y-m-d'), //默认当天
                'subject' => $data['subject'] ? $data['subject'] : 2, //默认科目二
                'school_id' => $this->_school_id
            );
//            $Cache_Key = 'COACH_INFO_' . $options['subject'] . "_" . $this->_user_id . "_" . $options['date'];
//            $Response = json_decode(Redis::GET($Cache_Key), true);
//            if (!$Response) {
                //读取数据库
                /*$carinfo = $this->getAppointCarList($options['subject']);
                if ($carinfo) {
                    $options['carlist'] = $carinfo;
                }*/
                $listinfo = Appointment::getCoachClass(options_filter($options), array('id', 'admin_id', 'vehicle_id', 'course_id'));
                $Cache = array();
                foreach ($listinfo as $k => $v) {
                    $Cache[$k]['id'] = $v->admin->id;
                    $Cache[$k]['coach_name'] = $v->admin->admin_name;
                }
//                Redis::setex($Cache_Key, 600, json_encode($Cache));
                $Response = $Cache;
//            }
            return response()->json(['result' => 'success', 'code' => 200, 'data' => $Response]);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail', 'code' => $e->getCode(), 'msg' => $e->getMessage()]);
        }

    }

    public function getAppointCarList($subject)
    {

        //根据车型取得 可约车辆
        $carinfotype = VehicleType::where(array('subject_id' => $subject, 'school_id' => $this->_school_id))->get();
        foreach ($carinfotype as $k => $v) {
            $cartypes[] = $v->id;
        }
//        print_r($cartypes);
//        exit;
        $carinfo = Vehicle::whereIn('vehicle_type_id', $cartypes)->where('school_id', '=', $this->_school_id)->get();

//        print_r($carinfo->toArray());
        $carlist = array();
        foreach ($carinfo as $k => $v) {
            $carlist[] = $v->id;
        }



        //此处 有问题 需优化  暂时 先搁置
        /*if (!$carlist) {
            //读取对科目对应车型
            $carinfo = VehicleType::where(array('subject_id' => $subject, 'school_id' => $this->_school_id))->get();
            foreach ($carinfo as $k => $v) {
                $cartypes[] = $v->id;
            }
            $carinfo = Vehicle::whereIn('vehicle_type_id', $cartypes)->where('school_id', '=', $this->_school_id)->get();
            foreach ($carinfo as $k => $v) {
                $carlist[] = $v->id;
            }
        }*/
        return $carlist;
    }
    
    /**
     * 查看当前教练指定月份的教练学时统计
     * @Des:教练学时统计
     * */
    public function coachStatistics()
    {
        
        $options = [
            'admin_id' => $this->_admin_id,
            'school_id' => $this->_school_id,
//             'year' => request('year')?request('year'):intval(date("Y",time())),
//             'month' => request('month')?request('month'):intval(date("m",time())),
            'date' => request('date')?request('date'):date('Y-m'),
        ];
        //$date = $options['year']."-".$options['month']."-10";
        $date = $options['date']."-10";
        $limittime = strtotime($date);
        $year = date('Y',$limittime);
        $month = date('m',$limittime);
        $options['year'] = $year;
        $options['month'] = $month;
        
        $days = date('t', $limittime);
        for($i=1;$i<=$days;$i++){
            $dayslist[$i] = $i;
        }
        $listinfo = CoachStatistics::GetCoachStatistics(options_filter($options))->with(['admin'=>function($query){
            $query->select('id','admin_name','id_card');
        }])->with('statisticslist');
        //数据导出
//         if(request('doexport')==1){
//             $export =$listinfo->get();
//             $this->export_coach_statistics($export,$days,$limittime);
//         }
        $listinfo = $listinfo->paginate(10);
        foreach ($listinfo as $v)
        {
            unset($v->deleted_at);
            unset($v->created_at);
            unset($v->updated_at);
            $tmpnums =0;
            foreach ($v->statisticslist as $v2)
            {
                $tmpnums += $v2->classnums;
                unset($v2->created_at);
                unset($v2->updated_at);
                unset($v2->deleted_at);
            }
            $v->firstdate = date("Y-m",$limittime)."-01";
            $v->enddate = date("Y-m",$limittime)."-".$days;
            $v->allnums = $tmpnums;
        }
        $response = $listinfo->ToArray();
        unset($response['total']);
        $response = $response['data'];
        return response()->json(['result' => 'success', 'code' => 200, 'data' => $response]);
    }
    
    /**
     * 预约统计
     * 查看指定某天当前教练学员训练学时列表
     * @param unknown $type
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function appointments()
    {
        $options = [
            'admin_id' => $this->_admin_id,
            'date' => request('date') ? request('date') : date('Y-m-d'),
            'type' => request('appointment_type')?request('appointment_type'):1,

        ];
//        if ($type == 'export') {
//             $appointmentexport = Statistics::appointments(options_filter($options))->get();
//             $cell = [];
//             $cell[] = '学员名称';
//             $cell[] = '教练名称';
//             $cell[] = '车牌号码';
//             $cell[] = '培训课程';
//             $cell[] = '训练日期';
//             $cell[] = '开始时间';
//             $cell[] = '结束时间';
//             $cell[] = '训练时长';
//             $cell[] = '训练里程';
//             $cellData[] = $cell;
//             foreach ($appointmentexport as $item) {
//                 $min = $item->min ? $item->min : 0;
//                 $distance = $item->distance ? $item->distance : 0;
//                 $cell = [];
//                 $cell[] = $item->user->user_truename;
//                 $cell[] = $item->admin->admin_name;
//                 $cell[] = $item->vehicle->car_num;
//                 $cell[] = $item->type->name;
//                 $cell[] = $item->date;
//                 $cell[] = $item->sign_in_time;
//                 $cell[] = $item->sign_out_time;
//                 $cell[] = $min . "Min";
//                 $cell[] = $distance . "Km";
//                 $cellData[] = $cell;
//             }
//             \Excel::create('训练情况数据', function ($excel) use ($cellData) {
//                 /** @var \Maatwebsite\Excel\Writers\LaravelExcelWriter $excel */
//                 $excel->sheet('训练情况数据', function ($sheet) use ($cellData) {
//                     /** @var \Maatwebsite\Excel\Classes\LaravelExcelWorksheet $sheet */
//                     $sheet->rows($cellData);
//                     $sheet->setAutoSize(true);
//                 });
//             })->export('xls');
//        }
        if($options['type']==1)
        {
            $appointments = Statistics::appointments(options_filter($options))->paginate();
        }
        else 
        {
            $appointments = Statistics::appointments_no(options_filter($options))->paginate();
        }

        $cellData = [];
        foreach ($appointments as $item) {
            $min = $item->min ? $item->min : 0;
            $distance = $item->distance ? $item->distance : 0;
            $cell = [];
            $cell['id'] = $item->id;
            $cell['user_truename'] = $item->user->user_truename;
            //$cell['admin_name'] = $item->admin->admin_name;
            $cell['car_num'] = $item->vehicle->car_num;
            //$cell[] = $item->type->name;
            $cell['date'] = $item->date;
            //$cell['start_time'] = substr($item->start_time,0,strlen($item->start_time)-3);
            //$cell['finish_time'] = substr($item->finish_time,0,strlen($item->finish_time)-3);
            $cell['time'] = substr($item->start_time,0,strlen($item->start_time)-3).'-'.substr($item->finish_time,0,strlen($item->finish_time)-3);;
            $cell['sign_in_time'] = $item->sign_in_time;
            $cell['sign_out_time'] = $item->sign_out_time;
            //$cell['domin'] = $min . "Min";
            //$cell['distance'] = $distance . "Km";
            $cellData[] = $cell;
        }
        //$response = $appointments->ToArray();
        
        return response()->json(['result' => 'success', 'code' => 200, 'data' => $cellData]);
    }


    /*
     * @Des:检测token
     * */
    public function checktoken()
    {
        return response()->json(['result' => 'success', 'code' => 200]);
    }
}
