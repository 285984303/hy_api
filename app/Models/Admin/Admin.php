<?php

namespace App\Models\Admin;

use App\Library\Http;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\AppointmentType;
use App\Models\Appointment\Comment;
use App\Models\Business\UserProduct;
use App\Models\Data\LicenceType;
use App\Models\Data\Region;
use App\Models\Data\School;
use App\Models\Finance\Wages;
use App\Models\Student\Examination;
use App\Models\NotFound;
use App\Models\ParameterError;
use App\Models\StoreError;
use App\Models\Vehicle\Vehicle;
use App\Models\Vehicle\VehicleType;
use DB;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator;
use Zizaco\Entrust\Traits\EntrustUserTrait;
//use zgldh\QiniuStorage\QiniuStorage;
use App\Models\Home\User;

/**
 * Class Admin
 *
 * @package App\Models\Admin
 * @property $id
 * @property $admin_name        string      管理员名称
 * @property $mobile_phone      string      手机号
 * @property $admin_email       string      邮箱
 * @property $password          string      密码
 * @property $salt              string      加盐
 * @property $gender            boolean     性别
 * @property $birthday          integer     出生年月日
 * @property $nation            string      民族
 * @property $id_card           string      身份证号
 * @property $married           boolean     是否结婚（0、否 1、是）
 * @property $admin_thumb       string      个人照片
 * @property $contract_start    timestamp   合同开始时间
 * @property $contract_stop     timestamp   合同结束时间
 * @property $coach_group_id    string      教练组id  (注：不是教练 id为空)
 * @property $coach_level_id    integer     教练等级（a、b、c、d）
 * @property $vehicle_id        integer     车辆 id
 * @property $extra_charges     integer     额外费用
 * @property $school_id         string      所属驾校id
 * @property $is_dismiss        boolean     是否离职（0、在职 1、离职 默认0）
 * @property $status            integer     状态（0、无效 1、有效 默认为1)
 * @property $sequence          float       教练排序
 * @property $finger_id         string      指纹
 * @property $finger_data       string      指纹信息
 * @property $staff_id
 * @property $customize_img     string      自定义上传的照片 json
 * @property $positive_time     date        实习转正时间
 * @property $admin_numbers     string      统一编号
 * @property $job_people        string      1教练员2安全员3考核员
 * @property-read \App\Models\Vehicle\Vehicle $vehicle
 * @property-read \App\Models\Appointment\AppointmentType[]|\Illuminate\Database\Eloquent\Collection $appointmentTypes
 * @property-read \App\Models\Appointment\Comment[]|\Illuminate\Database\Eloquent\Collection $comments
 *
 * @mixin \Eloquent|\Illuminate\Foundation\Auth\User
 */
class Admin extends Authenticatable
{

    use EntrustUserTrait;

    protected $table = 'admin';

    public function getGender()
    {
        if ($this->gender == 1) {
            return '男';
        } else {
            return '女';
        }
    }

    /*
     * @Des:根据手机号检索教练信息
     * */
    public static function getAdminByPhone($phone,$fields=array('*')){
        return self::where('mobile_phone','=',$phone)->select($fields)->first();
    }

    /*
     * @Des:获取分组内教练信息
     * */
    public static function getCoachGroupList($school_id,$group_id){
        return self::where('school_id','=',$school_id)->where('coach_group_id','=',$group_id)->pluck('id');
    }


}
