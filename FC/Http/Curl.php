<?php

namespace FC\Http;

/**
 * 简单方便的curl类
 * @Author: lovefc 
 * @Date: 2019/03/20 14:07:11 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-09-30 08:05:30
 */

class Curl
{
    public $ua;
    public $ip;
    public $data;
    public $backurl;
    public $url;
    // 设置超时时间
    public $timeout;
    // 得到的cookies值
    public $getcookie = array();
    // 设置的cookies值
    public $setcookie;
    // 结果解析方式,json,xml,head返回结果都是数组
    public $mode = 'text';
    // header头数组存放
    public $headers = array();
    // session名称
    public $session_name = 'PHPSESSID';


    /**
     * header设置，可以是数组或者是一个值
     *
     * @param string $header
     * @return object
     */
    public function head($header = '')
    {
        if (empty($header)) {
            return $this;
        }
        //如果是数组的话
        if (is_array($header)) {
            foreach ($header as $k => $v) {
                $this->headers[] = "{$k}:{$v}";
            }
        } else {
            $this->headers[] = $header;
        }
        return $this;
    }

    /**
     * 定义一个ua
     *
     * @param string $ua
     * @return object
     */
    public function ua($ua = '')
    {
        $ua = $this->fixed_ua($ua);
        $this->ua = empty($ua) ? $this->rand_ua() : $ua;
        return $this;
    }

    /**
     * 定义cookies，可以是数组或者是a=b&c=d这样的格式
     *
     * @param string $cookie
     * @return object
     */
    public function cookie($cookie = '')
    {
        if (empty($cookie))
            return $this;
        if (is_array($cookie)) {
            foreach ($cookies as $name => $value) {
                $this->setcookie .= $name . '=' . $value . ';'; // 拼接一下cookies
            }
        } else {
            $this->setcookie = $cookie;
        }
        return $this;
    }

    /**
     * 定义一个IP,值为空就随机
     *
     * @param string $ip
     * @return object
     */
    public function ip($ip = '')
    {
        $this->ip = empty($ip) ? $this->rand_ip() : $ip;
        return $this;
    }

    /**
     * 定义超时时间,值为空就默认为5
     *
     * @param string $timeout
     * @return object
     */
    public function timeout($timeout = '')
    {
        $this->timeout = empty($timeout) ? 5 : (int) $timeout;
        return $this;
    }

    /**
     * 定义来源地址
     *
     * @param string $backurl
     * @return obiect
     */
    public function backurl($backurl = '')
    {
        if (!empty($backurl)) {
            $this->backurl = $backurl;
        }
        return $this;
    }

    /**
     * 定义访问的url
     *
     * @param string $url
     * @return object
     */
    public function url($url = '')
    {
        if (!empty($url)) {
            $this->url = $url;
        }
        return $this;
    }

    /**
     * 定义post的数据,数组或者字符串形式
     *
     * @param string $data
     * @return object
     */
    public function post($data = '')
    {
        if (!empty($data)) {
            $this->data = $data;
        }
        return $this;
    }

    /**
     * 返回值
     *
     * @param string $mode
     * @return void
     */
    public function results($mode = '')
    {
        switch ($mode) {
            case 'json':
                $this->mode = $mode;
                break;
            case 'xml':
                $this->mode = $mode;
                break;
            case 'head':
                $this->mode = $mode;
                break;
            default:
                $this->mode = 'text';
        }
        return $this->gets();
    }

    /**
     * 解析数据
     *
     * @param [type] $result
     * @return void
     */
    public function jx_result($result)
    {
        if (empty($result) && $result != 0) {
            return null;
        }
        switch ($this->mode) {
            case 'json':
                return json_decode($result, true);
                break;
            case 'xml':
                return (array) simplexml_load_string($result);
                break;
            default:
                return $result;
        }
    }

    /**
     * 获取cookies的值,是一个数组
     *
     * @return array
     */
    public function getCookie()
    {
        return $this->getcookie;
    }

    /**
     * 获取字符串形式的cookie
     *
     * @return string
     */
    public function getCookieStr()
    {
        $ck = $this->getcookie;
        // unset($ck[$this->session_name]);
        return http_build_query($ck);
    }

    /**
     * curl封装
     *
     * @return void
     */
    public function gets()
    {
        $url = $this->url;
        $backurl = $this->backurl;
        $ua = $this->ua;
        $ip = $this->ip;
        $data = $this->data;
        $headers = $this->headers;
        $ch = curl_init();
        $timeout = empty($this->timeout) ? 5 : $this->timeout;
        $SSL = substr($url, 0, 8) == "https://" ? true : false; //判断是不是https链接

        if (empty($url)) {
            return null;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($ua)) {
            curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        if (!empty($ip)) {
            $headers[] = 'X-FORWARDED-FOR:' . $ip;
            $headers[] = 'CLIENT-IP:' . $ip;
        }
        isset($_SESSION) || session_start();
        $headers[$this->session_name] = isset($_COOKIE[$this->session_name]) ? $_COOKIE[$this->session_name] : null;
        session_write_close();
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if (!empty($backurl)) {
            curl_setopt($ch, CURLOPT_REFERER, $backurl); //构造来路
        } else {
            curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        }
        if ($SSL === true) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // https请求 不验证证书和hosts
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (!empty($this->setcookie)) {
            curl_setopt($ch, CURLOPT_COOKIE, $this->setcookie);    //设置cookies值
        }
        if ($this->mode == 'head') {
            curl_setopt($ch, CURLOPT_HEADER, 1); //设置是否返回头信息
            //不要正文
            curl_setopt($ch, CURLOPT_NOBODY, true);
        } else {
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }
        if ($data) {
            curl_setopt($ch, CURLOPT_POST, 1);
            $data = is_array($data) ? http_build_query($data, null, '&') : $data;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); //设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 不直接输出	
        preg_match_all('/^Set-Cookie: (.*?);/m', curl_exec($ch), $m);
        if (isset($m[1])) {
            $cookies = '';
            foreach ($m[1] as $k => $v) {
                $cookies .= $v . '&';
            }
        }
        parse_str(trim($cookies, '&'), $cookarr);
        $this->getcookie = $cookarr;
        if ($this->mode == 'head') {
            $out = (array) curl_getinfo($ch);
        } else {
            $out = curl_multi_getcontent($ch);
        }
        curl_close($ch);
        return $this->jx_result($out);
    }

    /**
     * UA类型
     *
     * @param string $bs
     * @return string
     */
    public function fixed_ua($bs = 'windows')
    {
        $uas = array();
        $uas['android'] = 'Mozilla/5.0 (Linux; U; Android 7.1.1; zh-cn; MI 6 Build/NMF26X) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/53.0.2785.146 Mobile Safari/537.36 XiaoMi/MiuiBrowser/9.2.1';
        $uas['iphone'] = 'Mozilla/5.0 (iPhone 84; CPU iPhone OS 10_3_3 like Mac OS X) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.0 MQQBrowser/7.8.0 Mobile/14G60 Safari/8536.25 MttCustomUA/2 QBWebViewType/1 WKType/1';
        $uas['macos'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36';
        $uas['windows'] = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36';
        return isset($uas[$bs]) ? $uas[$bs] : $bs;
    }

    /**
     * 随机生成ua
     *
     * @return string
     */
    public function rand_ua()
    {
        $ua_long = array(
            'Mozilla/5.0 (Linux; U; Android 2.3.7; zh-cn; c8650 Build/GWK74) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.5 Mobile Safari/533.1s',
            'Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.2.149.27 Safari/525.13 ',
            'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.12) Gecko/20080219 Firefox/2.0.0.12 Navigator/9.0.0.6',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; 360SE)',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1) ;  QIHU 360EE)',
            'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1) ; Maxthon/3.0)',
            'Nokia5320/04.13 (SymbianOS/9.3; U; Series60/3.2 Mozilla/5.0; Profile/MIDP-2.1 Configuration/CLDC-1.1 ) AppleWebKit/413 (KHTML, like Gecko) Safari/413',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 5_1_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9B206 Safari/7534.48.3',
            'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)'
        );
        return $ua_long[array_rand($ua_long, 1)];
    }

    /**
     * 随机生成ip
     *
     * @return void
     */
    public function rand_ip()
    {
        $ip_long = array(
            array(
                '607649792',
                '608174079'
            ), //36.56.0.0-36.63.255.255
            array(
                '975044608',
                '977272831'
            ), //58.30.0.0-58.63.255.255
            array(
                '999751680',
                '999784447'
            ), //59.151.0.0-59.151.127.255
            array(
                '1019346944',
                '1019478015'
            ), //60.194.0.0-60.195.255.255
            array(
                '1038614528',
                '1039007743'
            ), //61.232.0.0-61.237.255.255
            array(
                '1783627776',
                '1784676351'
            ), //106.80.0.0-106.95.255.255
            array(
                '1947009024',
                '1947074559'
            ), //116.13.0.0-116.13.255.255
            array(
                '1987051520',
                '1988034559'
            ), //118.112.0.0-118.126.255.255
            array(
                '2035023872',
                '2035154943'
            ), //121.76.0.0-121.77.255.255
            array(
                '2078801920',
                '2079064063'
            ), //123.232.0.0-123.235.255.255
            array(
                '-1950089216',
                '-1948778497'
            ), //139.196.0.0-139.215.255.255
            array(
                '-1425539072',
                '-1425014785'
            ), //171.8.0.0-171.15.255.255
            array(
                '-1236271104',
                '-1235419137'
            ), //182.80.0.0-182.92.255.255
            array(
                '-770113536',
                '-768606209'
            ), //210.25.0.0-210.47.255.255
            array(
                '-569376768',
                '-564133889'
            ) //222.16.0.0-222.95.255.255
        );
        $rand_key = mt_rand(0, 9);
        $huoduan_ip = long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));

        return $huoduan_ip;
    }
}

/*

$ch  = new Curl();
$url = 'https://passport2-api.chaoxing.com/v11/loginregister';
$data = '&uname=15995762831&code=tzc808809';// 提交POST数据
// ip参数为空会进行随机，ua为空也会进行随机
$content = $ch->ua('widowns')->ip()->post($data)->url($url)->results('head');// 获取内容
print_r($ch->getCookie()); // 获取cookies，数组形式,只有设置results('head'),才会返回cookies
print_r($content);

*/
