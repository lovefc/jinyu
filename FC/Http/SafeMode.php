<?php

namespace FC\Http;

use FC\File;

/*
 * 安全模块
 * 主要针对xss跨站攻击、sql注入等敏感字符串进行过滤
 * @Author: hkshadow
 * @Date: 2019-09-30 09:23:21 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-09-30 15:29:32
 */

class SafeMode
{

    /**
     * 执行过滤
     *
     * @param integer $status 开启或关闭
     * @param [type] $projectName // 文件路径
     * @return void
     */
    public function xss($status = 1, $projectName = null)
    {
        if (empty($status)) {
            return false;
        }
        //正则条件
        $referer = empty($_SERVER['HTTP_REFERER']) ? array() : array($_SERVER['HTTP_REFERER']);
        $getfilter = "'|<[^>]*?>|^\\+\/v(8|9)|\\b(and|or)\\b.+?(>|<|=|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
        $postfilter = "^\\+\/v(8|9)|\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|<\\s*img\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
        $cookiefilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";

        //遍历过滤
        if (is_array($_GET) && count($_GET) > 0) {
            foreach ($_GET as $key => $value) {
                $this->stopAttack($key, $value, $getfilter, $projectName);
            }
        }
        //遍历过滤
        if (is_array($_POST) && count($_POST) > 0) {
            foreach ($_POST as $key => $value) {
                $this->stopAttack($key, $value, $postfilter, $projectName);
            }
        }
        //遍历过滤
        if (is_array($_COOKIE) && count($_COOKIE) > 0) {
            foreach ($_COOKIE as $key => $value) {
                $this->stopAttack($key, $value, $cookiefilter, $projectName);
            }
        }
        //遍历过滤
        foreach ($referer as $key => $value) {
            $this->stopAttack($key, $value, $getfilter, $projectName);
        }
    }

    /**
     * 匹配敏感字符串，并处理
     * 
     * @param 参数key $strFiltKey
     * @param 参数value $strFiltValue
     * @param 正则条件 $arrFiltReq
     * @param 项目名 $joinName
     * @param 项目名/文件名/null $projectName
     * @return void
     */
    public function stopAttack($strFiltKey, $strFiltValue, $arrFiltReq, $projectName = NULL)
    {
        $strFiltValue = $this->arr_foreach($strFiltValue);
        //匹配参数值是否合法
        if (preg_match("/" . $arrFiltReq . "/is", $strFiltValue) == 1) {
            //记录ip
            $ip = "操作IP: " . \FC\Help::GetIP();
            //记录操作时间
            $time = " 操作时间: " . strftime("%Y-%m-%d %H:%M:%S");
            //记录详细页面带参数
            $thePage = " 操作页面: " . $this->request_uri();
            //记录提交方式
            $type = " 提交方式: " . $_SERVER["REQUEST_METHOD"];
            //记录提交参数
            $key = " 提交参数: " . $strFiltKey;
            //记录参数
            $value = " 提交数据: " . htmlspecialchars($strFiltValue);
            //写入日志
            $strWord = $ip . $time . $thePage . $type . $key . $value;
            //保存日志
            $this->sLog($strWord, $projectName);
            //过滤参数
            $_REQUEST[$strFiltKey] = '';
        }
    }

    /**
     * 获取当前url带具体参数
     * 
     * @return string
     */
    public function request_uri()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        } else {
            if (isset($_SERVER['argv'])) {
                $uri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['argv'][0];
            } else {
                $uri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
            }
        }
        return $uri;
    }


    /**
     * 写入日志
     * 
     * @param 日志内容 $strWord
     * @param 文件名 $fileName
     * @return void
     */
    public function sLog($strWord, $dirName = NULL)
    {
        if (!$dirName) {
            $dirName = getcwd() . '/XssLog';
        }else{

        }
        if (!is_dir($dirName)) {
            File::create($dirName);
        }
        $strDay = date('Y-m-d');
        $file = realpath($dirName) . '/XSS_' . $strDay . '.LOG';
        $Ts = fopen($file, "a+");
        fputs($Ts, $strWord . PHP_EOL);
        fclose($Ts);
    }

    /**
     * 递归数组
     * 
     * @param array $arr
     * @return unknown|string
     */
    public function arr_foreach($arr)
    {
        static $str = '';
        if (!is_array($arr)) {
            return $arr;
        }
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $this->arr_foreach($val);
            } else {
                $str[] = $val;
            }
        }
        return implode($str);
    }
}