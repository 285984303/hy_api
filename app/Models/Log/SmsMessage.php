<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 30/09/2016
 * Time: 10:35 AM
 */

namespace App\Models\Log;


use App\Models\Admin\Admin;
use App\Models\BaseModel;
use App\Models\Home\User;

class SmsMessage extends BaseModel {
    protected $table = 'sms_log';

    public static function log($content, $users) {
        try {
            $now = date('Y-m-d H:i:s');
            $logs = [];
            foreach ($users as $user) {
                $log = [
                    'handle_admin_id' => auth('admin')->user()->id,
                    'school_id'       => auth('admin')->user()->school_id,
                    'time'            => $now,
                    'content'         => $content,
                    'user_id'         => $user->id,
                    'mobile'          => $user->user_telphone,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];
                $logs[] = $log;
            }

            self::insert($logs);
        } catch (\Exception $e){
            // throw $e;
        }
    }

    public static function getLogsQuery($school_id,$options = []) {
        $query = self::where('school_id',$school_id);
        foreach ($options as $key=>$value) {
            switch ($key) {
            case 'admin_name' :
                $admin_ids = Admin::where('admin_name', 'like', "%$value%")
                                  ->where('school_id', $school_id)
                                  ->pluck('id');
                $query->whereIn('handle_admin_id',$admin_ids);
                break;
            case 'user_truename' :
            case 'id_card' :
            case 'user_telphone' :
                $query_user = $query_user??User::query();
                $query_user->where($key, 'like', "%$value%");
                break;
            case 'start_date' :
                $query->where('time','>=',$value.' 00:00:00');
                break;
            case 'finish_date' :
                $query->where('time','<=',$value.' 23:59:59');
                break;
            default:
                $query->where($key,$value);
                break;
            }
        }
        if (isset($query_user))
            $query->whereIn('user_id',$query_user->distinct()->pluck('id'));

        return $query->orderBy('time','desc');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function handle_admin(){
        return $this->belongsTo(Admin::class);
    }
}