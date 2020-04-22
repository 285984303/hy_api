<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/13/16
 * Time: 5:47 PM
 */

namespace App\Models\Home;


use App\Models\BaseModel;
use App\Models\ParameterError;
use App\Models\Student\Node;
use App\Models\Student\Stage;
use Excel;

/**
 * Class Examination
 * @package App\Models\Home
 * @property $user_id
 * @property $date
 * @property $score
 * @property $subject
 * @property $school_id
 */
class Examination extends BaseModel{
    protected $table = 'examination';

    const SUBJECT_1 = 1;
    const SUBJECT_2 = 2;
    const SUBJECT_3 = 3;
    const SUBJECT_4 = 4;

    public static $subjects = [
        self::SUBJECT_1 => '科目一',
        self::SUBJECT_2 => '科目二',
        self::SUBJECT_3 => '科目三',
        self::SUBJECT_4 => '科目四',
    ];

    public static function getSubjects() {
        return self::$subjects;
    }

    public function getSubject(){
        return self::$subjects[$this->subject];
    }
    
    public static function getList($school_id, $options = []) {
        $query = self::where('school_id', $school_id);
        foreach ($options as $key => $value) {
            switch ($key) {
            case 'user_truename' :
            case 'id_card' :
            case 'user_telphone' :
                $user_ids = User::where($key,'like',"%$value%")->pluck('id');
                $query->whereIn('user_id',$user_ids);
                break;
            case 'gender' :
                $user_ids = User::where('user_sex', $value)->pluck('id');
                $query->whereIn('user_id',$user_ids);
                break;
            default :
                $query->where($key, $value);
            }
        }
        $list = $query->orderBy('date','desc')->paginate();

        return $list;
    }
    

    public static function importFromExcel($school_id, $subject, $path)
    {
        \DB::beginTransaction();

        // 地区敏感
        // setlocale(LC_ALL, 'zh_CN');
        /**
         * @var $cells \Maatwebsite\Excel\Collections\SheetCollection
         */
        $sheets = Excel::load($path)->get();
        $rows = [];
        foreach ($sheets as $cells) {
            /**
             * @var $cells \Maatwebsite\Excel\Collections\RowCollection
             */
            foreach ($cells as $cell) {
                // 不匹配
                if(!preg_match('/\d{17}(\d|X){1}/', $cell['证件号码'],  $id_card)) continue;
                // 找不到学员
                $user = User::where('id_card', $id_card[0])->first();
                if (!$user) continue;
                // 重复
                if(self::where('user_id',$user->id)->where('date',date('Y-m-d', strtotime($cell['考试日期'])))->count()) continue;
                // 创建节点

                Node::set($user,Stage::findByName(self::$subjects[$subject]),date('Y-m-d', strtotime($cell['考试日期'])).' '.self::$subjects[$subject].' 考试, 成绩: '.$cell['成绩'].', '.($cell['成绩'] >= 60 ? '通过' : '未通过'));

                $row['user_id']   = $user->id;
                $row['date']      = date('Y-m-d', strtotime($cell['考试日期']));
                $row['score']     = $cell['成绩'];
                $row['school_id'] = $school_id;
                $row['subject']   = $subject;
                $rows[]           = $row;
            }

            if(count($rows) && $affected = self::insert($rows)) {
                \DB::commit();
                return [$affected, $cells->count()];
            }
            \DB::rollBack();
            throw new ParameterError('没有可导入数据');
        }
    }

    public function user() { return $this->belongsTo(User::class); }

    /*
     * @Des:读取通过考试的科目一考试的userids
     * */

    public static function ExamPassUserIds($subject=1){
        return self::where(array('subject'=>$subject,'is_ok'=>1))->get()->pluck('user_id');
    }





}