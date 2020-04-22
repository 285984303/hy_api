<?php
namespace App\Models\Packages;

use App\Models\BaseModel;

/*
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '费用产生',
  `package_id` int(11) NOT NULL COMMENT '套餐id',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '收费项目',
  `type` int(11) NOT NULL,
  `price` int(11) NOT NULL COMMENT '价格',
  `start_time` int(11) NOT NULL COMMENT '有效时间',
  `end_time` int(11) NOT NULL COMMENT '有效时间',
  `status` int(11) NOT NULL COMMENT '状态',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
 */

class ChargingItem extends BaseModel
{
    protected $table = 'charging_item';
    protected $rules = [

    ];
}
