<?php

namespace App\Models\Cost;

use Illuminate\Database\Eloquent\Model;
use App\Models\Appointment\Appointment;

class CostArrearageCourse extends Model
{
    protected $table = 'c_cost_arrearage_course';


    /*
     * @Des: 数据添加 返回插入ID
     * */
    public static function costArrearageCourseAdd($parms=array()){
        return self::insertGetId($parms);
    }

    /*
     * @Des: 数据添加 返回对象及执行成功数据
     * */
    public static function costArrearageCourseCreate($parms=array()){
        return self::create($parms);
    }

    /*
     * @Des:关联课程类型模型
     * */
    public function getCourseInfo()
    {
        return $this->belongsTo(Appointment::class,'course_id');
    }
}
