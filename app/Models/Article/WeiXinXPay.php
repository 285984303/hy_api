<?php

namespace App\Models\Article;

use Illuminate\Database\Eloquent\Model;

class WeiXinXPay extends Model {

	
	public $config;
    public function __construct( $config )
    {
        $this->config = $config;
    }

    /**
     * 预支付请求接口(POST)
     * 返回json的数据
     */
    public function prepay( $obj )
    {
        $config = $this->config;
        $unifiedorder = array(
            'appid'            =>$config['appid'],
            'mch_id'        =>$config['pay_mchid'],
            'nonce_str'         =>self::getNonceStr(),
            'body'         =>$obj->body,
            'out_trade_no'   =>$obj->order_sn,
            'total_fee'         =>$obj->total_fee * 100,
            'spbill_create_ip'=>$_SERVER['REMOTE_ADDR'],
            'notify_url'     =>$config['notify_url'],
            'trade_type'     =>'JSAPI',
            'openid'        =>$obj->openid
        );
        $unifiedorder['sign'] = self::makeSign($unifiedorder);
        //请求数据
        $xmldata = self::array2xml($unifiedorder);
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $res = self::curl_post_ssl($url, $xmldata);
        if(!$res){
            self::return_err("Can't connect the server");
        }
        $content = self::xml2array($res);
        
        var_dump($content);exit;
        
        if(strval($content['result_code']) == 'FAIL'){
            self::return_err(strval($content['err_code_des']));
        }
        if(strval($content['return_code']) == 'FAIL'){
            self::return_err(strval($content['return_msg']));
        }
        //拼接小程序的接口数据
        $resData = array(
            'appId'       => strval($content['appid']),
            'timeStamp'    => time(),
            'nonceStr' => self::getNonceStr(),
            'package'  => 'prepay_id='.strval($content['prepay_id']),
            'signType' => 'MD5',
        );
        //加密签名
        $resData['paySign'] = self::makeSign($resData);
        exit(json_encode(array('status'=>1,'data'=>$resData)));
    }


    /**
     * @return array|bool
     * 微信支付回调验证
     * 返回数据
     */
    public function notify()
    {
        //$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml = file_get_contents('php://input');
        //将服务器返回的XML数据转化为数组
        $data = self::xml2array($xml);
        // 保存微信服务器返回的签名sign
        $data_sign = $data['sign'];
        // sign不参与签名算法
        unset($data['sign']);
        $sign = self::makeSign($data);
        // 判断签名是否正确  判断支付状态
        if ( ($sign===$data_sign) && ($data['return_code']=='SUCCESS') && ($data['result_code']=='SUCCESS') )
        {
            $result = $data;
            //获取服务器返回的数据
            //更新数据库
        }else
        {
            $result = false;
        }
        // 返回状态给微信服务器
        if ( $result )
        {
            $str='<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        }else
        {
            $str='<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
        }
        echo $str;
        return $result;
    }

//---------------------------------------------------------------用到的函数------------------------------------------------------------
    /**
     * 错误返回提示
     * @param string $errMsg 错误信息
     * @param string $status 错误码
     * @return  json的数据
     */
    protected function return_err($errMsg='error',$status=0)
    {
        exit(json_encode(array('status'=>$status,'result'=>'fail','errmsg'=>$errMsg)));
    }


    /**
     * 正确返回
     * @param  array $data 要返回的数组
     * @return  json的数据
     */
    protected function return_data($data=array())
    {
        exit(json_encode(array('status'=>1,'result'=>'success','data'=>$data)));
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

    /**
     * 微信支付发起请求
     */
    protected function curl_post_ssl($url, $xmldata, $second=30,$aHeader=array())
    {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        if( count($aHeader) >= 1 ){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xmldata);
        $data = curl_exec($ch);
        if($data){
            curl_close($ch);
            return $data;
        }
        else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }
}