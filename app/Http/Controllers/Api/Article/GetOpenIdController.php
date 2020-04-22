<?php

namespace App\Http\Controllers\Api\Article;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class GetOpenIdController extends Controller
{
    private $appid;
    private $secret;
    public function __construct()
    {
        error_reporting(1);
        $this->appid = "wxa4b3f11c6043a16d";
        $this->secret = "4176f5ff84bdad745f465570798a2ed3";
    }

    public  function getopenid(){
        $code = request('code');
        if(!$code){
            return response()->json(['result' => 'fail', 'code' =>405, 'msg' => "无法识别"]);
        }
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$this->appid."&secret=".$this->secret."&js_code=".$code."&grant_type=authorization_code";
        $info  = file_get_contents($url);
//        log::info("++++++++++:url:".$url);
        $Response = json_decode($info,true);
        if($Response['openid']){
            return response()->json(['result' => 'success', 'code' => 200, 'data' => $Response]);
        }else{
            log::info("++++++++++:".print_r($Response));
            return response()->json(['result' => 'fail', 'code' =>405, 'msg' => "无法获取openid"]);
        }
    }
}
