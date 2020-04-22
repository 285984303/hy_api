<?php
namespace App\Models\Breach;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;


class Breach extends BaseModel
{
    use SoftDeletes;
    protected $table = 'breach';
//    protected $rules = [];
    protected $fillable = ['sign_type','sign_type','person_type','appoint_id']; //批量复制

    protected $dates = ['deleted_at'];//软删除
//    public $timestamps = false;  //关闭自动更新时间戳
    protected $primaryKey = 'id';
}