<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/7/16
 * Time: 4:12 PM
 */

namespace App\Models\Log;


use App\Models\BaseModel;

/**
 * Class BusAppointment
 * @package App\Models\Log
 * @property $bus_id
 * @property $bus_time
 * @property $user_id
 * @property $date
 */
class BusAppointment extends BaseModel{
    protected $table = 'bus_appointment';
    protected $rules   = [
        'bus_id' => 'required|exists:bus,vehicle_id',
        'user_id' => 'required|exists:user,id',
        'date' => 'required|date_format:Y-m-d',
        'time' => 'required|date_format:H:i',
        'school_id' => 'required|exists:school,id',
    ];

    public static function appointment($bus, $user, $date, $time) {
        $appointment            = new self();
        $appointment->bus_id    = $bus->id;
        $appointment->user_id   = $user->id;
        $appointment->school_id = $bus->school_id;
        $appointment->date      = $date;
        $appointment->time      = $time;
        $appointment->save();
        return $appointment;
    }

    public static function getList($school_id, $date) {
        return self::where('school_id', $school_id)
                   ->where('date', $date)
                   ->orderBy('time')
                   ->get();
    }

    // public static function list($school_id, $date){
    //     $query = self::where('school_id',$school_id);
    //     $query->where('date',$date);
    //     return $query->paginate();
    // }
}
