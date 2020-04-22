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

class PackagesChargingItem extends BaseModel
{
    protected $table = 'packages_charging_item';
    protected $rules = [];



}
