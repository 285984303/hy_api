<?php

namespace App\Console;

use App\Console\Commands\TcpMsg;
use App\Console\Commands\Trans\TeachLog;
use App\Models\Student\Theory;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Psy\Command\Command;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
          Commands\SendMsg\sendmsg::class,
    ];
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('command:sendmsg')->everyMinute();
    }
}

