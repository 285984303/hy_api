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

class WxPayController extends Controller
{
    use AuthenticatesAndRegistersUsers, ThrottlesLogins;
    
    protected $guard = 'admin';
    protected $redirectTo = '/admin';
    
    protected $config = [
        //'appid' => 'wxcb9cb0e0cc0bf74c', // APP APPID
        //'app_id' => 'wxb3fxxxxxxxxxxx', // 公众号 APPID
        'miniapp_id' => 'wxcb9cb0e0cc0bf74c', // 小程序 APPID
        'mch_id' => '1525900271',
        'key' => '5eb0a646479227984887daa8fbc72601',
        'notify_url' => 'http://yanda.net.cn/notify.php',
        'cert_client' => './cert/apiclient_cert.pem', // optional，退款等情况时用到
        'cert_key' => './cert/apiclient_key.pem',// optional，退款等情况时用到
        'log' => [ // optional
            'file' => './logs/wechat.log',
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
        'mode' => 'dev', // optional, dev/hk;当为 `hk` 时，为香港 gateway。
    ];
    
    public function index()
    {
        $order = [
            'out_trade_no' => time(),
            'total_fee' => '1', // **单位：分**
            'body' => 'test body - 测试',
            'openid' => 'onkVf1FjWS5SBIixxxxxxx',
        ];
        
        $pay = Pay::wechat($this->config)->mp($order);
        
        // $pay->appId
        // $pay->timeStamp
        // $pay->nonceStr
        // $pay->package
        // $pay->signType
    }
    
    public function pay()
    {
        $insert_id = request('id');
        $money = trim(request('money'));
        $user_id = request('userid');
        $path = public_path();
        $config = [
            //'app_id' => 'wxcb9cb0e0cc0bf74c',
            'miniapp_id' => 'wxcb9cb0e0cc0bf74c',
            'mch_id' => '1525900271',
            'key' => '5eb0a646479227984887daa8fbc72601',
            'cert_path' => $path.'/apiclient_cert.pem', //绝对
            'key_path' => $path.'/apiclient_key.pem',
            'notify_url' => 'http://dev.backend.krttech.com/wxnotify/'.$insert_id.'/'.$user_id.'/'.$money, // 支付通知地址
            //'mode' => 'dev', // optional, dev/hk;当为 `hk` 时，为香港 gateway。
        ];

       
        $this->config = $config;
        var_dump($config);
        $order = [
            'out_trade_no' => time(),
            'body' => 'subject-测试',
            'total_fee'      => '1',
        ];
        
        // 扫码支付使用 模式二
        //$result = $wechat->scan($order);
        $pay = Pay::wechat($this->config)->miniapp($order);
        $qr = $pay->code_url;
    }
    
    public function notify()
    {
        $pay = Pay::wechat($this->config);
        
        try{
            $data = $pay->verify(); // 是的，验签就这么简单！
            
            Log::debug('Wechat notify', $data->all());
        } catch (Exception $e) {
            // $e->getMessage();
        }
        
        return $pay->success()->send();// laravel 框架中请直接 `return $pay->success()`
    }
    
    /**
     * 将一个数组转换为 XML 结构的字符串
     * @param array $arr 要转换的数组
     * @param int $level 节点层级, 1 为 Root.
     * @return string XML 结构的字符串
     */
    protected function array2xml($arr, $level = 1)
    {
        $s = $level == 1 ? "<xml>" : '';
        foreach($arr as $tagname => $value)
        {
            if (is_numeric($tagname))
            {
                $tagname = $value['TagName'];
                unset($value['TagName']);
            }
            if(!is_array($value))
            {
                $s .= "<{$tagname}>".(!is_numeric($value) ? '<![CDATA[' : '').$value.(!is_numeric($value) ? ']]>' : '')."</{$tagname}>";
            }else
            {
                $s .= "<{$tagname}>" . $this->array2xml($value, $level + 1)."</{$tagname}>";
            }
        }
        $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
        return $level == 1 ? $s."</xml>" : $s;
    }
    
    /**
     * 将xml转为array
     * @param  string  $xml xml字符串
     * @return array    转换得到的数组
     */
    protected function xml2array($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $result= json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }
    
    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    protected function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )
        {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }
    
    /**
     * 生成签名
     * @return 签名
     */
    protected function makeSign($data)
    {
        //获取微信支付秘钥
        $key = $this->config['pay_apikey'];
        //去空
        $data = array_filter($data);
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string_a = http_build_query($data);
        $string_a = urldecode($string_a);
        //签名步骤二：在string后加入KEY
        $string_sign_temp = $string_a."&key=".$key;
        //签名步骤三：MD5加密
        $sign = md5($string_sign_temp);
        // 签名步骤四：所有字符转为大写
        $result=strtoupper($sign);
        return $result;
    }
    
    
    
}
