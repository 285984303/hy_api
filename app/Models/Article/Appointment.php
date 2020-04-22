<?php namespace App\Models\Appointment;

use App\Models\Admin\Admin;
use App\Models\BaseModel;
use App\Models\Home\Examination;
use App\Models\Home\User;
use App\Models\ParameterError;
use App\Models\Vehicle\Vehicle;
use DB;


class Appointment extends BaseModel
{
    protected $table = 'appointment';
    const STATUS_ABLE = 'ABLE';      // 可预约
    const STATUS_TAKEN = 'TAKEN';     // 已预约
    const STATUS_CANCELED = 'CANCELED';  // 已取消
    const STATUS_DONE = 'DONE';      // 已完成
    const STATUS_BROKEN = 'BROKEN';    // 违约
    const STATUS_DISABLE = 'DISABLE';   // 不可预约
    const STATUS_EVALUATED = 'EVALUATED'; // 已评价
    const STATUS_DOING = 'DOING';
    const STATUS_TIME_OUT = 'TIME_OUT';
    const STATUS_FORCE_DISABLE = 'FORCE_DISABLE';   // 强制不可预约
    const VALID = 'T';
    const INVALID = 'F';
    const LIMITNUMS = 4;

    /*
     * @Des:预约课程展示 根据教练ID分组
     * */
    public static function getCoachClass($parms, $fields = "*",$limit = true)
    {

        $date = $parms['date'];
        $query = self::where('school_id', '=', $parms['school_id'])->where('is_hours',0)->where('date', '=', $date)->where('status','<>','CANCELED');
        foreach ($parms as $key => $value) {
            switch ($key) {
                case 'carlist':
                    $query->whereIn('vehicle_id',$value);
                    break;
                case 'coach':
                    if(!empty($value))
                    $query->where('admin_id','=',$value);
                    break;
                case 'coach_group_admins':
                    $query->whereIn('admin_id',$value);
                    break;
                default:
                    break;
            }
        }
        $array = array(
            'date' =>$date,
            'limit'=>$limit
        );
//        return $query = $query->groupBy('admin_id')->select($fields)->get();
        return $query = $query->select($fields)->with(['admin' => function ($query) {
            $query->select('id', 'admin_name');
        }])->with(['vehicle' => function ($query) {
            $query->select('id', 'car_num','vehicle_type_id','licence_type_id')->with('vehicle_type');
        }])->with(['appointments' => function ($query) use ($array) {
            $query->select('id', 'admin_id', 'user_id', 'start_time', 'finish_time', 'course_id', 'status', 'is_change', 'date', 'is_valid', 'vehicle_id');
            $query->where('date', '=', $array['date']);
            if($array['limit']){
                $query->where('status', '=', 'ABLE');
                if($array['date'] == date("Y-m-d",time())){
                    $query->where('start_time', '>', date("H:i:s",(time()+600)));
                }
                $query->whereNull('user_id');
            }else{
                $query->where('status', '<>', 'CANCELED');
            }
            $query->with(['user' => function ($query) {
                $query->select('id', 'user_truename');
            }]);
            $query->orderBy('start_time', 'asc');
        }])->where('is_valid','=','T')->groupBy('admin_id')->get();
    }

    /*
     * @Des:执行预约信息
     * */

    public static function setAppointInfo($parms=array(),$username=''){
        $info = self::find($parms['id']);
        $start = date("H:i",strtotime($info->start_time));
        $end = date("H:i",strtotime($info->finish_time));
        //兼容现有科目ID  科目一(1,2,3) 科目二(4,5,6)
        if($parms['type_id']==3){
            //$parms['type_id']==4;
            $subject_id = 4;
            $subject = 3;
        }else{
            $subject_id = 2;
            $subject = 2;
        }
        if(!$info){
            throw new ParameterError('预约课程存在异常无法预约!');
        }
        //判断科目考试是否通过 通过则不允许预约
        $whereexaminfo = array(
            'user_id' => $parms['user_id'],
            'subject' => $subject
        );
        $countexam = Examination::where($whereexaminfo)->where('score','>=','80')->count();
        if($countexam){
            throw new ParameterError('您的科目'.$subject."考试成绩已通过无法预约此科目！");
        }

        //9点后不允许预约第二天课程
        if (date("H:i", time()) >= '21:00') {
            $nextdate = date("Y-m-d", strtotime("+1 day"));
            if($info->date == $nextdate){
                throw new \Exception('21点之后不允许预约第二天课程，请您重新选择预约时间！');
            }
        }else{
            if($info->date == date("Y-m-d")){
                throw new \Exception('您预约的课程已经超出预约时间！');
            }
        }
        if($info->date < date("Y-m-d")){
            throw new ParameterError($info->date." ".$start."-".$end.'预约课程已结束无法预约!');
        }
        $hours_start_timestamp = strtotime($info->date . ' ' . $info->start_time);
        if ($hours_start_timestamp <= time()) {
            throw new ParameterError($start."-".$end.'课程已经开始,不可预约!');
        }
        if($info->status !='ABLE'){
            throw new ParameterError($start."-".$end."课程已被其他人预约您无法预约此课程!");
        }
        if($info->is_valid !='T'){
            throw new ParameterError($start."-".$end."此课时未放课无法预约!");
        }
        //当天 预约冲突时段检测
        $checktime = self::where(array('date'=>$info->date,'status'=>'TAKEN','user_id'=>$parms['user_id'],'course_id'=>$info->course_id))->count();
        if($checktime){
            throw new ParameterError("课时".$start."-".$end.'存在冲突,无法预约!');
        }
        //套餐课时总数检测
        /*$count = self::GetClassCount($parms['user_id']);
        if(session('class_nums') > 0){ //设置课时数时候启用
            if(session('class_nums') <= $count){
                throw new ParameterError("已超出您的课时总数,无法再继续预约!");
            }
        }*/
        /*if($count>0){
            if(session('class_nums') <= $count){
                throw new ParameterError("已超出您的学时总数,无法预约!");
            }
        }*/

        $info->user_id = $parms['user_id'];
        $info->type_id = $subject_id;
        $info->appointment_time = date('Y-m-d H:i:s');
        $info->status  = 'TAKEN';
        $info->is_valid  = 'T';
        $info->appointname  = $username;
        $info->appointment_type = $parms['appointment_type'];
        $response = $info->save();
        //查询超出回滚


        if(!$response){
            throw new ParameterError('网络异常，无法预约!');
        }

        $response = $info->date." ".$start."-".$end;
        return $response;
    }


    /*
     * @Des:学员列表信息
     * */
    public static function getAppointments($user_id,$type='CANCLE',$parms=array()){

        $query = self::where(array('user_id'=>$user_id))->where('is_hours',0)->select('id','admin_id','vehicle_id','type_id','is_valid','date','start_time','finish_time','appointment_time','sign_in_time','sign_out_time','status','cancel_time','course_id');
        foreach ($parms as $key => $value) {
            switch ($key) {
                case 'subject':
                    if($value==2){
                        $query->whereIn('type_id',[1,2,3]);
                    }elseif($value==3){
                        $query->whereIn('type_id',[4,5,6]);
                    }
                    break;
                case 'namestatus':
                    $query->where('status','=',$value);
                    break;
                case 'date':
                    $query->where('date','=',$value);
                    break;
                default:
                    break;
            }
        }
        if($type=='CANCLE'){ //可取消预约课程列表
           $allowcancledate = self::setcancletime();
            /*$query = $query->where(array('status'=>'TAKEN'))->where(function ($query){
                $query->where(function ($query){
                    $query->where('date','=',date("Y-m-d"))->where('start_time','>=',date("H:i:s"));
                })->orwhere(function ($query){
                    $query->where('date','>',date("Y-m-d"));
                });
            })->where('status','<>', 'CANCELED');*/
            $query = $query->where(array('status'=>'TAKEN'))->where('date','>=',$allowcancledate);
        }elseif ($type=='CANCLED'){//已取消课程列表
            $query = $query->where(array('status'=>'CANCELED'));
        }elseif($type=='NOTCANCLED'){
            $query->where('status','<>', 'CANCELED');
        }
        $query = $query->with(['vehicle' => function ($query) {
            $query->select('id', 'car_num','vehicle_type_id','licence_type_id');
        }])->with(['admin' => function ($query) {
            $query->select('id', 'admin_name');
        }])->orderby('date','DESC')->orderby('start_time','DESC');
        return $query;
    }



    /*
     * @Des: 计算已上学时数，除违约学时,取消学时 status !=BROKEN  status !=BROKEN
     * */
    public static function GetClassCount($userid,$subject='')
    {
        $query = self::where('user_id','=',$userid);
        if($subject==2){
            $query->whereIn('type_id',[1,2,3]);
        }elseif($subject==3){
            $query->whereIn('type_id',[4,5,6]);
        }
        return $query->where('status', '<>', 'BROKEN')->where('status', '<>', 'CANCELED')->count();
    }

    /*
     * @Des:学员预约数计算 判断是否允许预约
     * */
    public static function getAllAppointments($userid,$date){
        $count = self::where(array('user_id'=>$userid))->where('date','=',$date)->where('status', '<>', 'CANCELED')->count();
        if($count>=4){
            return false;
        }
        return array('nums'=>$count);
    }

    /*
     * @Des:允许取消日期
     * */
    public static function setcancletime(){
        if(date("H:i",time())>='17:00'){
            $date = date("Y-m-d",strtotime("+2 day"));
        }else{
            $date = date("Y-m-d",strtotime("+1 day"));
        }
        return $date;
    }


    /*
     * @Des:教练读取近3天课时信息,小程序教练端用
     *
     * */
    public static function getAppointmentInfo($admin_id,$parms=array()){

        $query = self::where('admin_id','=',$admin_id)->where('status','<>','CANCELED')->select('id','user_id','is_valid','admin_id','vehicle_id','date','start_time','finish_time','appointment_time','sign_in_time','sign_out_time','status','cancel_time','is_change');
        foreach ($parms as $key => $value) {
            switch ($key) {
                case 'startdate':
                    $query->where('date','>=',$value);
                    break;
                case 'enddate':
                    $query->where('date','<=',$value);
                    break;
                default:
                    break;
            }
        }
        $query = $query->with(['vehicle' => function ($query) {
            $query->select('id', 'car_num','vehicle_type_id','licence_type_id');
        }]);
        $query = $query->with(['user' => function ($query) {
            $query->select('id', 'user_truename','user_telphone');
        }]);
        $query = $query->orderby('date','ASC')->orderby('start_time','ASC')->get();
        return $query;
    }



    /*
     * @Des:关联模型
     * */

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'admin_id', 'admin_id');

    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }



}