<?php

namespace FC\Glue;

use FC\View\Eztpl;

/*
 * 视图模版
 * @Author: lovefc 
 * @Date: 2019-09-24 15:24:20
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-11 11:24:57
 */

class View extends Eztpl
{
    use \FC\Traits\Parents; //继承


    //初始设置
    public function _init()
    {
        $this->ReadConf('default'); //默认配置，此选项用于多配置选择
        (!empty($this->P_Config['TPL_REPLACE'])) ? $this->binds($this->P_Config['TPL_REPLACE']) : '';
    }

    //错误消息
    public function error($msg)
    {
        \FC\Log::Show($msg);
    }
}
