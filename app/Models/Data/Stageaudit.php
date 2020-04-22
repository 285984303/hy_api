<?php
/*
 * @Des:    阶段审核相关处理
 * @Author: Joe
 * @Date:   2017.08.10
 * */
namespace App\Models\Data;

use App\Models\Admin\Admin;
use Illuminate\Database\Eloquent\Model;
use App\Models\Home\User;
use App\Models\BaseModel;
use App\Library\Http;
use App\Models\Data\School;
use App\Models\Business\UserProduct;
use Illuminate\Support\Facades\Log;

class Stageaudit extends BaseModel
{
    protected $table = 'user_stage_audit';
    public $timestamps = false;

    /*
     * @Des:读取阶段记录
     * */
    public static function StageInfo($where, $options = array())
    {
        $query = self::where($where);
        foreach ($options as $k => $v) {
            if ($k == 'ids') {
                $query->whereIn('user_id', $v);
            }
        }
        return $query->get();
    }
    /*
    * @Des: 数据列表分页查询
    * */
    public static function stageSelectPage($school_id,$parms= array(),$orderfileds='created_at',$orderby='DESC'){

        $query = self::where('school_id','=',$school_id);
        foreach ($parms as $key => $value) {
            switch ($key) {
               case 'subject_id':
                    $query->where($key,'=',$value);
                    break;
                case 'name':
                    $user_ids = User::where('user_truename', 'like', "%$value%")->pluck('id');
                    $query->whereIn('user_id', $user_ids);
                    break;
                case 'id_card':
                    $user_ids = User::where('id_card', 'like', "%$value%")->pluck('id');
                    $query->whereIn('user_id', $user_ids);
                    break;
               /*  case 'pay_type':
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
                    break;*/
                default:
                    break;
            }
        }
        $query->orderby($orderfileds,$orderby);
        return $query;
    }

    /*用户信息*/
    public function userinfo()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
     * @Des:根据ID获取单条数据
     * */
    public static function StageInfoOne($id)
    {
        return self::where(array('id' => $id))->first();
    }

    /*
     *@Des: 统计是否存在统计信息
     * */
    public static function StageAuditCount($where)
    {
        return self::where($where)->count();
    }


    /*后来加入*/
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'deal_person_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function userProduct()
    {
        return $this->belongsTo(UserProduct::class, 'user_id');
    }

    //阶段培训记录上传接口
    public function multiUpload($esignature, $option = array())
    {
        $url = '/stagetrainningtime?v={version}&ts={timestamp}&sign={sign_str}&user={cert_sn}';
        $data = $this->toStandard_stage($esignature, $option);
        $result = Http::jsonPost($url, $data);
        \App\Models\BaseModel::insert_send($url, $result, $method = "jsonPost", json_encode($data));
        return $result;
    }

    public function toStandard_stage($esignature, $option)
    {

        $imgSrc = public_path('upload') . '/pdf/' . $option['fileid'] . '.pdf';
        $url = '/imageup/{type}?v={version}&ts={timestamp}&sign={sign_str}&user={cert_sn}';
        $data = [
            'type' => 'epdfimg',
            'file' => $imgSrc,
        ];
        log::info("DATA:====:" . print_r($data, true));
        $result = Http::file($url, $data);
        if ($result->error()) {
//            throw new \Exception($result->error());
        }
        if ($result->result_array['errorcode'] != 0) {
            throw new \Exception('审核文件上传不成功');
        }
        $photo_id = $result->result_array['data']['id'];
        $result = $this->getSignData($option);
        $result['pdfid'] = $photo_id;
        $result['esignature'] = $esignature;
        return $result;
    }

    // 获取学员电子签章数据
    public function getSignData($option)
    {
        $where = array(
            'user_id' => $option['user_id'],
            'subject_id' => $option['subject_id']
        );
//        $totalinfo = \DB::table('user_stage_audit')->where($where)->first();
        return $signdata = [
            'inscode' => $this->school->school_numbers,
            'stunum' => $this->user->user_numbers,
            'subject' => $option['subject_id'],
            'totaltime' => $this->totaltime,
            'vehicletime' => $this->vehicletime,
            'classtime' => $this->classtime,
            'simulatortime' => $this->simulatortime,
            'networktime' => $this->networktime,
            'duration' => $this->duration,
            'examresult' => 1,
            'mileage' => $this->mileage,
            'rectype' => $this->rectype,
            'recarray' => json_decode($this->recarray, true)

        ];
    }
    //导出阶段审核
    public static function getStageQuery($school_id, $options = [])
    {
        $query = self::where(['school_id' => $school_id]);
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'name':
                    $query_user = $query_user??User::query();
                    $query_user->where($key, 'like', "%$value%");
                    break;
                case 'id_card':
                    $query_user = $query_user??User::query();
                    $query_user->where($key, $value);
                    break;
                case 'subject':
                    $query->where('subject_id', $value);
                    break;
                default:
                    break;
            }
        }

        if (isset($query_user)) {
            $user_ids = $query_user->pluck('id');
            $query->whereIn('user_id', $user_ids);
        }
        $query->distinct();
        return $query;
    }


}
