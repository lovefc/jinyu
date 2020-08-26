<?php

namespace FC\Cache;

/*
 * 数据缓存类
 * memcache or redis or file
 * @Author: lovefc
 * @Date: 2019-10-03 00:24:47
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-11 15:00:37
 */

class Cache
{
    // 地址
    public $Path;
    // 端口
    public $Port;
    // 方式
    public $Mode;
    // 类的接口
    public $Obj = array();
    // 缓存文件的时候,保存的文件名是否md5加密
    public $IsMd5 = true;
    // 缓存的文件后缀
    public $Ext = '.cache';
    // 文件缓存过期时间
    public $Time = '60';
    // 类型
    public $ConfigType;

    /**
     * 判断缓存方式,返回句柄
     *
     * @return void
     */
    public function obj()
    {
        if (isset($this->Obj[$this->ConfigType])) {
            return $this->Obj[$this->ConfigType];
        }
        switch ($this->Mode) {
            case 'memcache':
                $obj = $this->memcache();
                break;
            case 'redis':
                $obj = $this->redis();
                break;
            case 'file':
                $obj = $this->files();
                break;
        }
        if (isset($obj)) {
            $this->Obj[$this->ConfigType] = $obj;
            return $obj;
        } else {
            $this->error('缓存方式配置出错');
        }
        return false;
    }

    /**
     * memcache
     *
     * @return object
     */
    public function memcache()
    {
        if (class_exists('Memcache', false)) {
            $obj = new \Memcached();
        } else {
            $obj = new \FC\Cache\Memcache();
        }
        try {
            $obj->connect($this->Path, $this->Port);
        } catch (\Exception $e) {
            $this->error('Memcache 链接出错，请检查配置');
        }
        return $obj;
    }

    /**
     * redis
     *
     * @return object
     */
    public function redis()
    {
        $obj = null;
        if (class_exists('Redis', false)) {
            $obj = new \Redis();
        } else {
            $obj = new \FC\Cache\Redis();
        }
        try {
            $obj->connect($this->Path, $this->Port);
        } catch (\Exception $e) {
            $this->error('Redis 链接出错，请检查配置');
        }
        return $obj;
    }

    /**
     * file
     *
     * @return object
     */
    public function files()
    {
        $obj = new \FC\Cache\Files();
        $obj->connect($this->Path, $this->IsMd5, $this->Ext, $this->Time);
        return $obj;
    }

    /**
     * 打印错误消息
     *
     * @param [type] $msg
     * @return void
     */
    public function error($msg)
    {
        die($msg);
    }
}
