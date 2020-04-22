<?php namespace App\Models\Data;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 5/27/16
 * Time: 3:54 PM
 */

use App\Models\BaseModel;
use App\Models\NotFound;

/**
 * Class Region
 * @package App\Models\Data
 * @property $pid         integer
 * @property $code         integer
 * @property $name string
 * @property $type int
 */
class Region extends BaseModel {
    protected $table   = 'region';

    const TYPE_PROVINCE = 1;
    const TYPE_CITY     = 2;
    const TYPE_AREA     = 3;

    public static function getChildren($id)
    {
        $regions = \Cache::remember('region_of_pid_'.$id,24*60,function() use ($id) {
            $regions = self::where('pid', intval($id))->orderBy('id','asc')->get();

            if (!$regions) {
                throw new NotFound;
            }

            return $regions;
        });

        return $regions;
    }

    public static function getRegionByCode($code){
        if (!$code) {
            return new self(['name'=>'']);
        }
        $region = \Cache::remember('region_code_'.$code,24*60,function() use ($code) {
            return self::where('code',$code)->first();
        });
        if (!$region) {
            $region = new self(['name'=>'']);
        }
        return $region;
    }
}
