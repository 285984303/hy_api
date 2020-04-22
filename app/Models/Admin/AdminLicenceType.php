<?php namespace App\Models\Admin;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/6/16
 * Time: 5:28 PM
 */
use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminLicenceType
 * @package App\Models\Admin
 * @property $admin_id
 * @property $licence_type_id
 */
class AdminLicenceType extends Model {
    protected $table    = 'admin_licence_type';
    protected $fillable = ['admin_id', 'licence_type_id'];
}