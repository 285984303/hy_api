<?php namespace App\Library\YunpianSMS\Lib;
/**
 * Created by PhpStorm.
 * User: bingone
 * Date: 16/1/19
 * Time: 下午5:42
 */

class VoiceOperator
{
    public $apikey;
    public $api_secret;

    public function __construct($apikey = null, $api_secret = null)
    {
        if ($api_secret == null)
            $this->api_secret = Config::API_SECRET;
        else
            $this->api_secret = $api_secret;
        if ($apikey == null)
            $this->apikey = Config::API_KEY;
        else
            $this->apikey = $apikey;
    }

    public function encrypt(&$data)
    {

    }

    public function send($data = [])
    {
        if (!array_key_exists('mobile', $data))
            return new Result($error = 'mobile 为空');
        if (!array_key_exists('code', $data))
            return new Result($error = 'code 为空');
        $data['apikey'] = $this->apikey;
//        encrypt($data);
        return HttpUtil::PostCURL(API::URI_SEND_VOICE_SMS, $data);
    }

    public function pull_status($data=[])
    {
        $data['apikey'] = $this->apikey;
        return HttpUtil::PostCURL(API::URI_PULL_VOICE_STATUS, $data);
    }

}
