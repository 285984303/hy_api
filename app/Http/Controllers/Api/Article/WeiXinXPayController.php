<?php namespace App\Http\Controllers\Api\Article;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 5/31/16
 * Time: 11:12 AM
 */

//use App\Library\Log;
//use App\Models\Log\Login;
//use App\Models\NotFound;
//use App\Models\ParameterError;
use View, Session, Validator;
use App\Models\Admin\Admin;
//use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Yansongda\Pay\Pay;
use App\Http\Controllers\Controller;
use Yansongda\Pay\Log;
use App\Models\Article\WeiXinXPay;

class WeiXinXPayController extends Controller
{
    //use AuthenticatesAndRegistersUsers, ThrottlesLogins;
    
    protected $guard = 'admin';
    protected $redirectTo = '/admin';
    
    public $config;
    public function __construct()
    {
        $config = array(
            'appid'=>'wxcb9cb0e0cc0bf74c',
            'pay_mchid'=>'1525900271',
            'pay_apikey'=>'chennianqing20180529lysfxydzswxy',//5eb0a646479227984887daa8fbc72601
            'notify_url'=>'xxxxxx',
            'body'=>'xxxxxx'
        );
        $this->config = $config;
    }
    
    
    /**
     * 预支付请求接口(POST)
     * $openid         openid
     * $body      商品简单描述
     * $order_sn    订单编号
     * $total_fee   金额
     * json的数据
     */
    public function requestPayment()
    {
        $errors = new \stdClass();
        $request = request()->all();
        //$object = $this->JsonToArray($request);
        //$res = Order::where(['order_id'=>trim($object['order_id']),'openid'=>trim($object['openid'])])->select('openid','price','pay_status','order_id')->first();
        $res = true;
        if( $res  )//&& $res->pay_status == 0
        {
            $pay = new WeiXinXPay( $this->config );
            $obj = new \stdClass();
//             $obj->openid = $res->openid;
//             $obj->body = $this->config['body'];
//             $obj->order_sn = $res->order_id;
//             $obj->total_fee = $res->price;
            $obj->openid = 'openidopenidopenid';
            $obj->body = $this->config['body'];
            $obj->order_sn = '1234567890';
            $obj->total_fee = 1;
            $result = $pay->prepay( $obj );
            $result = json_decode($result);
            if( $result->status == 1 )
            {
                $errors->status = 1;
                $errors->data = $result->data;
                return response()->json($errors, 200);
            }else
            {
                $errors->status = 0;
                $errors->result = '支付异常';
                return response()->json($errors, 200);
            }
        }else
        {
            $errors->status = 0;
            $errors->result = '支付异常';
            return response()->json($errors, 200);
        }
    }
    
    /**
     * 支付回调
     * 返回数组去修改支付订单数据
     */
    public function notifyPay()
    {
        $notify = new WeiXinXPay( $this->config );
        $data = $notify->notify();
        if( $data )
        {
            //修改数据库订单状态
        }
    }
    

    
    
    
}
