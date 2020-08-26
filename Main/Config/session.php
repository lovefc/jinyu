<?php

/*
 * Session 配置
 * @Author: lovefc 
 * @Date: 2019-09-24 12:32:45 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-12 13:18:02
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
    ],
];
