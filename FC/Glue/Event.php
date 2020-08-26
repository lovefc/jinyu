<?php

namespace FC\Glue;

/*
 * 全局初始化类
 * @Author: lovefc 
 * @Date: 2019-09-20 14:04:27 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-09-23 11:06:49
 */

class Event
{
    use \FC\Traits\Parents;

    // 运行
    public function run()
    {
        try {
            $config = $this->P_Config;
            if (is_array($config) && count($config) >= 1) {
                foreach ($config as $k => $v) {
                    if (is_array($v)) {
                        $callback = $v[0] ?? false;
                        $one = $v[1] ?? true;
                        // 添加事件
                        if ($callback) {
                            \FC\Event::listen($k, $v[0], $one);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    // 错误消息
    public function error($msg)
    {
        \FC\Log::Show($msg);
    }
}
