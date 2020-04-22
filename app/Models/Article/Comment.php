<?php

namespace App\Models\Appointment;

use App\Models\Admin\Notification;
use App\Library\Holiday;
 use App\Models\Business;
use App\Models\BaseModel;
use App\Models\Admin\Admin;
use App\Models\ParameterError;
use App\Library\Http;
use App\Models\Home\User;
 use App\Models\Data\School;
 use App\Library\Loglc\Word;
 use DB;
 use Illuminate\Database\Eloquent\Collection;
 use App\Models\Data\SchoolSetting;
 use App\Models\Student\Examination;
 use App\Models\Data\Schoolarea;
 use App\Models\Finance\Income;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator;
class Comment extends BaseModel
{
    protected $table = 'comment';

    protected $rules            = [
        'content'  => 'required|string|min:2',
        'general'  => 'required|in:1,2,3,4,5',
        'attitude' => 'required|in:1,2,3,4,5',
        'quality'  => 'required|in:1,2,3,4,5',
        'behavior' => 'required|in:1,2,3,4,5',
        'teach'    => 'required|in:1,2,3,4,5',
    ];

    protected $customAttributes = [
        'content'  => '内容',
        'general'  => '综合评价',
        'attitude' => '服务质量',
        'quality'  => '廉洁教学',
        'behavior' => '行为',
        'teach'    => '教学',
    ];
/*
 *  * @property-read \App\Models\Home\User                   $user
 * */
    const POOR = 1;      //差
    const INDIFFERENT = 2;             //一般
    const SATISFACTORY = 3;             //满意
    const SATISFACTORYER = 4;          //非常满意
    const RECOMMEND = 5;               //推荐

    public static function addComment($appointment_id, $comment_data){
        $comment_data['appointment_id'] = $appointment_id;

        try {
            \DB::beginTransaction();
            $comment         = self::create($comment_data);
            $appoint         = Appointment::find($appointment_id);
            if( $appoint->status == Appointment::STATUS_EVALUATED  )
                throw new ParameterError('已经评价过了该次训练!');
            if($appoint->status != Appointment::STATUS_DONE)
                throw new ParameterError('只有已完成才能评价!');
            $appoint->status = Appointment::STATUS_EVALUATED;
            $appoint->save();
            \DB::commit();

            return $comment;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
    public function school(){
        return $this->belongsTo(School::class);
    }
    public function user() { return $this->belongsTo(User::class); }
    /**
     * [notComment 评价列表]
     * @param  [collection] $not_comment [description]
     * @return [type]              [description]
     */
    public static function commentList($comment_list){
        $comment_list->transform(function($comment){
            $comment['admin_name']   = Admin::find($comment['admin_id'])->admin_name;
            $comment['vehicle_code'] = Admin::find($comment['admin_id'])->vehicle->car_num;
            $comment['type_name']    = AppointmentType::find($comment['type_id'])->name;
            return $comment;
        });
        return $comment_list;
    }
    /**
     * [commentDetail 带评论内容的已评价列表]
     * @param  [type] $comment_list [description]
     * @return [type]              [description]
     */
    public static function commentDetail($comment_list){
        $comment_list->transform(function($comment){
            $comment['admin_name']     = Admin::find($comment['admin_id'])->admin_name;
            $comment['vehicle_code']   = Admin::find($comment['admin_id'])->vehicle->car_num;
//            $comment['type_name']      = AppointmentType::find($comment['type_id'])->name;
            $comment['comment_detail'] = Appointment::find($comment['id'])->comment;
            return $comment;
        });
        return $comment_list;
    }
    /**
     * [commentAnalysis 评论分析]
     *
     * @param $admin_id
     *
     * @return boolean [description]
     */
    public static function commentAnalysis($admin_id){
        $coach = Admin::find($admin_id);
        $general = [];
        $general['avg'] = round($coach->comments->pluck('general')->transform(function($general) {
            return self::getGeneralAttribute($general);
        })->avg(), 1);
        $general['count']                = $coach->comments->count();
        $general['poor_count']           = $coach->comments->where('general', self::POOR)->count();
        $general['indifferent_count']    = $coach->comments->where('general', self::INDIFFERENT)->count();
        $general['satisfactory_count']   = $coach->comments->where('general', self::SATISFACTORY)->count();
        $general['satisfactoryer_count'] = $coach->comments->where('general', self::SATISFACTORYER)->count();
        $general['recommend_count']      = $coach->comments->where('general', self::RECOMMEND)->count();
        if ($general['count'] < 1) {
            $general['poor_ratio']           = 0;
            $general['indifferent_ratio']    = 0;
            $general['satisfactory_ratio']   = 0;
            $general['satisfactoryer_ratio'] = 0;
            $general['recommend_ratio']      = 0;
        } else {
            $general['poor_ratio']           = round($general['poor_count'] / $general['count'], 2) * 100;
            $general['indifferent_ratio']    = round($general['indifferent_count'] / $general['count'], 2) * 100;
            $general['satisfactory_ratio']   = round($general['satisfactory_count'] / $general['count'], 2) * 100;
            $general['satisfactoryer_ratio'] = round($general['satisfactoryer_count'] / $general['count'], 2) * 100;
            $general['recommend_ratio']      = round($general['recommend_count'] / $general['count'], 2) * 100;
        }
        $general['attitude'] = round($coach->comments->avg('attitude'), 1);
        $general['teach']    = round($coach->comments->avg('teach'), 1);
        $general['behavior'] = round($coach->comments->avg('behavior'), 1);
        $general['quality']  = round($coach->comments->avg('quality'), 1);

        return $general;
    }

    public static function getGeneralAttribute($value)
    {
        switch ($value) {
        case self::POOR:
            return '差';
            break;
        case self::INDIFFERENT:
            return '一般';
            break;
        case self::SATISFACTORY:
            return '满意';
            break;
        case self::SATISFACTORYER:
            return '非常满意';
            break;
        case self::RECOMMEND:
            return '推荐';
            break;
        default :
            return $value;
            break;
        }
    }

    /**
     * Define a has-many-through relationship.
     *
     * @param  string $related
     * @param  string $through
     * @param  string|null $firstKey
     * @param  string|null $secondKey
     * @param  string|null $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function hasManyThrough($related, $through, $firstKey = null, $secondKey = null, $localKey = null)
    {
        $through = new $through;

        $firstKey = $firstKey ?: $this->getForeignKey();

        $secondKey = $secondKey ?: $through->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return new HasManyThrough((new $related)->newQuery(), $this, $through, $firstKey, $secondKey, $localKey);
    }
    /**
     * Define a one-to-one relationship.
     *
     * @param  string $related
     * @param  string $foreignKey
     * @param  string $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $instance = new $related;

        $localKey = $localKey ?: $this->getKeyName();

        return new HasOne($instance->newQuery(), $this, $instance->getTable() . '.' . $foreignKey, $localKey);
    }

    public function HasOneThrough($related, $through, $firstKey = null, $secondKey = null, $localKey = null)
    {
        $through = new $through;

        $firstKey = $firstKey ?: $this->getForeignKey();

        $secondKey = $secondKey ?: $through->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

    }
    //评价信息交换接口
    public function evaluate(){
        $url = '/evaluation?v={version}&ts={timestamp}&sign={sign_str}&user={cert_sn}';
        $result = Http::jsonPost($url,$this->toStandard_evalu());
        if ($result->error()) {
            throw new \Exception($result->error());
        }
    }
    public function toStandard_evalu(){
       $a_id=$this->appointment_id;
       $appoint= \App\Models\Appointment\Appointment::find($a_id);
        $numbers=$appoint->admin->admin_numbers;
        return [
            'stunum' => $appoint->user->user_product->student_id,
            'evalobject'=>$numbers,
            'type'=>'1',
            'overall'=>$this->behavior,
            'evaluatetime'=>date("YmdHis",strtotime($this->created_at))
        ];
    }
    //评价消息查询
    public function evaluationquery(){
        $time=date("Ymd",time());
       // $school=$this->school->school_numbers;
        //var_dump($school);
        $url = "/2236581429084424-evaluationquery-{$time}?v={version}&ts={timestamp}&sign={sign_str}&user={cert_sn}";
        $data=[
            'cardnum'=>"2236581429084424",
            'name' =>$time,
        ];
        $result = Http::jsonGet($url,$data);
        if ($result->error()) {
            throw new \Exception($result->error());
        }
    }
}
