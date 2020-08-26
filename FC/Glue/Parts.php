<?php

namespace FC\Glue;

/*
 * 多继承容器
 * @Author: lovefc
 * @Date: 2016/8/29 10:17:40
 * @Last Modified by: lovefc
 * @Last Modified time: 2020-04-27 14:17:58
 */

class Parts
{
    // 多继承，继承配置
    use \FC\Traits\Parents;
	
    /**
     * 用来获取属性
     *
     * @param [type] $name
     * @return string
     */
    public function get($name)
    {
        return isset($this->P_Config[$name]) ? $this->_GetObj($name) : '';
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
            $method = isset($this->P_Config[$name][1]) ? $this->P_Config[$name][1] : false;
			$values = isset($this->P_Config[$name][2]) ? $this->P_Config[$name][2] : false;
            $obj = \FC\obj($this->P_Config[$name][0]);
            if ($method) {
                try {
					if($values){
                        return $obj->$method($values);
					}
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
