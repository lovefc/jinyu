<?php

namespace FC\Http;

/*
 * 简单的cookies封装
 * @Author: lovefc 
 * @Date: 2019-09-26 10:20:07 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-09-26 16:26:00
 */

class Cookies
{
    public $prefix = '';
    public $expire = 3600;
    public $path;
    public $domain;
    // 设置这个 Cookie 是否仅仅通过安全的 HTTPS 连接传给客户端(这不是全局设置)
    public $secure;

    /**
     * 设置cookies的过期时间
     * @param $expire
     * @return object
     */
    public function expire($expire)
    {
        if (is_numeric($expire) && $expire != '') {
            $this->expire = $expire;
        }
        return $this;
    }

    /**
     * 设置cookies的前缀
     * @param $prefix
     * @return object
     */
    public function prefix($prefix)
    {
        if (is_string($prefix) && $prefix != '') {
            $this->prefix = $prefix;
        }
        return $this;
    }

    /**
     * 设置cookies的路径
     * @param $path
     * @return object
     */
    public function path($path)
    {
        if (is_string($path) && $path != '') {
            $this->path = $path;
        }
        return $this;
    }

    /**
     * 设置cookies的域名
     * @param $domain
     * @return object
     */
    public function domain($domain)
    {
        if (is_string($domain) && $domain != '') {
            $this->domain = $domain;
        }
        return $this;
    }

    /**
     * 规定是否通过安全的 HTTPS 连接来传输 cookie
     */
    public function secure($secure)
    {
        if (is_string($secure) && $secure != '') {
            $this->secure = $secure;
        }
        return $this;
    }

    /**
     * 设置cookies
     * @param $name cookies的名称
     * @param $value cookies的值
     * @returen true|false
     */
    public function set($name, $value = null)
    {
        $name = empty($this->prefix) ? $name : $this->prefix . $name;
        $expire = empty($this->expire) ? 0 : $this->expire;
        $baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $baseUrl = empty($baseUrl) ? '/' : '/' . trim($baseUrl, '/') . '/';
        $path = empty($this->path) ? $baseUrl : $this->path;
        $server_name = isset($_SERVER['SERVER_NAME']) ? trim($_SERVER['SERVER_NAME'], '/') : '';
        $domain = empty($this->domain) ? $server_name : $this->domain;
        $secure = $this->secure == true ? true : false;
        if (is_string($name) && $name != '') {
            return setcookie($name, $value, time() + $expire, $path, $domain, $secure);
        }
    }

    /**
     * 检测一个cookies的值
     * @param $name 键名
     * @return true|false
     */
    public function has($name)
    {
        $name = empty($this->prefix) ? $name : $this->prefix . $name;
        if (isset($_COOKIE[$name])) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 删除一个cookies的值
     * @param $name 键名
     * @return true|false
     */
    public function del($name)
    {
        $name = empty($this->prefix) ? $name : $this->prefix . $name;
        $expire = empty($this->expire) ? 0 : $this->expire;
        $baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $baseUrl = empty($baseUrl) ? '/' : '/' . trim($baseUrl, '/') . '/';
        $path = empty($this->path) ? $baseUrl : $this->path;
        $domain = empty($this->domain) ? $_SERVER['SERVER_NAME'] : $this->domain;
        $secure = $this->secure == true ? true : false;
        if (is_string($name) && $name != '') {
            return setcookie($name, '', time() - 36000, $path, $domain, $secure);
        }
    }

    /**
     * 获取一个cookies的值
     * @param $name 键名
     * @return false|string
     */
    public function get($name)
    {
        $name = empty($this->prefix) ? $name : $this->prefix . $name;
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    /**
     * 注销所有的cookies
     */
    public function clear()
    {
        $_COOKIE = array();
    }
}
