<?php

namespace App\Http\Controllers\Api\Small;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class GetOpenIdController extends Controller
{
    //

    public  function getopenid(){
        $code = request('code');
        if(!$code){
            return response()->json(['result' => 'fail', 'code' =>405, 'msg' => "无法识别"]);
        }
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=wx94373c89a4410b92&secret=17a52f286a893c6c4884c493a3f9a8e5&js_code=".$code."&grant_type=authorization_code";
        $info  = file_get_contents($url);
        $Response = json_decode($info,true);
        if($Response['openid']){
            return response()->json(['result' => 'success', 'code' => 200, 'data' => $Response]);
        }else{
//            log::info("++++++++++:".print_r($Response));
            return response()->json(['result' => 'fail', 'code' =>405, 'msg' => "无法获取openid"]);
        }
    }
}
