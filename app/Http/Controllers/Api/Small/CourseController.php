<?php

namespace App\Http\Controllers\Api\Small;

use App\Library\Holiday;
use App\Models\Admin\Admin;
use App\Models\Admin\CoachReview;
use App\Models\Appointment\Appointment;
use App\Models\Cost\CostArrearage;
use App\Models\Course\CourseNodeSet;
use App\Models\Packages\PackagesTimeCharge;
use App\Models\Student\Examination;
use App\Models\Vehicle\Vehicle;
use App\Models\Vehicle\VehicleType;
use App\Models\Vehicle\VehicleTypeTimeCost;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Models\Data\LicenceType;
use App\Models\Home\User;
use App\Models\Business\UserProduct;
use App\Models\Exam\ExaminationRrecord;
/**
 * 学员预约
 * @author Ricky
 *
 */
class CourseController extends BaseController
{


    private $_school_id;
    private $_user_id;
    private $_packages_id;
    private $_setcost;
    private $_recedata;
    private $_username;
    private $_coach_group;

    /*
     * @Des：析构函数
     * */
    public function __construct()
    {
        parent::__construct();
        $this->_recedata = request()->all();
        $parms = array(
           'openid' => $this->_openid,
           'token' => $this->_token,
           //'user_id' => session('user_id')
            'user_id' => $this->_userid
        );

        if (!$this->checkIsLogin($parms)) {
            echo $this->notLoginInfo();
            exit;
        }
        //接收参数检测
        $info = $this->checkParms($this->_recedata);
        if ($info) {
            echo $this->notPassParms($info);
            exit;
        }
        $this->_packages_id = session('packages_id');
        $this->_setcost = session('setcost');
        $this->_user_id = session('user_id');
        $this->_school_id = session('school_id');
        $this->_username = session('username');
        $this->_coach_group = session('coach_group');
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
            $data = $this->_recedata;
            /*if($data['coach']==0 || $data['coach'] ==''){
                $coach_id = '';
            }*/
            $options = array(
                'date' => request('date') ? request('date') : date("Y-m-d", strtotime("+1 day")), //默认当天
                'coach' => request('coach')==0?'':$data['coach'],
                'subject' => $data['subject'] ? $data['subject'] : 2, //默认科目二
                'school_id' => $this->_school_id
            );
            //echo session('user_id');exit;
            //$user = User::where('id', '=', session('user_id'))->select(array('id','school_id','licence_type_id'))->first();
            $user_pro = UserProduct::where('user_id','=',session('user_id'))->select(['old_licence_type'])->first();
            //var_dump($user_pro->old_licence_type);
            
            $Response = array();
            $limitdate = $this->setappointtime();
            $options['date'] = date('Y-m-d',strtotime($options['date']));
            Log::info($this->_user_id.'报的是'.$user_pro->old_licence_type."选择日期".$options['date']." 预约日期$limitdate");
            if ($options['date'] >= $limitdate) //只能选择大于等于当前日期的预约课程列表
            { 
                if (date("H:i", time()) >= '21:00') {
                    $nextdate = date("Y-m-d", strtotime("+1 day"));
                    if($options['date'] == $nextdate)
                    {
                        Log::info($this->_user_id.'报的是'.$user_pro->old_licence_type."选择日期超时".$options['date']);
                        return response()->json(['result' => 'success', 'code' => 200, 'data' => $Response,'date'=>$nextdate]);
                    }
                }
                //获取学员可预约车辆列表
                $carinfo = $this->getAppointCarList($options['subject']);
                if(empty($carinfo))
                {
                    Log::info($this->_user_id."没有获取到可预约车辆");
                }
                if ($carinfo) {
                    $options['carlist'] = $carinfo;
                }
                //取得可预约教练组 没有分组默认是全部教练 -1 是没有分组
                if($this->_coach_group !=-1){
                    $admins = Admin::getCoachGroupList($this->_school_id,$this->_coach_group);
                    $options['coach_group_admins'] = $admins;
                    if(empty($admins))
                    {
                        Log::info($this->_user_id."没有获取到可预约教练组");
                    }
                }
                //取到all驾照类型
                $license_list = LicenceType::all(['id','name'])->toArray();
                $licen_arr = [];
                foreach ($license_list as $item=>$val)
                {
                    //$info[]= $val['name'];
                    $licen_arr[$val['id']] = $val;
                }
                
                //var_dump($licen_arr);exit;
                
                $listinfo = Appointment::getCoachClass(options_filter($options), array('id', 'admin_id', 'vehicle_id', 'course_id'));
                if(empty($listinfo))
                {
                    Log::info($this->_user_id."没有获取到教练信息");
                }
                //组装返回页面数据
                $Cache = array();
                $Cache_Key = 'COACH_INFO_' . $options['subject'] . "_" . $this->_user_id . "_" . $options['date'];
                //取得课时时段费用
                $coursecost = $this->getPackagesTimeCharge();
                foreach ($listinfo as $k => $v) 
                {
                    if($user_pro->old_licence_type != $licen_arr[$v->vehicle->licence_type_id]['name'])
                    {
                        //echo $user->licence_type_id;
                        Log::info($this->_user_id.'驾照类型不一致 报的是'.$user_pro->old_licence_type.' 车='.$licen_arr[$v->vehicle->licence_type_id]['name']);
                        continue;
                    }
                    else 
                    {
                        Log::info($this->_user_id.'驾照类型一致 报的是'.$user_pro->old_licence_type.' 车='.$licen_arr[$v->vehicle->licence_type_id]['name']);
                    }
                    $Cache[$k]['id'] = $v->admin->id;
                    $Cache[$k]['coach_name'] = $v->admin->admin_name;
                    $Response[$k]['car_type'] = $v->vehicle->vehicle_type->name;
                    $Response[$k]['type'] = $cartypelist[$v->vehicle->vehicle_type->vehicle_version_id];
                    $Response[$k]['coach_name'] = $v->admin->admin_name;
                    $Response[$k]['license_num'] = $v->vehicle->car_num;
                    $Response[$k]['license_type_name'] = $licen_arr[$v->vehicle->licence_type_id]['name'];
                    $Response[$k]['course_list'] = array();
                    
                    foreach ($v->appointments as $k2 => $v2) 
                    {
                        
                        $Response[$k]['course_list'][$k2]['id'] = $v2->id;
                        $Response[$k]['course_list'][$k2]['time'] = date("H:i", strtotime($v2->start_time)) . "-" . date("H:i", strtotime($v2->finish_time));
                        if ($this->isHolidayinfo($options['date']) == 'holiday_fee'){
                            $Response[$k]['course_list'][$k2]['cost'] = $coursecost[$v->vehicle->vehicle_type->id][$v2->course_id]['holiday_price'];
                        } else {
                            $Response[$k]['course_list'][$k2]['cost'] = isset($coursecost[$v->vehicle->vehicle_type->id][$v2->course_id]['work_price'])?$coursecost[$v->vehicle->vehicle_type->id][$v2->course_id]['work_price']:'';
                        }

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
                
                if (!$options['coach']) { //不存在教练ID时 写入缓存
                    Redis::setex($Cache_Key, 60, json_encode($Cache));
                }
                
            }
            else 
            {
                Log::info($this->_user_id."选择日期超时了=".$options['date']);
            }
            
            return response()->json(['result' => 'success', 'code' => 200, 'data' => $Response]);
        } catch (\Exception $e) 
        {
            Log::info($this->_user_id."执行异常=".'result fail code' . $e->getCode(). 'msg' . $e->getMessage().'line'.$e->getLine());
            return response()->json(['result' => 'fail', 'code' => $e->getCode(), 'msg' => $e->getMessage(),'line'=>$e->getLine()]);
        }

    }


    /*
     * @Des:预约课程操作
     * @Request URL: 域名/api/small/doappointment
     * @Request Method:POST
     * @Parms:$ids   必填 预约课程ID 数据形式
     * */

    public function doappointment()
    {
        //是否欠费 判断
        \DB::beginTransaction();
        try {
            $date = request('date');
            if(session('class_auto')!=1){
                throw new \Exception('您所报的课程无法自主预约,如有疑问请联系招生教练!');
            }
            if(!$date){
                throw new \Exception('数据异常无法预约!');
            }
            //判断传过来的参数数量
            $parmscount = count($this->_recedata['ids']);
            if($parmscount > Appointment::LIMITNUMS){
                throw new \Exception('一天最多只能预约'.Appointment::LIMITNUMS.'节课,请重新选择!');
            }
            //判断科目考试通过情况 科目一通过才能预约
            /*if (!$this->checkExamination(1)) {
                throw new \Exception('系统没有检测到您科目一考试通过成绩，无法预约！');
            }*/
            //判断欠费
            if (!$this->checkIsArrears()) {
                throw new \Exception('检测到您存在未缴清的费用无法预约，请联系管理员解决！');
            }
            //判断预约总数
            $appointinfo = Appointment::getAllAppointments($this->_user_id, $date);
            if (!$appointinfo) {
                throw new \Exception('您'.$date.'最多只可以预约' . Appointment::LIMITNUMS . '节课时,您已经预约了'.Appointment::LIMITNUMS.'节课时!');
            }
            //判断今天还剩 还剩几节课 可以预约
            $hasnums = Appointment::LIMITNUMS - $appointinfo['nums'];
            if ($hasnums < $parmscount) {
                throw new \Exception('今天最多还可以预约' . $hasnums . "节课时,请重新选择！");
            }
            //科23合格不能预约
            $user_cord = ExaminationRrecord::where('user_id','=',session('user_id'))->where('score_2','=',2)->where('score_3','=',2)->select(['user_id'])->first();
            if($user_cord)
            {
                throw new \Exception('学员训练已结束!');
            }
            //9点后不允许预约第二天课程过滤
            /*if (date("H:i", time()) >= '21:00') {
                $nextdate = date("Y-m-d", strtotime("+1 day"));
                if($date == $nextdate){
                    throw new \Exception('21点之后不允许预约第二天课程，请您重新选择预约时间！');
                }
            }else{
                if($date == date("Y-m-d")){
                    throw new \Exception('您预约的课程已经超出预约时间！');
                }
            }*/

            //生成预约信息
            $tmpclass = array();
            foreach ($this->_recedata['ids'] as $v) {
                $map = array(
                    'id' => $v,
                    'user_id' => $this->_user_id,
                    'type_id' => $this->_recedata['subject'] ? $this->_recedata['subject'] : 2,
                    'appointment_type' => request('appointment_type')?request('appointment_type'):2
                );
                $backinfo = Appointment::setAppointInfo($map,$this->_username);
                $tmpclass[$this->_user_id][] = $backinfo;
            }
            //校验今日预约总数
            $where = array(
                'date'=>$date,
                'user_id' =>$this->_user_id,
                'status'  => 'TAKEN',

            );
            $appointcount = Appointment::where($where)->count();
            if($appointcount>Appointment::LIMITNUMS){
                throw new \Exception('您一天最多只能预约'.Appointment::LIMITNUMS.'节课!');
            }
            //发送通知消息
            //$tmp = json_encode($tmpclass[$this->_user_id]);
            $tmp = array(
                'phone' =>session('phone'),
                'data' => $tmpclass[$this->_user_id],
                'msgtype' => 1
            );
            Redis::Lpush("MSGLIST",json_encode($tmp));
            unset($tmp);
            //.....
            \DB::commit();
            return response()->json(['result' => 'success', 'code' => 200]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['result' => 'fail', 'code' => $e->getCode(), 'msg' => $e->getMessage()]);
        }
    }

    /*
     * @Des:取消预约信息列表
     * @Request URL: 域名/api/small/cancleappointmentlist
     * @Request Method:GET
     * */
    public function cancleappointmentlist()
    {
        try {
            $listinfo = Appointment::getAppointments($this->_user_id)->get();
            $Response = array();
            foreach ($listinfo as $k => $v) {
                $Response[$k]['id'] = $v->id;
                $Response[$k]['coach_name'] = $v->admin->admin_name;
                $Response[$k]['license_num'] = $v->vehicle->car_num;
                $Response[$k]['training_time'] = $v->date . " " . date("H:i", strtotime($v->start_time)) . "-" . date("H:i", strtotime($v->finish_time));
            }
            return response()->json(['result' => 'success', 'code' => 200, 'data' => $Response]);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail', 'code' => $e->getCode(), 'msg' => $e->getMessage()]);
        }
    }

    /*
     * @Des:  执行取消操作
     * @Request URL: 域名/api/small/cancle?id=
     * @Request Method:GET
     * @Parms:$id 必选 课程ID
     * */
    public function docancle()
    {
        /*\DB::beginTransaction();*/
        try {
//            sleep(2);
            $id = $this->_recedata['id'];
            if (!$id) {
                throw new \Exception('数据异常无法取消!');
            }
            $info = Appointment::find($id);
            if (!$info) {
                throw new \Exception('数据异常无法取消!');
            }
            if ($info->user_id != $this->_user_id) {
                throw new \Exception('课程数据异常无法取消!');
            }
            if ($info->status != 'TAKEN') {
                throw new \Exception('此课程为不可取消状态!');
            }
            if (date("H:i", time()) >= '17:00') {
                $nextdate = date("Y-m-d", strtotime("+1 day"));
                if($info->date == $nextdate){
                    throw new \Exception('5点之后不允许取消第二天课程！');
                }
            }else{
                if($info->date == date("Y-m-d")){
                    throw new \Exception('取消时间已过无法取消此课程!');
                }
            }
            /*$starttime = strtotime($info->date . " " . $info->start_time);
            if(date("H:i",time())>"17:00"){
                $nowtime = time() + 3600*31;
            }else{
                $nowtime = time() + 3600*7;
            }
            if ($nowtime > $starttime) {
                throw new \Exception('取消时间已过无法取消此课程!');
            }*/
            $info->status = Appointment::STATUS_CANCELED;
            $info->handle_admin_id = $this->_user_id;
            $info->cancel_time = date("Y-m-d H:i:s");
            $info->cancelname = $this->_username;
            $info->save();
            //搜索此教练此课时数据是否存在
            $where = array(
                'admin_id' =>$info->admin_id,
                'course_id' => $info->course_id,
                'status' => Appointment::STATUS_ABLE,
                'date' => $info->date
            );
            $countcourse = Appointment::where($where)->count();
            if(!$countcourse){
                //appointment表需要补充的数据
                $map['status'] = Appointment::STATUS_ABLE;
                $map['school_id'] = $info->school_id;
                $map['date'] = $info->date;
                $map['start_time'] = $info->start_time;
                $map['finish_time'] = $info->finish_time;
                $map['admin_id'] = $info->admin_id;
                $map['vehicle_id'] = $info->vehicle_id;
                $map['is_valid'] = Appointment::VALID;
                $map['course_id'] = $info->course_id;
                Appointment::insert($map);
            }
            /*\DB::commit();*/
            return response()->json(['result' => 'success', 'code' => 200]);
        } catch (\Exception $e) {
           /* \DB::rollBack();*/
            return response()->json(['result' => 'fail', 'code' => $e->getCode(), 'msg' => $e->getMessage()]);
        }
    }

    /*
     * @Des:预约日志-课程管理
     * @Request URL: 域名/api/small/appointmentrecord
     * @Request Method:GET
     * @Parms:$date    可选 默认为当前日期
     * */
    public function appointmentrecord()
    {
        try {
            $listinfo = Appointment::getAppointments($this->_user_id, 'NOTCANCLED')->paginate(10);
            $pageinfo = $listinfo->toArray();
            $coursecost = $this->getPackagesTimeCharge();
            $Response = array();
            foreach ($listinfo as $k => $v) {
                if (in_array($v->type_id, [1, 2, 3])) {
                    $Response[$k]['subject'] = "科目二";
                } else {
                    $Response[$k]['subject'] = "科目三";
                }
                $Response[$k]['id'] = $v->id;
                $Response[$k]['coach_name'] = $v->admin->admin_name;
                $Response[$k]['license_num'] = $v->vehicle->car_num;
                $Response[$k]['training_time'] = $v->date . " " . date("H:i", strtotime($v->start_time)) . "-" . date("H:i", strtotime($v->finish_time));
                $Response[$k]['name'] = $this->_username;
                $Response[$k]['status'] = $v->status;
                if ($this->isHolidayinfo($v->date) == 'holiday_fee'){
//                    $Response[$k]['cost'] = $v->vehicle->vehicle_type_id."==".$v->course_id;
                    $Response[$k]['cost'] =  $coursecost[$v->vehicle->vehicle_type_id][$v->course_id]['holiday_price'];
                } else {
                    $Response[$k]['cost'] = $coursecost[$v->vehicle->vehicle_type_id][$v->course_id]['work_price'];
                }
                $starttime = strtotime($v->date . " " . date("H:i", strtotime($v->start_time)));
                $endtime = strtotime($v->date . " " . date("H:i", strtotime($v->finish_time)));
                $sign_in_time = $v->sign_in_time;
                $sign_out_time = $v->sign_out_time;
                if(($starttime < time()) && (time()<$endtime)){
                    if(!$sign_in_time){
                        $Response[$k]['status'] = 'NOTSIGNIN';
                    }
                }
                $lastlimittime = time()-180; //延后3分钟时间仍未签退 提示课时未签退
                if($Response[$k]['status'] == 'DOING'){
                    if($lastlimittime > $endtime){
                        if(!$sign_out_time){
                            $Response[$k]['status'] = 'NOTSIGNOUT';
                        }
                    }
                }
            }
            return response()->json(['result' => 'success', 'totalpage' => $pageinfo['last_page'], 'page' => $pageinfo['current_page'], 'code' => 200, 'data' => $Response]);
//            return response()->json(['result' => 'success', 'code' => 200, 'data' => $Response]);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail', 'code' => $e->getCode(), 'msg' => $e->getMessage()]);
        }
    }

    /*
     * @Des:预约日志-已取消课程
     * @Request URL: 域名/api/small/appointmentrecord
     * @Request Method:GET
     * @Parms:$date    可选 默认为当前日期
     * @Response: Json
     * */
    public function cancledappointmentlist()
    {

        try {
            $listinfo = Appointment::getAppointments($this->_user_id, 'CANCLED')->paginate(10);
            $pageinfo = $listinfo->toArray();
//            $listinfo = Appointment::getAppointments($this->_user_id, 'CANCLED')->get();
            $Response = array();
            foreach ($listinfo as $k => $v) {
                if (in_array($v->type_id, [1, 2, 3])) {
                    $Response[$k]['subject'] = "科目二";
                } else {
                    $Response[$k]['subject'] = "科目三";
                }
                $Response[$k]['id'] = $v->id;
                $Response[$k]['coach_name'] = $v->admin->admin_name;
                $Response[$k]['license_num'] = $v->vehicle->car_num;
                $Response[$k]['training_time'] = $v->date . " " . date("H:i", strtotime($v->start_time)) . "-" . date("H:i", strtotime($v->finish_time));
                $Response[$k]['name'] = $this->_username;
                $Response[$k]['cancle_name'] = $this->_username;
                $Response[$k]['cancle_time'] = $v->cancel_time;
                $Response[$k]['status'] = $v->status;
            }
            return response()->json(['result' => 'success', 'totalpage' => $pageinfo['last_page'], 'page' => $pageinfo['current_page'], 'code' => 200, 'data' => $Response]);
//            return response()->json(['result' => 'success', 'code' => 200, 'data' => $Response]);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail', 'code' => $e->getCode(), 'msg' => $e->getMessage()]);
        }
    }

    /*
     * @Des:日期对应可预约教练
     * @Request URL: 域名/api/small/cancle?id=
     * @Request Method:GET
     * @Parms: $date 可选 默认当天
     * @Response: Json
     * */
    public function getcoach()
    {
        try {
            $data = $this->_recedata;
            $options = array(
                'date' => $data['date'] ? $data['date'] : date('Y-m-d'), //默认当天
                'subject' => $data['subject'] ? $data['subject'] : 2, //默认科目二
                'school_id' => $this->_school_id
            );
            $Cache_Key = 'COACH_INFO_' . $options['subject'] . "_" . $this->_user_id . "_" . $options['date'];
            $Response = json_decode(Redis::GET($Cache_Key), true);
            if (!$Response) {
                //读取数据库
                $carinfo = $this->getAppointCarList($options['subject']);
                if ($carinfo) {
                    $options['carlist'] = $carinfo;
                }
                $listinfo = Appointment::getCoachClass(options_filter($options), array('id', 'admin_id', 'vehicle_id', 'course_id'));
                $Cache = array();
                foreach ($listinfo as $k => $v) {
                    $Cache[$k]['id'] = $v->admin->id;
                    $Cache[$k]['coach_name'] = $v->admin->admin_name;
                }
                Redis::setex($Cache_Key, 600, json_encode($Cache));
                $Response = $Cache;
            }
            return response()->json(['result' => 'success', 'code' => 200, 'data' => $Response,'msg'=>'getcoach日期对应可预约教练']);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail', 'code' => $e->getCode(), 'msg' => $e->getMessage()]);
        }

    }


    /*
     * @Des:获取可预约车辆
     * @Parms: $subject
     * @Response: array()  车辆ID 数组
     * */
    public function getAppointCarList($subject)
    {
        //学员已上课时数统计
        $count = Appointment::GetClassCount($this->_user_id, $subject);
        //检测用户套餐节点数据 取得节点车型
        $parms = array(
            'packages_id' => $this->_packages_id,
            'subject_id' => $subject,
            'start' => $count + 1
        );
        $info = CourseNodeSet::getNodeInfo($parms);

        if ($info) {
            $cartypes = explode(',', $info->cartype);
            //根据车型取得可预约车辆
        } else {
            //读取对科目对应车型
            $carinfo = VehicleType::where(array('subject_id' => $subject, 'school_id' => $this->_school_id))->get();
            foreach ($carinfo as $k => $v) {
                $cartypes[] = $v->id;
            }
        }
        //根据车型取得 可约车辆
        $carinfo = Vehicle::whereIn('vehicle_type_id', $cartypes)->where('licence_type_id','=',session('licencetype_id'))->where('school_id', '=', $this->_school_id)->get();
        $carlist = array();
        foreach ($carinfo as $k => $v) {
            $carlist[] = $v->id;
        }
        //此处 有问题 需优化  暂时 先搁置
        if (!$carlist) {
            //读取对科目对应车型
            $carinfo = VehicleType::where(array('subject_id' => $subject, 'school_id' => $this->_school_id))->get();
            foreach ($carinfo as $k => $v) {
                $cartypes[] = $v->id;
            }
            $carinfo = Vehicle::whereIn('vehicle_type_id', $cartypes)->where('licence_type_id','=',session('licencetype_id'))->where('school_id', '=', $this->_school_id)->get();
            foreach ($carinfo as $k => $v) {
                $carlist[] = $v->id;
            }
        }
        return $carlist;
    }

    /*
     * @Des:取得套餐车型不同时段费用
     * */
    public function getPackagesTimeCharge(){
        $Response = array();
        if($this->_setcost==1){
            $listinfo = VehicleTypeTimeCost::get();
        }else{
            $where = array(
                'packages_id' => $this->_packages_id
            );
            $listinfo = PackagesTimeCharge::GetChargePrice($where);
        }
        foreach ($listinfo as $v){
            $Response[$v->vehicle_type_id][$v->course_id]['holiday_price'] = $v->holiday_price;
            $Response[$v->vehicle_type_id][$v->course_id]['work_price'] = $v->work_price;
            /*if ($this->isHolidayinfo($date) == 'holiday_fee'){
                $Response[$v->vehicle_type_id][$v->course_id] = $v->holiday_price;
            } else {
                $Response[$v->vehicle_type_id][$v->course_id] = $v->work_price;
            }*/
        }
       return $Response;
    }

    /*
     * @Des:家假日判断
     * */
    public function isHolidayinfo($date){
        return Holiday::isHoliday(strtotime($date)) ? 'holiday_fee' : 'normal_fee';
    }

    /*
     * @Des:  学员扫码签到
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
            log::info("+++++:APPOINT:".$info->user_id);
            log::info("+++++:SESSION:".$this->_user_id);
            if($info->user_id !=$this->_user_id){
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
                'type'    => 1,
                'logintype'=> request('logintype'),
            );
            $sendinfo = json_encode($data);
            $hmkey = "USERLOGIN_".$termphone."_".request('logintype')."_".$classid;
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


    public function bustimelist(){

        $info = array(
            'head' => '宏强驾校训练场公交车时刻表（暂定）',
            'title' => array(
                'ride' => '乘车时间',
                'back' => '返程时间',
                'site' => '乘车地点',
                'hint' => '预约小提示'
            ),
            'title_text' =>'16点后可预约新的课程，17点后不能取消已预约第二天课程，21点后不能预约第二天课程.',
            'remark_text'=>'注：1.学员可以根据交通车运行时间，自主预约学习时段，并自觉遵守预约及交通车运行时间，不得迟到；2、学员乘坐交通车时，请主动出示学员证，否则不能乘坐；3、交通车在运行中（三皇宫华海职业学校门口、织金洞南三甲收费站路口）在不超员的情况下可以上下人员，其余路段不停车上下人员，若有违规，将会受到行业主管部门处罚，希望学员朋友理解与支持！4、交通车单程运行时间需要50分钟，学员可根据预约时段自主选择乘车时间；5、以上运行时间从2018年1月1日早上06:40开始执行，望相互转告！',
            'phone' => '投诉电话：13985363663、17785719563',
            'data_list'=> array(
                0 => array(
                    'ride_time' => '06:00',
                    'back_time' => '09:00',
                    'type' => '南门狗桥、权洪停车场',
                ),
                1 => array(
                    'ride_time' => '08:00',
                    'back_time' => '11:10',
                    'type' => '南门狗桥、权洪停车场',
                ),
                2 => array(
                    'ride_time' => '10:30',
                    'back_time' => '12:50',
                    'type' => '南门狗桥、权洪停车场',
                ),
                3 => array(
                    'ride_time' => '12:20',
                    'back_time' => '15:40',
                    'type' => '南门狗桥、权洪停车场',
                ),
                4 => array(
                    'ride_time' => '14:20',
                    'back_time' => '18:00',
                    'type' => '南门狗桥、权洪停车场',
                ),
                5 => array(
                    'ride_time' => '17:00',
                    'back_time' => '20:10',
                    'type' => '南门狗桥、权洪停车场',
                ),
            )
        );

        /*$content = "<table><tr><td>测试下1<td><td>测试下2<td></tr><tr><td>11111<td><td>222222<td></tr></table><p>16点后可预约新的课程，17点后不能取消已预约第二天课程，21点后不能预约第二天课程.</p>";*/

        return response()->json(['result' => 'success', 'code' => 200,'data'=>$info]);
    }





    /*
     * @Des:检测欠费信息
     * @Parms:
     * @Response: bool
     * */

    public function checkIsArrears()
    {
        $count = CostArrearage::where(array('is_paid' => 1, 'student_id' => $this->_user_id))->whereIn('expense_type', [1, 3, 4, 7, 9])->count();
        if ($count > 0) {
            return false;
        }
        return true;
    }

    /*
     * @Des:检测科目一考试成绩
     * */
    public function checkExamination($subject)
    {
        return Examination::where(array('user_id' => $this->_user_id, 'subject' => $subject))->where('score', '>=', 90)->count();

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
     * @Des:检测token
     * */
    public function checktoken()
    {
        return response()->json(['result' => 'success', 'code' => 200]);
    }


    /*
     * @Des:教练评价接口
     * */

    public function getcoachlist(){

        try {
            $options = [
                'user_truename' => request('user_truename'),
                'admin_name' => request('admin_name'),
                'start_date' => request('start_date'),
                'finish_date' => request('finish_date'),
                'user_id'=>$this->_user_id
            ];
            $listinfo = CoachReview::GetCoachEvaluation($this->_school_id,options_filter($options),[1,2])->paginate(10);
            foreach ($listinfo as $v){
                $v->class_list;
            }
            $pageinfo = $listinfo->toArray();
            $Response = array();
            foreach ($listinfo as $k => $v) {
                foreach ($v->class_list as $kk=>$vv){
                    $Response[$k]['class_list'][$kk] = $vv->class_time;
                }
                $Response[$k]['id'] = $v->id;
                $Response[$k]['coach_name'] = $v->admin->admin_name;
                $Response[$k]['date'] = $v->date;
                $Response[$k]['license_num'] = $v->vehicle->car_num;
                $Response[$k]['name'] = $this->_username;
                $Response[$k]['status'] = $v->status;
                $Response[$k]['type'] = $v->type;
            }
            return response()->json(['result' => 'success', 'totalpage' => $pageinfo['last_page'], 'page' => $pageinfo['current_page'], 'code' => 200, 'data' => $Response]);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail', 'code' => $e->getCode(), 'msg' => $e->getMessage()]);
        }

    }

    //教练评价
    public function student_assess(){
        //是否欠费 判断
        \DB::beginTransaction();
        try {
            $id = $this->_recedata['id'];
            $info = CoachReview::find($id);
            if($info->user_id != $this->_user_id){
                throw new \Exception('您无法评价此教练!');
            }
            if($info->status >1){
                throw new \Exception('不能重复评价!');
            }
            if($this->_recedata['assess_tag']){
                $info->remark = implode(",",$this->_recedata['assess_tag']);
            }
            $info->score      = $this->_recedata['assess_num'];
            $info->description = $this->_recedata['assess_content'];
            $info->status = 2;
            $info->save();
            \DB::commit();
            return response()->json(['result' => 'success', 'code' => 200]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['result' => 'fail', 'code' => $e->getCode(), 'msg' => $e->getMessage()]);
        }
    }

    //教练投诉
    public function student_complain(){
        //是否欠费 判断
        \DB::beginTransaction();
        try {
            $id = $this->_recedata['id'];
            $info = CoachReview::find($id);
            if($info->user_id != $this->_user_id){
                throw new \Exception('您无法评价此教练!');
            }
            if($info->status >1){
                throw new \Exception('不能重复评价!');
            }
            if($this->_recedata['complain_tag']){
                $info->remark = implode(",",$this->_recedata['complain_tag']);
            }
            $info->description = $this->_recedata['complain_content'];
            $info->status = 2;
            $info->type = 2;
            $info->anonymous = $this->_recedata['submit_code'];
            $info->save();
            \DB::commit();
            return response()->json(['result' => 'success', 'code' => 200]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['result' => 'fail', 'code' => $e->getCode(), 'msg' => $e->getMessage()]);
        }
    }

    //我的评价
    public function myassess(){
        try {
            $options = [
                'user_id'=>$this->_user_id,
                'status' => [2,3],
                'type'  => 1,
            ];
            $listinfo = CoachReview::GetCoachEvaluation($this->_school_id,options_filter($options),[1])->paginate(10);
            foreach ($listinfo as $v){
                $v->class_list;
            }
            $pageinfo = $listinfo->toArray();
            $Response = array();
            foreach ($listinfo as $k => $v) {
                foreach ($v->class_list as $kk=>$vv){
                    $Response[$k]['train_list'][$kk] = $vv->class_time;
                }
                $Response[$k]['id'] = $v->id;
                $Response[$k]['coach_name'] = $v->admin->admin_name;
                $Response[$k]['assess_time'] = $v->date;
                $Response[$k]['assess_tag'] = explode(',',$v->remark);
                $Response[$k]['status'] = $v->status;
                $Response[$k]['assess_content'] = $v->description;
                $Response[$k]['assess_replay'] = $v->replay?$v->replay:'';
            }
            return response()->json(['result' => 'success', 'totalpage' => $pageinfo['last_page'], 'page' => $pageinfo['current_page'], 'code' => 200, 'data' => $Response]);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail', 'code' => $e->getCode(), 'msg' => $e->getMessage()]);
        }
    }

    //我的评价
    public function mycomplain(){
        try {
            $options = [
                'user_id'=>$this->_user_id,
                'status' => [2,3],
            ];
            $listinfo = CoachReview::GetCoachEvaluation($this->_school_id,options_filter($options),[2])->paginate(10);
            foreach ($listinfo as $v){
                $v->class_list;
            }
            $pageinfo = $listinfo->toArray();
            $Response = array();
            foreach ($listinfo as $k => $v) {
                foreach ($v->class_list as $kk=>$vv){
                    $Response[$k]['train_list'][$kk] = $vv->class_time;
                }
                $Response[$k]['id'] = $v->id;
                $Response[$k]['coach_name'] = $v->admin->admin_name;
                $Response[$k]['complain_time'] = $v->date;
                $Response[$k]['complain_tag'] = explode(',',$v->remark);
                $Response[$k]['status'] = $v->status;
                $Response[$k]['complain_content'] = $v->description;
                $Response[$k]['complain_replay'] = $v->replay?$v->replay:'';
            }
            return response()->json(['result' => 'success', 'totalpage' => $pageinfo['last_page'], 'page' => $pageinfo['current_page'], 'code' => 200, 'data' => $Response]);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail', 'code' => $e->getCode(), 'msg' => $e->getMessage()]);
        }
    }




}
