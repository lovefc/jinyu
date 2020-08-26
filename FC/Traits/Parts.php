<?php

namespace FC\Traits;

/*
 * 多继承容器
 * @Author: lovefc
 * @Date: 2016/8/29 10:17:40
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-12 14:01:58
 */

trait Parts
{
    // 多继承，继承配置
    use \FC\Traits\Parents;

    /**
     * 声明配置文件
     *
     * @return string
     */
    public static function SetConfigName()
    {
        return 'parts.php';
    }

    /**
     * 魔术方法  __get()方法用来获取私有属性
     *
     * @param [type] $name
     * @return string
     */
    public function __get($name)
    {
        return $this->$name = isset($this->P_Config[$name]) ? $this->_GetObj($name) : '';
    }

    /**
     * 获取每一个类的实例化
     *
     * @param [type] $name
     * @return object
     */
    public function _GetObj($name)
    {
        if (is_array($this->P_Config[$name]) && count($this->P_Config[$name]) > 0) {
            $method = (bool) isset($this->P_Config[$name][1]) ? $this->P_Config[$name][1] : false;
            $obj = \FC\obj($this->P_Config[$name][0]);
            if ($method) {
                try {
                    return $obj->$method();
                } catch (\Exception $e) {
                    \FC\Log::Show($e->getMessage());
                }
            }
            return $obj;
        } else {
            return \FC\obj($this->P_Config[$name]);
        }
    }
}
