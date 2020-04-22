<?php
namespace App\Models\Appointment;

use App\Library\Holiday;
use App\Library\Http;
use App\Models\Admin\Admin;
use App\Models\Admin\Notification;
use App\Models\BaseModel;
use App\Models\Data\Schoolarea;
use App\Models\Finance\Income;
use App\Models\Business\Product;
use App\Models\Business\UserProduct;
use App\Models\Data\IncomeType;
use App\Models\Data\School;
use App\Models\Data\SchoolSetting;
use App\Models\Student\Examination;
use App\Models\Home\Hour;
use App\Models\Home\User;
use App\Models\NotFound;
use App\Models\ParameterError;
use App\Models\StoreError;
use App\Models\Term\TimingTerm;
use App\Models\Vehicle\Vehicle;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;


class Useappove extends BaseModel {
    protected $table   = 'user_approve';

    public function user(){
        return $this->belongsTo(User::class, 'uid');
    }
    public function school() {return $this->belongsTo(School::class);}
    public function userProduct() {return $this->belongsTo(UserProduct::class, 'uid');}

    //阶段培训记录上传接口
    public function multiUpload($esignature,$option=array()){
        $url = '/stagetrainningtime?v={version}&ts={timestamp}&sign={sign_str}&user={cert_sn}';
        $data=$this->toStandard_stage($esignature,$option);
        $result = Http::jsonPost($url,$data);
        \App\Models\BaseModel::insert_send($url,$result,$method="jsonPost",json_encode($data));
        return $result;
    }

    public function toStandard_stage($esignature,$option){

        $imgSrc = public_path('upload').'/pdf/'.$option['fileid'].'.pdf';
        $url = '/imageup/{type}?v={version}&ts={timestamp}&sign={sign_str}&user={cert_sn}';
        $data = [
            'type' => 'epdfimg',
            'file' => $imgSrc,
        ];
        log::info("DATA:====:".print_r($data,true));
        $result = Http::file($url,$data);
        if ($result->error()) {
            throw new \Exception($result->error());
        }
        $photo_id = $result->result_array['data']['id'];
        $result = $this->getSignData($option);
        $result['pdfid'] = $photo_id;
        $result['esignature'] = $esignature;
        return $result;
    }

    // 获取学员电子签章数据
    public function getSignData($option) {
        $where = array(
            'user_id' => $option['user_id'],
            'subject_id' => $option['subject_id']
        );
        $totalinfo = \DB::table('user_stage_audit')->where($where)->first();

//        log::info("DATAtotal:====:".print_r($totalinfo,true));
        return $signdata= [
            'inscode' => $this->school->school_numbers,
            'stunum' => $this->user->user_numbers,
            'subject'=>$option['subject_id'],
            'totaltime'=>$totalinfo->totaltime,
            'vehicletime'=>$totalinfo->vehicletime,
            'classtime'=>$totalinfo->classtime,
            'simulatortime'=>$totalinfo->simulatortime,
            'networktime'=>$totalinfo->networktime,
            'duration'=>$totalinfo->duration,
            'examresult'=>1,
            'mileage'=>$totalinfo->mileage,
            'rectype'=>$totalinfo->rectype,
//            'recarray'=>$record2
            'recarray'=>json_decode($totalinfo->recarray,true)

        ];
    }
}