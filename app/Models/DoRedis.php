<?php
namespace App\Models;
/**
 * +----------------------------------------------------------------------
 * | 北京科锐特信息科技有限公司
 * +----------------------------------------------------------------------
 * | Copyright (c) 2016-2017  All rights reserved.
 * +----------------------------------------------------------------------
 * | Author: 乔增浩Joe <joe.qiao@krttech.com>
 * +----------------------------------------------------------------------
 * | Blog: http://www.qiaozenghao.com
 * +----------------------------------------------------------------------
 */
use Illuminate\Support\Facades\Redis;
class DoRedis{
    /*
     * @Des:    Redis 操作封装
     * @Date:   2017.7.28
     * */
    static $_REDIS;
    public function __construct()
    {
        self::$_REDIS = self::initredis();
    }
    public static function initredis(){
        return Redis::connection();
    }

    /*
     * @SET
     * */
    public static function RedisSet($key,$string){
        return self::$_REDIS->set($key,$string);
    }
    /*
     * @GET
     * */
    public static function RedisGet($key){
        return self::$_REDIS->get($key);
    }


}