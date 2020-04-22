<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        // if (!(isset($_SERVER['argv']) && $_SERVER['argv'][1] == 'schedule:run')) {
        //     \Log::debug('#' . $_SERVER['REQUEST_TIME_FLOAT'] . '# REQUEST_URI:' . request()->server('REQUEST_URI'), ['PARAMETERS' => [request()->server(),request()->toArray()]]);
        // }
        \DB::listen(function($query) {
            $sql = str_replace("?", "'%s'", $query->sql);
            $params = $query->bindings;
            array_unshift($params, $sql);
            \Log::debug('#'.$_SERVER['REQUEST_TIME_FLOAT'].'# SQL:'.call_user_func_array('sprintf',$params),['time'=>$query->time]);
        });

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
