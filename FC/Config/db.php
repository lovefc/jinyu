<?php

/*
 * 数据库配置
 * @Author: lovefc 
 * @Date: 2019-10-09 16:27:47 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-28 13:56:50
 */

return [

        'mysql' => [
                // 主机地址
                'Host' => '127.0.0.1',
                // 端口号
                //'Port' => '3306',
                // 数据库名
                'DbName' => 'ceshi',
                // 数据库用户名
                'DbUser' => 'root',
                // 数据库密码
                'DbPwd' => 'root',
                // 长链接
                'Attr' => false,
                // 数据库编码
                'Charset' => 'utf8',
                // 数据库表前缀
                'Prefix' => '',
                // 数据库类型
                'DbType' => 'mysql',
                // 缓慢查询记录时间，单位为s,大于这个值就会被记录下来
                // 设为0,则会关闭缓慢查询,可设为浮点数,默认为false
                'LongQueryTime' => false,
                // 统计数据执行时间
                'SqlTime' => 0,
        ],

        'sqlite' => [
                // 数据库路径
                'DbName' => PATH['NOW'] . '/Sql/ceshi.db',

                // 数据库用户名
                'DbUser' => 'root',

                // 数据库密码
                'DbPwd' => '',

                // 长链接
                'Attr' => false,

                // 数据库类型
                'DbType' => 'sqlite',

                // 数据库编码
                'Charset' => 'utf8',

                // 数据库表前缀
                'Prefix' => '',

        ],


];
