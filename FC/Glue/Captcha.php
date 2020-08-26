<?php

namespace FC\Glue;

use FC\Http\Captcha as Code;

/*
 * 验证码类
 * @Author: lovefc 
 * @Date: 2019-09-27 15:06:40
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-11 11:25:04
 */

class Captcha extends Code
{
    // 继承配置
    use \FC\Traits\Parents;

    // 初始设置
    public function _init()
    {
        // 默认配置，此选项用于多配置选择
        $this->ReadConf('default');
    }
    // 错误消息
    public function error($msg)
    {
        \FC\Log::Show($msg);
    }
}
