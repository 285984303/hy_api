<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 18/11/2016
 * Time: 9:55 AM
 */

namespace App\Library;

use App\Models\Log\Operate;
use Illuminate\Support\Facades\Log;

/**
 * Class Http
 * @package App\Library
 *
 *
 * eg : $result =  Http::get('http://www.baidu.com');
 *      if(!$result->error()) {
 *          return $result->result_array;
 *      } else {
 *          return $result->error();
 *      }
 */
class Http
{
    const YUNGUAN_HOST = "http://114.55.58.112:8085";
    const PROVINCE_HOST = "http://202.98.194.238:8091/hz";
    const CERT_SN  = '15E171BD22A';
    const CERT_FILE  = 'apk/beijingkeruite.pfx';
    const CERT_PASSWORD  = 'Bjkrt2314';
//    const YUNGUAN_HOST = "http://114.55.89.156:8085";//测试地址
//    const PROVINCE_HOST = "http://183.252.17.24/hz/";//测试地址
//    const CERT_SN  = '15B3D4E5575';//测试地址
//    const CERT_FILE  = 'apk/xw0066051183145632.pfx';//测试地址
//    const CERT_PASSWORD  = '1';//测试地址
    const YUNGUAN_VERSION = '1.0.0.e2';
    const YUNGUAN_CERT_SN = '';
    public $result_array;
    public $errorcode;
    public $message;
    public $data;
    public $id;

    public $version;
    public $timestamp;
    public $sign_str;
    // public $cert_sn;

    public $url;
    public $method;
    public $param;
    public $json;
    public $timeout;
    public $CA;

    public $code;

    private $ch;

    public function __construct($url, $method, $data, $json, $timeout, $CA)
    {
//        self::YUNGUAN_HOST=env('YUNGUAN_HOST',null);
        //var_dump($data);
        //$data=['type'=>'stuimg','file'=>storage_path('a.png')];
        $this->param = array_merge($data, [
            'version' => self::YUNGUAN_VERSION,
            'cert_sn' => self::CERT_SN,
        ]);
//        if (strpos('https://', $url) !== false && strpos('http://', $url) !== false) {
//            $url = self::YUNGUAN_HOST . $url;
//        }
        if (!empty(strstr($url, 'top/')) && empty(strstr($url, 'http://'))) {
            $url = self::YUNGUAN_HOST . $url;
        } elseif (empty(strstr($url, 'http://'))) {
            $url = self::PROVINCE_HOST . $url;
        }
        foreach ($this->param as $key => $value) {
            $key_string = '{' . $key . '}';
            if (strpos($url, $key_string) !== FALSE) {
                unset($this->param[$key]);
                $url = str_replace($key_string, $value, $url);
            }
        }

        $timestamp = round(microtime(true) * 1000);

        $method = strtoupper($method);
        if ($method === 'FILE') {
            $url = str_replace('{sign_str}', $this->get_sign(file_get_contents($this->param['file']), $timestamp, true), $url);
        } elseif ($method === 'DELETE') {
            if (count($this->param) < 2) {
                $url = str_replace('{sign_str}', $this->get_sign($this->param['delete_num'], $timestamp), $url);
            } else {
                $url = str_replace('{sign_str}', $this->get_sign($this->param['cardnum'] . $this->param['name'], $timestamp), $url);
            }
        } elseif ($method == 'GET') {
            if (count($this->param) < 2) {
                $url = str_replace('{sign_str}', $this->get_sign($this->param['common'], $timestamp), $url);
            } else {
                $url = str_replace('{sign_str}', $this->get_sign($this->param['cardnum'] . $this->param['name'], $timestamp), $url);
            }
        } else {
            $url = str_replace('{sign_str}', $this->get_sign(json_encode($this->param, JSON_UNESCAPED_UNICODE), $timestamp), $url);

        }

        $url = str_replace('{timestamp}', $timestamp, $url);
        //var_dump($url);
        $this->url = $url;
        $this->method = $method;
        $this->json = $json;
        $this->timeout = $timeout;
        $this->CA = $CA;
        log::info("接口URL:========================" . $this->url);
        $SSL = substr($url, 0, 8) == 'https://' ? TRUE : FALSE;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout - 2);

        if ($SSL && $CA) {
            $cacert = 'file path'; //CA根证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);   // 只信任CA颁布的证书
            curl_setopt($ch, CURLOPT_CAINFO, $cacert); // CA根证书（用来验证的网站证书是否是CA颁布）
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名，并且是否与提供的主机名匹配
        } else {
            if ($SSL && !$CA) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 信任任何证书
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 检查证书中是否设置域名
            }
        }

        switch ($this->method) {
            case 'POST' :
                curl_setopt($ch, CURLOPT_POST, TRUE);
                break;// post
            case 'PUT' :
                curl_setopt($ch, CURLOPT_PUT, TRUE);
                break;// post
            case 'DELETE' :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;// post
            case 'FILE' :
                curl_setopt($ch, CURLOPT_POST, TRUE);
                $fi = new \finfo(FILEINFO_MIME_TYPE);
                $filename = $this->param['file'];
//            var_dump($filename);
//            var_dump($fi->file($filename));exit;
                $data = [];
                $data['file'] = new \CURLFile(ltrim($filename, '@'), $fi->file($filename));
                break;// post
            default :
                break;
        }
        log::info("接口传输DATE:========================" . print_r($data, true));
        if ($this->json) {

            $data = json_encode($this->param, JSON_UNESCAPED_UNICODE);
            // var_dump($data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json;charset=utf-8',
                'Content-Length: ' . strlen($data),
                // 'Accept: application/json',
            ]);
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
//                'Content-Length: ' . strlen($data),
                // 'Accept: application/json',
            ]);
            //$data['test'] = '1231';
        }
        if ($this->method == 'GET') {

        } else {

            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        // var_dump($ch);
        $this->ch = $ch;
    }

    public function get_sign($data, $timestamp, $is_file = false)
    {

        openssl_pkcs12_read(file_get_contents(storage_path(self::CERT_FILE)), $key, self::CERT_PASSWORD);
        $key = $key['pkey']; // cert pkey
        // var_dump($key);
        if ($is_file) {
            $data_bin = $data;
        } else {
            $data_bin = pack('a*', mb_convert_encoding($data, 'UTF-8'));
        }
        $high_time = $timestamp / pow(2, 32);

        $timestamp_hex = bin2hex(pack('N', $high_time)) . bin2hex(pack('N', $timestamp));

        for ($i = strlen($timestamp_hex); $i < 16; $i++) {
            $timestamp_hex = '0' . $timestamp_hex;
        }
        $timestamp_reverse_bin = hex2bin($timestamp_hex);

        $hash = hash("sha256", $data_bin . $timestamp_reverse_bin, true);

        openssl_private_encrypt($hash, $encrypted, $key);
        $sign = bin2hex($encrypted);
        $sign = strtoupper($sign);
        $this->sign_str = $sign;
        $this->timestamp = $timestamp;
        return $sign;
    }

    public static function request($url, $method = 'GET', $data = [], $json = TRUE, $timeout = 300, $CA = TRUE)
    {
        $response = new self($url, $method, $data, $json, $timeout, $CA);
//        curl_setopt($response->ch,CURLOPT_URL,'http://localhost/carschool/public/test7');
        /*GET方式执行 走独立外部方法*/

        if($response->method=='GET'){

            return $response->url;
        }

        $result = curl_exec($response->ch);
//        dump( $response->ch);
        $return_code = curl_getinfo($response->ch, CURLINFO_HTTP_CODE);

        $result_array = json_decode($result, TRUE);
//        curl_close($response->ch);
        //dump($result_array);

        log::info("接口返回数据:========================" . print_r($result_array, true));

        $response->errorcode = $result_array['errorcode'];
        $response->message = $result_array['message'];
        $response->data = $result_array['data'];
        // $response->id         = $result_array['id'];
        $response->result_array = $result_array;
        $response->code = $return_code;

        /**/
//        dump($response);
        return $response;
    }

    public static function get($url, $data = [], $CA = TRUE)
    {
        return self::request($url, 'GET', $data, FALSE, 30, $CA);
    }

    public static function post($url, $data = [], $CA = TRUE)
    {
        return self::request($url, 'POST', $data, FALSE, 30, $CA);
    }

    public static function file($url, $data = [], $CA = TRUE)
    {
        return self::request($url, 'FILE', $data, FALSE, 30, $CA);
    }

    public static function put($url, $data = [], $CA = TRUE)
    {
        return self::request($url, 'PUT', $data, FALSE, 30, $CA);
    }

    public static function delete($url, $data = [], $CA = TRUE)
    {
        return self::request($url, 'DELETE', $data, FALSE, 30, $CA);
    }

    public static function jsonGet($url, $data = [], $CA = TRUE)
    {
        return self::request($url, 'GET', $data, TRUE, 30, $CA);
    }

    public static function jsonPost($url, $data = [], $CA = TRUE)
    {
        return self::request($url, 'POST', $data, TRUE, 30, $CA);
    }

    public static function jsonFile($url, $data = [], $CA = TRUE)
    {
        return self::request($url, 'FILE', $data, TRUE, 30, $CA);
    }

    public static function jsonPut($url, $data = [], $CA = TRUE)
    {
        return self::request($url, 'PUT', $data, TRUE, 30, $CA);
    }

    public static function jsonDelete($url, $data = [], $CA = TRUE)
    {
        return self::request($url, 'DELETE', $data, TRUE, 30, $CA);
    }

    public function error()
    {
        if ($this->code != 200) {
            return $this->code;
        }
        if ($this->errorcode) {
            switch ($this->errorcode) {
                case 1 :
                    return '执行失败';
                    break;
                case 100 :
                    return '请求的服务/资源不存在';
                    break;
                case 200 :
                    return '数据格式错误，无法正确解析';
                    break;
                case 201 :
                    return '时间戳重复或错误:时间戳重复或与真实时间误差大于 3min';
                    break;
                case 202 :
                    return '证书无法通过验证';
                    break;
                default:
                    return '未知错误';
                    break;
            }
        } else {
            return 0;
        }
    }


    /**
     * public static function __callStatic($name, $parameters) {
     * if (strpos($name,'json') === 0) {
     * $json = true;
     * $method = substr($name,4);
     * } else {
     * $json = FALSE;
     * $method = $name;
     * }
     *
     * $method = strtoupper($method);
     *
     * if (in_array($method, ['GET','PUT','POST','DELETE'])) {
     * $parameters['method'] = $method;
     * $parameters['json'] = $json;
     * call_user_func([self::class, 'request'], $parameters);
     * }
     * }
     **/
}