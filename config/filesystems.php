<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [
        //https://blog.csdn.net/Zss19951212/article/details/124291041
        'oss' => [
            'driver' => 'oss',
            'access_id' => 'LTAI5tFiPTaudARRDbQQDCZn',  // 阿里云OSSAccessKeyId
            'access_key' => 'OOUX1o9oxxpPOCPeONP7SL8TOFqpob',    // 阿里OSSAccessKeySecret
            'bucket' => 'shangyuandiaobit',                   // bucket名称
            'endpoint' => 'oss-cn-hongkong.aliyuncs.com', // OSS 外网节点或自定义外部域名
            'cdnDomain' => '',   // 使用 cdn 时才需要写(Bucket 域名)
            'isCname' => false,      // 为true时，cdnDomain必填
            'debug'=>true
        ],
        
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],
        /* 
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],
         */
        'public' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'visibility' => 'public',
            'url' => env('APP_URL').'/uploads',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        ],


        'admin' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'visibility' => 'public',
            'url' => env('APP_URL').'/uploads',
        ],

        'head_img' => [
            'driver' => 'local',
            'root' => public_path('head_img'),
            'visibility' => 'public',
            'url' => env('APP_URL').'/head_img',
        ]

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];