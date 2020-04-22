<?php

namespace App\Models\Packages;

use App\Models\BaseModel;

class SchoolGlobal extends BaseModel
{
    protected $table = 'school_global';
//    protected $rules = [];
    protected $fillable = ['school_id', "name", "inside_price", "outside_price", "inside", "outside", "charge_type", "type", "status"];

//    protected $dates = ['deleted_at'];//软删除
//    public $timestamps = false;  //关闭自动更新时间戳
    protected $primaryKey = 'id';

}