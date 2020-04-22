<?php
namespace App\Models\Course;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(11) DEFAULT NULL COMMENT '驾校id',
  `start_time` int(11) DEFAULT NULL COMMENT '开始时间',
  `end_time` int(11) DEFAULT NULL COMMENT '结束时间',
  `type` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL COMMENT '是否启用',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
 */

class Course extends BaseModel {
    use SoftDeletes;                  //开启软删除

    protected $table = 'course';
    protected $primaryKey = 'id';
    protected $fillable = ['school_id', 'start_time', 'end_time']; //批量复制
    protected $dates = ['deleted_at'];//软删除
    //    public $timestamps = false;  //关闭自动更新时间戳
    //    protected $rules = [];




}