<?php namespace App\Models\Admin;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/6/16
 * Time: 5:28 PM
 */
use App\Models\Appointment\AppointmentType;
use App\Models\NotFound;
use App\Models\Vehicle\Vehicle;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminAppointmentType
 * @package App\Models\Vehicle
 * @property $admin_id
 * @property $vehicle_id
 * @property $appointment_type_id
 * @property $school_id
 */
class AdminVehicleAppointmentType extends Model {
    protected $table = 'admin_vehicle_appointment_type';

    public static function adminKey() {
        return Admin::getForeignKey();
    }

    public static function vehicleKey() {
        return Vehicle::getForeignKey();
    }

    public static function appointmentTypeKey() {
        return AppointmentType::getForeignKey();
    }

    public static function table(){
        return parent::getTable();
    }

    public function save(array $options = [])
    {
        // parent::save();
        return FALSE;
    }

    public function getCoaches($school_id, $appointment_type_id, $perPage = 10)
    {
        $result = $this->where('school_id', $school_id)
                       ->where('appointment_type_id', $appointment_type_id)
                       ->paginate($perPage);
        if (!$result->count()) {
            throw new NotFound();
        }

        return $result;
    }
}