<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 8/18/16
 * Time: 4:01 PM
 */

namespace App\Models\Student;

use App\Library\Http;
use App\Models\Admin\Admin;
use App\Models\Appointment\Appointment;
use App\Models\BaseModel;
use App\Models\Data\School;
use App\Models\Home\User;
use App\Models\Packages\Packages;
use Behat\Mink\Exception\Exception;
use Doctrine\DBAL\Driver\IBMDB2\DB2Driver;

class ChangePackages extends BaseModel
{
    protected $table = 'packages_change_record';

//    public $timestamps = false;

    /**
     * @desc 获取更换套餐记录
     * @param $school_id
     * @param array $options
     * @param bool $useView
     * @return $this
     */
    public static function getChangePackageQuery($school_id, $options = [], $useView = false)
    {
        $query = self::where('school_id', $school_id);
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'user_truename':
                    $query_user = $query_user??User::query();
                    $query_user->where($key, 'like', "%$value%");
                    break;
                case 'user_telphone':
                case 'id_card':
                    $query_user = $query_user??User::query();
                    $query_user->where($key, $value);
                    break;
                case 'licence_type':
                case 'new_product_id':
                case 'change_date':
                    $query->where($key, $value);
                    break;
                case 'handle_name':
                    $query_admin = $query_admin??Admin::query();
                    $query_admin->where('admin_name', $value);
                    break;
                case 'start_date':
                    $query->where('change_date', '>=', $value);
                    break;
                case 'end_date':
                    $query->where('change_date', '<=', $value);
                    break;
                default:
//                    $query ->where($key, $value);
                    break;
            }
        }

        if (isset($query_user)) {
            $user_ids = $query_user->pluck('id');
            $query->whereIn('user_id', $user_ids);
        }
        if (isset($query_admin)) {
            $admin_ids = $query_admin->pluck('id');
            $query->whereIn('handle_id', $admin_ids);
        }
        $query->distinct();
        return $query;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'handle_id', 'id');
    }

    public function old_product()
    {
        return $this->belongsTo(Packages::class, 'old_product_id', 'id');
    }

    public function new_product()
    {
        return $this->belongsTo(Packages::class, 'new_product_id', 'id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

}