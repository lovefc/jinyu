<?php

/*
 * 验证码 配置
 * @Author: lovefc 
 * @Date: 2019-09-27 15:19:42
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-09-30 08:17:39
 */

return [

    'default' => [
        // 验证码宽度
        'width' => 200,
        // 验证码高度
        'height' => 60,
        // 验证码个数
        'nums' => 4,
        // 随机数
        'random' => '1234567890zxcvbnmasdfghjklqwertyuiop',
        // 随机数大小
        'font_size' => 35,
        // 字体路径
        //'font_path' => PATH['FC'].'/Http/Font/zhankukuhei.otf'
    ],
    'default2' => [
        // 验证码宽度
        'width' => 300,
        // 验证码高度
        'height' => 100,
        // 验证码个数
        'nums' => 4,
        // 随机数0
        'random' => '舔狗不得好死',
        // 随机数大小
        'font_size' => 30,
        // 字体路径
        //'font_path' => PATH['FC'].'/Http/Font/zhankukuhei.ttf'
    ],   
];
