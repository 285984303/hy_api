<?php namespace App\Models\Admin;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/6/16
 * Time: 5:28 PM
 */
use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminAppointmentType
 * @package App\Models\Admin
 * @property $admin_id
 * @property $appointment_type_id
 */
class AdminAppointmentType extends Model {
    protected $table    = 'admin_appointment_type';
    protected $fillable = ['admin_id', 'appointment_type_id'];
    public $timestamps = false;
}