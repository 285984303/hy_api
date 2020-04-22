<?php namespace App\Library\YunpianSMS\Lib;
/**
 * Created by PhpStorm.
 * User: bingone
 * Date: 16/1/19
 * Time: 下午5:42
 */

class SmsOperator
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

    public function single_send($data = [])
    {
        if (!array_key_exists('mobile', $data))
            return new Result(null, $data, null, 'mobile 为空');
        if (!array_key_exists('text', $data))
            return new Result(null, $data, null, 'text 为空');
        $data['apikey'] = $this->apikey;

        return HttpUtil::PostCURL(API::URI_SEND_SINGLE_SMS, $data);
    }

    public function batch_send($data = [])
    {
        if (!array_key_exists('mobile', $data))
            return new Result(null, $data, null, $error = 'mobile 为空');
        if (!array_key_exists('text', $data))
            return new Result(null, $data, null, $error = 'text 为空');
        $data['apikey'] = $this->apikey;

        return HttpUtil::PostCURL(API::URI_SEND_BATCH_SMS, $data);
    }

    public function multi_send($data = [])
    {
        if (!array_key_exists('mobile', $data))
            return new Result(null, $data, null, $error = 'mobile 为空');
        if (!array_key_exists('text', $data))
            return new Result(null, $data, null, $error = 'text 为空');
        if (count(explode(',', $data['mobile'])) != count(explode(',', $data['text'])))
            return new Result(null, $data, null, $error = 'mobile 与 text 个数不匹配');
        $data['apikey'] = $this->apikey;
        $text_array = explode(',', $data['text']);
        $data['text'] = '';
        for ($index = 0; $index < count($text_array); $index++) {
            $data['text'] .= urlencode($text_array[$index]) . ',';
        }
        $data['text'] = substr($data['text'],0,-1);
        return HttpUtil::PostCURL(API::URI_SEND_MULTI_SMS, $data);
    }

    public function tpl_send($data = [])
    {
        if (!array_key_exists('mobile', $data))
            return new Result(null, $data, null, 'mobile 为空');
        if (!array_key_exists('tpl_id', $data))
            return new Result(null, $data, null, 'tpl_id 为空');
        if (!array_key_exists('tpl_value', $data))
            return new Result(null, $data, null, 'tpl_value 为空');

        $data['apikey'] = $this->apikey;

        return HttpUtil::PostCURL(API::URI_SEND_TPL_SMS, $data);
    }
}
