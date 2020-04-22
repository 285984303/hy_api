<?php
namespace App\Models\Packages;

use App\Models\BaseModel;
use App\Models\Data\LicenceType;
use App\Models\Vehicle\VehicleType;

/*
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '套餐id(收费标准编号)',
  `school_id` int(11) NOT NULL COMMENT '驾校id(培训机构编号)',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '套餐名称(班级名称)',
  `driving_licence` tinyint(2) DEFAULT NULL COMMENT '驾照类型(培训车型)',
  `training_model` tinyint(2) DEFAULT NULL COMMENT '培训模式',
  `pay_model` tinyint(11) DEFAULT NULL COMMENT '付费模式',
  `charging_mode` tinyint(11) DEFAULT NULL COMMENT '收费模式',
  `train_time` tinyint(11) DEFAULT NULL COMMENT '培训时段',
  `subject_two` int(11) DEFAULT NULL COMMENT '科目二学时',
  `subject_three` int(11) DEFAULT NULL COMMENT '科目三学时',
  `vehicle_type` tinyint(11) DEFAULT NULL COMMENT '套餐准驾车型',
  `limit_times` int(11) DEFAULT NULL COMMENT '限制课时',
  `training_cost` int(11) DEFAULT NULL COMMENT '培训费用',
  `status` tinyint(1) DEFAULT NULL COMMENT '套餐状态',
  `start_time` timestamp NULL DEFAULT NULL COMMENT '套餐开始时间',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '套餐结束时间',
  `content` varchar(255) DEFAULT NULL COMMENT '套餐说明',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
 */

class Packages extends BaseModel
{
    protected $table = 'packages';
    protected $fillable = ['school_id','number','name','driving_licence','training_model',
        'pay_model','charging_mode','train_time','subject_two','subject_three',
        'vehicle_type','limit_times','penalties','training_cost','status',
        'start_time','end_time','content']; //批量复制

//    protected $dates = ['deleted_at'];//软删除
//    public $timestamps = false;  //关闭自动更新时间戳
    protected $primaryKey = 'id';

    //教练车辆费用
    public function coachVehicle()
    {
        return $this->hasMany('App\Models\Packages\PackagesCoachVehicle', 'packages_id', 'id');
    }

    //时段收费
    public function timeCharge()
    {
        return $this->hasMany('App\Models\Packages\PackagesTimeCharge', 'packages_id', 'id');
    }

    public function licence_type(){
        return $this->belongsTo(LicenceType::class,'driving_licence','id');
    }

    /*
     * @Des:关联车型
     * */
    public function vehicle_type(){

        return $this->belongsTo(VehicleType::class,'driving_licence');
    }

    //额外收费项目
    public function chargingItem()
    {
        return $this->hasMany('App\Models\Packages\PackagesChargingItem', 'packages_id', 'id');
    }

    //初学车型限制
    public function beginnerLimit()
    {
        return $this->hasMany('App\Models\Packages\PackagesBeginnerLimit', 'packages_id', 'id');
    }

    //查询初学学时车辆限制
    //$hours 已学学时
    static function limit($packages_id, $hours)
    {
        $package = self::find($packages_id);
        if($hours <= $package->limit_times) {
            $limits = $package->beginnerLimit()->get()->toArray();
            return $limits; //返回限制车辆 array
        }

        return false; //超出学时，没有限制
    }


    /*
     * @Des:根据驾校统计套餐数据量
     * */
    public static function GetPackageCount($school_id){
        return self::where(array('school_id'=>$school_id))->count();
    }



}