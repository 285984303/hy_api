<?php

namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendExceptionEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $lines;
    protected $title;

    /**
     * Create a new job instance.
     *
     * @param $text
     */
    public function __construct($text)
    {
        $this->lines = explode("\n", $text);
        $this->title = $this->lines[0];
        array_unshift($this->lines,'Log Tag: #'.request()->server('REQUEST_TIME_FLOAT').'# Url:http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Mail::raw(implode("\n",$this->lines), function($message) {
            $to = env('ADMIN_EMAIL');
            $message->to($to)->subject('Exception Handle :' . $this->title);
        });
    }
}
