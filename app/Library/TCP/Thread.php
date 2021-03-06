<?php namespace App\Library\TCP;
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 2017/4/14
 * Time: 17:44
 */


use Illuminate\Support\Facades\Redis;

class Thread
{
    private $node;
    private $data;

    function __construct(\Hoa\Socket\Node $node,$data)
    {
        $this->node = $node;
        $this->data = $data;
    }

    public function run()
    {
        // parent::run(); // TODO: Change the autogenerated stub
        $connection = $this->node->getConnection();
        /*  @var $connection \Hoa\Socket\Connection\Connection */

        $line_hex = $this->data;
        \Log::debug('原始消息:'.$line_hex);

        try {
            $line = hex2bin($line_hex);
        } catch (\Exception $e) {
            Answer::message_error(['message_no'=>0,'message_id'=>'0000','mobile'=>0],[]);
            // $connection->disconnect();
            return;
        }

        if (empty($line)) {
            Answer::message_error(['message_no'=>0,'message_id'=>'0000','mobile'=>0],[]);
            // $connection->disconnect();
            return;
        }

        // \Log::debug('原始消息:'.bin2hex($line));

        if (bin2hex($line) == '7e657869747e') {
//            $connection->disconnect();
            return;
        }

        try {
            // \Log::debug('原始消息:'.bin2hex($line));
            $message = bin2hex(Bin::unpack($line));
            \Log::debug('接收消息:'.$message);
            $header_hex = substr($message,0,32);
            $body_hex = substr($message,32);

            $header = Bin::unpack_header_hex($header_hex);
            \Log::debug('header:',$header);

            $redis = Redis::connection();
            /*  @var $redis \Redis */
            $redis->hSet('tcp_nodes',$header['mobile'],$this->node->getId());

            if($header['body_multi']) {
                $total    = hexdec(substr($body_hex,0,4));
                $num      = hexdec(substr($body_hex,4,4));
                $body_hex = substr($body_hex,8);
                $start_no = $header['message_no']-$num+1;
                $end_no = $header['message_no']-$num+$total;
                \Log::debug('merge,start_'.$start_no.'_'.$num.'/'.$total.':'.$body_hex);
                if ($end_no > 0xffff) {
                    $end_no = $end_no - 0xffff - 1;
                }
                $key = 'message_'.$header['mobile'].'_'.$start_no;
                
                $redis->expire($key, 500);

                // todo 合包
                $redis->hSet($key,$num,$body_hex);

                // todo try get
                if ($num == $total) { // todo 合包条件需更改,应为收到流水号 >= $end_no 的消息
                    $count = $redis->hLen($key);
                    if ($count == $total) {
                        $packs = $redis->hGetAll($key);
                        $redis->del($key);
                        ksort($packs);
                        // \Log::debug('packs:',$packs);
                        $body_hex = implode($packs);
                        \Log::debug('header:',$header);
                        \Log::debug('merge_body:'.$body_hex);
                        $function_name = 'message_'.$header['message_id'];
                        Answer::$function_name($header,$body_hex);

                        $count = $redis->lLen('message_list_'.$this->node->getId());
                        for ($ri = 0;$ri < $count;$ri++) {
                            $message = $redis->rPop('message_list_'.$this->node->getId());
                            if ($message && strlen($message) > 4) $connection->writeString(hex2bin($message));
                        }

                    } else {
                        $keys = $redis->hKeys($key);
                        $numbers=range(1,$total);
                        $lose = array_diff($numbers,$keys);
                        Request::message_8003($header['mobile'],$start_no,$lose);
                    }
                } else {
                    Answer::message_8001($header,[]);

                    $count = $redis->lLen('message_list_'.$this->node->getId());
                    for ($ri = 0;$ri < $count;$ri++) {
                        $message = $redis->rPop('message_list_'.$this->node->getId());
                        if ($message && strlen($message) > 4) $connection->writeString(hex2bin($message));
                    }
                }
            } else {
                $function_name = 'message_'.$header['message_id'];
                Answer::$function_name($header,$body_hex);

                $count = $redis->lLen('message_list_'.$this->node->getId());
                for ($ri = 0;$ri < $count;$ri++) {
                    $message = $redis->rPop('message_list_'.$this->node->getId());
                    if ($message && strlen($message) > 4) $connection->writeString(hex2bin($message));
                }
            }
        } catch (\Exception $e) {
            \Log::debug($e->getLine().'：'.$e->getMessage());
            if (isset($header)) {
                Answer::message_error($header,[]);

                $redis = Redis::connection();
                $count = $redis->lLen('message_list_'.$this->node->getId());
                for ($ri = 0;$ri < $count;$ri++) {
                    $message = $redis->rPop('message_list_'.$this->node->getId());
                    if ($message && strlen($message) > 4) $connection->writeString(hex2bin($message));
                }
            } else {
                Answer::message_error(['message_no'=>0,'message_id'=>'0000','mobile'=>0],[]);

                $redis = Redis::connection();
                $count = $redis->lLen('message_list_'.$this->node->getId());
                for ($ri = 0;$ri < $count;$ri++) {
                    $message = $redis->rPop('message_list_'.$this->node->getId());
                    if ($message && strlen($message) > 4) $connection->writeString(hex2bin($message));
                }
            }
        }
        return;
    }
}
