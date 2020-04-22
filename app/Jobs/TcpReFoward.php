<?php

/**
 * @desc 消息转发重试
 * author:Frank
 * date:2017/09/13
 * 重试消息:refoward:*
 * 失败消息:tcp_msg_fail
 */
class TcpReFoward
{
    private $redisHost = '127.0.0.1';
    private $redisPort = '6379';
    private $redis;

    public function __construct()
    {
        ini_set('date.timezone','Asia/Shanghai');
        $this->redis = new redis();
        $this->redis->connect($this->redisHost, $this->redisPort);
        if (!$this->redis) {
            echo date("Y-m-d H:i:s") . "--error msg:redis connect fail\n";
        }

//        $this->redis->select(1);

        $this->forward();
    }

    //转发消息
    public function forward()
    {
        error_reporting(E_ERROR);
        set_time_limit(0);
        ini_set("allow_call_time_pass_reference", true);

        while (true) {
            $keys = $this->redis->keys('refoward:*');
            foreach ($keys as $v) {
                $time = (time() - $this->redis->hGet($v, 'time')) / 3600;
                echo date("Y-m-d H:i:s") . "--old-time:" . $this->redis->hGet($v, 'time') . "\n";
                echo date("Y-m-d H:i:s") . "--now-time:" . time() . "\n";
                echo date("Y-m-d H:i:s") . "--last-time:" . $time . "\n";

                $message = $this->redis->hGet($v, 'message');
                if ($this->redis->hGet($v, 'num') <= 3 && $time >= 1) {
                    $this->redis->hIncrBy($v, 'num', 1);//重试次数
                    $this->redis->lPush('tcp_msg_send', $message);//消息
                    $this->redis->hSet($v, 'time', time());//修改为当前时间
                    echo date("Y-m-d H:i:s") . "--push msg:" . $message . "\n";
                } elseif ($this->redis->hGet($v, 'num') > 3) {
                    //记录失败的消息
                    $this->redis->lPush('tcp_msg_fail', $message);
                    echo date("Y-m-d H:i:s") . "--fail msg:" . $message . "\n";
                    $this->redis->del($v);
                }
            }

            sleep(60);
        }
    }

}

new TcpReFoward();





