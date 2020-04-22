<?php

namespace App\Http\Controllers\User;

use App\Models\Admin\Admin;
use App\Models\Appointment\Appointment;
use App\Models\Cost\CostArrearage;
use App\Models\Course\Course;
use App\Models\Course\CourseNodeSet;
use App\Models\Student\Examination;
use App\Models\Vehicle\Vehicle;
use App\Models\Vehicle\VehicleType;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    private $_school_id;
    private $_user_id;
    private $_packages_id;
    private $_username;

    /*
     * @Des：析构函数
     * */
    public function __construct()
    {
        error_reporting(1);
        $this->_packages_id = session('packages_id');
        $this->_user_id = session('user_id');
        $this->_school_id = session('school_id');
        $this->_username = session('username');
        $this->_coach_group = session('coach_group');

    }

    /*
     * @Des:预约课程
     *
     * */
    public function appointment()
    {
        try {
            $options = array(
                'date' => request('date') ? request('date') : date("Y-m-d", strtotime("+1 day")), //第二天
                'coach' => request('coach') ? request('coach') : '',
                'subject' => request('subject') ? request('subject') : 2, //默认科目二
                'school_id' => $this->_school_id
            );
            //获取课时 时间段
            
            $coursetime = $this->getcoursetime();
            $courselistinfo = array();
            foreach ($coursetime as $v) {
                $courselistinfo[] = date('H:i', strtotime($v->start_time)) . "-" . date('H:i', strtotime($v->end_time));
            }
            $Response = array();
            $limitdate = $this->setappointtime();
            
            if ($options['date'] >= $limitdate) { //只能选择大于等于当前日期的预约课程列表
                //获取学员可预约车辆列表
                $carinfo = $this->getAppointCarList($options['subject']);
                if ($carinfo) {
                    $options['carlist'] = $carinfo;
                }
                
                //取得可预约教练组 没有分组默认是全部教练 -1 是没有分组
                if($this->_coach_group !=-1){
                    $admins = Admin::getCoachGroupList($this->_school_id,$this->_coach_group);
                    $options['coach_group_admins'] = $admins;
                }
                
                $listinfo = Appointment::getCoachClass(options_filter($options), array('id', 'admin_id', 'vehicle_id', 'course_id'), false);
                //var_dump($listinfo);exit;
                //组装返回页面数据
                foreach ($listinfo as $k => $v) {
                    $Cache[$k]['id'] = $v->admin->id;
                    $Cache[$k]['coach_name'] = $v->admin->admin_name;
                    $Response[$k]['coach_name'] = $v->admin->admin_name;
                    $Response[$k]['license_num'] = $v->vehicle->car_num;
                    
                    $Response[$k]['course_list'] = array();
                    foreach ($v->appointments as $k2 => $v2) {
                        $coursetime = strtotime($v2->date . " " . $v2->start_time) - 7200;
                        if ($coursetime < time()) {
                            if ($v2->status == 'ABLE') {
                                $Response[$k]['course_list'][$k2]['status'] = "DISABLE";
                            }
                        } else {
                            if ($v2->is_valid == 'F') {
                                $Response[$k]['course_list'][$k2]['status'] = "DISABLE";
                            }else{
                                $Response[$k]['course_list'][$k2]['status'] = $v2->status;
                            }
                        }
                        if ($v2->user_id == $this->_user_id) {
                            $Response[$k]['course_list'][$k2]['username'] = $this->_username;
                        } else {
                            $Response[$k]['course_list'][$k2]['username'] = '';
                        }
                        $Response[$k]['course_list'][$k2]['date'] = $v2->date;
                        $Response[$k]['course_list'][$k2]['subject'] = $options['subject'];
                        $Response[$k]['course_list'][$k2]['id'] = $v2->id;
                        $Response[$k]['course_list'][$k2]['time'] = date("H:i", strtotime($v2->start_time)) . "-" . date("H:i", strtotime($v2->finish_time));
                    }
                    if (count($Response[$k]['course_list'])== 0) {
                        unset($Response[$k]);
                    }
                }
            }
            return view('home.appointment')->with('courselist', $Response)->with('options', $options)->with('catname', '训练预约')->with('courselistinfo', $courselistinfo);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail', 'code' => $e->getCode(), 'msg' => $e->getMessage()]);
        }
    }
    /*
     * @Des:取消预约列表
     *
     * */
    public function cancleappointment()
    {
        try {
            $listinfo = Appointment::getAppointments($this->_user_id)->get();
            $Response = array();
            foreach ($listinfo as $k => $v) {
                $Response[$k]['id'] = $v->id;
                $Response[$k]['coach_name'] = $v->admin->admin_name;
                $Response[$k]['license_num'] = $v->vehicle->car_num;
                if (in_array($v->type_id, [1, 2, 3])) {
                    $Response[$k]['subject'] = "科目二";
                } else {
                    $Response[$k]['subject'] = "科目三";
                }
                $Response[$k]['date'] = $v->date;
                $Response[$k]['training_time'] = date("H:i", strtotime($v->start_time)) . "-" . date("H:i", strtotime($v->finish_time));
            }
            return view('home.student-cancle')->with('catname', '取消预约')->with('listinfo', $Response);//取消预约页面
        } catch (\Exception $e) {
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
        $options = array(
            'date' => request('date'),
            'namestatus' => request('namestatus'),
            'subject' => request('subject') ? request('subject') : '', //默认科目二
        );
        $Response = Appointment::getAppointments($this->_user_id, 'all', options_filter($options))->paginate(10);
        foreach ($Response as $v) {
            if (in_array($v->type_id, [1, 2, 3])) {
                $v->subject = "科目二";
            } else {
                $v->subject = "科目三";
            }
            if (!$v->sign_in_time) {
                $v->sign_in_time = "------------";
            }
            if (!$v->sign_out_time) {
                $v->sign_out_time = "------------";
            }
            $v->training_time = date("H:i", strtotime($v->start_time)) . "-" . date("H:i", strtotime($v->finish_time));
        }
        return view('home.student-already')->with('catname', '预约记录')->with('options', $options)->with('listinfo', $Response);//课程记录界面
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
     * @Des:读取课时时间点
     * */
    public function getcoursetime()
    {
        return Course::where('school_id', '=', $this->_school_id)->orderby('start_time','asc')->get();
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

}
