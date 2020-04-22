<?php
/**
 * Created by PhpStorm.
 * User: link
 * Date: 2016/12/13
 * Time: 11:34
 */
namespace App\Models\Log;

use App\Models\BaseModel;
use App\Models\ParameterError;
use Illuminate\Support\Facades\Log;
use PhpSms;
use App\Models\Home\User;
use App\Models\Admin\Admin;
//use Toplan\PhpSms\PhpSmsException;

/**
 * 登录短信验证
 *
 * Class LaravelSms
 *
 * @package App\Models\Log
 * @property integer $id
 * @property string $to 短信手机号
 * @property string $code 验证码
 * @property string $type 类型 登录验证码/修改密码验证码
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @mixin \Eloquent
 */
class LoginVerifyCode extends BaseModel
{

    protected $table = 'login_verify_code';

    /*protected $rules = [
        'to'   => 'required|between:1,20',
        'code' => 'required|between:1,10',
        'type' => 'required|between:1,255',
    ];*/

    // 验证码有效时间 分钟
    public static $effective_time = 30;


    /**
     * 发送短信验证码
     *
     * @param        $mobile
     * @param string $type
     *
     * @return bool
     * @throws ParameterError
     * @throws \Exception
     */
    public static function sendMobileCode($mobile, $type = 'login')
    {
        if (preg_match('/^1[3456789]\d{9}$/',$mobile)){
            $templates = [
                'Aliyun' => 'SMS_106920199',
//                'Aliyun' => 'SMS_119920819',
            ];
            $code = self::generateCode($type);
            $content = "";

            $sendinfo = [ 'code' => $code ,'product'=>'dsd'];
            /*$sendinfo = array(
                'class' => "2018-01-03 [12:30-14:30、12:30-14:30、12:30-14:30、12:30-14:30]",
                'name' => '乔增浩',
                'vehicle' => '[贵3698学]',
                'product'=>'dsd'
            );*/
            $return  = PhpSms::make()
                ->to($mobile)
                ->template($templates)
                ->data($sendinfo)
                ->content($content)
                ->send();
            log::info($mobile."+++++++:".print_r($return,true));
            if ($return['success'] === true){
                // 和之前数据兼容
                \Cache::put(session()->getId().'_captcha_mobile', $mobile, 1);
                \Cache::put(session()->getId().'_captcha', $code, 1);
                
                $name = '';
                $idcard = '';
                if($type == 'login')
                {
                    $user = User::findUser($mobile);
                    if($user)
                    {
                        $name = $user->user_truename;
                        $idcard = $user->id_card;
                    }
                }
                if($type == 'coach')
                {
                    if(empty($name))
                    {
                        $admin = Admin::getAdminByPhone($mobile);
                        if($admin)
                        {
                            $name = $admin->admin_name;
                            $idcard = $admin->id_card;
                        }
                    }
                }
                
                $verify_code = (new self());
                $verify_code->fill([
                    'to'   => $mobile,
                    'name'   => $name,
                    'id_card' =>$idcard,
                    'code' => $code,
                    'type' => $type,
                ]);
                $verify_code->save();
                return session()->getId().'_captcha_mobile';
            } else {
                $return_code = json_decode($return['logs'][0]['result']['info'], true);
                throw new \Exception("发送失败:".$return_code['sub_msg']);
            }
        } else {
            throw new ParameterError('手机号格式不正确！');
        }
    }


    /**
     * 发送财务短信验证码
     *
     * @param        $mobile
     * @param        $money
     * @param string $type
     *
     * @return bool
     * @throws ParameterError
     * @throws \Exception
     */
    public static function sendFinanceVerifyCode($mobile, $code, $money)
    {
        if (preg_match('/^1[34578]\d{9}$/', $mobile)) {
            $templates = [
                'Alidayu' => 'SMS_15410153',
            ];

            $code = (string)$code;

            $content = "";
            $return  = PhpSms::make()
                             ->to($mobile)
                             ->template($templates)
                             ->data(['code'=>$code, 'date'=> date('Y-m-d'), 'money' => (string)$money])
                             ->content($content)
                             ->send();

            if ($return['success'] === true) {

                return true;
            } else {
                $return_code = json_decode($return['logs'][0]['result']['info'], true);
                throw new \Exception("发送失败".$return_code['sub_msg']);
            }
        } else {
            throw new ParameterError('手机号格式不正确！');
        }
    }


    /**
     * 通过验证码获取登录的手机号
     *
     * @param        $captcha
     * @param string $mobile
     * @param string $type
     *
     * @return mixed|string
     * @throws ParameterError
     */
    public static function getLoginMobile($captcha, $mobile = "", $type = 'login')
    {
        if(empty($captcha))
            throw  new ParameterError('请输入验证码！');
        $query = (new self())::where('code', $captcha)
            ->where('type', $type);
        if(!empty($mobile))
            $query->where('to',$mobile);

        $data = $query ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime("-".self::$effective_time." minute")))
            ->first();
        if(!$data){
//            throw new \Exception();
            throw new ParameterError('验证码不正确.');
        }


        return $data->to;
    }


    /**
     * 生成验证码
     *
     * @param string $type
     *
     * @return int|string
     */
    protected static function  generateCode($type = 'login'){
        $code = "";
        // 保证验证码唯一
        while (1) {
            $code = mt_rand(111111, 999999);
            $data = (new self())::where('code', $code)
                ->where('type', $type)
                ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime("-".self::$effective_time." minute")))
                ->first();
            if ( ! $data) {
                break;
            }
        }
        return (string)$code;
    }


    /*
     * @Des：发送
     * */
    public static function sendmsgphone($message =array()){

        $content = "";
        if($message['data']){
            $classinfo = implode(",",$message['data']);
        }else{
            $classinfo = "";
        }
        $sendinfo = array(
            'class' => $classinfo,
            'product'=>'dsd'
        );
        if($message['msgtype']==1){
            //短信验证
            $template = "SMS_120130482";
        }else if($message['msgtype']==2){
            $template = "SMS_119915833";
            $sendinfo['dealname'] = $message['name'];
        }
        $templates = [
            'Aliyun' => $template,
        ];
        $phone= $message['phone'];
        log::info("message:++++++:".print_r($sendinfo,true));
        //发送短信
        $return = PhpSms::make()
            ->to($phone)
            ->template($templates)
            ->data($sendinfo)
            ->content($content)
            ->send();
        //插入记录
        $map = array(
            'to' => $phone,
            'template' => $template,
            'content' => $classinfo,
            'msgtype' => $message['msgtype'],
        );
        if ($return['success'] === true){
            SendMsg::insert($map);
        } else {
            $addmap = array(
                'failmsg' =>json_encode($return)
            );
            $mapmerge = array_merge($map,$addmap);
            SendMsg::insert($mapmerge);
        }
    }








}
