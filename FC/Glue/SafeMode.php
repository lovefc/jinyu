<?php

namespace FC\Glue;

use FC\Http\SafeMode as Safe;

/*
 * 安全过滤类库
 * @Author: lovefc 
 * @Date: 2019-09-30 14:39:12
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-09-30 15:26:47
 */

class SafeMode extends Safe
{
    use \FC\Traits\Parents;

    // 运行
    public function run()
    {
        $status = $this->status;
        $log_dir = $this->log_dir;
        $this->xss($status, $log_dir);
    }

    // 错误消息
    public function error($msg)
    {
        \FC\Log::Show($msg);
    }
}
