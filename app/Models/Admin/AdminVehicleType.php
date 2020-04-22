<?php namespace App\Models\Admin;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/6/16
 * Time: 5:28 PM
 */
use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminVehicleType
 * @package App\Models\Admin
 * @property $admin_id
 * @property $vehicle_type_id
 */
class AdminVehicleType extends Model {
    protected $table    = 'admin_vehicle_type';
    protected $fillable = ['admin_id', 'vehicle_type_id'];
}