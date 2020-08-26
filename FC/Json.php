<?php

namespace FC;

/*
 * JSON
 *
 * @Author: lovefc 
 * @Date: 2019-09-17 10:25:58 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-08 10:34:15
 */

class Json
{
    /**
     * 生成JSON格式的正确消息
     * 
     * @param string $data 数据
     * @param string $msg 提示消息
     * @param array $append
     */
    public static function result($data, $code = 0, $msg = '', $append = array())
    {
        self::apiJsonResponse($data, $code, $msg, $append);
    }

    /**
     * 生成JSON格式的错误信息
     * 
     * @param string $error 错误代码
     * @param string $msg 提示消息
     */
    public static function error($code, $msg)
    {
        self::apiJsonResponse('', $code, $msg);
    }

    /**
     * 创建一个JSON格式的数据
     *
     * @access  public
     * @param   string $data
     * @param   integer $error
     * @param   string $msg
     * @return  void
     */
    private static function apiJsonResponse($data = '', $code = '200', $msg = '', $append = [])
    {

        $res = array('code' => $code, 'msg' => $msg, 'data' => $data);
        if (!empty($append)) {
            foreach ($append as $key => $val) {
                $res[$key] = $val;
            }
        }
        $val = json_encode($res);
        //Jquery + Zeptojs jsonp
        $back  = isset($_GET['jsoncallback']) ? strip_tags($_GET['jsoncallback']) : false;
        $back2 = isset($_GET['callback']) ? strip_tags($_GET['callback']) : false;
        // 定义一下head头
        head('json');
        if ($back) {
            $val = $back . '(' . $val . ')';
            exit($val);
        } elseif ($back2) {
            $val = $back2 . '(' . $val . ')';
            exit($val);
        }
        exit($val);
    }
}
