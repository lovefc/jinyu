<?php

namespace FC;

/*
 * 错误处理类
 * @Author: lovefc 
 * @Date: 2019-09-18 08:18:11 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-09 16:09:34
 */

class Log
{
    // 错误日志目录
    public static $LogDir;

    // 是否显示错误
    public static $ErrorShow;

    // 要记录的错误等级(错误等级都是倍数递增)
    public static $Level = [1, 2, 4, 8, 16, 32, 64, 128, 256, 512, 1024, 2048, 4096, 8192, 16384, 32767];
    
    // 错误文件模版
    public static $ViewFile = PATH['FC'] . '/Error.html';

    /**
     * 错误处理函数
     *
     * @return void
     */
    public static function Error()
    {
        $lasterror = error_get_last();
        $type = isset($lasterror['type']) ? $lasterror['type'] : '';
        if (!in_array($type, self::$Level)) {
            $lasterror = '';
        }
        if ($lasterror) {
            // 添加监听错误的事件l
            \FC\Event::trigger('Error', $lasterror);
        }
		if(defined('DEBUG') && (DEBUG === false)){
			die();
		}		
        if (IS_AJAX === true || IS_CLI === true) {
            if ($lasterror) {
                ob_clean();
                $err  = PHP_EOL . 'Type:' . $lasterror['type'] . PHP_EOL;
                $err .= 'Line:' . $lasterror['line'] . PHP_EOL;
                $err .= 'File:' . $lasterror['file'] . PHP_EOL;
                $err .= 'Message:' . $lasterror['message'] . PHP_EOL;
                echo (IS_WIN === true && IS_CLI === true) ? iconv('UTF-8', 'GBK', $err) : $err;
                self::WriteLog(array_unique($lasterror));
            }
            exit;
        }
        if ($lasterror) {
            ob_clean();
            // 获取程序执行结束的时间
            $lasterror['run_time'] = round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 5);
            // 获取错误行号处的代码
            $lasterror['code'] = trim(self::GetLine($lasterror['file'], $lasterror['line'], $length = 500));
            $error = $lasterror;
            require(self::$ViewFile);
            self::WriteLog(array_unique($lasterror));
        }
        exit;
    }

    /**
     * 输出消息提示
     *
     * @param [type] $err
     * @return void
     */
    public static function Show($err)
    {
        $error = array();
        if (is_array($err)) {
            $err = $err['message'];
        }
        if (IS_AJAX === true) {
            die($err);
        } elseif (IS_CLI === true) {
            die((IS_WIN === true) ? iconv('UTF-8', 'GBK', $err) : $err);
        }		
        $error['message'] = $err;
        require(self::$ViewFile);
        die();
    }

    /**
     * 获取指定行内容
     * 
     * @param $file 文件路径
     * @param $line 行数
     * @param $length 指定行返回内容长度
     */
    public static function GetLine($file, $line, $length = 500)
    {
        $returnTxt = null;
        // 初始化返回
        $i = 1;
        // 行数
        $handle = @fopen($file, "r");
        if ($handle) {
            while (!feof($handle)) {
                $buffer = fgets($handle, $length);
                if ($line == $i) {
                    $returnTxt = $buffer;
                }
                $i++;
            }
            fclose($handle);
        }
        return $returnTxt;
    }

    /**
     * 生成写入错误日志文件
     * 
     * @param $lasterror 错误数组
     * @return void
     */
    public static function WriteLog($lasterror)
    {
        // 清空文件信息
        clearstatcache();
        $str = PHP_EOL . "time:" . date("Y-m-d H:i:s");
        // 日志记录路径
        self::$LogDir = defined('LOG_DIR') ? LOG_DIR : dirname(PATH['FC']) . '/Log';
        // 循环取数据
        if (is_array($lasterror)) {
            foreach ($lasterror as $key => $value) {
                $str .= PHP_EOL . $key . '：' . $value;
                if ($key == 'type') {
                    $str .= PHP_EOL . 'explain' . '：' . self::ErrLevelMap($value);
                }
            }
        }

        // cli模式下的错误处理
        if (IS_CLI === true) {
            $cli = isset($_SERVER['argv'][0]) ? implode(' ', $_SERVER['argv']) : null;
            $str .= PHP_EOL . 'pattern：cli' . PHP_EOL . 'cli：' . $cli . PHP_EOL;
        } else {
            $str .= PHP_EOL . 'url：' . NOW_URL . PHP_EOL . 'ip：' . getIP() . PHP_EOL . 'os：' . getOS() . PHP_EOL;
        }

        // 检测目录，不存在或者没有权限就赋予一下
        if (!is_dir(self::$LogDir) || !is_writable(self::$LogDir)) {
            File::create(self::$LogDir);
        }

        $path = self::$LogDir . '/' . date("Ymd") . '.txt';
        $temp_path = self::$LogDir . '/temp.txt'; //临时记录
        $temp = '';
        // 读取临时错误文件
        if (is_file($temp_path)) {
            $temp = file_get_contents($temp_path);
        }
        // 创建文件
        if (!is_file($path)) {
            file_put_contents($path, $str, LOCK_EX);
        }
        // 相同的错误，判断不允许写入
        if ($temp == $lasterror['message']) {
            return false;
        }
        // 错误每10s写入一次,避免在大并发下,写的越来越大
        if (filemtime($path) < TIME - 10) {
            $file = fopen($path, 'a+b');
            fwrite($file, $str, 4096);
            fclose($file);
			file_put_contents($temp_path, $lasterror['message'], LOCK_EX);
        }
    }

    /**
     * 错误代码对照表,来自naples
     *
     * @param [type] $level
     * @return string
     */
    public static function ErrLevelMap($level)
    {
        $map = array(
            '1' => '运行时致命的错误',
            '2' => '运行时非致命的错误',
            '4' => '编译时语法解析错误',
            '8' => '运行时通知',
            '16' => 'PHP 初始化启动过程中发生的致命错误',
            '32' => 'PHP 初始化启动过程中发生的警告 ',
            '64' => '致命编译时错误',
            '128' => '编译时警告',
            '256' => '用户产生的错误信息',
            '512' => '用户产生的警告信息',
            '1024' => '用户产生的通知信息',
            '2048' => 'PHP 对代码的修改建议',
            '4096' => '可被捕捉的致命错误',
            '8192' => '运行时通知',
            '16384' => '用户产生的警告信息',
            '32767' => 'E_STRICT 触发的所有错误和警告信息'
        );
        return isset($map[$level]) ? $map[$level] : '未知错误';
    }
}
