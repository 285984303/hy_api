<?php
namespace App\Models\Course;

use App\Models\BaseModel;
/*
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '手动排课',
  `school_id` int(11) DEFAULT NULL COMMENT '驾校id',
  `course_date` timestamp NULL DEFAULT NULL COMMENT '排课日期',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
 */
class CourseManual extends BaseModel
{
    protected $table = 'course_manual';
//    protected $rules = [];
    protected $fillable = ['school_id']; //批量复制
//    protected $dates = ['deleted_at'];//软删除
//    public $timestamps = false;  //关闭自动更新时间戳
    protected $primaryKey = 'id';

}