<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 15/08/2017
 * Time: 9:55 AM
 */

namespace App\Library;

use Illuminate\Support\Facades\Log;

class TCP
{
    const PROVINCE_HOST = "60.205.191.72";
    const PORT = '9527';

    private static $_ins = null;

    private function __construct()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket < 0) {
            log::debug('socket创建失败原因:', [socket_strerror($this->socket)]);
        } else {
            log::debug('socket创建成功:', ['创建成功']);
        }
        $socketConn = socket_connect($this->socket, self::PROVINCE_HOST, self::PORT);
        if ($socketConn < 0) {
            log::debug('socket链接失败原因:', [socket_strerror($socketConn)]);

//            socket_clear_error();
        } else {
            log::debug('socket链接成功:', ['链接成功']);
        }
        $str = hex2bin('7e8001F0001700000000000A01070001004130313037313233343536373800bc614eb77e');
        $this->sendMsg($str);
//        $rs = $this->getMsg(1024);
//        var_dump('test1---',bin2hex($rs));
//        $this->sendMsg(hex2bin('7e8001F0001700000000000A01070001004130313037313233343536373800bc614eb77e7e800100003d0000018230103547003600000b0001424a4b525453444a2d584b313730302d5941000000000000004831313033363538363438383130323734313237383001bea94431343537d1a7197e'));
//        $out = $this->getMsg(1024);
//        var_dump('test2---',bin2hex($out));
    }

    static public function getIns()
    {
        \log::debug('TCP客户端',['test'=>self::$_ins]);
        if (is_null(self::$_ins)) {
            self::$_ins = new self();
        }

//        var_dump(self::$_ins);

        return self::$_ins;
    }

    final protected function __clone()
    {

    }

    public function sendMsg($msg)
    {
        return socket_write($this->socket, $msg, strlen($msg));
    }

    public function getMsg($byte)
    {
       return socket_read($this->socket, $byte);
    }
}

