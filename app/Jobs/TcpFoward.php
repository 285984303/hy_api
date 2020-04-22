<?php

/**
 * @desc 终端消息转发
 * author:Frank
 * date:2017/09/07
 * 消息队列:tcp_msg_send
 * 重发消息:refoward:*
 * 运管正式IP:202.98.194.238 测试IP:183.252.17.20  端口:6488
 */
class TcpFoward
{
    private $redisHost = '127.0.0.1'; //redis HOST
    private $provinceHost = "202.98.194.238"; //运管HOST
    private $redisPort = '6379'; //redis PORT
    private $proPort = '6488'; //运管PORT
    private $redis;
    private $client;
    private $conn;

    public function __construct()
    {
        ini_set('date.timezone', 'Asia/Shanghai');
//                error_reporting(E_ERROR | E_WARNING | E_PARSE);
        error_reporting(E_ERROR);
        set_time_limit(0);
        ini_set("allow_call_time_pass_reference", true);
        ini_set('default_socket_timeout', -1);

        $this->redis = new redis();
        $this->redis->connect($this->redisHost, $this->redisPort);
        if (!$this->redis) {
            echo date("Y-m-d H:i:s") . "--error msg:redis connect fail\n";
        }

        $this->client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        $this->conn = $this->client->connect($this->provinceHost, $this->proPort, 0.5, 0);

        $this->login();//登陆平台
        $this->forward();
    }

    //转发消息
    public function forward()
    {
        while (true) {

            $write = $error = array();
            $read = [$this->client];
            $n = swoole_client_select($read, $write, $error, 0.6);

            echo "当前状态:" . socket_strerror($this->client->errCode) . '->' . $this->client->errCode . "\n";

            if (!$message = $this->redis->rPop('tcp_msg_send')) {
                $message = '7e80000200000000018230103547000000537e';
                sleep(5);
            }

            $preFlag = substr($message, 0, 2);
            $afterFlag = substr($message, -2, 2);
            if ($preFlag != '7e' || $afterFlag != '7e') {
                echo date("Y-m-d H:i:s") . "--消息不合法:" . $message . "\n";
                break;
            }
            $aa = $this->client->send(hex2bin($message) . "\n");
            echo "发送状态:" . $aa . "\n";
            echo date("Y-m-d H:i:s") . "--send msg:" . $message . "\n";
            $answer = $this->client->recv(1024, 0);

            if (!$answer && $n == 1) {//服务器链接断开
                $this->client->close(true);
                unlink($this->client);
                $this->client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
                $this->conn = $this->client->connect($this->provinceHost, $this->proPort, 0.5, 0);
                if ($this->conn) {
                    $this->client->errCode = 0;
                }
                $this->login();//登陆平台
                sleep(40);

            }
            //删除指定元素
            $this->remove(bin2hex($answer));
            echo date("Y-m-d H:i:s") . "--answer msg[" . $this->provinceHost . "]:[status->" . $n . "]:" . bin2hex($answer) . "\n";

            usleep(30000);
        }

    }

    //平台登录
    public function login()
    {
        $code = '7e8001F0001700000000000A01070001004130313037313233343536373800bc614eb77e';
        $this->client->send(hex2bin($code) . "\n");
        echo date("Y-m-d H:i:s") . "--send login msg:{$code}\n";
        sleep(2);
        $answer = $this->client->recv(1024, 0);
        if ($answer) {
            echo date("Y-m-d H:i:s") . "--answer login msg:" . bin2hex($answer) . "\n";
        }

    }

    //删除
    public function remove($answer)
    {
        //解析answer获取到消息id和流水号
        $answer = trim($answer, '7e');
        $messageId = substr($answer, 36, 4);
//        echo date("Y-m-d H:i:s") . "--messageid:" . 'refoward:' . $messageId . "\n";

        $num = substr($answer, 32, 4);
        echo date("Y-m-d H:i:s") . "--num:" . 'refoward:' . $num . "\n";

        $key = $messageId . str_pad(hexdec($num), 4, '0', STR_PAD_LEFT);
        echo date("Y-m-d H:i:s") . "--fowardbak key:" . 'refoward:' . $key . "\n";

        $rs = $this->redis->del('refoward:' . $key);
        echo date("Y-m-d H:i:s") . "--key{$rs}:" . 'refoward:' . $key . "\n";

    }

}

new TcpFoward();





