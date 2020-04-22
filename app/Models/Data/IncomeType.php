<?php namespace App\Models\Data;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/3/16
 * Time: 11:15 AM
 */
use App\Models\BaseModel;
use App\Models\Finance\Income;
use App\Models\NotFound;

/**
 * Class CostType
 * @package App\Models\Data
 * @property $name      string
 * @property $type      string
 * @property $school_id           integer
 */
class IncomeType extends BaseModel {
    protected $table   = 'income_type';

    const TYPE_APPOINTMENT = 'APPOINTMENT';
    const TYPE_EXAM        = 'EXAM';
    const TYPE_SITE        = 'SITE';
    const TYPE_REGISTER    = 'REGISTER';
    const TYPE_CHARGE      = 'CHARGE';
    const TYPE_OTHER       = 'OTHER';

    // public static function getTypes($school_id)
    // {
    //     $types = self::where('school_id',$school_id)->get();
    //
    //     if (!$types->count()) {
    //         throw new NotFound;
    //     }
    //
    //     return $types;
    // }

    public static function findByType($type){
        $row = self::where('type',$type)->first();
        if ($row) {
            return $row;
        } else {
            throw new NotFound('收入类型"'.$type.'"不存在');
        }
    }

    public function incomes(){
        return $this->hasMany(Income::class);
    }

}