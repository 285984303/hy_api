<?php
namespace App\Models\Course;

use App\Models\BaseModel;
/*
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '排课黑名单',
  `school_id` int(11) DEFAULT NULL COMMENT '驾校id',
  `course_id` int(11) DEFAULT NULL COMMENT '时段id',
  `instructor_id` int(11) DEFAULT NULL COMMENT '教练id',
  `course_date` int(11) DEFAULT NULL COMMENT '日期',
  `status` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
 */
class CourseBlackList extends BaseModel
{
    protected $table = 'course_black_list';
//    protected $rules = [];
    protected $fillable = ['school_id']; //批量复制
//    protected $dates = ['deleted_at'];//软删除
//    public $timestamps = false;  //关闭自动更新时间戳
    protected $primaryKey = 'id';

}