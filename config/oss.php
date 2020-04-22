<?php
/**
 * Created by PhpStorm.
 * User: frank
 * Date: 2017/7/28
 * Time: 下午4:21
 */

return [
    'CONFIG' => [
        'BUCKET' => env('BUCKET') ?: 'sass-test',
        'ACCESSID' => env('ACCESSID') ?: 'LTAIQ4sQkN9yFHLh',
        'ACCESSKEY' => env('ACCESSKEY') ?: 'wNcjtAhYWJk5wqqjcXFsowZ49R4iLu',
        'HOST' => env('OSS_HOST') ?: 'http://sass-test.oss-cn-beijing.aliyuncs.com',//外网
    ],

    'log' => [
        'crash' => 'logs/crash/',
        'train' => 'logs/train/'
    ],
    'pdf' => [
        'endinfo' => 'pdf/endinfo/',
        'train' => 'pdf/train/',
    ],
    'tcp' => [
        'train' => 'images/tcp/train/',
        'theory' => 'images/tcp/theory/'
    ],
    'images' => [
        'avatar' => 'images/avatar/'
    ]

];