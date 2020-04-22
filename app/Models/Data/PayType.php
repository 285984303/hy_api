<?php namespace App\Models\Data;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/3/16
 * Time: 11:15 AM
 */
use App\Models\BaseModel;

/**
 * Class PayType
 * @package App\Models\Data
 * @property $name      string
 * @property $type      string
 * @property $school_id integer
 */
class PayType extends BaseModel {
    protected $table   = 'pay_type';

    const TYPE_ONLINE  = 'ONLINE';
    const TYPE_OFFLINE = 'OFFLINE';
    const TYPE_BALANCE = 'BALANCE';
}