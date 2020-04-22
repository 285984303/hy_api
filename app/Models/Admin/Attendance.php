<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 7/18/16
 * Time: 11:43 AM
 */

namespace App\Models\Admin;

use App\Models\BaseModel;

/**
 * Class Attendance
 * @package App\Models\Admin
 * @property $admin_id
 * @property $date
 * @property $status
 * @property $school_id
 */
class Attendance extends BaseModel {
    protected $table = 'admin_attendance';

    const STATUS_CHECKED = 'CHECKED'; //已签到
    const STATUS_LEAVED  = 'LEAVED'; // 请假
    const STATUS_FREE    = 'FREE';  // 休息

    public static function getAttendanceCount4Month($admin_id,$year,$month){
        $query = self::where('admin_id',$admin_id);
        $query->where('date','>=',sprintf('%04d-%02d-01',$year,$month));
        if ($month<12) {
            $month++;
        } else {
            $year++;
            $month=1;
        }
        $query->where('date','<',sprintf('%04d-%02d-01',$year,$month));
        return $query->where('status',self::STATUS_CHECKED)->count();
    }

    public static function getAttendance4Month($admin_id,$year,$month){
        $query = self::where('admin_id',$admin_id);
        $query->where('date','>=',sprintf('%04d-%02d-01',$year,$month));
        if ($month<12) {
            $month++;
        } else {
            $year++;
            $month=1;
        }
        $query->where('date','<',sprintf('%04d-%02d-01',$year,$month));
        return $query->where('status',self::STATUS_CHECKED)->get();
    }

    public static function signIn($admin) {
        $attributes = [
            'admin_id' => $admin->id,
            'date' => date('Y-m-d'),
            'school_id' => $admin->school_id
        ];
        $values['status'] = self::STATUS_CHECKED;
        self::updateOrCreate($attributes, $values);
    }

}