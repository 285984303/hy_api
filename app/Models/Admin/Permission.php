<?php namespace App\Models\Admin;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 5/30/16
 * Time: 2:23 PM
 */

use App\Models\NotFound;
use App\Models\StoreError;
use Zizaco\Entrust\EntrustPermission;

/**
 * Class Permission
 * @package App\Models\Admin
 * @property $id           string
 * @property $name         string
 * @property $display_name string
 * @property $description  string
 * @property $permission_group_id int
 *
 * @mixin \Eloquent|\Zizaco\Entrust\EntrustPermission
 */
class Permission extends EntrustPermission {
    protected $guarded = ['id'];

    public static function getPermissions($options = [], $perPage = 10)
    {
        $query = new self;
        foreach ($options as $key=>$value) {
            switch ($key) {
                case 'display_name' :
                    $query = $query->where($key, 'like', "%$value%");
                    break;
                default:
                    $query = $query->where($key, $value);
                    break;
            }
        }
        $permissions = $query->paginate($perPage);

        if (!$permissions->count()) {
            throw new NotFound;
        }

        return $permissions;
    }

    public function find($id, $columns = ['*'])
    {
        $permission = parent::find($id, $columns);
        if (!$permission) {
            throw new NotFound;
        }

        return $permission;
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

    public function permission_group(){
        return $this->belongsTo('App\Models\Admin\PermissionGroup');
    }
}