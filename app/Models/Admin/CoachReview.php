<?php

namespace App\Models\Admin;

use App\Models\Home\User;
use App\Models\Vehicle\Vehicle;
use Illuminate\Database\Eloquent\Model;

class CoachReview extends Model
{
    protected $table   = 'c_coach_review';

    public static function GetCoachEvaluation($shool_id,$options=[],$type=array()){
        $query = self::where('school_id','=',$shool_id)->whereIn('type',$type);
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'user_truename':
                    $user_ids = User::where('user_truename', 'like', "%$value%")
                        ->pluck('id');
                    $query->whereIn('user_id', $user_ids);
                    break;
                case 'admin_name':
                    $coach_ids = Admin::where('admin_name', 'like', "%$value%")
                        ->pluck('id');
                    $query->whereIn('coach_id', $coach_ids);
                    break;
                case 'start_date':
                    $query->where('date','>=',$value);
                    break;
                case 'finish_date':
                    $query->where('date','<=',$value);
                    break;
                case 'status':
                    $query->whereIn('status',$value);
                    break;
                case 'user_id':
                    $query->where('user_id','=',$value);
                    break;
                default:
                    break;
            }
        }
        /*$query->with(['class_list'=>function($query){
            $query->select('id','class_time');
        }]);*/
        $query->with(['user'=>function($query){
            $query->select('id','user_truename','user_telphone');
        }]);
        $query->with(['admin'=>function($query){
            $query->select('id','admin_name','mobile_phone');
        }]);
        $query->with(['dealinfo'=>function($query){
            $query->select('id','admin_name','mobile_phone');
        }]);
        $query->orderby("created_at",'DESC');
        return $query;
    }

    /*
     * @Des:关联用户模型
     * */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    /*
     * @Des:关联车辆
     * */
    public function vehicle()
    {

        return $this->belongsTo(Vehicle::class,'vehicle_id');
    }

    /*
     * @Des:关联教练模型
     * */
    public function admin()
    {
        return $this->belongsTo(Admin::class,'coach_id');
    }

    /*
     * @Des:关联教练模型
     * */
    public function dealinfo()
    {
        return $this->belongsTo(Admin::class,'deal_id');
    }

    /*
     * @Des:课时列表
     * */
    public function class_list()
    {
        return $this->HasMany(CoachStatiscsRecord::class,'review_id');

//        return $this->belongsToMany(CoachStatiscsRecord::class,'c_coach_statiscs_record');
//        return $this->HasMany(CoachStatiscsRecord::class,'review_id','id');
    }


}
