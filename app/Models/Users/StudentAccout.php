<?php

namespace App\Models\Student;

use App\Models\BaseModel;
use App\Models\Packages\Packages;
use Illuminate\Database\Eloquent\Model;
use App\Models\Home\User;
use App\Models\Business\UserProduct;

class StudentAccout extends BaseModel
{
    protected $table = 'c_student_account';

    /*
     * @Des: 数据添加 返回插入ID
     * */
    public static function accountAdd($parms=array()){
        return self::insertGetId($parms);
    }
    /*
    * @Des: 数据列表分页查询
    * */
    public static function accountSelectPage($school_id,$parms= array(),$orderfileds='updated_at',$orderby='DESC'){

        $query = self::where('school_id','=',$school_id);
        foreach ($parms as $key => $value) {
            switch ($key) {
                case 'is_paid':
                    $query->whereIn($key,$value);
                    break;
                case 'name':
                    $user_ids = User::where('user_truename', 'like', "%$value%")->pluck('id');
                    $query->whereIn('student_id', $user_ids);
                    break;
                case 'id_card':
                    $user_ids = User::where('id_card', 'like', "%$value%")->pluck('id');
                    $query->whereIn('student_id', $user_ids);
                    break;
                case 'phone':
                    $user_ids = User::where('user_telphone', 'like', "%$value%")->pluck('id');
                    $query->whereIn('student_id', $user_ids);
                    break;
                case 'status':
                    $query->where('status', '=', $value);
                    break;
                case 'date':
                    $query->where('arrears_time', '>=',$value);
                    break;
                case 'paydate':
                    $query->where('updated_at', '>=',$value);
                    break;
                case 'income_type_id':
                    $query->where('expense_type', '=',$value);
                    break;
                case 'start_date':
                    $query->where('updated_at', '>=', "$value 00:00:00");
                    break;
                case 'finish_date':
                    $query->where('updated_at', '<=', "$value 23:59:59");
                    break;
                default:
                    break;
            }
        }
        $query->orderby($orderfileds,$orderby);
        return $query;
    }
    /*
     * @Des:关联欠费类型模型
     * */
    public function package()
    {
        return $this->belongsTo(Packages::class,'package_id');
    }
    /*
     * @Des:关联学员模型
     * */
    public function user()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
    /*
    * @Des:关联学员模型
    * */
    public function userproduct()
    {
        return $this->hasOne(UserProduct::class,'user_id','student_id');
    }
}
