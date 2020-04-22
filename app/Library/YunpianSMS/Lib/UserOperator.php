<?php namespace App\Library\YunpianSMS\Lib;
/**
 * Created by PhpStorm.
 * User: bingone
 * Date: 16/1/20
 * Time: 上午10:11
 */

class UserOperator
{
    public $apikey;
    public $api_secret;

    public function __construct($apikey=null,$api_secret=null)
    {
        if($api_secret == null)
            $this->api_secret = Config::API_SECRET;
        else
            $this->api_secret = $api_secret;
        if($apikey == null)
            $this->apikey = Config::API_KEY;
        else
            $this->apikey = $apikey;
    }
    public function encrypt(&$data){

    }
    public function get($data=[]){
        $data['apikey'] = $this->apikey;

        return HttpUtil::PostCURL(API::URI_GET_USER_INFO,$data);
    }
    public function set($data=[]){
        $data['apikey'] = $this->apikey;
        return HttpUtil::PostCURL(API::URI_SET_USER_INFO,$data);
    }
}
?>