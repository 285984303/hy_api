<?php namespace App\Models\Admin;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 5/30/16
 * Time: 2:22 PM
 */

use App\Library\JPush\JPushLibrary;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use JPush\Exceptions\JPushException;

/**
 * Class Notification
 * @package App\Models\Admin
 * @property $id integer
 * @property $admin_id        string
 * @property $title           string
 * @property $content         string
 * @property $read            boolean
 * @property $status          integer
 */
class Notification extends BaseModel {
    use SoftDeletes;
    protected $table   = 'notification';
    protected $guarded = ['id'];

    const STATUS_READ   = 1;
    const STATUS_UNREAD = 0;

    protected $urls = [];

    public function __construct(array $attributes = []) {
        $this->rules = [
            '欠费信息' => url('user/tuituion'),
            '学习进程' => url('user'),
            '排课信息' => url('coach'),
            '考试信息' => url('/'),
            '评价信息' => url('comment'),
        ];
        parent::__construct($attributes);
    }

    public function url($type,$suffix = ''){
        return $this->rules[$type].$suffix;
    }

    public static function appointmentUrl($date) {
        return '/admin/coach/schedule?date='.$date;
    }


    public function read($id)
    {
        $notification = $this->find($id);
        $notification->read = self::STATUS_READ;
        $notification->save();
    }

    public static function send($from, $to, $title, $content)
    {
        /** @var Admin|\App\Models\Home\User $to */
        $data         = [
            'from'      => $from->id,
            'from_type' => class_basename($from),
            'to'        => $to->getKey(),
            'to_type'   => class_basename($to),
            'title'     => $title,
            'content'   => $content,
        ];
        $notification = new self($data);
        $notification->save();
    }

    public static function sendMultipleSystemNotification($to, $title, $content)
    {
        $now = date('Y-m-d H:i:s');
        /** @var Admin|\App\Models\Home\User $to */
        $rows = [];
        foreach ($to as $target) {
            $row         = [
                'from'      => 0,
                'from_type' => 'system',
                'to'        => $target->id,
                'to_type'   => class_basename($target),
                'title'     => $title,
                'content'   => $content,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $rows[] = $row;
        }
        self::insert($rows);
    }

    public static function sendSystemNotification($to, $title, $content)
    {
        /** @var Admin|\App\Models\Home\User $to */
        $data         = [
            'from'      => 0,
            'from_type' => 'system',
            'to'        => $to->id,
            'to_type'   => class_basename($to),
            'title'     => $title,
            'content'   => $content,
        ];
        $notification = new self($data);
        $notification->save();


        if(class_basename($to) == 'User' && !empty($to->jpush_id)){
            // 极光消息推送
           /* try {
                (new JPushLibrary())->setUserNotification([ $to->jpush_id ], $title);
                \Log::info('极光推送成功', [ 'message' => $to->id ]);
            } catch (JPushException $e) {
                \Log::info('极光推送失败', [ 'code' => $e->getCode(), 'message' => $e->getMessage() ]);
            } catch (\Exception $e) {
                \Log::info('极光推送错误', [ 'code' => $e->getCode(), 'message' => $e->getMessage() ]);
            }*/
        }

    }

    public static function allRead($to)
    {
        /** @var Admin|\App\Models\Home\User $to */
        try {
            $result = self::where( ['to' => $to->getKey(), 'to_type' => class_basename($to)])->update(['read' => self::STATUS_READ]);

            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
