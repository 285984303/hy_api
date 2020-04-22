<?php
namespace App\Models\Packages;

use App\Models\BaseModel;
use App\Models\Vehicle\VehicleType;

/*
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '套餐教练车辆',
  `package_id` int(11) NOT NULL COMMENT '套餐id',
  `related_id` int(11) NOT NULL COMMENT '教练或车辆id',
  `type` int(11) NOT NULL COMMENT '1教练 2车辆',
  `price` int(11) NOT NULL COMMENT '收费价格',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
 */

class PackagesCoachVehicle extends BaseModel {
    protected $table = 'packages_coach_vehicle';

    protected $rules = [];

    protected $fillable = ['packages_id','related_id','type','price']; //批量复制




    /*
     * @Des:关联车型模型
     * */

    public function vehicletype(){
        return $this->belongsTo(VehicleType::class,'related_id');
    }

    public static function GetCoachInfo($where){
        return self::where($where)->first();
    }

}