<?php namespace App\Models\Admin;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 5/30/16
 * Time: 2:22 PM
 */

use App\Models\NotFound;
use App\Models\ParameterError;
use App\Models\StoreError;
use Zizaco\Entrust\EntrustRole;

/**
 * Class Role
 * @package App\Models\Admin
 * @property $name         string
 * @property $display_name string
 * @property $description  string
 * @property $school_id
 * @mixin \Eloquent|\Zizaco\Entrust\EntrustRole
 */
class Role extends EntrustRole {

    protected $protectedRoles = ['admin','human','coach','finance','administration','superadmin'];
    protected $guarded        = ['id'];

    public static function getRoles($school_id, $perPage = 10)
    {
        $roles = self::where('school_id', $school_id)->paginate($perPage);

//        if (!$roles->count()) {
//            throw new NotFound;
//        }

        return $roles;
    }

    public static function getAllRoles($school_id)
    {
        $roles = self::where('school_id', $school_id)->get();

//        if (!$roles->count()) {
//            throw new NotFound;
//        }

        return $roles;
    }

    /**
     * @param       $id
     * @param array $columns
     *
     * @return self
     * @throws NotFound
     */
    public static function find($id, $columns = ['*'])
    {
        $role = self::where('id',$id)->first($columns);
        if (!$role) {
            throw new NotFound;
        }

        return $role;
    }

    /**
     * @param $name
     * @param $school_id
     *
     * @return self
     * @throws NotFound
     */
    public static function getByName($name, $school_id)
    {
        $role = self::where('name', $name)->where('school_id', $school_id)->first();
        if (!$role) {
            throw new NotFound;
        }

        return $role;
    }

    public function save(array $options = [])
    {
        $this->validator();
        $this->isThisProtected();
        if (!parent::save($options)) {
            throw new StoreError;
        }

        return TRUE;
    }

    protected function validator(){
        $result = $this->where('school_id', $this->school_id)->where('display_name',$this->display_name)->where('id','!=',$this->id)->count();
        if ($result) {
            throw new ParameterError('此用户组已存在');
        }
    }

    public function delete(array $options = [])
    {
        $this->isThisProtected();
        if (!parent::delete($options)) {
            throw new StoreError();
        }

        return TRUE;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|\Illuminate\Database\Eloquent\Builder
     */
    public function admins()
    {
        return $this->belongsToMany(
            'App\Models\Admin\Admin',
            config('entrust.role_user_table'),
            config('entrust.role_foreign_key'),
            config('entrust.user_foreign_key')
        );
    }

    public function isThisProtected()
    {
        if (in_array($this->name, $this->protectedRoles)) {
            throw new \Exception('this role is protected');
        }
    }

    public function users()
    {
        return $this->belongsToMany(
            'App\Models\Admin\Admin',
            config('entrust.role_user_table'),
            config('entrust.role_foreign_key'),
            config('entrust.user_foreign_key')
        );
    }


    /**
     * Save the inputted permissions.
     *
     * @param mixed $inputPermissions
     *
     * @return void
     */
    public function savePermissions($inputPermissions)
    {
        $this->isThisProtected();
        parent::savePermissions($inputPermissions);
    }

    /**
     * Attach permission to current role.
     *
     * @param object|array $permission
     *
     * @return void
     */
    public function attachPermission($permission)
    {
        $this->isThisProtected();
        parent::attachPermission($permission);
    }

    /**
     * Detach permission from current role.
     *
     * @param object|array $permission
     *
     * @return void
     */
    public function detachPermission($permission)
    {
        $this->isThisProtected();
        parent::detachPermission($permission);
    }
}
