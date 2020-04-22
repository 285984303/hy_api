<?php
namespace App\Models\Packages;

use App\Models\BaseModel;
use App\Models\Course\Course;
use Illuminate\Database\Eloquent\SoftDeletes;
/*
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `package_id` int(11) NOT NULL COMMENT '套餐id',
  `start_time` timestamp NULL DEFAULT NULL COMMENT '开始时间',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '结束时间',
  `price` int(11) NOT NULL COMMENT '价格',
  `type` tinyint(2) NOT NULL COMMENT '1工作日，2节假日',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
 */

class PackagesTimeCharge extends BaseModel
{
    use SoftDeletes;
   // use SoftDeletes;
    protected $table = 'packages_time_charge';
    protected $dates = ['deleted_at'];//软删除
//    protected $rules = [];
//    protected $foreignKey = 'packages_id';

    /*
     * @Des:取得课时时间段费用
     *
     * */
    public static function GetChargePrice($where=array(),$fields=array('*')){
        return self::select($fields)->where($where)->get();
    }


    /*
     * @Des:关联课时时间段模型
     * */
    public function course()
    {

        return $this->belongsTo(Course::class,'course_id');
    }

}