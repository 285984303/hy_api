<?php

/**
 * @desc 测试数据
 * author:Frank
 * date:2017/09/07
 */
class SendTcpMsg
{
    private $redisHost = '127.0.0.1';
    private $provinceHost = "183.252.17.20";//运管 202.98.194.238:6488//正式IP 183.252.17.20//测试IP
    private $redisPort = '6379';
    private $proPort = '6488';
    private $redis;
    private $client;
    private $conn;

    public function __construct()
    {
        $i = 0;
        $this->redis = new redis();
        $this->redis->connect($this->redisHost, $this->redisPort);
        if (!$this->redis) {
            echo date("Y-m-d H:i:s") . "--error msg:redis connect fail\n";
        }

        $this->client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); //同步阻塞
        $this->conn = $this->client->connect($this->provinceHost, $this->proPort, 0.5, 0);
        if (!$this->conn) {
            $i += 1;
            if ($i <= 3) {
                $this->conn = $this->client->connect($this->provinceHost, $this->proPort, 0.5, 0);
            }
            echo date("Y-m-d H:i:s") . "--error msg:swoole_client connect fail\n";
        }
        $this->login();//登陆平台
        $this->forward();
    }

    //转发消息
    public function forward()
    {
        error_reporting(E_ERROR);
        set_time_limit(0);
        ini_set("allow_call_time_pass_reference", true);
        $file_path = "/home/backend/public/upload/data.txt";
        $file = fopen($file_path, "r");
        $i = 1;

        while (true) {
            if (!$this->conn) {//断开重连
                $this->client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); //同步阻塞
                $this->conn = $this->client->connect($this->provinceHost, $this->proPort, 0.5, 0);
                $this->login();//登陆平台
            }
            if ($i <= 69) {
                $message = fgets($file);//fgets()函数从文件指针中读取一行

                $this->client->send(hex2bin($message));

                echo $i . "\n" . '--' . date("Y-m-d H:i:s") . "--send msg:" . $message . "\n";
                $answer = @$this->client->recv(1024, 0);
                echo date("Y-m-d H:i:s") . "--answer msg[" . $this->provinceHost . "]:" . bin2hex($answer) . "\n";
            }
            $i++;

            sleep(1);
        }

        fclose($file);

    }

}

new SendTcpMsg();





