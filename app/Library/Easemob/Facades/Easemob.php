<?php namespace App\Library\Easemob\Facades;

use Illuminate\Support\Facades\Facade;

class Easemob extends Facade
{

    /**
     *
     * /link/www/src/vendor/goodspb/laravel-easemob/src/Facades/Easemob.php
     *
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'easemob';
    }
}
