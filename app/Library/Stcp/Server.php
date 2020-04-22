<?php namespace App\Library\Stcp;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 2017/3/10
 * Time: 15:02
 */

use Illuminate\Support\Facades\Redis;

class Server
{
    public function __construct()
    {
        $ip = '0.0.0.0';
        $port = '9527';
        set_time_limit(0);
        $serv = new \swoole_server($ip, $port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        $serv->set(array(
            'worker_num' => 8,   //工作进程数量
            'daemonize' => true, //是否作为守护进程
            'open_eof_check' => true,
            'package_eof' => hex2bin('7e'),
            'open_eof_split' => true,
            'package_max_length' => 2000000,  //协议最大长度
            'socket_buffer_size' => 1024 * 1024 * 500,
            'buffer_output_size' => 1024 * 1024 * 2,
            'heartbeat_check_interval' => 12,
            'heartbeat_idle_time' => 120
        ));

        $serv->on('connect', function ($serv, $node_id) {
        });

        //必须在onWorkerStart回调中创建redis/mysql连接
        $serv->on('workerstart', function ($serv, $node_id) {
            $redis = Redis::connection();
            $serv->redis = $redis;
        });

        $serv->on('receive', function ($serv, $node_id, $from_id, $data) {
            $key = 'node_data_' . $node_id . '_' . $from_id;
            \Log::info("connected--$node_id-----$from_id");
            $serv->redis->setnx($key, '');
            // 从队列中取消息并发送给客户端
            $message = $serv->redis->rPop('message_list_' . $node_id);
            if ($message && strlen($message) > 4) {
                $this->message = $message;
                $serv->send($node_id, hex2bin($this->message));
            }
            // 无消息跳过循环
//                if (strlen(bin2hex($data)) < 1) {
//                    usleep(500);
//                    continue;
//                }
            //region 消息处理
//            if (preg_match("/^7e.*/i", $string, $matches))


            if (bin2hex($data) != '7e') {
                $serv->redis->append($key, '7e' . bin2hex($data) . ' ');

                \Log::info("------data-----: " . '7e' . bin2hex($data));
                $string = $serv->redis->get($key);
                $obj = new Thread($node_id, $serv, '7e' . bin2hex($data));
                $obj->run();
            } else {
                usleep(500);
            }


            // \Log::info($key.'o_string:'.$string);
//                $matches = [];
//                if (preg_match("/(7e)([^e]|[^7]e)+(7e)/i", $string, $matches)) {
//                    if($matches[0]) {
//                        $pos = strpos($string, $matches[0]);
//                        $string = substr($string, $pos + strlen($matches[0]));
//                        $serv->redis->set($key, $string);
//                        // 设置过期时间
//                        $serv->redis->expire($key, 5);
//                        if(strlen(bin2hex($data))>=1000) {
//                            $obj = new Thread($node_id, $serv,bin2hex($data));
//                        } else {
//                            $obj = new Thread($node_id, $serv, str_replace(' ','',$matches[0]));
//                        }
//                        $obj = new Thread($node_id, $serv, str_replace(' ','',$matches[0]));
//                        $obj->run();
//                    }
//                }
//            $serv->send($this->node_id, "Server: ".$this->data);
//            $serv->close($node_id);
        });
        $serv->on('close', function ($serv, $node_id) {
            \Log::debug('end');
        });
        $serv->start();
    }

    public function dealMsg()
    {
        // todo 做两个队列,用来收发消息,
        // redis lpush 插值进列表头
        // redis rpop  取值从列表尾
        // 收发消息同时执行,需要缓存区存储未完成消息,读取完成消息进队列
        $key = 'node_data_' . self::$node_id;
        \Log::info(self::$node_id . ' connected');
        $redis = Redis::connection();
        $redis->setnx($key, '');
        do {
            // 从队列中取消息并发送给客户端
            $message = $redis->rPop('message_list_' . self::$node_id);
            if ($message && strlen($message) > 4) {
                $this->message = $message;
                self::$serv->send(self::$node_id, hex2bin($this->message));
            }
            // 无消息跳过循环
            if (strlen(bin2hex(self::$data)) < 1) {
                usleep(500);
                continue;
            }
            //region 消息处理
            $redis->append($key, bin2hex(self::$data) . ' ');
            $string = $redis->get($key);
            // \Log::info($key.'o_string:'.$string);
            $matches = [];
            if (preg_match("/(7e)([^e]|[^7]e)+(7e)/i", $string, $matches)) {
                $pos = strpos($string, $matches[0]);
                $string = substr($string, $pos + strlen($matches[0]));
                $redis->set($key, $string);
                try {
                    $obj = new \App\Library\Stcp\Thread(self::$node_id, self::$serv, str_replace(' ', '', $matches[0]));
                    $obj->run();
                } catch (\Exception $e) {
                    \Log::debug('error:' . $e->getMessage());
                }
            }
            // 设置过期时间
            $redis->expire($key, 5);
        } while (1);
        return true;
    }
}
