<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 7/19/16
 * Time: 10:28 AM
 */

namespace App\Models\Log;


use App\Models\Admin\Admin;
use App\Models\BaseModel;

/**
 * Class Login
 * @package App\Models\Log
 * @property $admin_id
 * @property $ip
 * @property $time
 * @property $school_id
 */
class Login extends BaseModel {
    protected $table = 'login_log';

    public static function log(Admin $admin){
        $log = [
            'admin_id' => $admin->id,
            'ip' => request()->ip(),
            'school_id' => $admin->school_id,
            'time' => date('Y-m-d H:i:s')
        ];
        return self::create($log);
    }

    public static function getLogs($school_id,$options = []) {
        $query = self::where('school_id',$school_id);
        foreach ($options as $key=>$value) {
            switch ($key) {
            case 'name' :
                $admin_ids = Admin::where('admin_name', 'like', "%$value%")
                                          ->where('school_id', $school_id)
                                          ->pluck('id');
                $query->whereIn('admin_id',$admin_ids);
                break;
            case 'date' :
                $query->whereBetween('time',[$value.' 00:00:00', $value.' 23:59:59']);
                break;
            default:
                $query->where($key,$value);
                break;
            }
        }
        return $query->orderBy('time','desc')->paginate();
    }

    public function admin(){
        return $this->belongsTo(Admin::class);
    }
}
