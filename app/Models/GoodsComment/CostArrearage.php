<?php
/*
 * @File: CostArrearage.php
 * @Des:  学员欠费处理model
 * @Author: Joe
 * @Date: 2017.10.11
 **/
namespace App\Models\Cost;

use App\Models\Admin\Admin;
use App\Models\Student\ChangePackages;
use App\Models\Student\StudentAccout;
use Illuminate\Database\Eloquent\Model;
use App\Models\Home\User;
use App\Models\Data\PayType;
use App\Models\Data\IncomeType;
use App\Models\BaseModel;
use App\Models\Packages\Packages;
use App\Models\Finance\Preferential;


class CostArrearage extends BaseModel
{
    protected $table = 'c_cost_arrearage';

    /*
     * @Des: 数据添加 返回插入ID
     * */
    public static function costArrearageAdd($parms=array()){
       return self::insertGetId($parms);
    }

    /*
     * @Des: 数据添加 返回对象及执行成功数据
     * */
    public static function costArrearageCreate($parms=array()){
        return self::create($parms);
    }

    /*
    * @Des: 数据删除
    * */
    public static function costArrearageDelete(){

    }

    /*
    * @Des: 数据修改
    * */
    public static function costArrearageUpdate($id,$parms=array()){
        return self::where(array('id'=>$id))->update($parms);
    }

    /*
    * @Des: 数据列表分页查询
    * */
    public static function costArrearageSelectPage($school_id,$parms= array(),$orderfileds='created_at',$orderby='DESC'){

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
                case 'pay_type':
                    $query->where('pay_type', '=', $value);
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
     * @Des:关联学员模型
     * */
    public function user()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
    /*
     * @Des:关联支付类型模型
     * */
    public function pay_type()
    {
        return $this->belongsTo(PayType::class);
    }
    /*
     * @Des:关联欠费类型模型
     * */
    public function income_type()
    {
        return $this->belongsTo(IncomeType::class,'expense_type');
    }

    /*
     * @Des:关联欠费类型模型
     * */
    public function package()
    {
        return $this->belongsTo(Packages::class,'package_id');
    }

    /*
     * @Des:关联员工模型
     * */
    public function staffinfo()
    {
        return $this->belongsTo(Admin::class,'deal_id');
    }

    /*
     * @Des:关联学员账户
     * */
    public function studentAccount()
    {
        return $this->hasOne(StudentAccout::class,'student_id','student_id');
    }

    /*
     * @Des:关联退费操作记录表
     * */
    public function userrecord()
    {
        //packages_change_record

        return $this->belongsTo(ChangePackages::class,'relation_id');
    }

    public function canUsePreferential($school_id)
    {
        return Preferential::getValid($school_id);
    }



}
