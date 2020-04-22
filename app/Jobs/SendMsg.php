<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\Home\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use PhpSms;
class SendMsg extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user)
    {

        $this->user = $user;
    }




    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

//$info = User::where('id','=',28)->get();
        /*
        //Admin::find(1);
        //$query = User::find(10);
        //User::find(10);
        // echo "adsfsd";
        //User::
        //$user = User::where('id','=',4)->first();
        //print_r($user);
        $job = (new SendMsg($info))->onQueue('collectionbook')->delay(60);
        dispatch($job);*/

       /* $templates = [
                'Aliyun' => 'SMS_119920819',
        ];
        $content = "";
        $sendinfo = array(
            'class' => "2018-01-03 [12:30-14:30、12:30-14:30、12:30-14:30、12:30-14:30]",
            'name' => '乔增浩',
            'vehicle' => '[贵3698学]',
            'product'=>'dsd'
        );
        $mobile = "15801195012";
        PhpSms::make()
            ->to($mobile)
            ->template($templates)
            ->data($sendinfo)
            ->content($content)
            ->send();*/
    }
}
