<?php

/*
 * 路由访问配置
 * @Author: lovefc
 * @Date: 2019-09-16 15:52:35
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-07 00:16:12
 */

/**
 * 要注意
 * 设置访问键值的时候,可以和该工作目录下拥有的的目录名称一样,但要注意大小写
 * 如果大小写不一致,依赖的全局变量QUERY_STRING 会自动转成该目录下文件命名的大小写,从而导致访问失败.
 */
return [
    // 默认访问
    'default' => function () {
        echo 'hello world';
    },
];
