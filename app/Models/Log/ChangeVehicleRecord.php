<?php namespace App\Models\Log;

use App\Models\Admin\Admin;
use App\Models\BaseModel;
use App\Models\Vehicle\Vehicle;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/17/16
 * Time: 5:22 PM
 */

/**
 * Class ChangeVehicleRecord
 * @package App\Models\Log
 * @property $admin_id
 * @property $old_vehicle_id
 * @property $new_vehicle_id
 * @property $reason
 * @property $handle_admin_id
 * @property $school_id
 * @property-read $new_car_num
 * @property-read $old_car_num
 * @property-read $admin_name
 * @property-read $hadmin_name
 * @property-read $vehicle_status
 */
class ChangeVehicleRecord extends BaseModel
{
    protected $table = 'change_vehicle_record';

    public static function getRecords($school_id, $options = [], $perPage = 10)
    {
        $model = new self;
        $model->setTable('change_vehicle_record');
        $query = $model->where('school_id', $school_id);
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'car_num':
                    /*根据名称检索 车号id*/
                    $query->where(function ($query) use ($value) {
                        $vehicle_ids = Vehicle::GetVehicleIds($value);
                        $query->whereIn('old_vehicle_id', $vehicle_ids);
                        $query->orwhereIn('new_vehicle_id', $vehicle_ids);
                    });
                    break;
                case 'startdate':
                    /* $query->whereBetween('created_at', [$value.' 00:00:00', $value.' 23:59:59']);*/
                    $query->where('created_at', '>=', $value . ' 00:00:00');
                    break;
                case 'enddate':
                    /* $query->whereBetween('created_at', [$value.' 00:00:00', $value.' 23:59:59']);*/
                    $query->where('created_at', '<=', $value . ' 23:59:59');
                    break;
                case 'name':
                    /*检索员工 ids*/
                    $admin_ids = Admin::GetAdminIds($value);
                    $query->whereIn('admin_id', $admin_ids);
                    break;
                default:
                    $query = $query->where($key, $value);
                    break;
            }
        }
        $records = $query->orderBy('created_at', 'desc')->paginate($perPage);

//        $model->setTable('change_vehicle_record');
        return $records;
    }

    public function new_vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'new_vehicle_id');
    }

    public function old_vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'old_vehicle_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function handle_admin()
    {
        return $this->belongsTo(Admin::class, 'handle_admin_id');
    }
}
