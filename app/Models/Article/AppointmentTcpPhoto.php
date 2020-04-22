<?php namespace App\Models\Appointment;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/3/16
 * Time: 11:15 AM
 */

use App\Models\BaseModel;

class AppointmentTcpPhoto extends BaseModel
{
    protected $table = 'appointment_tcp_photo';

    public static function getSchoolIdByPhotoNum($num)
    {
        $date = date("Y-m-d");
        return self::where('photo_number', $num)
            ->where('create_date', '>=', $date)
            ->where('create_date', '<=', $date . ' 23:59:59')
            ->select('school_id')
            ->first();
    }

    /**
     * @desc 校验终端照片编号是否重复
     * @param $photo_id
     */
    public static function checkTcpPhotoExists($photo_id)
    {
        return self::where('photo_number', $photo_id)
            ->where('create_date', '>=', date("Y-m-d"))
            ->where('create_date', '<=', date("Y-m-d") . ' 23:59:59')
            ->count();
    }

}