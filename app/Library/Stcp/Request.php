<?php namespace App\Library\Stcp;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 2017/4/17
 * Time: 19:22
 */

use Illuminate\Support\Facades\Redis;

class Request {

    // 请求补传分包
    public static function message_8003($mobile, $message_no, $keys){
        $message_body = [
            Bin::word($message_no),
            Bin::byte(count($keys)),
        ];
        foreach ($keys as $key) {
            $message_body[] = Bin::word($key);
        }

        $message_header = [
            Bin::byte(0),
            hex2bin('8003'),
            pack('n',Bin::length(implode($message_body)) & 0b0000001111111111),
            Bin::bcd_array($mobile,16),
            Bin::word(1),
            Bin::byte(0),
        ];
        self::request($mobile,implode(array_merge($message_header,$message_body)));
    }

    // 设置终端参数
    public static function message_8103($mobile,$data){
        $message_body = [
            Bin::byte(count($data)),
            Bin::byte(count($data)),
        ];
        $dword = function($key,$value){
            $message_body[] = hex2bin(substr($key,2));
            $message_body[] = Bin::byte(4);
            $message_body[] = Bin::dword($value);
        };
        $byte = function($key,$value){
            $message_body[] = hex2bin(substr($key,2));
            $message_body[] = Bin::byte(1);
            $message_body[] = Bin::byte($value);
        };
        $word = function($key,$value){
            $message_body[] = hex2bin(substr($key,2));
            $message_body[] = Bin::byte(2);
            $message_body[] = Bin::word($value);
        };
        $string = function($key,$value){
            $message_body[] = hex2bin(substr($key,2));
            $message_body[] = Bin::byte(strlen(Bin::string($value)));
            $message_body[] = Bin::string($value);
        };

        foreach ($data as $key => $value) {
            switch ($key) {
            case  '0x0001' :$dword($key,$value);break;
            case  '0x0002' :$dword($key,$value);break;
            case  '0x0003' :$dword($key,$value);break;
            case  '0x0004' :$dword($key,$value);break;
            case  '0x0005' :$dword($key,$value);break;
            case  '0x0006' :$dword($key,$value);break;
            case  '0x0007' :$dword($key,$value);break;
            // 08-0F 保留
            case  '0x0010' :$string($key,$value);break;
            case  '0x0011' :$string($key,$value);break;
            case  '0x0012' :$string($key,$value);break;
            case  '0x0013' :$string($key,$value);break;
            case  '0x0014' :$string($key,$value);break;
            case  '0x0015' :$string($key,$value);break;
            case  '0x0016' :$string($key,$value);break;
            case  '0x0017' :$string($key,$value);break;
            case  '0x0018' :$dword($key,$value);break;
            case  '0x0019' :$dword($key,$value);break;
            // 1A-1F 保留
            case  '0x0020' :$dword($key,$value);break;
            case  '0x0021' :$dword($key,$value);break;
            case  '0x0022' :$dword($key,$value);break;
            case  '0x0027' :$dword($key,$value);break;
            case  '0x0028' :$dword($key,$value);break;
            case  '0x0029' :$dword($key,$value);break;
            // 2A-2B 保留
            case  '0x002C' :$dword($key,$value);break;
            case  '0x002D' :$dword($key,$value);break;
            case  '0x002E' :$dword($key,$value);break;
            case  '0x002F' :$dword($key,$value);break;
            case  '0x0030' :$dword($key,$value);break;
            // 31-3F 保留
            case  '0x0040' :$dword($key,$value);break;
            case  '0x0041' :$dword($key,$value);break;
            case  '0x0042' :$dword($key,$value);break;
            case  '0x0043' :$string($key,$value);break;
            case  '0x0044' :$string($key,$value);break;
            case  '0x0045' :$dword($key,$value);break;
            case  '0x0046' :$dword($key,$value);break;
            case  '0x0047' :$dword($key,$value);break;
            case  '0x0048' :$string($key,$value);break;
            case  '0x0049' :$string($key,$value);break;
            // 4A-4F 保留
            case  '0x0050' :$dword($key,$value);break;
            case  '0x0051' :$dword($key,$value);break;
            case  '0x0052' :$dword($key,$value);break;
            case  '0x0053' :$dword($key,$value);break;
            case  '0x0054' :$dword($key,$value);break;
            case  '0x0055' :$dword($key,$value);break;
            case  '0x0056' :$dword($key,$value);break;
            case  '0x0057' :$dword($key,$value);break;
            case  '0x0058' :$dword($key,$value);break;
            case  '0x0059' :$dword($key,$value);break;
            case  '0x005A' :$dword($key,$value);break;
            // 5B-6F 保留
            case  '0x0070' :$dword($key,$value);break;
            case  '0x0071' :$dword($key,$value);break;
            case  '0x0072' :$dword($key,$value);break;
            case  '0x0073' :$dword($key,$value);break;
            case  '0x0074' :$dword($key,$value);break;
            // 75-7F 保留
            case  '0x0080' :$dword($key,$value);break;
            case  '0x0081' :$word($key,$value);break;
            case  '0x0082' :$word($key,$value);break;
            case  '0x0083' :$string($key,$value);break;
            case  '0x0084' :$byte($key,$value);break;
            case  '0x0085' :$dword($key,$value);break;
            }
        }

        $message_header = [
            Bin::byte(0),
            hex2bin('8103'),
            pack('n',Bin::length(implode($message_body)) & 0b0000001111111111),
            Bin::bcd_array($mobile,16),
            Bin::word(1),
            Bin::byte(0),
        ];

        self::request($mobile,implode(array_merge($message_header,$message_body)));
    }

    // 查询终端参数
    public static function message_8104($mobile,$data){
        /*
        $message_body = [
            Bin::byte(count($data)),
            Bin::byte(count($data)),
        ];
        $dword = function($key,$value){
            $message_body[] = hex2bin(substr($key,2));
            $message_body[] = Bin::byte(4);
            $message_body[] = Bin::dword($value);
        };
        $byte = function($key,$value){
            $message_body[] = hex2bin(substr($key,2));
            $message_body[] = Bin::byte(1);
            $message_body[] = Bin::byte($value);
        };
        $word = function($key,$value){
            $message_body[] = hex2bin(substr($key,2));
            $message_body[] = Bin::byte(2);
            $message_body[] = Bin::word($value);
        };
        $string = function($key,$value){
            $message_body[] = hex2bin(substr($key,2));
            $message_body[] = Bin::byte(strlen(Bin::string($value)));
            $message_body[] = Bin::string($value);
        };

        foreach ($data as $key => $value) {
            switch ($key) {
            case  '0x0001' :$dword($key,$value);break;
            case  '0x0002' :$dword($key,$value);break;
            case  '0x0003' :$dword($key,$value);break;
            case  '0x0004' :$dword($key,$value);break;
            case  '0x0005' :$dword($key,$value);break;
            case  '0x0006' :$dword($key,$value);break;
            case  '0x0007' :$dword($key,$value);break;
                // 08-0F 保留
            case  '0x0010' :$string($key,$value);break;
            case  '0x0011' :$string($key,$value);break;
            case  '0x0012' :$string($key,$value);break;
            case  '0x0013' :$string($key,$value);break;
            case  '0x0014' :$string($key,$value);break;
            case  '0x0015' :$string($key,$value);break;
            case  '0x0016' :$string($key,$value);break;
            case  '0x0017' :$string($key,$value);break;
            case  '0x0018' :$dword($key,$value);break;
            case  '0x0019' :$dword($key,$value);break;
                // 1A-1F 保留
            case  '0x0020' :$dword($key,$value);break;
            case  '0x0021' :$dword($key,$value);break;
            case  '0x0022' :$dword($key,$value);break;
            case  '0x0027' :$dword($key,$value);break;
            case  '0x0028' :$dword($key,$value);break;
            case  '0x0029' :$dword($key,$value);break;
                // 2A-2B 保留
            case  '0x002C' :$dword($key,$value);break;
            case  '0x002D' :$dword($key,$value);break;
            case  '0x002E' :$dword($key,$value);break;
            case  '0x002F' :$dword($key,$value);break;
            case  '0x0030' :$dword($key,$value);break;
                // 31-3F 保留
            case  '0x0040' :$dword($key,$value);break;
            case  '0x0041' :$dword($key,$value);break;
            case  '0x0042' :$dword($key,$value);break;
            case  '0x0043' :$string($key,$value);break;
            case  '0x0044' :$string($key,$value);break;
            case  '0x0045' :$dword($key,$value);break;
            case  '0x0046' :$dword($key,$value);break;
            case  '0x0047' :$dword($key,$value);break;
            case  '0x0048' :$string($key,$value);break;
            case  '0x0049' :$string($key,$value);break;
                // 4A-4F 保留
            case  '0x0050' :$dword($key,$value);break;
            case  '0x0051' :$dword($key,$value);break;
            case  '0x0052' :$dword($key,$value);break;
            case  '0x0053' :$dword($key,$value);break;
            case  '0x0054' :$dword($key,$value);break;
            case  '0x0055' :$dword($key,$value);break;
            case  '0x0056' :$dword($key,$value);break;
            case  '0x0057' :$dword($key,$value);break;
            case  '0x0058' :$dword($key,$value);break;
            case  '0x0059' :$dword($key,$value);break;
            case  '0x005A' :$dword($key,$value);break;
                // 5B-6F 保留
            case  '0x0070' :$dword($key,$value);break;
            case  '0x0071' :$dword($key,$value);break;
            case  '0x0072' :$dword($key,$value);break;
            case  '0x0073' :$dword($key,$value);break;
            case  '0x0074' :$dword($key,$value);break;
                // 75-7F 保留
            case  '0x0080' :$dword($key,$value);break;
            case  '0x0081' :$word($key,$value);break;
            case  '0x0082' :$word($key,$value);break;
            case  '0x0083' :$string($key,$value);break;
            case  '0x0084' :$byte($key,$value);break;
            case  '0x0085' :$dword($key,$value);break;
            }
        }
        */

        $message_header = [
            Bin::byte(0),
            hex2bin('8104'),
            pack('n',0 & 0b0000001111111111),
            Bin::bcd_array($mobile,16),
            Bin::word(1),
            Bin::byte(0),
        ];
        self::request($mobile,implode(array_merge($message_header)));
    }

    // 查询指定终端参数
    public static function message_8106($mobile,$data){
        $message_body = [];
        $message_body[] = Bin::byte(count($data));

        foreach ($data as $value) {
            $message_body[] = hex2bin(substr($value,2));
        }

        $message_header = [
            Bin::byte(0),
            hex2bin('8106'),
            pack('n',0 & 0b0000001111111111),
            Bin::bcd_array($mobile,16),
            Bin::word(1),
            Bin::byte(0),
        ];
        self::request($mobile,implode(array_merge($message_header)));
    }


    // todo 终端控制


    // 位置信息查询
    public static function message_8201($mobile,$data){
        $message_header = [
            Bin::byte(0),
            hex2bin('8201'),
            pack('n',0 & 0b0000001111111111),
            Bin::bcd_array($mobile,16),
            Bin::word(1),
            Bin::byte(0),
        ];
        self::request($mobile,implode(array_merge($message_header)));
    }

    // 临时位置跟踪控制
    public static function message_8202($mobile,$data){
        $message_body = [
            Bin::word(5),
            Bin::dword(60)
        ];

        $message_header = [
            Bin::byte(0),
            hex2bin('8202'),
            pack('n',Bin::length(implode($message_body)) & 0b0000001111111111),
            Bin::bcd_array($mobile,16),
            Bin::word(1),
            Bin::byte(0),
        ];

        self::request($mobile,implode(array_merge($message_header)));
    }

    //region 数据下行透传
    static public function message_8900($mobile, $id, $data){
        $message_body = [
            hex2bin('13'), // 透传类型
            hex2bin($id),// 透传ID
            Bin::word(1),// 扩展消息属性
            Bin::word(1),// 驾培包序号
            Bin::byte_array($mobile,16), // 计时终端编号
            Bin::length($data),// 数据长度
            // 数据内容
        ];
        $message_body = array_merge($message_body,$data);

        $message_header = [
            Bin::byte(0),
            hex2bin('8900'),
            pack('n',Bin::length(implode($message_body)) & 0b0000001111111111),
            Bin::bcd_array($mobile,16),
            Bin::word(1),
            Bin::byte(0),
        ];

        self::request($mobile,implode(array_merge($message_header,$message_body)));
    }
    // 命令上报学时记录
    static public function message_cross_8205($mobile,$start_time,$end_time){

        $body = [
            Bin::byte(1),
            hex2bin(date('YmdHis',strtotime($start_time))),
            hex2bin(date('YmdHis',strtotime($end_time))),
            Bin::word(20),
        ];

        self::message_8900($mobile,'8205',implode($body));
    }
    // 立即拍照
    static public function message_cross_8301($mobile){
        $body = [
            Bin::byte(1),
            Bin::byte(0),
            Bin::byte(8),
        ];

        self::message_8900($mobile,'8301',implode($body));
    }
    // 查询照片
    static public function message_cross_8302($mobile,$start_time,$end_time){
        $body = [
            Bin::byte(1),
            hex2bin(date('YmdHis',strtotime($start_time))),
            hex2bin(date('YmdHis',strtotime($end_time))),
        ];

        self::message_8900($mobile,'8302',implode($body));
    }
    // 命令上传指定照片
    static public function message_cross_8304($mobile,$num){
        $body = Bin::byte_array($num,10);

        self::message_8900($mobile,'8304',implode($body));
    }

    //设置计时终端参数
    static public function message_cross_8501($mobile,$num){
        // todo 读取学校设置并发送到终端
        $body = [
            Bin::byte(0),// 参数编号
            Bin::byte(15),// 定时拍照时间间隔
            Bin::byte(1),// 照片上传设置
            Bin::byte(1),// 是否报读附加消息
            Bin::byte(5),// 熄火后停止学时计时延时时间
            Bin::word(3600),// 熄火后GNSS数据包上传间隔
            Bin::word(150),// 熄火后教练自动登出的延时时间
            Bin::word(30),// 重新验证身份时间
            Bin::byte(2),// 教练跨校教学
            Bin::byte(1),// 学员跨校学习
            Bin::word(3),// 响应平台同类消息时间间隔
        ];

        self::message_8900($mobile,'8501',implode($body));
    }
    //设置禁训状态
    static public function message_cross_8502($mobile,$message){
        $message = Bin::string($message);
        $body = [
            Bin::byte(0),// 参数编号
            Bin::byte($message),// 定时拍照时间间隔
            $message
        ];

        self::message_8900($mobile,'8502',implode($body));
    }
    //查询终端应用参数
    static public function message_cross_8503($mobile){
        self::message_8900($mobile,'8503',null);
    }


    //endregion

    public static function request($mobile, $message) {
        $redis = Redis::connection();
        /*  @var $redis \Redis */
        $node_id = $redis->hGet('tcp_nodes', $mobile);
        $redis->lPush('message_list_'.$node_id, bin2hex($message));
    }
}