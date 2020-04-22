<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class CoachStatistics extends Model
{
    protected $table   = 'c_coach_time_statistics';

    public static function GetCoachStatistics($options=[]){
        $query = self::where('school_id','=',session('school_id'));
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'year':
                    $query->where('year','=',$value);
                    break;
                case 'month':
                    $query->where('month','=',$value);
                    break;
                case 'admin_id':
                    $query->where('coach_id','=', $value);
                    break;
                case 'coach_truename':
                    $coach_ids = Admin::where('admin_name', 'like', "%$value%")
                        ->pluck('id');
                    $query->whereIn('coach_id', $coach_ids);
                    break;
                default:
                    break;
            }
        }
        return $query;
    }

    /*
     * @Des:关联教练每日数据
     * */
    public function statisticslist()
    {
        return $this->hasMany(CoachStatisticsList::class,'statistic_id');
    }

    /*
     * @Des:关联教练模型
     * */
    public function admin()
    {
        return $this->belongsTo(Admin::class,'coach_id');
    }

}
