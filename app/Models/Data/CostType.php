<?php namespace App\Models\Data;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/3/16
 * Time: 11:15 AM
 */
use App\Models\NotFound;
use App\Models\StoreError;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CostType
 * @package App\Models\Data
 * @property $cost_type_name      string
 * @property $school_id           integer
 */
class CostType extends Model {
    protected $table   = 'cost_type';
    protected $guarded = ['id'];

    const TYPE_CERTIFICATE = 0;
    const TYPE_SCENE       = 1;

    public function getTypes($perPage = 10)
    {
        $types = $this->paginate($perPage);

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
}