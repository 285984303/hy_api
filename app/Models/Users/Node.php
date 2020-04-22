<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/18/16
 * Time: 4:01 PM
 */

namespace App\Models\Student;


use App\Models\BaseModel;
use App\Models\Home\User;

class Node extends BaseModel {
    protected $table = 'node';

    public static function set(User $user,Stage $stage, $content){
        $attributes = [
            'user_id' => $user->id,
            'stage_id' => $stage->id,
            'content'  => $content,
        ];
        return parent::create($attributes);
    }
}