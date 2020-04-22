<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/18/16
 * Time: 4:01 PM
 */

namespace App\Models\Student;

use App\Library\Http;
use App\Library\YunpianSMS\Lib\Config;
use App\Models\Appointment\Appointment;
use App\Models\BaseModel;
use App\Models\Data\School;
use App\Models\Home\User;
use Behat\Mink\Exception\Exception;
use Doctrine\DBAL\Driver\IBMDB2\DB2Driver;
use App\Library\OSS;

class Theory extends BaseModel
{
    protected $table = 'theory_train';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    //电子教学日志接口
    public function upload()
    {
        $url = '/classrecord?v={version}&ts={timestamp}&sign={sign_str}&user={cert_sn}';
        $result = Http::jsonPost($url, $this->toStandard());

        return $result;
    }

    private function toStandard()
    {
        \DB::beginTransaction();
        try {
            $total = 0;
            $train_subject = '';
            //科目一
            $part1 = (int)Theory::where(['school_id' => session('school_id'), 'user_id' => $this->user_id, 'subject' => 1])
                ->where('min', '>=', 5)
                ->sum('min');
            //科目四
            $part4 = (int)Theory::where(['school_id' => session('school_id'), 'user_id' => $this->user_id, 'subject' => 4])
                ->where('min', '>=', 5)
                ->sum('min');
            //科目二
            $part2 = Appointment::partTrainForUser(session('school_id'), $this->user_id, 2);
            //科目三
            $part3 = Appointment::partTrainForUser(session('school_id'), $this->user_id, 3);
            if ($this->subject == 1) {
                $total = $part1;
                $train_subject = '03';
            } elseif ($this->subject == 4) {
                $total = (int)($part1 + $part2 + $part3 + $part4);
                $train_subject = '43';
            }
            //获取车型编码
            $train_vehicle_type = \Config::get('constants.VEHICLE_TYPE');
            $user = User::where(['school_id' => session('school_id'), 'id' => $this->user_id])->first();
            $vehicle_type = $train_vehicle_type[$user->user_product->old_licence_type];
            //课时编码
            $subjcode = '2' . $vehicle_type . $this->subject . $train_subject . '0000';
            Theory::where('id', $this->id)->update(['rnum' => $subjcode]);
            \DB::commit();

            return [
                'inscode' => $this->school->school_numbers,
                'stunum' => $this->user->user_numbers,
                'platnum' => "A0107",
//            'recnum' => $this->id,
                'recnum' => "00001",//todo
//            'subjcode' => 1212130000,
                'subjcode' => $subjcode,
                'photo1' => $this->sign_in_image_id,
                'photo3' => $this->image_id,
                'starttime' => date("YmdHis", strtotime($this->sign_in_time)),
                'endtime' => date("YmdHis", strtotime($this->sign_out_time)),
                'duration' => $this->min,
                'mileage' => 0,
                'avevelocity' => 0,
                'total' => $total,
                'part1' => $part1,
                'part2' => $part2,
                'part3' => $part3,
                'part4' => $part4,
            ];

        } catch (Exception $e) {
            \DB::rollBack();
        }

        return [];
    }

    //牌照生成id
    public static function base64_upload($base64)
    {
        $base64_image = str_replace(' ', '+', $base64);
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)) {
            $image_file = date('Y-m-d-H-i-S') . '-' . uniqid() . '.' . $result[2];
            $url = '/tmp/' . $image_file;

            if (file_put_contents($url, base64_decode(str_replace($result[1], '', $base64_image)))) {
                //上传到运管
                $img = $url;

                $path = session('school_id') . '/' . \Config::get('oss.tcp.theory') . date("Y-m-d");
                OSS::publicUpload(\Config::get('oss.CONFIG.BUCKET'), $path . '/' . $image_file, $img);
//                unlink($img);
            } else {

                return false;
            }
        } else {
            return false;
        }

        return OSS::getPublicObjectURL(\Config::get('oss.CONFIG.BUCKET'), $path . '/' . $image_file);
    }

    //上传照片生成id
    public static function imageId($img)
    {
        $path = storage_path() . '/imgs/' . date('Y-m-d');
        $imginfo = \App\Models\BaseModel::getImage($img, $path);
        $img = $imginfo['save_path'];

        return $img;
    }

    //上传照片到运管
    public static function uploadImageToYUNGUAN($img)
    {
        //远程下载图片
        $path = storage_path() . '/imgs/' . date('Y-m-d');
        $imginfo = \App\Models\BaseModel::getImage($img, $path);
        $img = $imginfo['save_path'];

        $url = '/top/imageup/{type}?v={version}&ts={timestamp}&sign={sign_str}&user={cert_sn}';
        $data = [
            'type' => 'stuimg',
            'file' => $img,
        ];
        $resultObj = Http::file($url, $data);

        if ($resultObj->error()) {
            throw new \Exception($resultObj->error());
        }
        $photo_id = $resultObj->result_array['data']['id'];

        return $photo_id;
    }


    public static function checkUser($id_card)
    {
        return User::where(['school_id' => session('school_id'), 'id_card_type' => 1, 'id_card' => trim($id_card)])->first();

    }

    /**
     * @desc 总学时-指定学员
     * @param $user_id
     * @param $subject
     * @return mixed
     */
    public static function partTheoryForUser($user_id, $subject)
    {
        return self::where(['school_id' => session('school_id'), 'subject' => $subject])
            ->where('user_id', $user_id)
            ->where('min', '>=', 45)
            ->sum('min');
    }

    /**
     * @desc 所有学员科目一及科目四总学时
     * @param $school_id
     * @param $subject
     * @return array|\Illuminate\Support\Collection|mixed
     */
    public static function PartTheoryForUsers($school_id, $subject)
    {
        return Theory::where(['school_id' => $school_id, 'subject' => $subject])
            ->where('min', '>=', 45)
            ->groupBy('user_id')
            ->selectRaw('sum(min) as sum, user_id')
            ->pluck('sum', 'user_id');
    }

}