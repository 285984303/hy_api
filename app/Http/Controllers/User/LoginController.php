<?php

namespace App\Http\Controllers\User;

use App\Models\Data\LicenceType;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Home\User;
use App\Models\Log\LoginVerifyCode;


class LoginController extends Controller
{
    public function __construct()
    {
        error_reporting(1);
    }
    public function login(){
        return view('home.login.login'); //登录界面
    }

    public function dologin(){
        try {
            $showfields = [];
            $fields = ['id', 'user_truename', 'password', 'pass_salt', 'user_img', 'user_telphone', 'new_province_id', 'new_city_id', 'new_area_id','coach_group'];
            // 密码登录
            if (!request('msgcode')) {
                $mobile = request('user_telphone');
                if (!$mobile) {
                    throw new \Exception('不存在此报名学员!');
                }
                $user = User::findUser($mobile, $fields);
                $password = request('password');
                $user->isMyPassword($password);
            } else {
                // 验证码登录
                $mobile = LoginVerifyCode::getLoginMobile(request('msgcode'));
                try {
                    $user = User::findUser($mobile, $fields);
                } catch (\Exception $e) {
                    // 用户不存在 初始化用户
                    throw new \Exception('不存在此报名学员!');
                }
                auth()->login($user);
            }
            $query = User::find($user->id);
            $token = md5(md5(time().$user->id.$user->user_product->school_id));
            $query->token = $token;
            \Session::put('appoint-token',$token);
            $query->save();

            $showfields['id'] = $user->id;
            $showfields['name'] = $user->user_truename;
            $showfields['school_id'] = $user->user_product->school_id;
            $showfields['user_img'] = $user->user_img;
            $showfields['address'] = $user->xz_address;
            $showfields['packages_id'] = $user->user_product->product_id;
            $showfields['licence_type'] = $user->user_product->old_licence_type;
            //读取
            $licence = LicenceType::where('name','=',$user->user_product->old_licence_type)->first();
            $showfields['licencetype_id'] = $licence->id;
//            auth()->login($showfields);
            \Session::put('licencetype_id',$showfields['licencetype_id']);
            \Session::put('coach_group',$user->coach_group);
            return response()->json(['result' => 'success', 'code' => 200, 'token' => $token, 'data' => $showfields]);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail','code'=>$e->getCode(),'msg' => $e->getMessage()]);
        }
    }

    public function loginout()
    {
        /*$data = request()->all();
        if($data['openid']){
            $info = User::where('openid','=',$data['openid'])->first();
            if($info){
                $update = User::find($info->id);
                $update->openid = '';
                $update->token  = '';
                $update->save();
            }

        }*/
        auth()->logout();
//        return response()->json(['result' => 'success','code'=>200]);
        header('Location:/');
    }


}
