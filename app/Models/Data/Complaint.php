<?php namespace App\Models\Data;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/3/16
 * Time: 2:18 PM
 */

use App\Models\NotFound;
use App\Models\StoreError;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Complaint
 * @package App\Models\Data
 * @property $user_id          integer
 * @property $type             string  SCHOOL OR COACH 投诉对象
 * @property $type_id          integer  id
 * @property $complaint_reason string
 */
class Complaint extends Model {
    protected $table   = 'complaint';
    protected $guarded = ['id'];

    const TYPE_CERTIFICATE = 0;
    const TYPE_SCENE       = 1;

    const TYPE_COACH      = 'COACH';      // 教练
    const TYPE_SCHOOL     = 'SCHOOL';     // 驾校

    public function getComplaints($perPage = 10)
    {
        $complaints = $this->paginate($perPage);

        if (!$complaints->count()) {
            throw new NotFound;
        }

        return $complaints;
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
        $complaint = parent::find($id, $columns);
        if (!$complaint) {
            throw new NotFound();
        }

        return $complaint;
    }

    public function delete()
    {
        if (!parent::delete()) {
            throw new StoreError();
        }

        return TRUE;
    }
}