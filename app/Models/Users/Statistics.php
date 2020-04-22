<?php
/*
 * @Des:    学时model
 * @Date:   2017-08-09
 * @Author: Joe
 * */
namespace App\Models\Student;

use Illuminate\Database\Eloquent\Model;

class Statistics extends Model
{
    protected $table   = 'statistics_hours';

    public static function all(){
        return self::all();
    }
}
