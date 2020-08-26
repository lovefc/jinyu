<?php

namespace FC\Traits;

/**
 * 缓存一些常用的数据
 * 
 * @Author: lovefc 
 * @Date: 2016/8/29 13:49:27 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-12 14:02:01
 */

class CacheVars
{
    //保存例实例在此属性中
    private static $_instance;
    public $P_Configs;
    public $P_PublicConfig;
    public $P_ArrayConfig;

    /**
     * 构造函数声明为private,防止直接创建对象
     */
    private function __construct()
    { }

    /**
     * 单例方法
     *
     * @return object
     */
    public static function singleton()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
}
