<?php

namespace App\Http\Controllers\Api\Small;

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
    protected $_userid;

    public function __construct()
    {

        $this->_openid = request('openid');
        $this->_token  = request('token');
        $this->_userid  = empty(session('user_id'))? request('userid'):session('user_id');

    }

    /*
     * @Des:根据openid 检测用户登录信息
     * */
    public function getUserInfoByOpenid($openid)
    {
        $Cache_Key = "UserInfo_".$openid;
        if(!Redis::GET($Cache_Key)){
            $fields = ['id', 'user_truename','licence_type_id', 'password', 'pass_salt', 'user_img', 'user_telphone', 'new_province_id', 'new_city_id', 'new_area_id','token'];
            $user = User::findUserByOpenid($openid, $fields);
            if(!$user){
                return false;
            }
            $showfields['id'] = $user->id;
            $showfields['name'] = $user->user_truename;
            $showfields['token'] = $user->token;
            $showfields['school_id'] = $user->user_product->school_id;
            $showfields['user_img'] = $user->user_img;
            $showfields['address'] = $user->xz_address;
            $showfields['packages_id'] = $user->user_product->product_id;
            $showfields['licence_type'] = $user->user_product->old_licence_type;
            $showfields['user_licencetype_id'] = $user->licence_type_id;
            Redis::setex($Cache_Key,3600,json_encode($showfields));
        }else{
            $showfields = json_decode(Redis::GET($Cache_Key),true);
        }
        \Session::put('school_id', $showfields['school_id']);
        \Session::put('user_id', $showfields['id']);
        \Session::put('username', $showfields['name']);
        \Session::put('packages_id', $showfields['packages_id']);
        \Session::put('user_licencetype_id', $showfields['user_licencetype_id']);
        return $showfields;
    }

    /*
     * @Des:接口返回状态码定义
     * */
    public function code()
    {

        $code = array(
            //1000 ——1999:
            '100' => '',
            '101' => '',
            '200' => '请求成功且存在数据',
            '201' => '请求成功数据为空',
            '300' => '',
        );


    }

    /*
     * @Des:检测用户是否需要登录
     * */
    public function checkIsLogin($parms=array())
    {
        if((!trim($parms['openid'])) && (!trim($parms['user_id']))){
            return false;
        }
        if($parms['openid']){
            $Cache_Key = "UserIsLogin_".$parms['openid'];
        }
        if($parms['token']){
            $Cache_Key = "UserIsLogin_".$parms['token'];
        }
        //$Cache_Key = "UserIsLogin_".$parms['user_id'];
        if(!Redis::GET($Cache_Key)){
            if($parms['openid'])
            {
                $user = User::where('openid', '=', $parms['openid'])->first();
//                $Cache_Key_ = "UserIsLogin_".$parms['openid'];
            }else{
                $user = User::where('token', '=', $parms['token'])->first();
//                $Cache_Key_ = "UserIsLogin_".$parms['token'];
            }
            if($parms['user_id'])
            {
                $user = User::where('id', '=', $parms['user_id'])->first();
            }
            if(!$user)
            {
                return false;
            }
            $info['school_id'] = $user->user_product->school_id;
            $info['user_id'] = $user->id;
            $info['user_licencetype_id'] = $user->licence_type_id;
            $info['username'] = $user->user_truename;
            $info['packages_id'] = $user->user_product->product_id;
            //读取套餐内学时
            $allcourse = Packages::find($info['packages_id']);
            $info['class_nums'] = $allcourse->subject_two;
            $info['setcost'] = $allcourse->setcost;
            $info['class_auto'] = $allcourse->autoappointment;
            $info['user_telphone'] = $user->user_telphone;
            $info['coach_group'] = $user->coach_group;

            //读取驾照类型ID
            $licence = LicenceType::where('name','=',$user->user_product->old_licence_type)->first();
            $info['licencetype_id'] = $licence->id;
            Redis::setex($Cache_Key,60,json_encode($info));
        }else{
            $info = json_decode(Redis::GET($Cache_Key),true);
        }
        \Session::put('school_id', $info['school_id']);
        \Session::put('user_id', $info['user_id']);
        \Session::put('username', $info['username']);
        \Session::put('packages_id', $info['packages_id']);
        \Session::put('setcost', $info['setcost']);
        \Session::put('class_nums', $info['class_nums']);
        \Session::put('licencetype_id', $info['licencetype_id']);
        \Session::put('user_licencetype_id', $info['user_licencetype_id']);
        \Session::put('class_auto', $info['class_auto']);
        \Session::put('phone', $info['user_telphone']);
        \Session::put('coach_group', $info['coach_group']);
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
        $listparms = array('phone','openid','userid','msgcode','token','subject','coach','date','ids','id','page','appointment_type','s','classid','termphone','logintype'
        ,'assess_num','assess_tag','assess_content','complain_num','complain_tag','complain_content','submit_code'
        );
        foreach ($parms as $k=>$v){
            if(!in_array($k,$listparms)){
                return $k;
            }
        }
        return false;
    }


}
