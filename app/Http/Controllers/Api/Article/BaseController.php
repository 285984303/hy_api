<?php

namespace App\Http\Controllers\Api\Article;

use App\Models\Admin\Admin;
use App\Models\Data\LicenceType;
use App\Models\Home\User;
use App\Models\Packages\Packages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class BaseController extends Controller
{
    protected $_openid;
    protected $_token;
    public function __construct()
    {
        $this->_openid = request('openid');
        $this->_token  = request('token');
    }

    /*
     * @Des:检测用户是否需要登录
     * */
    public function checkIsLogin($parms=array())
    {
        if((!trim($parms['openid'])) && (!trim($parms['token']))){
            return false;
        }
        $Coch_Cache_Key = "Coach_Info_".session('token');
        if(session('openid')!=$parms['openid']){
            Redis::del($Coch_Cache_Key);
        }
        if(!Redis::GET($Coch_Cache_Key)){
            if($parms['openid']){
                $admin = Admin::where('openid', '=', $parms['openid'])->first();
            }else{
                $admin = Admin::where('token', '=', $parms['token'])->first();
            }
            if(!$admin){
                return false;
            }
            $token = $admin->token;
            $info['id'] = $admin->id;
            $info['name'] = $admin->admin_name;
            $info['phone'] = $admin->mobile_phone;
            $info['school_id'] = $admin->school_id;
            $info['address'] = $admin->addr_detail;
            $info['user_img'] = $admin->admin_thumb;
            $info['licence_type_id'] = $admin->licence_type_id;
            Redis::setex("Coach_Info_".$token,600,json_encode($info));
        }else{
            $token = session('token');
            $info = json_decode(Redis::GET($Coch_Cache_Key),true);
        }
        \Session::put('school_id', $info['school_id']);
        \Session::put('openid', $parms['openid']);
        \Session::put('admin_id', $info['id']);
        \Session::put('admin_name', $info['name']);
        \Session::put('token', $token);
        \Session::put('licence_type_id', $info['licence_type_id']);
        return true;
    }

    /*
     * @Des:未登录信息处理
     * */
    public function notLoginInfo(){
        header('content-type:application/json;charset=utf8');
        $array=array(
            'result' => 'fail', 'code' => 400,'msg'=>'用户未登录'
        );
        return json_encode($array);
    }

    /*
     * @Des:不通过参数
     * */
    public function notPassParms($p){
        header('content-type:application/json;charset=utf8');
        $array=array(
            'result' => 'fail', 'code' => 402,'msg'=>'参数['.$p."]不合法!"
        );
        return json_encode($array);
    }



    /*
     * @Des:参数检测允许通过参数检测
     * */
    public function checkParms($parms=array()){
        $listparms = array('phone','openid','msgcode','token','subject','coach','date','ids','id','page','appointment_type','s','classid','termphone','logintype');
        foreach ($parms as $k=>$v){
            if(!in_array($k,$listparms)){
                return $k;
            }
        }
        return false;
    }

    /*
     * @Des:Redis缓存信息处理
     * */
    public function initRedisInfo(){

    }


}
