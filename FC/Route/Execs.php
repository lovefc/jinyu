<?php

namespace FC\Route;

/**
 * 类与函数执行处理，参数判断类
 * by lovefc
 */
class Execs
{

    public static $rulearr = [], $errors = []; //错误数组

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
        if (($func instanceof \Closure) || function_exists($func)) {
            return true;
        }
        return false;
    }

    //解析函数
    public static function func($func, $arg = null)
    {
        if (empty($func) || is_array($func)) {
            return false;
        }		
        //检查是不是匿名函数
        if (($func instanceof \Closure) || function_exists($func)) {
            $r = new \ReflectionFunction($func);
            $arg = is_array($arg) ? $arg : $r->getParameters();
            return call_user_func_array($func, static::getMethodVar($arg));
        } else {
            return false;
        }
    }

    //解析类方法
    public static function method($func, $arg = null)
    {
        if (empty($func[0]) || empty($func[1])) {
            return false;
        }
        list($class, $method) = $func;
        $r = new \ReflectionMethod($class, $method);
        //分析这个方法是不是静态
        if (!$r->isStatic()) {
            if (!is_object($class)) {
                $class = self::constructor($class); //不是静态属性就实例化
            }
        }
        if (!is_object($class)) {
            return false;
        }
        // 验证方法是否存在
        if (!method_exists($class, $method)) {
            return false;
        }
        $func = array(
            $class,
            $method
        );
        $arg = is_array($arg) ? $arg : $r->getParameters();
        return call_user_func_array($func, static::getMethodVar($arg));
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
	
    //处理构造函数二
    public static function _constructor($className,$args='')
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
		if(!$args){
            return $reflector->newInstanceArgs($constructor->getParameters());
		}
		return $reflector->newInstanceArgs($args);
    }
	
    //魔术方法__callStatic,检测getMethodVar方法是否存在的
    public static function __callStatic($method, $arg)
    {
        if ($method == 'getMethodVar') {
            return $arg[0];
        }
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
     *  $preg = array('函数名 || 匿名函数 || 类方法 || 简写名' , '默认值','错误信息');
     *  $preg =  array(
     *       array('必填','英文'),
     *       '',
     *       array('必须要填写值','必须要是英文哦')
     *   )
     *  $str 要检测的参数
     */
    public static function regularHandles($preg, $str)
    {
        $str = trim($str); //过滤字符串  
        if (empty($str)) {
            return '';
        }
        if (!$preg) {
            return $str;
        }
        $default = (isset($preg[1]) && !is_array($preg[1])) ? $preg[1] : null;
        $errormsg = (isset($preg[2]) && !is_array($preg[2])) ? $preg[2] : null;
        //如果preg是数组
        if (is_array($preg)) {
            //$preg[0]不是数组
            if (!is_array($preg[0]) || is_object($preg[0][0]) || self::getMaxdim($preg[0]) <= 1) {
                $values = [];
                $values[0] = $preg[0];
                $values[1] = $default;
                $values[2] = $errormsg;
                $val = self::regularHandle($values, $str);
                return $val;
            } else {
                foreach ($preg[0] as $k => $v) {
                    $val = '';
                    $values = [];
                    $values[0] = $v;
                    $values[1] = isset($preg[1][$k]) ? $preg[1][$k] : $default;
                    $values[2] = isset($preg[2][$k]) ? $preg[2][$k] : $errormsg;
                    $val = self::regularHandle($values, $str);
                    unset($values);
                }

                return $val;
            }
        } else {
            return self::regularHandle($preg, $str);
        }
    }

    //单一处理
    public static function regularHandle($preg, $str)
    {
        $default = $errormsg = $pregs = null;
        if (is_array($preg)) {
            $default  = isset($preg[1]) ? $preg[1] : null; //获取默认值
            $errormsg = isset($preg[2]) ? $preg[2] : null; //获取错误消息
            $preg = $preg[0]; //获取验证规则
        }
        if (is_array($preg)) {
            $str = self::method($preg, (array) $str);
            if (!empty($str)) {
                return $str;
            } else {
                return self::returnError($errormsg, $default);
            }
        } elseif (self::isFunc($preg)) {
            if ($str = self::func($preg, (array) $str)) {
                return $str;
            } else {
                return self::returnError($errormsg, $default);
            }
        } else {
            if (empty($preg)) {
                return $default;
            }
            $preg2 = self::ruleArrs($preg);

            if (is_array($preg2)) {
                $preg = $preg2[0]; //获取验证规则
                $default = $preg2[1]; //获取默认值
                $errormsg = $preg2[2]; //获取错误消息
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
                return self::returnError($errormsg, $default);
            }
        }
    }

    //返回错误
    public static function returnError($errormsg, $default)
    {
        empty($errormsg) && $errormsg = '';
        self::$errors[] = $errormsg;
        if (!empty($default)) {
            return $default;
        } else {
            return $errormsg;
        }
    }

    //常用正则检测
    public static function ruleArrs($rule)
    {
        if (empty($rule) || !is_string($rule)) {
            return false;
        }
        self::$rulearr = array(
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
            '英数' => '/^[_0-9a-z]+$/i'
        );

        $preg = isset(self::$rulearr[$rule]) ? self::$rulearr[$rule] : $rule;
        return $preg;
    }
}
