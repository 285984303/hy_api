<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/18/16
 * Time: 4:01 PM
 */

namespace App\Models\Student;


use App\Models\BaseModel;
use App\Models\NotFound;

class Stage extends BaseModel {
    protected $table = 'stage';

    public static function findByName($name, $columns = ['*']) {
        $stage = self::where('name',$name)->first($columns);
        if (!$stage)
            throw new NotFound('没有找到节点'.$name);
        return $stage;
    }
}