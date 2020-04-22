<?php namespace App\Library\TCP;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 2017/3/10
 * Time: 15:02
 */

use Illuminate\Support\Facades\Redis;

class Server extends \Hoa\Socket\Connection\Handler
{
    protected function _run (\Hoa\Socket\Node $node)
    {
        // todo 做两个队列,用来收发消息,
        // redis lpush 插值进列表头
        // redis rpop  取值从列表尾
        // 收发消息同时执行,需要缓存区存储未完成消息,读取完成消息进队列
        set_time_limit(0);
        $connection = $node->getConnection();
        // \Log::debug('old_nodes',$connection->getNodes());
        /*  @var $connection \Hoa\Socket\Connection\Connection */

        // \Log::info('node:'.$node->getId());
        $node_id = $node->getId();

        $redis = Redis::connection();
        /*  @var $redis \Redis */

        $key = 'node_data_'.$node_id;
//         \Log::info($node_id.' connected');
        $redis->setnx($key,'');
            // \Log::debug('start');
        $stream = $connection->getStream();
        stream_set_blocking($stream, 0);
            // 超时断开
            if ( !$redis->exists($key) ||$connection->isDisconnected() || $connection->isMute() || $connection->isQuiet()) {
                \Log::info($node_id.' disconnected');
//                $redis->delete($key);
                $connection->disconnect();
                return;
            }

            // \Log::info('try send message');
            // 从队列中取消息并发送给客户端
            $message = $redis->rPop('message_list_'.$node_id);
            // \Log::debug('message:'.strlen($message));
            if ($message && strlen($message) > 4) {
                // \Log::debbug('write1');
                $connection->writeString(hex2bin($message));
                // \Log::debbug('write2');
            }
            // 消息接收
            // if (TRUE === $connection->isEncrypted()) {
            //    $buffer = fgets($stream, 1024);
            // } else {
                $buffer = stream_get_line($stream,1);
            //}
            // \Log::debug('read:'.bin2hex($buffer));

            // 无消息跳过循环
//            if (strlen(bin2hex($buffer)) < 1){
//                usleep(500);
//                continue;
//            }

            //region 消息处理
            $redis->append($key, bin2hex($buffer).' ');
            $string = $redis->get($key);
            // \Log::info($key.'o_string:'.$string);
            $matches = [];
            if (preg_match("/(7e)([^e]|[^7]e)+(7e)/i", $string, $matches)) {
                // \Log::info($key.'m_string:'.$matches[0]);
                // if (strlen($matches[0])%2) continue;
                $pos    = strpos($string, $matches[0]);
                $string = substr($string, $pos + strlen($matches[0]));
                // \Log::info($key.'ot_string:'.$string);
                $redis->set($key, $string);
                // 设置过期时间
                $redis->expire($key, 5);
                (new Thread($node, str_replace(' ','',$matches[0])))->run();
            }
            //endregion
            // \Log::debug('end');
        return;
    }

    protected function _send ($message, \Hoa\Socket\Node $node)
    {
        return $node->getConnection()->writeString($message);
    }
}
