<?php
namespace App\Models\Packages;

use App\Models\BaseModel;

/*
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `packages_id` int(11) DEFAULT NULL COMMENT '套餐id',
  `charging_item_id` int(11) DEFAULT NULL COMMENT '费用id',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
 */

class PackagesBeginnerLimit extends BaseModel
{
    protected $table = 'packages_beginner_limit';
    protected $rules = [];

    public static function GetCarTypeInfo($type,$packages_id,$fields=array("*")){
       return self::where(array('type'=>$type,'packages_id'=>$packages_id))->select($fields)->get();
    }

    /*统计是否存在数据*/
    public static function GetCarTypeCount($type,$packages_id,$vehicle_id){
        return self::where(array('type'=>$type,'packages_id'=>$packages_id,'vehicle_type_id'=>$vehicle_id))->count();
    }


}
