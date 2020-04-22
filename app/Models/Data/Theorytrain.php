<?php

namespace App\Models\Data;

use Illuminate\Database\Eloquent\Model;

class Theorytrain extends Model
{
    protected $table   = 'theory_train';
    public    $timestamps = false;

    /*
     * @Des:根据学员id统计需要上报的理论课程
     * */
    public static function GetTheoryInfo($subject,$fields=array("*")){

        return self::where(array('subject'=>$subject))->where('is_deal','<>',1)->where('datatype','=',2)->select($fields)->get()->groupBy('user_id');
    }
    /*
     *@Des: 统计是否存在统计信息
     * */
    public static function TheoryInfoCount($where){
        return self::where($where)->count();
    }
    /*用户信息*/
    public function userinfo(){
        return $this->belongsTo(User::class,'user_id');
    }

}
