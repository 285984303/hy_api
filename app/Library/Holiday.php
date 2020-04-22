<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 7/19/16
 * Time: 3:10 PM
 */

namespace App\Library;

class Holiday {
    /**
     * @param int $time
     *
     * @return bool
     */
    public static function isHoliday($time){
        if(date('N',$time)==6 || date('N',$time)==7){
            return TRUE;
        } else {
            return FALSE;
        }
    }
}