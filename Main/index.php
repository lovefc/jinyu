<?php

// 报错显示
define('DEBUG', true);

// 定义错误日志
define('LOG_DIR',__DIR__.'/Log');

// 引入框架
require dirname(__DIR__) . '/vendor/autoload.php';

\FC\Main::init();

\FC\Main::run();
