<?php

namespace FC;

/*
 * 框架公用函数库
 * @Author: lovefc 
 * @Date: 2016/9/09 13:29:34 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-12 15:27:32
 */


/**
 * 获取类的对象
 *
 * @param [type] $class
 * @param string $dir
 * @param string $mode
 * @return void
 */
function obj($class, $arg = null, $mode = 'cache')
{
    if (!$class) {
        return false;
    }
    $class = ltrim($class, '\\');
    static $fcobj = [];
    if (isset($fcobj[$class]) && $mode != 'notcache') {
        return $fcobj[$class];
    }
    if (class_exists($class)) {
        switch ($mode) {
            case 'cache':
                $fcobj[$class] = \FC\Route\Execs::_constructor($class, $arg);
                break;
            case  'notcache':
                return \FC\Route\Execs::_constructor($class, $arg);
                break;
            default:
                $fcobj[$class] = \FC\Route\Execs::_constructor($class, $arg);
        }
        return $fcobj[$class];
    }
    return false;
}

/**
 * in_array系统函数的替换方案
 *
 * @param [type] $item 键名|键值
 * @param [type] $array 数组
 * @param boolean $status 翻转数组，查找键值
 * @return void
 */
function inArray($item, $array, $status = true)
{
    if ($status === true) {
        $flipArray = array_flip($array);
    }
    return isset($flipArray[$item]);
}

/**
 * 获取数组键名
 * @param $config 数组
 * @param $array 键名，多个
 */
function ImpArray($config, $array)
{
	if(!is_array($config)) return false;
    if (is_array($array) && count($array) > 0) {
        foreach ($array as $value) {
            $config = isset($config[$value]) ? $config[$value] : null;
        }
        return $config;
    }
    return $config;
}

/**
 * 转义变量,检测变量
 * 
 * @param $input 要转义的值，可以是一个值或者是一个数组,或者是某一个数组的键名
 * 当检测数组的键值并转义的时候，input的值可以用a::b这样来表示['a']['b']
 * @param $var 一个数组，如果存在的话，会把第一个参数当做键名检查
 * @return void
 */
function input($input, $var = null)
{
    if (is_array($var)) {
        $inputs = isset($var[$input]) ? addslashes($var[$input]) : ImpArray($var, explode('::', $input));;
        $var = MAGIC_QUOTES_GPC === 0 ? addslashes($inputs) : $inputs;
    } else {
        if (is_array($input)) {
            $var = [];
            foreach ($input as $key => $value) {
                $var[$key] = MAGIC_QUOTES_GPC === 0 ? addslashes($value) : $value;
            }
        } else {
            $var = MAGIC_QUOTES_GPC === 0 ? addslashes($input) : $input;
        }
    }
    return $var;
}

/**
 * 获取GET的值
 *
 * @param [type] $key GET的值
 * @param bool $case 是否检测大小写
 * @return voidtrue
 */
function get($key = null, $default= null, $case = true)
{
	if(empty($key)){
		return Input($_GET);
	}
    if ($case === false) {
        $key = strtolower($key);
    }
    $v = Input($key, $_GET);
	return $v ?? $default;
}

/**
 * 获取POST的值
 *
 * @param [type] $key
 * @return void
 */
function post($key = null, $default= null)
{
	if(empty($key)){
		return Input($_POST);
	}	
    $v = Input($key, $_POST);
	return $v ?? $default;
}


/**
 * 设置可跨域访问的域名
 * 
 * @param $allow_origin  允许的域名, 为false表示所有域名都可以访问，可以是一个包含域名列表的数组
 * @param $method  请求方式，多个用，号分割(POST, GET, OPTIONS, PUT, DELETE)
 * @param $credentials 支持跨域发送cookies
 * @return void
 */
function setOrigin($allow_origin = false, $method = 'GET', $credentials = false)
{
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    $allow_origin = (empty($allow_origin) || $allow_origin == '*') ? '*' : (array) $allow_origin;
    if ($allow_origin == '*') {
        if ($credentials === true) {
            header("Access-Control-Allow-Credentials: true");
        }
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:' . $method);
        header('Access-Control-Allow-Headers:Content-Type, Content-Length, Authorization, Accept, X-Requested-With , yourHeaderFeild');
    } elseif (in_array($origin, $allow_origin)) {
        if ($credentials === true) {
            header("Access-Control-Allow-Credentials: true");
        }
        header('Access-Control-Allow-Origin:' . $origin);
        header('Access-Control-Allow-Methods:' . $method);
        header('Access-Control-Allow-Headers:Content-Type, Content-Length, Authorization, Accept, X-Requested-With , yourHeaderFeild');
    } else {
        head(400);
        die(); //不允许访问
    }
}

/**
 * 获取当前的url
 * 获取 $_SERVER['REQUEST_URI'] 值的通用解决方案
 * 因为$_SERVER["REQUEST_URI"]这个值只有在apache下才会起作用
 * 
 * @return string
 */
function requestUri()
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $purl = getHttpType() . $host;
    if (isset($_SERVER['REQUEST_URI'])) {
        $uri = $_SERVER['REQUEST_URI'];
        if (!strstr($uri, 'http://') || !strstr($uri, 'http://')) {
            $uri = $purl . $_SERVER["REQUEST_URI"];
        }
    } else {
        if (isset($_SERVER['argv'])) {
            $uri = $purl . $_SERVER['PHP_SELF'] . '?' . $_SERVER['argv'][0];
        } else {
            $uri = $purl . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
        }
    }
    return $uri;
}


/**
 * 获取主域名
 *
 * @return string
 */
function getHostDomain()
{
    return getHttpType() . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '');
}
 
/**
 * 获取 HTTPS协议类型
 *
 * @return string
 */
function getHttpType()
{
    return $type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
}

/**
 * 获取客户端ip
 *
 * @return string
 */
function getIP()
{
    if (isset($_SERVER["HTTP_CLIENT_IP"]) && strcasecmp($_SERVER["HTTP_CLIENT_IP"], "unknown")) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    } else {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) && strcasecmp($_SERVER["HTTP_X_FORWARDED_FOR"], "unknown")) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            if (isset($_SERVER["REMOTE_ADDR"]) && strcasecmp($_SERVER["REMOTE_ADDR"], "unknown")) {
                $ip = $_SERVER["REMOTE_ADDR"];
            } else {
                if (
                    isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp(
                        $_SERVER['REMOTE_ADDR'],
                        "unknown"
                    )
                ) {
                    $ip = $_SERVER['REMOTE_ADDR'];
                } else {
                    $ip = "0.0.0.0";
                }
            }
        }
    }
    return $ip;
}

/**
 * 获取客户端类型(简单检测)
 *
 * @return string
 */
function getOS()
{
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $os = false;
    if (strpos($agent, 'Windows')) {
        $os = 'Windows';
    } elseif (strpos($agent, "Linux")) {
        $os = 'Linux';
    } elseif (strpos($agent, "Android")) {
        $os = 'Android';
    } elseif (strpos($agent, "iPhone")) {
        $os = 'iPhone';
    } elseif (strpos($agent, "iPad")) {
        $os = 'iPad';
    } elseif (strpos($agent, "Nokia")) {
        $rel = 'Nokia';
    } else {
        $os = 'Unknown';
    }
    return $os;
}

/**
 * UUID
 * 
 * @return string
 */
function uuid($num = 36): string
{
    $str = '%04x%04x-%04x-%03x4-%04x-%04x%04x%04x';
    if ($num == 32) {
        $str = '%04x%04x%04x%03x4%04x%04x%04x%04x';
    }
    return sprintf(
        $str,
        mt_rand(0, 65535),
        mt_rand(0, 65535),
        mt_rand(0, 65535),
        mt_rand(0, 4095),
        bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
        mt_rand(0, 65535),
        mt_rand(0, 65535),
        mt_rand(0, 65535)
    );
}


/**
 * 将指定字符串字符转换成大写
 *
 * @param  string  $value
 * @return string
 */
function upper(string $value): string
{
    return mb_strtoupper($value, 'UTF-8');
}

/**
 * 将给定的字符串所有字母转换成小写
 *
 * @param  string  $value
 * @return string
 */
function lower(string $value): string
{
    return mb_strtolower($value, 'UTF-8');
}

/**
 * 页面跳转
 * 
 * @return void
 */
function jump($url)
{
    if (!$url) {
        return false;
    }
    header('Location: ' . $url);
    exit();
}

/**
 * 设定http的状态
 * 
 * @param $num 状态码
 * @return void
 */
function head($status = 200)
{
    $http = array(
        100 => 'HTTP/1.1 100 Continue',
        101 => 'HTTP/1.1 101 Switching Protocols',
        200 => 'HTTP/1.1 200 OK',
        201 => 'HTTP/1.1 201 Created',
        202 => 'HTTP/1.1 202 Accepted',
        203 => 'HTTP/1.1 203 Non-Authoritative Information',
        204 => 'HTTP/1.1 204 No Content',
        205 => 'HTTP/1.1 205 Reset Content',
        206 => 'HTTP/1.1 206 Partial Content',
        300 => 'HTTP/1.1 300 Multiple Choices',
        301 => 'HTTP/1.1 301 Moved Permanently',
        302 => 'HTTP/1.1 302 Found',
        303 => 'HTTP/1.1 303 See Other',
        304 => 'HTTP/1.1 304 Not Modified',
        305 => 'HTTP/1.1 305 Use Proxy',
        307 => 'HTTP/1.1 307 Temporary Redirect',
        400 => 'HTTP/1.1 400 Bad Request',
        401 => 'HTTP/1.1 401 Unauthorized',
        402 => 'HTTP/1.1 402 Payment Required',
        403 => 'HTTP/1.1 403 Forbidden',
        404 => 'HTTP/1.1 404 Not Found',
        405 => 'HTTP/1.1 405 Method Not Allowed',
        406 => 'HTTP/1.1 406 Not Acceptable',
        407 => 'HTTP/1.1 407 Proxy Authentication Required',
        408 => 'HTTP/1.1 408 Request Time-out',
        409 => 'HTTP/1.1 409 Conflict',
        410 => 'HTTP/1.1 410 Gone',
        411 => 'HTTP/1.1 411 Length Required',
        412 => 'HTTP/1.1 412 Precondition Failed',
        413 => 'HTTP/1.1 413 Request Entity Too Large',
        414 => 'HTTP/1.1 414 Request-URI Too Large',
        415 => 'HTTP/1.1 415 Unsupported Media Type',
        416 => 'HTTP/1.1 416 Requested range not satisfiable',
        417 => 'HTTP/1.1 417 Expectation Failed',
        422 => 'HTTP/1.1 422 Expectation Unprocesable entity',
        500 => 'HTTP/1.1 500 Internal Server Error',
        501 => 'HTTP/1.1 501 Not Implemented',
        502 => 'HTTP/1.1 502 Bad Gateway',
        503 => 'HTTP/1.1 503 Service Unavailable',
        504 => 'HTTP/1.1 504 Gateway Time-out',
        'css' => 'Content-type:  application/css',
        'json' => 'Content-type: application/json',
        'js' => 'Content-type:  application/javascript',
        'xml' => 'Content-type:  application/xml',
        'text' => 'Content-Type:  application/plain',
        'zip' => 'Content-Type: application/zip',
        'pdf' => 'Content-Type: application/pdf',
        'jpeg' => 'Content-Type: image/jpeg',
        'gif' => 'Content-Type: image/gif',
        'text' => 'Content-type: application/text'
    );
    $hstatus = isset($http[$status]) ? $http[$status] : null;
    !empty($hstatus) && header($hstatus);
}

/**
 * 雪花算法，获取唯一的值
 *
 * @return int(23)
 */
function snowFlakeID()
{
    //假设一个机器id
    $machineId = 5219930613;
    //41bit timestamp(毫秒)
    $time = floor(microtime(true) * 1000);
    //0bit 未使用
    $suffix = 0;
    //datacenterId  添加数据的时间
    $base = decbin(pow(2, 40) - 1 + $time);
    //workerId  机器ID
    $machineid = decbin(pow(2, 9) - 1 + $machineId);
    //毫秒类的计数
    $random = mt_rand(1, pow(2, 11) - 1);
    $random = decbin(pow(2, 11) - 1 + $random);
    //拼装所有数据
    $base64 = $suffix . $base . $machineid . $random;
    //将二进制转换int
    $base64 = bindec($base64);
    $id = sprintf('%.0f', $base64);
    return $id;
}

/**
 * 格式化数组输出
 * @param $vars 需要格式化的数组
 * @param $label 名称
 * @param $return 是否返回值，默认直接输出
 * @return string
 */
function pre($vars, $label = '', $return = false)
{
    if (ini_get('html_errors')) {
        $content = "<pre>\n";
        if ($label != '') {
            $content .= "<strong>{$label} :</strong>\n";
        }
        $content .= htmlspecialchars(print_r($vars, true));
        $content .= "\n</pre>\n";
    } else {
        $content = $label . " :\n" . print_r($vars, true);
    }
    if ($return) {
        return $content;
    }
    echo $content;
}

/**
 * 隐藏电话号码中间4位和邮箱
 *
 * @param [type] $phone 手机号码或者邮箱
 * @return string
 */
function hide4PhoneEmail($phone)
{
    //隐藏邮箱
    if (strpos($phone, '@')) {
        $email_array = explode("@", $phone);
        $prevfix = (strlen($email_array[0]) < 4) ? "" : substr($phone, 0, 3); //邮箱前缀
        $count = 0;
        $str = preg_replace('/([\d\w+_-]{0,100})@/', '***@', $phone, -1, $count);
        $rs = $prevfix . $str;
        return $rs;
    } else {
        //隐藏联系方式中间4位
        $Istelephone = preg_match('/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i', $phone); //固定电话
        if ($Istelephone) {
            return preg_replace('/(0[0-9]{2,3}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i', '$1****$2', $phone);
        } else {
            return preg_replace('/(1[0-9]{1}[0-9])[0-9]{4}([0-9]{4})/i', '$1****$2', $phone);
        }
    }
}
