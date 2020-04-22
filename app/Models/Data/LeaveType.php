<?php namespace App\Models\Data;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/1/16
 * Time: 6:24 PM
 */

use App\Models\NotFound;
use App\Models\StoreError;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OilGasType
 * @package App\Models\Data
 * @property $name string
 */
class LeaveType extends Model {
    protected $table   = 'leave_type';
    protected $guarded = ['id'];

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