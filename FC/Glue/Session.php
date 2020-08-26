<?php

namespace FC\Glue;

use FC\Http\Session as Sess;

/*
 * SESSION
 * @Author: lovefc 
 * @Date: 2019-09-24 10:58:20
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-11 11:25:00
 */

class Session extends Sess
{
    // 继承配置
    use \FC\Traits\Parents;
    
    // 初始设置
    public function _init()
    {
        // 默认配置，此选项用于多配置选择
        $this->ReadConf('default');
    }

    public function _start(){
        $this->init();
    }

    // 错误消息
    public function error($msg)
    {
        \FC\Log::Show($msg);
    }
}
