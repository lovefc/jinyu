<?php

namespace FC;

/**
 * 框架基本入口
 * @Author: lovefc 
 * @Date: 2019-09-09 01:07:17 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-12 14:23:27
 * @Last Modified time: 2020-06-29 16:37:27 
 */

class Main
{
	// 启动
    public static function run($call = '')
    {
        try {
            $obj = obj('FC\Glue\Route');
            $obj::run();
        } catch (\Exception $e) {
			$error = $e->getMessage();
            if (empty($call)) {
                 Log::Show($error);
            }elseif(Route::isFunc($call)){
				 $call($error);
			}
        }
    }
    
    // 初始化设置
    public static function init()
    {

        if (defined('DEBUG') && empty(DEBUG)) {
            // 关闭错误
            ini_set("display_errors", "Off");
            error_reporting(0);
        } else {
            // 开启错误
            ini_set("display_errors", "On");
            error_reporting(E_ALL);
        }

        // 定义版本信息，用于覆盖原来的php版本
        header("X-Powered-By: FC/6.0");

        // 判断运行版本
        version_compare(PHP_VERSION, '7.0.0', '<=') && exit("FC框架只能运行在php7版本或以上的环境中,敬请见谅!\n");

        // 屏蔽PHP启动过程中的错误信息，不建议显示
        ini_set('display_startup_errors', 0);

        // 定义时区
        !defined('TIMEZONE') ? date_default_timezone_set('PRC') : date_default_timezone_set(TIMEZONE);

        // 定义编码
        !defined('CHARSET') ? header("Content-type:text/html; charset=utf-8") : header('Content-type: text/html; charset=' . CHARSET);

        // 判断swoole
        if (isset($_SERVER['SERVER_SOFTWARE']) && $_SERVER['SERVER_SOFTWARE'] === 'swoole-http-server') {
        }


        // 检测是否定义fastcgi_finish_request
        if (!function_exists("fastcgi_finish_request")) {
            function fastcgi_finish_request()
            {
            }
        }

        // 判断get_magic_quotes_gpc
        if (function_exists('get_magic_quotes_gpc')) {
            define('MAGIC_QUOTES_GPC', 1);
        } else {
            define('MAGIC_QUOTES_GPC', 0);
        }

        // 判断web运行的绝对目录，在ng和ap中，这个值是不同的
        $ROOT_PATH = ($_SERVER['CONTEXT_DOCUMENT_ROOT'] ?? '') || ($_SERVER['HOME'] ?? '');

        // 是否为AJAX请求 
        // jquery.js发起的请求，会包含HTTP_X_REQUESTED_WITH字段，如果自定义请求，需要这样定义请求来判断
        /**
         * var xmlhttp = new XMLHttpRequest();
         * xmlhttp.open("GET","test.php",true);
         * xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
         * xmlhttp.send();
         */
        $IS_AJAX = ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) ? true : false;
        // 是否为ajax请求
        define('IS_AJAX', $IS_AJAX);

        // pathinfo下对于PHP_SELF的兼容，避免出现不必要的值和安全问题,如果要获取本页面地址，推荐使用$_SERVER['SCRIPT_NAME']
        isset($_SERVER['PATH_INFO']) ? $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'] : '';

        // 获取框架目录
        $FC_PATH = strtr(__DIR__, '\\', '/');

        // 获取当前目录
        $NOW_PATH =  strtr(getcwd(), '\\', '/');

        // 时间常量,避免多次使用函数
        define('TIME', time());

        // 在CLI，CGI模式下的一些设置和兼容
        if (PHP_SAPI === 'cli') {
            define('FC_EOL', PHP_EOL);
            define('IS_CLI', true);
        } else {
            define('FC_EOL', '<br />');
            define('IS_CLI', false);
        }

        // 判断是不是win系统
        define('IS_WIN', (PATH_SEPARATOR === ':') ? false : true);

        // 定义框架的目录常量
        define('PATH', [
            // 框架目录
            'FC' => $FC_PATH,
            // web运行的绝对目录
            'ROOT'  => $ROOT_PATH,
            // 框架配置目录
            'FC_CONFIG' => $FC_PATH . '/Config',
            // 当前执行脚本的绝对路径
            'NOW'   => $NOW_PATH, // 当前路径
            // 当前配置目录
            'NOW_CONFIG' => $NOW_PATH . '/Config',
        ]);

        // 引入加载类
        require $FC_PATH . '/Load/LoaderClass.php';

        // 引入函数库
        require $FC_PATH . '/Func.php';

        // 加载类库
        Load\LoaderClass::AddPsr0($NOW_PATH,null,true);

        // 自动加载
        Load\LoaderClass::register();

        //加载初始化类
        $load = obj('FC\Glue\Load');

        //第三方扩展设置
        $load->ExtendConfig(PATH['FC_CONFIG'] . '/config.php');

        // 触发事件设置
        obj('FC\Glue\Event')->run();

        // 添加事件
        Event::trigger('OnLoad');

        // 错误处理和记录
        register_shutdown_function(['\FC\Log', 'Error']);

        // 获取当前地址，兼容方案
        define('NOW_URL', requestUri());

        // 获取主域名
        define('HOST_DOMAIN', getHostDomain());
        
		// 获取目录名
		$host_dir = dirname($_SERVER['PHP_SELF']);
		$host_dir = ($host_dir != '/') ? $host_dir : '';
        define('HOST_DIR', $host_dir);

        // 安全过滤
        obj('FC\Glue\SafeMode')->run();

        // 插件目录设置
        //\FC\obj('FC\Glue\Load')->ExtendConfig(PATH['FC_PLUG'] . '/config.php');
    }
}
