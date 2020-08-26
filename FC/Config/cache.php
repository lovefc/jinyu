<?php

/*
 * 缓存配置
 * 
 * @Author: lovefc 
 * @Date: 2019-10-04 13:06:03 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-10 16:44:43
 */
 
return [

    // 文件缓存配置
    'files' => [

        //缓存目录
        'Path' => PATH['NOW'] . '/Cache',

        //缓存方式，有三种情况，memcache,redis，file
        'Mode' => 'file',

        //针对文件缓存的过期时间,redis和memcache设置这个选项无效
        'Time' => 999999

    ],

    // redis配置
    'redis' => [

        'Path'   => '127.0.0.1',

        //memcache或者redis的端口
        'Port' => '6379',

        //缓存方式，有三种情况，memcache,redis，file
        'Mode' => 'redis',

        //针对文件缓存的过期时间,redis和memcache设置这个选项无效
        'Time' => 60

    ],

    // memcache配置
    'memcache' => [

        'Path'   => '127.0.0.1',

        //memcache或者redis的端口
        'Port' => '11211',

        //缓存方式，有三种情况，memcache,redis，file
        'Mode' => 'memcache',

        //针对文件缓存的过期时间,redis和memcache设置这个选项无效
        'Time' => 60

    ],

];
