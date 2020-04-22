<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 7/27/16
 * Time: 3:42 PM
 */

namespace App\Library;

use App\Library\AliSMS\Top\TopClient;
use App\Library\AliSMS\Top\Request\AlibabaAliqinFcSmsNumSendRequest;
use App\Library\YunpianSMS\Lib\SmsOperator;
use App\Models\ParameterError;

class SMS {
    // todo modify to config
    const APP_KEY = '23401842';
    const SECRET_KEY = '6355894c0d55f7109bddf60875983f41';

    public function __construct() {
    }

    /**
     * @param string $rec_num           手机号
     * @param string $sms_template_code 模板 ID
     * @param string $sign_name         签名
     * @param array  $sms_param         参数
     * @param string $sms_type          短信类型
     * @param string $extend            传回参数
     *
     * @return mixed
     * @throws \Exception
     */
    public static function sendMessage($rec_num, $sms_template_code, $sign_name, $sms_param = [], $sms_type = 'normal', $extend = ''){
        try {
            if (!preg_match('/^1\d{10}$/',$rec_num))
                throw new ParameterError('wrong mobile phone number');
            $sms_param = json_encode($sms_param);

            $c            = new TopClient;
            $c->appkey    = self::APP_KEY;
            $c->secretKey = self::SECRET_KEY;
            $req          = new AlibabaAliqinFcSmsNumSendRequest;

            $req->setExtend($extend);
            $req->setSmsType($sms_type);
            $req->setSmsFreeSignName($sign_name);
            $req->setSmsParam($sms_param);
            $req->setRecNum($rec_num);
            $req->setSmsTemplateCode($sms_template_code);

            $c->execute($req);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $mobile
     * @param $code
     *
     * @throws \Exception
     */
    public static function sendVerifyCode($mobile, $code) {
        //验证码${code}，您正在登录${product}，若非本人操作，请勿泄露。
        self::sendMessage($mobile,'SMS_11885007','登录验证',['code'=>(string)$code, 'product'=>'56驾考']);
    }

    // SMS_15410153

    /**
     * @param $mobile
     * @param $code
     * @param $money
     *
     * @throws \Exception
     */
    public static function sendFinanceVerifyCode($mobile, $code, $money) {
        //${date} 财务收款总数为 ${money} 元，验证码为${code}，5分钟内有效。详情请进入收费详情查看。
        self::sendMessage($mobile,'SMS_15410153','登录验证',['code'=>(string)$code, 'date'=> date('Y-m-d'), 'money' => (string)$money]);
    }

    public static function sendMultiMessage($mobile, $content, $api_key = null, $api_secret = null){
        $smsOperator = new SmsOperator($api_key, $api_secret);
        if (is_array($mobile)) {
            if (count($mobile) > 999) {
                throw new ParameterError('一次最多只能发送1000条');
            }
            $data['mobile'] = implode(',',$mobile);
            $contents = [];
            for($i=0;$i<count($mobile);$i++) {
                $contents[] = $content;
            }
            $data['text'] = implode(',',$contents);
        } else {
            $data['mobile'] = $mobile;
            $data['text'] = $content;
        }
        // return $data;

        $result = $smsOperator->batch_send($data);
        return $result;
    }
}