<?php

/*
 * 模板引擎配置
 * @Author: lovefc 
 * @Date: 2019-09-22 17:28:00 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-09-23 00:07:40
 */

return [

    'default' => [

        //左分割符
        'TplBegin' => '{(',

        //右分割符
        'TplEnd' => ')}',

        //模版后缀
        'Suffix' => 'html',

        //模版文件路径
        'Dir' => PATH['NOW'] . '/View',

        'TempDir' => PATH['NOW'] . '/Runtime',

        //模板文件错误，要跳转的地址，可以是绝对地址,默认为文字提示
        'ErrorUrl' => '模版文件错误',

        //强制编译
        'TempOpen' => true,

        //引用编译
        'IncludeOpen' => true,

    ],

    //自定义模板替换，请注意这里是通用的，所有的配置都可以用哦
    'TPL_REPLACE' => [

        //文件引用的简写[include(模版名称或者文件路径)]
        '#\[include\((.*)\)\]#isuU' => '{(include file="\\1")}',

        //文件引用的简写{引用：模版名称或者文件路径}
        '#\{引用：(.*)\}#isuU' => '{(include file="\\1")}',

        //引入js的简写
        '#\[js=(.*)\]#' => '<script src="\\1" type="text/javascript"></script>',

        //jquery的简写
        '#\[jquery\]#' => 'http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js',

        //检测并输出变量（传入的变量）[@a]
        '#\[\@([_A-Za-z.0-9]+)\]#isuU' => '{(if isset(@\\1))}{(@\\1)}{(/if)}',

        //检测并输出变量（传入的变量） <<@a>>
        '#\<<\@([_A-Za-z.0-9]+)\>>#isuU' => '{(if isset(@\\1))}{(@\\1)}{(else)}0{(/if)}',

        //检测并输出,前面可以加函数名 [md5 @a]
        '#\[([_A-Za-z.0-9]+)\s+\@([_A-Za-z.0-9]+)\]#isuU' => '{(if isset(@\\2))}{(\\1(@\\2))}{(/if)}',

        //检测并输出,前面可以加函数名  [md5 $a]
        '#\[([_A-Za-z.0-9]+)\s+\$([_A-Za-z.0-9]+)\]#isuU' => '{(if isset($\\2))}{(\\1($\\2))}{(/if)}',

        //检测并输出,前面可以加函数名 [md5 @a]
        '#\[!([_A-Za-z.0-9]+)\s+\@([_A-Za-z.0-9]+)\]#isuU' => '{(if isset(@\\2))}{(!\\1(@\\2))}{(/if)}',

        //检测并输出,前面可以加函数名  [md5 $a]
        '#\\[!([_A-Za-z.0-9]+)\s+\$([_A-Za-z.0-9]+)\]#isuU' => '{(if isset($\\2))}{(!\\1($\\2))}{(/if)}',

        //循环数组加默认 [list default="没有"][$value.get][/list]
        '#\[list="([_A-Za-z-0-9.@$]+)"\s+default="(.*)"\]([\w\W]+?)\[\/list\]#' => '{(if isset(\\1) && is_array(\\1))}{(foreach \\1)}\\3{(/foreach)}{(else)}\\2{(/if)}',

        //循环无默认 [list][$value.get][/list]
        '#\[list="([_A-Za-z0-9.@$]+)"\]([\w\W]+?)\[\/list\]#' => '{(if isset(\\1) && is_array(\\1))}{(foreach \\1)}\\2{(/foreach)}{(/if)}',

        //输出时间
        '#\[time="(.*)"\]#' => '{(date("Y-m-d H:i",\\1))}'

    ],

];
