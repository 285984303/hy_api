<?php namespace App\Library\Stcp;
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 2017/3/13
 * Time: 15:23
 */

class Bin
{
    // 8
    public static function byte(int $data){
        return pack('c1',$data);
    }

    // 16
    public static function word(int $data){
        return pack('n1',$data);
    }

    // 32
    public static function dword(int $data){
        return pack('N1',$data);
    }

    public static function byte_array($data,int $n){
        // \Log::debug($data.':'.$n);
        if (is_string($data)) {
            $data = str_split($data);
        }
        while (count($data) < $n) {
            $data[] = NULL;
        }
        foreach ($data as &$value) {
            if(!is_int($value)) $value = ord($value);
        }
        return pack('c'.$n, ...$data);
    }

    public static function bcd_array($data, int $n, $left = true){
        for ($i=0;$i<strlen($data);$i++){
            if ($data[$i]>9) {
                throw new \Exception();
            }
        }

        if ($left) {
            $left = STR_PAD_LEFT;
        } else {
            $left = STR_PAD_RIGHT;
        }
        return hex2bin(str_pad($data,$n,'0',$left));
    }

    public static function string(string $data){
        return pack('a*x',iconv("GBK", "UTF-8",$data));
    }

    public static function pack($data){
        if ($data === null) return null;
        
        $hex = bin2hex($data);
        $hex = $hex.self::get_code($hex); // 计算并填充校验码
        //转义7d和7e
        $hex = str_split($hex,2);
        $hex = implode(' ',$hex);
        $hex = str_replace('7d','7d 01',$hex);
        $hex = str_replace('7e','7d 02',$hex);
        $hex = explode(' ',$hex);
        $hex = implode('',$hex);

        $bin = hex2bin('7e'.$hex.'7e');
        return $bin;
    }

    public static function unpack($data){
        $hex = substr(bin2hex($data),2,-2);

        $hex = str_split($hex,2);
        $hex = implode(' ',$hex);
        $hex = str_replace('7d 02','7e',$hex);
        $hex = str_replace('7d 01','7d',$hex);
        $hex = explode(' ',$hex);
        $hex = implode('',$hex);

        if (self::verify_code($hex)) {
            return hex2bin(substr($hex,0,-2));
        } else {
            throw new \Exception('校验码校验未通过');
        }
    }

    public static function unpack_header_hex($header_hex){
        // 0 byte 版本号
        // 1 word 消息 ID
        // 3 word 消息体属性
        // 5 bcd[8] 终端手机号
        // 13 word 消息流水号
        // 15 byte 预留

        $pattern = "/^(0+)(\d+)/i";
        $replacement = "\$2";
        $mobile = preg_replace($pattern,$replacement,substr($header_hex,10,16));

        $header =  [
            'version' => hexdec(substr($header_hex,0,2)),
            'message_id' => substr($header_hex,2,4),
            'message_opt' => hexdec(substr($header_hex,6,4)),
            'mobile' => $mobile,
            'message_no' => hexdec(substr($header_hex,26,4)),
        ];

        $header['length'] = 0b0000001111111111 & $header['message_opt'];
        $header['body_encrypt'] = 0b000111 & ($header['message_opt'] >> 10);
        $header['body_multi'] = 0b001 & ( $header['message_opt'] >> 13);

        return $header;
    }

    private static function get_code($hex):string
    {
        $count = strlen($hex)/2;
        $code = null;
        for ($i = 2; $i <= $count; $i++) {
            if ($i == 2) {
                $code = hex2bin($hex[0].$hex[1]) ^ hex2bin($hex[2].$hex[3]);
            } else {
                $code = $code ^ hex2bin($hex[$i * 2 - 2].$hex[$i * 2 - 1]);
            }
        }
        return bin2hex($code);
    }
    private static function verify_code($hex):bool
    {
//        $hex = str_split($hex,2);
//        $hex = implode(' ',$hex);
//        $hex = str_replace('7d 02','7e',$hex);
//        $hex = str_replace('7d 01','7d',$hex);
//        $hex = explode(' ',$hex);
//        $hex = implode('',$hex);

        \Log::debug('code: '.self::get_code(substr($hex,0,-2)).':'.substr($hex,-2).'='.(self::get_code(substr($hex,0,-2)) === substr($hex,-2)? 'T':'F'));

        if(self::get_code(substr($hex,0,-2))==substr($hex,-2)) {
            return true;
        }
        return false;
    }

    public static function length($bin){
        return strlen(bin2hex($bin))/2;
    }

    public static function hexXbin($data, $types = false){
        if(!is_string($data))
            return 0;
        if($types === false){
            $len = strlen($data);
            if ($len % 2) {
                return 0;
            }else if (strspn($data, '0123456789abcdefABCDEF') != $len) {
                return 0;
            }
            return pack('H*', $data);
        }else{
            return bin2hex($data);
        }
    }
}
