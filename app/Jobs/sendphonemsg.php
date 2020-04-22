<?php
/*namespace App\Jobs;*/
/*use Illuminate\Support\Facades\Redis;*/


class SendPhoneMsg
{
    private $redisHost = '127.0.0.1'; //redis HOST
    private $redisPort = '6379'; //redis PORT
    private $redis;
    public function __construct()
    {
        ini_set('date.timezone', 'Asia/Shanghai');
        error_reporting(E_ERROR);
        set_time_limit(0);
        $this->redis = new redis();
        $this->redis->connect($this->redisHost, $this->redisPort);
        if(!$this->redis) {
            echo date("Y-m-d H:i:s") . "--error msg:redis connect fail\n";
        }
    }

    //转发消息
    public function forward()
    {
        while (true) {
            $message = $this->redis->rPop('MSGLIST');
            if($message){
                $this->sendurl($message);
            }else{
                sleep(3000);
            }
        }
    }


    public function sendurl($sendinfo){
        $url = "http://localhost:810/api/small/sendmsginfo";
        $post_data = array ("sendinfo" => $sendinfo);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        print_r($output);
        curl_close($ch);
    }


}
$info = new SendPhoneMsg();
$info->forward();



