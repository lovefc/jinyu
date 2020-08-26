<?php

namespace FC\Model;

/**
 * 类与函数执行处理，参数判断类
 * by lovefc
 */
class Check
{

    public static $rulearr = [], $errors = []; //错误数组

    public static $showError = true;

    public static function errShow()
    {
        if (self::$showError == true) {
            throw new \Exception('function does not exist');
        } else {
            die();
        }
    }

    //判读字符串是否为一个可以实例化类
    public static function isClass($class)
    {
        try {
            $reflectionClass = new \ReflectionClass($class);

            if ($reflectionClass->isInstantiable()) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    //判断是不是函数或者匿名函数
    public static function isFunc($func)
    {
        if (empty($func) || is_array($func)) {
            return false;
        }
        if ($func instanceof \Closure) {
            return true;
        } else {
            if (function_exists($func)) {
                return true;
            }
        }
        return false;
    }

    //解析函数
    public static function func($func, $arg = null)
    {
        //检查是不是匿名函数
        if ($func instanceof \Closure) {
            $r = new \ReflectionFunction($func);
            $arg = is_array($arg) ? $arg : $r->getParameters();
            return call_user_func_array($func, static::getMethodVar($arg));
        } else {
            if (function_exists($func)) {
                $r = new \ReflectionFunction($func);
                $arg = is_array($arg) ? $arg : $r->getParameters();
                return call_user_func_array($func, static::getMethodVar($arg));
            } else {
                self::errShow('function does not exist');
            }
        }
    }

    //解析类方法
    public static function method($func, $arg = null)
    {
        if (empty($func[0]) || empty($func[1])) {
            self::errShow('Parameter values cannot be null');
        }
        try {
            $r = new \ReflectionMethod($func[0], $func[1]);
            //分析这个方法是不是静态
            if (!$r->isStatic()) {
                if (!is_object($func[0])) {
                    $func[0] = self::constructor($func[0]); //不是静态属性就实例化
                }
            }
            if ($func[0]) {
                $func = array(
                    $func[0],
                    $func[1]
                );
                $arg = is_array($arg) ? $arg : $r->getParameters();
                return call_user_func_array($func, static::getMethodVar($arg));
            } else {
                self::errShow('Object cannot be instantiated');
            }
        } catch (\Exception $e) {
            $func[0] = self::constructor($func[0]); //实例化
            if (!is_object($func[0])) {
                self::errShow('Object:' . $func[0] . ' cannot be instantiated');
            }
            // 验证方法是否存在
            if (!method_exists($func[0], $func[1])) {
                self::errShow('method:' . $func[1] . ' does not exist');
            }
            $func = array(
                $func[0],
                $func[1]
            );
            return call_user_func_array($func, (array) $arg);
        }
    }

    //处理构造函数
    public static function constructor($className)
    {
        $reflector = new \ReflectionClass($className); //反射这个类
        // 检查类是否可实例化, 排除抽象类abstract和对象接口interface
        if (!$reflector->isInstantiable()) {
            return false;
        }

        //获取类的构造函数
        $constructor = $reflector->getConstructor();

        // 若无构造函数，直接实例化并返回
        if (is_null($constructor)) {
            return new $className;
        }
        return $reflector->newInstanceArgs(static::getMethodVar($constructor->getParameters()));
    }

    //检测数组维度
    public static function getMaxdim($array)
    {
        if (!is_array($array)) {
            return 0;
        } else {
            $max1 = 0;
            foreach ($array as $item1) {
                $t1 = self::getMaxdim($item1);
                if ($t1 > $max1) {
                    $max1 = $t1;
                }
            }
            return $max1 + 1;
        }
    }

    /**
     *  正则检测参数
     *  $str 要检测的参数
     */
    public static function regularHandles($preg, $str)
    {
        // 如果是数组
        if(is_array($str)){
            return $str;
        }
        $str = trim($str); //过滤字符串  
        if (empty($str)) {
            return '';
        }
        if (!$preg) {
            return $str;
        }
        $default =  null;
        if (is_array($preg)) {
            $val = '';
            if (is_object($preg[0]) && self::getMaxdim($preg) <= 1) {
                $values = $preg;
                $val = self::regularHandle($values, $str);
                return $val;
            }
            foreach ($preg as $k => $v) {
                $values = $v;
                $val = self::regularHandle($values, $str);
                unset($values);
            }
            return $val;
        } else {
            return self::regularHandle($preg, $str);
        }
    }

    //单一处理
    public static function regularHandle($preg, $str)
    {
        // 设置错误等级
        \FC\log::$Level = [1, 2, 4, 8, 16, 32, 64, 128, 256, 512, 1024, 2048, 4096, 8192, 16384, 32767];
        $default  = null;
        if (is_array($preg)) {
            $str = self::method($preg, (array) $str);
            if (!empty($str)) {
                return $str;
            } else {
                return $default;
            }
        } elseif (self::isFunc($preg)) {
            if ($str = self::func($preg, (array) $str)) {
                return $str;
            } else {
                return  $default;
            }
        } else {
            if (empty($preg)) {
                return $default;
            }
            $preg2 = self::ruleArrs($preg);
            if (is_array($preg2)) {
                $preg = $preg2[0]; //获取验证规则
            } else {
                $preg = $preg2;
            }
            if ($preg == 'empty') {
                return empty($str) ? $default : $str;
            } elseif ($preg == 'isset') {
                return isset($str) ? $str : $default;
            }
            if (preg_match($preg, $str)) {
                return $str;
            } else {
                return $default;
            }
        }
    }

    //常用正则检测
    public static function ruleArrs($rule)
    {
        if (empty($rule) || !is_string($rule)) {
            return false;
        }
        self::$rulearr = [
            '必填' => '/.+/',
            '邮箱' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            '网址' => '/^(http|ftp|https|ftps):\/\/([a-z0-9\-_]+\.)/i',
            '数字' => '/^\d+$/',
            '手机号' => '/^((\(\d{3}\))|(\d{3}\-))?1[34578]\d{9}$/',
            '电话号' => '/^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/',
            '邮编' => '/^[1-9]\d{5}$/',
            '整数' => '/^[-\+]?\d+$/',
            '浮点数' => '/^[-\+]?\d+(\.\d+)?$/',
            '英文' => '/^[_A-Za-z]+$/',
            '中文' => '/^([\x{4e00}-\x{9fa5}])+$/u',
            '身份证号' => '/^(([0-9]{15})|([0-9]{18})|([0-9]{17}x))$/',
            'QQ' => '/^[1-9]\d{4,9}$/',
            '日期' => '/^\d{4}\-\d{1,2}-\d{1,2}$/',
            'IP' => '/^((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$/',
            '帐号' => '/^[a-zA-Z][a-zA-Z0-9_]{3,10}$/',
            '密码' => '/^[a-zA-Z]\w{5,17}$/', //以字母开头，长度在6~18之间，只能包含字母、数字和下划线
            '强密码' => '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,10}$/', //必须包含大小写字母和数字的组合，不能使用特殊字符，长度在8-10之间
            '英数' => '/^[_0-9a-z]+$/i',
            '年龄' => '/^[0-9]{1,2}$/'
        ];

        $preg = isset(self::$rulearr[$rule]) ? self::$rulearr[$rule] : $rule;
        return $preg;
    }

    //魔术方法__callStatic,检测getMethodVar方法是否存在的
    public static function __callStatic($method, $arg)
    {
        if ($method == 'getMethodVar') {
            return $arg[0];
        }
    }
    public function error($msg, $e = '')
    {
        die($msg);
    }
}
