<?php namespace App\Models\Data;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/3/16
 * Time: 11:15 AM
 */
use App\Models\Finance\Expenditure;
use App\Models\NotFound;
use App\Models\StoreError;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CostType
 * @package App\Models\Data
 * @property $name      string
 * @property $school_id           integer
 */
class ExpenditureType extends Model {
    protected $table   = 'expenditure_type';
    protected $guarded = ['id'];

    public static function getTypes()
    {
        $types = self::all();

        if (!$types->count()) {
            throw new NotFound;
        }

        return $types;
    }

    public function save(array $options = [])
    {
        if (!parent::save($options)) {
            throw new StoreError;
        }

        return TRUE;
    }

    public function find($id, $columns = ['*'])
    {
        $type = parent::find($id, $columns);
        if (!$type) {
            throw new NotFound();
        }

        return $type;
    }

    public function delete()
    {
        if (!parent::delete()) {
            throw new StoreError();
        }

        return TRUE;
    }

    public function expenditures(){
        return $this->hasMany(Expenditure::class);
    }
}