<?php
/*
 * @Des:    阶段审核信息记录
 * @Author: Joe
 * @Date:   2017.08.10
 * */
namespace App\Models\Data;

use Illuminate\Database\Eloquent\Model;
use App\Models\Home\User;
use App\Models\BaseModel;
use App\Library\Http;
use Illuminate\Support\Facades\Log;


class Stagerecord extends Model
{
    protected $table   = 'user_stage_record';
    public    $timestamps = false;
    /*
     * @Des:读取记录信息
     * */
    public static function StageRecord($where,$type='all'){
        $query = self::where($where);
        if($type=='all'){
            $query = $query->get();
        }else if($type=='one'){
            $query = $query->first();
        }
        return $query;
    }
    /*
     *@Des: 统计是否存在统计信息
     * */
    public static function StageRecordCount($where){
        return self::where($where)->count();
    }
    /*用户信息*/
    public function userinfo(){
        return $this->belongsTo(User::class,'user_id');
    }




}
