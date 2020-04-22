<?php
namespace App\Models\Course;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自动排课设置',
  `school_id` int(11) DEFAULT NULL COMMENT '驾校id',
  `instructor_id` int(11) DEFAULT NULL COMMENT '教练id',
  `course_date` timestamp NULL DEFAULT NULL COMMENT '排课日期',
  `is_auto` int(11) DEFAULT NULL COMMENT '是否自动排课',
  `auto_days` int(11) DEFAULT NULL COMMENT '自动排课天数',
  `auto_time` int(11) DEFAULT NULL COMMENT '自动开始排课时间',
  `reserve_time` timestamp NULL DEFAULT NULL COMMENT '预约截至',
  `reserve_days` int(11) DEFAULT NULL COMMENT '预约截至天数',
  `cancel_time` timestamp NULL DEFAULT NULL COMMENT '取消预约截至',
  `cancel_days` int(11) DEFAULT NULL COMMENT '取消预约截至天数',
  `type` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
 */

class CourseAutoSetting extends BaseModel {
    use SoftDeletes;

    protected $table = 'course_auto_setting';
    protected $fillable = ['school_id', 'course_date', 'is_auto', 'auto_time', 'auto_days', 'reserve_time', 'reserve_days', 'cancel_time', 'cancel_days']; //批量复制
    protected $dates = ['deleted_at'];//软删除
    protected $primaryKey = 'id';
    //    protected $rules = [];
    //    public $timestamps = false;  //关闭自动更新时间戳

}