<?php

/*
 * Session 配置
 * @Author: lovefc 
 * @Date: 2019-09-24 12:32:45 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-09-25 10:41:48
 */

return [
    'default' => [
        // Session前缀
        'prefix' => 'fc_',
        // Session的名称
        'name' => 'FCSESSION',
        // 存储路径
        //'save_path' => dirname(PATH['FC']).'/Sess',
        // 存储方式
        //'save_handler' => 'files', // redis,files        
        /*
        // 在读取完毕会话数据之后马上关闭会话存储文件
        'cache_limite' => 'private',
        // SessionID在客户端Cookie储存的时间，默认是0，代表浏览器一关闭SessionID就作废
        'cookie_lifetime' => 3600,
        // Cookies存储路径
        'cookie_path' => PATH['NOW'],
        // Cookies 域名
        'cookie_domain' => '',
        // 是否将httpOnly标志添加到cookie中，这使得浏览器脚本语言(如JavaScript)无法访问该标志。
        'cookie_httponly' => 0,
        // 定义“垃圾收集”过程启动的概率
        'gc_probability' => 1,
        // 垃圾收集，运行概率
        'gc_divisor' => 100,
        // Session在服务端存储的时间
        'gc_maxlifetime' => 1440,
        // 在读取完会话数据之后， 立即关闭会话存储文件，不做任何修改
        'read_and_close ' => true
        */
    ],
];
