<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class CoachStatisticsList extends Model
{
    protected $table   = 'c_coach_statistics_list';
    
    
    public static function GetCoachStatisticsList($options=[])
    {
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
}
