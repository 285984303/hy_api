<?php

namespace App\Models\Data;

use Illuminate\Database\Eloquent\Model;

class NotificationRecord extends Model
{
    protected $table   = 'notification_record';
    public    $timestamps = false;

    /*
     * @Des:读全部数据取数据
     * */
    public static function GetNotificationRecord($where=array()){
        return self::where($where)->get();
    }

    /*
     * @Des:修改数据
     * */
    public static function UpdateNotificationRecord(){

    }
}
