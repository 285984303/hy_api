<?php
namespace App\Models\Course;

use App\Models\BaseModel;
/*
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '学员预约',
  `student_id` int(11) DEFAULT NULL COMMENT '学员id',
  `instructor_id` int(11) DEFAULT NULL COMMENT '教练id',
  `reserve_id` int(11) DEFAULT NULL COMMENT '预约时段id',
  `reserve_date` timestamp NULL DEFAULT NULL COMMENT '预约日期',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
 */
class CourseStudentReservation extends BaseModel
{
    protected $table = 'course_student_reservation';
//    protected $rules = [];
    protected $fillable = ['school_id']; //批量复制
//    protected $dates = ['deleted_at'];//软删除
//    public $timestamps = false;  //关闭自动更新时间戳
    protected $primaryKey = 'id';

}