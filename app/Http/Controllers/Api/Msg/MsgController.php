<?php

namespace App\Http\Controllers\Api\Msg;

use App\Models\Admin\Admin;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Home\User;
use App\Models\Log\LoginVerifyCode;

class MsgController extends Controller
{
    public function mobile_captcha()
    {
        try {
            $mobile = request('phone');
            $type = request('type')?request('type'):'login';
            //检索手机号
            if($type=='login'){
                $where = array(
                    'user_telphone'=>$mobile
                );
                $count = User::where($where)->count();
                $msg = "不存在此学员!";
            }else if($type=='coach'){
                $where = array(
                    'mobile_phone' =>$mobile,
                    'position' => '教练员'
                );
                $count = Admin::where($where)->count();
                $msg = "不存在此教练员!";
            }else{
                $count = 0;
                $msg = "数据异常!";
            }
            if(!$count){
                throw new \Exception($msg);
            }
            LoginVerifyCode::sendMobileCode($mobile,$type);
            return response()->json(['result' => 'success','code'=>200,'data' =>'']);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail','code'=>'405', 'msg' => $e->getMessage()]);
        }
    }
}
