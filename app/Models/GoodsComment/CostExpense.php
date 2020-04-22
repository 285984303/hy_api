<?php

namespace App\Models\Cost;

use App\Models\Packages\Packages;
use Illuminate\Database\Eloquent\Model;
use App\Models\Home\User;
use App\Models\Admin\Admin;
class CostExpense extends Model
{
    protected $table = 'c_cost_expense';
    /*
     * @Des: 数据添加
     * */
    public static function costExpenseAdd($parms=array()){
        return self::insertGetId($parms);
    }

    /*
     * @Des:分页leftjoin 查询
     * */
    public static function costExpenseSelectPage($school_id,$parms= array(),$orderfileds='created_at',$orderby='DESC'){
        $query = self::from('c_cost_expense as a')->select('a.*','b.package_name','b.student_name','b.student_card','b.package_id','b.expense_type')->leftJoin('c_cost_arrearage as b','b.id','=','a.arrearage_id');
        $query->where('b.school_id','=',$school_id);
        foreach ($parms as $key => $value) {
            switch ($key) {
                case 'student_id':
                    $query->where('a.'.$key,'=',$value);
                    break;
                case 'startdate':
                    $query->where('a.created_at','>=',$value);
                    break;
                case 'enddate':
                    $query->where('a.created_at','<=',$value." 23:59:59");
                    break;
                case 'expense_type':
                    $query->whereIn('b.'.$key,$value);
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
     * @Des:关联员工模型
     * */
    public function staffinfo()
    {
        return $this->belongsTo(Admin::class,'deal_id');
    }




    /*
     * @Des:关联用户类型模型
     * */
    public function getAdminInfo()
    {
        return $this->belongsTo(Admin::class,'deal_id');
    }
}
