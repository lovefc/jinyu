<?php

namespace FC\Glue;

use FC\Route as LuYou;

/*
 * 路由
 * @Author: lovefc 
 * @Date: 2019-09-16 15:05:57 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-11 11:25:02
 */

class Route extends LuYou
{
    //继承配置
    use \FC\Traits\Parents;

    //初始设置
    public function _init()
    {
        self::$routeval = $this->P_Config;
    }

    //错误消息
    public function error($msg)
    {
        \FC\Log::Show($msg);
    }
}
