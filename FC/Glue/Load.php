<?php

namespace FC\Glue;

use FC\Load\LoaderClass;

/*
 * 处理公共中的加载配置
 * @Author: lovefc 
 * @Date: 2018/7/12 17:03:53 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-09 15:40:46
 */

class Load
{
    use \FC\Traits\Parents;

    // 初始化操作
    public function _init()
    {
        // 加载框架类库
        LoaderClass::AddFile($this->P_Config);
    }

    // 类扩展设置
    public function ExtendConfig($file = null)
    {
        if (!is_file($file)) {
            return false;
        }
        $namespace = self::P_GetConfigFile($file);
        if ($namespace) {
            LoaderClass::AddFile($namespace);
        }
    }

    // 错误消息
    public function error($msg)
    {
        \FC\Log::Show($msg);
    }
}
