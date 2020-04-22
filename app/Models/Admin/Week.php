<?php namespace App\Models\Admin;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 5/27/16
 * Time: 3:54 PM
 */

use App\Models\BaseModel;
use App\Models\Admin\Admin;

class Week extends BaseModel
{
    protected $table = 'admin_week';
    public $timestamps = false;
}