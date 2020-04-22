<?php

namespace App\Http\Controllers\Api\Small;

use App\Models\Home\User;
use App\Models\Log\LoginVerifyCode;
use App\Models\NotFound;
use App\Models\ParameterError;
use App\Models\StoreError;
use App\Models\Student\Examination;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class LoginController extends BaseController
{
    private $_userinfo;
    public function __construct()
    {
        parent::__construct();
    }

//    public $showfields;
    public function login(Request $request)
    {
        try {
            $showfields = [];
            $fields = ['id', 'user_truename', 'password', 'pass_salt', 'user_img', 'user_telphone', 'new_province_id', 'new_city_id', 'new_area_id'];
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
            $token = md5(md5(time().$user->id.$user->user_product->school_id));
            $query->token = $token;
            $query->openid= $this->_openid;
            $query->save();
            $showfields['id'] = $user->id;
            $showfields['name'] = $user->user_truename;
            $showfields['school_id'] = $user->user_product->school_id;
            $showfields['user_img'] = $user->user_img;
            $showfields['address'] = $user->xz_address;
            $showfields['packages_id'] = $user->user_product->product_id;
            $showfields['licence_type'] = $user->user_product->old_licence_type;
            return response()->json(['result' => 'success', 'code' => 200, 'token' => $token, 'data' => $showfields]);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail','code'=>$e->getCode(),'msg' => $e->getMessage()]);
        }
    }
    
    /**
     * 找回密码
     * @param Request $request
     * @throws \Exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function find_pass()
    {
        try {
            $showfields = [];
            $fields = ['id', 'user_truename', 'password', 'pass_salt', 'user_telphone'];
            // 密码登录
            $phone = request('phone');
            $password = request('password');
            $password2 = request('password2');
            $msgcode = request('msgcode');
            
            if($password != $password2)
            {
                return response()->json(['result' => 'fail', 'msg' => '新密码不一致']);
            }
            if (empty($phone))
            {
                return response()->json(['result' => 'fail', 'msg' => '手机号不能空']);
            }
            if (empty($msgcode))
            {
                //throw new \Exception('不存在报名学员');
                return response()->json(['result' => 'fail', 'msg' => '验证码不能空']);
            }
            // 验证码登录
            $mobile = LoginVerifyCode::getLoginMobile($msgcode,$phone);
            try {
                $user = User::findUser($mobile, $fields);
            } catch (\Exception $e) {
                // 用户不存在 初始化用户
                throw new \Exception('不存在此报名学员');
            }
            $query = User::find($user->id);
            //$token = md5(md5(time().$user->id.$user->user_product->school_id));
            $query->password = $password;
            $query->updated_at= date('Y-m-d H:i:s');
            $query->save();
            //$showfields['id'] = $user->id;

            return response()->json(['result' => 'success', 'code' => 200, 'data' => $user->id]);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail','code'=>$e->getCode(),'msg' => $e->getMessage()]);
        }
    }
    
    /**
     * 修改密码
     * @throws \Exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function up_pass()
    {
        try 
        {
            

        $userid = session('user_id');
        if(empty($userid))
        {
            $userid = request('userid');
        }
        //echo $userid;
        //$phone = request('phone');
        if (empty($userid)) 
        {
            throw new \Exception('不存在此报名学员!');
        }
        $oldpass = request('oldpass');
        $password = request('password');
        $password2 = request('password2');
        
        if($password != $password2)
        {
            return response()->json(['result' => 'fail', 'msg' => '新密码不一致']);
        }
        
        $query = User::find($userid);
        
        $fields = ['id',  'password', 'pass_salt', 'user_telphone'];
        $user = User::findUser($query->user_telphone, $fields);
        $user->isMyPassword($oldpass);
        

        $query->password = $password;
        $query->updated_at= date('Y-m-d H:i:s');
        $query->save();
        return response()->json(['result' => 'success','code'=>200,'id'=>$userid,'data' =>'']);
        }
        catch (\Exception $e)
        {
            return response()->json(['result' => 'fail', 'msg' => $e->getMessage(),'id'=>$userid]);
        }

        //auth()->login($user);
    }

    public function mobile_captcha()
    {
        try {
            $mobile = request('phone');
            //检索手机号
            $where = array(
                'user_telphone'=>$mobile
            );
            $count = User::where($where)->count();
            if(!$count){
                throw new \Exception('不存在此学员!');
            }
            $info = LoginVerifyCode::sendMobileCode($mobile);
            return response()->json(['result' => 'success','code'=>200,'data' =>'']);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail', 'msg' => $e->getMessage()]);
        }
    }

    public function loginout()
    {
        $data = request()->all();
        if($data['openid']){
            $info = User::where('openid','=',$data['openid'])->first();
            if($info){
                $update = User::find($info->id);
                $update->openid = '';
                $update->token  = '';
                $update->save();
            }

        }
        auth()->logout();
        //清空Redis

        return response()->json(['result' => 'success','code'=>200]);
    }

    /*
     * @Des:返回用户信息
     * */
    public function ResponseJson(){
        header('content-type:application/json;charset=utf8');
        $token = $this->_userinfo['token'];
        unset($this->_userinfo['token']);
        $array=array(
            'result' => 'success', 'code' => 201, 'token' => $token, 'data' => $this->_userinfo
        );
        return json_encode($array);
    }

}
