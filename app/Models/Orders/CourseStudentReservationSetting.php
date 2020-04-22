<?php
namespace App\Models\Course;

use App\Models\BaseModel;
/*
   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `reserve_time` timestamp NULL DEFAULT NULL COMMENT '预约截至时间',
  `cancel_time` timestamp NULL DEFAULT NULL COMMENT '取消截至时间',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
 */
class CourseStudentReservationSetting extends BaseModel
{
    protected $table = 'course_student_reservation_setting';
//    protected $rules = [];
    protected $fillable = ['school_id']; //批量复制
//    protected $dates = ['deleted_at'];//软删除
//    public $timestamps = false;  //关闭自动更新时间戳
    protected $primaryKey = 'id';

}