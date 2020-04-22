<?php namespace App\Library\YunpianSMS\Lib;
/**
 * Created by PhpStorm.
 * User: bingone
 * Date: 16/1/20
 * Time: 上午10:37
 */

class TplOperator
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
    public function get_default($data = [])
    {
        $data['apikey'] = $this->apikey;

        return HttpUtil::PostCURL(API::URI_GET_DEFAULT_TEMPLATE, $data);
    }
    public function get($data = [])
    {
        $data['apikey'] = $this->apikey;

        return HttpUtil::PostCURL(API::URI_GET_TEMPLATE, $data);
    }

    public function add($data = [])
    {
//        if (!array_key_exists('tpl_id',$data))
//            return new Result(null,$data,null,$error = 'tpl_id 为空');
        if (!array_key_exists('tpl_content',$data))
            return new Result(null,$data,null,$error = 'tpl_content 为空');
        $data['apikey'] = $this->apikey;
        return HttpUtil::PostCURL(API::URI_ADD_TEMPLATE, $data);
    }

    public function upd($data = [])
    {
        if (!array_key_exists('tpl_id',$data))
            return new Result(null,$data,null,$error = 'tpl_id 为空');
        if (!array_key_exists('tpl_content',$data))
            return new Result(null,$data,null,$error = 'tpl_content 为空');
        $data['apikey'] = $this->apikey;
        return HttpUtil::PostCURL(API::URI_UPD_TEMPLATE, $data);
    }

    public function del($data = [])
    {
        if (!array_key_exists('tpl_id',$data))
            return new Result(null,$data,null,$error = 'tpl_id 为空');
        $data['apikey'] = $this->apikey;

        return HttpUtil::PostCURL(API::URI_DEL_TEMPLATE, $data);
    }

}