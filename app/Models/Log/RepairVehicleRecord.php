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
 * @property $vehicle_id
 * @property $reason
 * @property $handle_admin_id
 * @property $school_id
 * @property $who_repair
 * @property $resolve
 * @property $status
 * @property-read $new_car_num
 * @property-read $old_car_num
 * @property-read $admin_name
 * @property-read $hadmin_name
 * @property-read $vehicle_status
 */
class RepairVehicleRecord extends BaseModel
{
    protected $table = 'repair_vehicle_record';

    const STATUS_REPAIRING = 'REPAIRING';
    const STATUS_DONE = 'DONE';

    public static function getStatusEnum()
    {
        return [
            self::STATUS_REPAIRING => '维修中',
            self::STATUS_DONE => '已完成'
        ];
    }

    public function getStatus()
    {
        return self::getStatusEnum()[$this->status];
    }

    public static function getRecords($school_id, $options = [], $perPage = 10)
    {
        $model = new self;
        $model->setTable('repair_vehicle_record');
        $query = $model->where('school_id', $school_id)->with('vehicle');

        foreach ($options as $key => $value) {
            switch ($key) {
                case 'car_num':
                    $query_vehicle = $query_vehicle??Vehicle::query();
                    $query_vehicle->where('car_num', 'like', "%$value%");
                    break;
                case 'start_date':
                    $query->where('created_at', '>=', $value . ' 00:00:00');
                    break;
                case 'finish_date':
                    $query->where('created_at', '<=', $value . ' 23:59:59');
                    break;
                case 'name':
                    $query_admin = $query_admin??Admin::query();
                    $query_admin->where('admin_name', 'like', "%$value%");
                    break;
                case 'status':
                    $query->where('status', $value);
                    break;
                default:
                    $query = $query->where($key, $value);
                    break;
            }
        }

        if (isset($query_vehicle)) {
            $vehicle_ids = $query_vehicle->pluck('id');
            $query->whereIn('vehicle_id', $vehicle_ids);
        }
        if (isset($query_admin)) {
            $admin_ids = $query_admin->pluck('id');
            $query->whereIn('admin_id', $admin_ids);
        }

        $records = $query->orderBy('id', 'desc')->paginate($perPage);
//        $model->setTable('repair_vehicle_record');
        return $records;
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
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