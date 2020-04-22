<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/13/16
 * Time: 5:47 PM
 */

namespace App\Models\Student;


use App\Models\BaseModel;
use App\Models\ParameterError;
use App\Models\Home\User;
use Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class Examination
 * @package App\Models\Home
 * @property $user_id
 * @property $date
 * @property $score
 * @property $subject
 * @property $school_id
 */
class Examination extends BaseModel
{
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

    public static function getSubjects()
    {
        return self::$subjects;
    }

    public function getSubject()
    {
        return self::$subjects[$this->subject];
    }

    public static function getList($school_id, $options = [])
    {
        $query = self::where('school_id', $school_id);
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'user_truename' :
                case 'id_card' :
                case 'user_telphone' :
                    $user_ids = User::where($key, 'like', "%$value%")->pluck('id');
                    $query->whereIn('user_id', $user_ids);
                    break;
                case 'gender' :
                    $user_ids = User::where('user_sex', $value)->pluck('id');
                    $query->whereIn('user_id', $user_ids);
                    break;
                default :
                    $query->where($key, $value);
            }
        }
        $list = $query->orderBy('date', 'desc')->paginate();

        return $list;
    }


    public static function importFromExcel($school_id, $subject, $path)
    {
        $subjectObjUp = [];

//        DB::beginTransaction();

        // 地区敏感
        // setlocale(LC_ALL, 'zh_CN');
        /**
         * @var $cells \Maatwebsite\Excel\Collections\SheetCollection
         */
        $sheets = Excel::load($path)->get();
//        log::info("+++++++:".print_r($sheets,true));
        foreach ($sheets[0] as $k => $v) {
            $tmp = array_values($v->toArray());
            if(!$tmp[1]){
                continue;
            }
            $row['name'] = $tmp[0]; //第一列为姓名
            $row['id_card'] = $tmp[1]; //第二列为证件号码

            $user = \DB::table('user')->where('id_card', '=', $tmp[1])->select('id')->first();

            if(empty($user->id)) {
                //DB::rollBack();
                throw new \Exception('学员名:' . $tmp[0] . "证件号:" . $tmp[1] . '不存在数据导入失败');
            }
            $row['user_id'] = $user->id;
            $row['user_phone'] = $tmp[2]; //第三列为手机号码
            $row['licence_type'] = $tmp[3]; //第四列为车型
            $row['score'] = $tmp[4]; //第五列为车型
            if ($tmp[4] >= 90) {
                $row['is_ok'] = 1; //合格
            } else {
                $row['is_ok'] = 2; //不合格
            }
            $row['date'] = $tmp[5]; //第六列为考试日期
            $row['school_id'] = $school_id;
            $row['subject'] = $subject;
            $data = self::insertGetId($row);
            if ($subject == 1) {
                $subjectObjUp = ['subject_1'=>$tmp[4]];
            } elseif ($subject == 2) {
                $subjectObjUp = ['subject_2'=>$tmp[4]];
            } elseif ($subject == 3) {
                $subjectObjUp = ['subject_3'=>$tmp[4]];
            } elseif ($subject == 4) {
                $subjectObjUp = ['subject_4'=>$tmp[4]];
            }
            User::where(['school_id' => $school_id, 'id' => $user->id])->update($subjectObjUp);

            if (!$data) {
//                DB::rollBack();
                throw new ParameterError('导入失败');
            }
        }
//        DB::commit();
    }

    public static function maxScoreOf(User $user, $subject, $school_id)
    {
        return self::where('user_id', $user->id)->where('subject', $subject)->where('school_id', $school_id)->max('score');
    }

    public static function examCountOf(User $user, $subject, $school_id)
    {
        return self::where('user_id', $user->id)->where('subject', $subject)->where('school_id', $school_id)->count('id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /*+++++++++++++++++++++Joe 新加部分功能++++++++++++++++++++++++++++*/

    /*
     * @Des:    根据身份证号检测学员是否为本校学员
     * @Parms:  $array  身份证号码
     * @Return: $array
     * @Author: Joe
     * */
    public static function GetExamInfo($where = array())
    {
        return self::where($where)->first();
    }

    /*
     * @Des:读取通过考试的科目一考试的userids
     * */

    public static function ExamPassUserIds($subject = 1)
    {
        return self::where(array('subject' => $subject, 'is_ok' => 1))->get()->pluck('user_id');
    }


}