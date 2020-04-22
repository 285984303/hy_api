<?php namespace App\Models\Admin;

use App\Models\NotFound;
use App\Models\StoreError;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;


/**
 * Class PermissionGroup
 * @package App\Models\Admin
 * @property $id
 * @property $name
 * @property $permissions Permissions[]
 */
class PermissionGroup extends Model {
    protected $table   = 'permission_group';
    protected $guarded = ['id'];

    /**
     * @return self[]|Collection
     * @throws NotFound
     */
    public static function getGroups()
    {
        $groups = self::all();

//        if (!$groups->count()) {
//            throw new NotFound;
//        }

        return $groups;
    }

    public function find($id, $columns = ['*'])
    {
        $group = parent::find($id, $columns);
        if (!$group) {
            throw new NotFound;
        }

        return $group;
    }

    public function save(array $options = [])
    {
        if (!parent::save($options)) {
            throw new StoreError;
        }

        return TRUE;
    }

    public function delete()
    {
        if (!parent::delete()) {
            throw new StoreError();
        }

        return TRUE;
    }

    public function permissions(){
        return $this->hasMany('App\Models\Admin\Permission');
    }
}