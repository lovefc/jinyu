<?php
/*
 * 多部件继承配置(简易容器)
 * 这里配置之后，继承之后，直接可以使用类变量
 * 例如 $this->IMG 
 * 如果配置为数组，第二个值为该类要执行的方法
 * 
 * @Author: lovefc 
 * @Date: 2019-09-22 17:41:41 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-10 16:44:47
 */
return [
    // Curl类
    'CURL'     => 'FC\Http\Curl',

    // 缓存类
    'CACHE'    => 'FC\Glue\Cache',

    // 文件缓存类
    'FCACHE'   => ['FC\Glue\Cache', 'F'],

    // redis类
    'REDIS'    => ['FC\Glue\Cache', 'R'],

    // memcache缓存类
    'MEMCACHE' => ['FC\Glue\Cache', 'M'],

    // 视图类
    'VIEW'     => 'FC\Glue\View',

    // Session类
    'SESSION'  => 'FC\Glue\Session',

    // Cookies类
    'COOKIES'  => 'FC\Http\Cookies',

    // 验证码类
    'CAPTCHA'  => 'FC\Glue\Captcha',

    // 数据库类
    'DB'       => 'FC\Glue\Db',   
];
