<?php namespace App\Models\Home;

use App\Models\BaseModel;
use App\Models\NotFound;
use App\Models\ParameterError;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Hour
 * @package App\Models\Home
 * @property $school_id
 * @property $start_time
 * @property $finish_time
 * @property $normal_fee
 * @property $holiday_fee
 * @property $time_length
 */
class Hour extends BaseModel {
    use SoftDeletes;
    protected $table = 'course';
    protected $rules = [
        'start_time' => 'required|date_format:H:i',
        'finish_time' => 'required|date_format:H:i|after:start_time',
    ];

    protected $customAttributes = [
        'start_time' => '开始时间',
        'finish_time' => '结束时间',
    ];
    protected $dates = ['deleted_at'];//软删除

    /**
     * @param $school_id
     *
     * @return static[] | Collection
     * @throws NotFound
     */
    public static function getHours($school_id)
    {
        $hours = Hour::where('school_id',$school_id)->orderBy('start_time','asc')->get();
        if (!$hours->count()) {
            throw new NotFound;
        }

        return $hours;
    }

    protected function validator() {
        parent::validator();

        // $sql   = "SELECT * FROM `$this->table` WHERE `school_id` = $this->school_id AND `id` != $this->id AND ((`start_time` > '$this->start_time' AND `start_time` < '$this->finish_time') OR (`finish_time` > '$this->start_time' AND `finish_time` < '$this->finish_time'))";
        // $query = self::selectRaw($sql);
        // $hours = $query->get();

        $query = self::where('school_id',$this->school_id);
        $query->where('id','!=',$this->id);
        $query->where(function($query){
            $query->where('start_time','>',$this->start_time);
            $query->where('start_time','<',$this->finish_time);
            $query->orWhere(function($query){
                $query->where('finish_time','>',$this->start_time);
                $query->where('finish_time','<',$this->finish_time);
            });
        });
        $hours = $query->get();
        if ($hours->count() > 0) {
            throw new ParameterError('课时设置有冲突,请查看设置');
        }
    }

    /**
     * @param $hours static[]|Collection
     *
     * @return static[]|Collection
     */
    public static function mergeHours($hours) {
        /**
         * @param $hour static
         * @param $hours Collection|static[]
         * @param $merged_hours Collection|static[]
         *
         * @return Collection|static[]
         */
        $merge = function(&$hour, &$hours, &$merged_hours) use (&$merge) {
            /** @var static $next_hour */
            if ($hours->count() < 1) {
                $merged_hours->add($hour);
                return $merged_hours;
            }

            $next_hour = $hours->shift();

            if ($next_hour->start_time == $hour->finish_time) {
                $hour->finish_time = $next_hour->finish_time;
                $merge($hour, $hours, $merged_hours);
            } else {
                $merged_hours->add($hour);
                $merge($next_hour, $hours, $merged_hours);
            }
            return $merged_hours;
        };

        $hour = $hours->shift();
        $merged_hours = new Collection();
        $merge($hour, $hours, $merged_hours);

        return $merged_hours;
    }
}
