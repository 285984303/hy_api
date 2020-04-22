<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. A "local" driver, as well as a variety of cloud
    | based drivers are available for your choosing. Just store away!
    |
    | Supported: "local", "ftp", "s3", "rackspace"
    |
    */

    'default' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => 's3',

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => 'your-key',
            'secret' => 'your-secret',
            'region' => 'your-region',
            'bucket' => 'your-bucket',
        ],
        'qiniu' => [
            'driver'  => 'qiniu',
            'domains' => [
                'default'   => 'ogvjr1bzn.bkt.clouddn.com/', //你的七牛域名
                'https'     => 'dn-yourdomain.qbox.me',         //你的HTTPS域名
                'custom'    => '',                //你的自定义域名
            ],
            'access_key'=> 'UZ3fkwcPN_55uV5Uvp7-B9-LJKg_J1OjDk9Qy8jM',  //AccessKey
            'secret_key'=> 'yo-sFhTv0huqrDuueJHwRwdvm61iaJTsGPCTK5Tv',  //SecretKey
            'bucket'    => 'file',  //Bucket名字
            'notify_url'=> '',  //持久化处理回调地址
        ],

    ],

];
