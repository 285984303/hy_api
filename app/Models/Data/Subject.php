<?php namespace App\Models\Data;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/7/16
 * Time: 5:26 PM
 */
use Illuminate\Database\Eloquent\Model;

class Subject extends Model {
    protected $table   = 'subject';
    protected $guarded = ['id'];
}