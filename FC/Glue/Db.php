<?php

namespace FC\Glue;

/*
 * 数据库链接
 * @Author: lovefc 
 * @Date: 2019-10-09 15:38:02 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-28 13:57:26
 */

class Db
{
    use \FC\Traits\Parents;

    public static $obj;

    public static $class_obj;

    // 初始设置
    public function _init()
    {
        // 默认配置，此选项用于多配置选择
        $this->ReadConf('mysql');
    }

    // 开始连接
    public function _start()
    {
        $type = strtolower($this->DbType);
        $obj = null;
        switch ($type) {
            case 'mysql':
                $obj = \FC\obj('FC\Db\Mysql');
                break;
            case 'sqlite':
                $obj = \FC\obj('FC\Db\Sqlite');
                break;
        }
        if ($obj) {
            self::$obj = $obj;
            $this->setObjectValue();
        }
        self::$class_obj = $this;
    }

    public static function __callStatic($method, $args)
    {
        if ($method == 'switch') {
            call_user_func_array([self::$class_obj, 'ReadConf'], $args);
            call_user_func_array([self::$class_obj, 'setObjectValue'], []);
            call_user_func_array([self::$class_obj, '_start'], []);
            return self::$class_obj;
        } else {
            return call_user_func_array([self::$obj, $method], $args);
        }
    }

    // 设置变量值
    private function setObjectValue()
    {
        foreach ($this->P_RegVar as $k => $v) {
            self::$obj->$v = $this->$v;
        }
    }
}
