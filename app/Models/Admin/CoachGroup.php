<?php namespace App\Models\Admin;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/3/16
 * Time: 11:15 AM
 */
use App\Models\BaseModel;

/**
 * Class CoachGroup
 * @package App\Models\Admin
 * @property $name      string
 * @property $school_id integer
 */
class CoachGroup extends BaseModel {
    protected $table   = 'coach_group';

    public static function getGroups($school_id){
        return self::where('school_id',$school_id)->get();
    }

    public function coaches(){
        return $this->hasMany(Admin::class);
    }

    public function leader(){
        return $this->belongsTo(Admin::class,'leader_id');
    }

    // /**
    //  * @param array $attributes
    //  *
    //  * @return static
    //  */
    // public static function create(array $attributes = [])
    // {
    //     $coach_ids = $attributes['coach_ids'];
    //     unset($attributes['coach_ids']);
    //
    //     $group = parent::create($attributes);
    //
    //     Admin::whereIn('id',$coach_ids)->update([self::getForeignKey() => $group->id]);
    //     return $group;
    // }
    //
    // /**
    //  * @return bool
    //  * @throws \App\Models\StoreError
    //  * @throws \Exception
    //  */
    // public function delete()
    // {
    //     $this->coaches()->update([self::getForeignKey() => NULL]);
    //     return parent::delete();
    // }
    //
}