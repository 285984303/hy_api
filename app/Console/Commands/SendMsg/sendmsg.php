<?php

namespace App\Console\Commands\SendMsg;

use App\Models\Log\LoginVerifyCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
//Use PhpSms;

class sendmsg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendmsg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $i = 1;
        while (true) {
            $message = json_decode(Redis::rPop('MSGLIST'),true);
            if(!$message){
                sleep(3);
                $i++;
            }else{
                $i=1;
                try{
                   if(!$message['phone']){
                        continue;
                   }
                   $result = LoginVerifyCode::sendmsgphone($message);
                   log::info("result:+++++++:".print_r($result,true));

                }catch (\Exception $e) {
                    log::info("ERROR:++++++:".$e->getMessage());
                }
            }
           if($i>=20){
               break;
           }
        }
    }
}
