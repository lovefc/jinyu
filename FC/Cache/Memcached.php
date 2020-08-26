<?php

namespace FC\Cache;

/*
 * @Author: Fwolf,lovefc
 * @Date: 2017/07/27 17:57
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-03 00:03:17
 */


class Memcached {

    //说实话，这一段常量我没怎么研究 -----by lovefc
    const OPT_COMPRESSION = -1001;
    const OPT_SERIALIZER = -1003;
    const SERIALIZER_PHP = 1;
    const SERIALIZER_IGBINARY = 2;
    const SERIALIZER_JSON = 3;
    const OPT_PREFIX_KEY = -1002;
    const OPT_HASH = 2;
    const HASH_DEFAULT = 0;
    const HASH_MD5 = 1;
    const HASH_CRC = 2;
    const HASH_FNV1_64 = 3;
    const HASH_FNV1A_64 = 4;
    const HASH_FNV1_32 = 5;
    const HASH_FNV1A_32 = 6;
    const HASH_HSIEH = 7;
    const HASH_MURMUR = 8;
    const OPT_DISTRIBUTION = 9;
    const DISTRIBUTION_MODULA = 0;
    const DISTRIBUTION_CONSISTENT = 1;
    const OPT_LIBKETAMA_COMPATIBLE = 16;    // MEMCACHED_BEHAVIOR_KETAMA_WEIGHTED
    const OPT_BUFFER_WRITES = 10;           // MEMCACHED_BEHAVIOR_BUFFER_REQUESTS
    const OPT_BINARY_PROTOCOL = 18;         // MEMCACHED_BEHAVIOR_BINARY_PROTOCOL
    const OPT_NO_BLOCK = 0;                 // MEMCACHED_BEHAVIOR_NO_BLOCK
    const OPT_TCP_NODELAY = 1;              // MEMCACHED_BEHAVIOR_TCP_NODELAY
    const OPT_SOCKET_SEND_SIZE = 4;         // MEMCACHED_BEHAVIOR_SOCKET_SEND_SIZE
    const OPT_SOCKET_RECV_SIZE = 5;         // MEMCACHED_BEHAVIOR_SOCKET_RECV_SIZE
    const OPT_CONNECT_TIMEOUT = 14;         // MEMCACHED_BEHAVIOR_CONNECT_TIMEOUT
    const OPT_RETRY_TIMEOUT = 15;           // MEMCACHED_BEHAVIOR_RETRY_TIMEOUT
    const OPT_SEND_TIMEOUT = 19;            // MEMCACHED_BEHAVIOR_SND_TIMEOUT
    const OPT_RECV_TIMEOUT = 20;            // MEMCACHED_BEHAVIOR_RCV_TIMEOUT
    const OPT_POLL_TIMEOUT = 8;             // MEMCACHED_BEHAVIOR_POLL_TIMEOUT
    const OPT_CACHE_LOOKUPS = 6;            // MEMCACHED_BEHAVIOR_CACHE_LOOKUPS
    const OPT_SERVER_FAILURE_LIMIT = 21;    // MEMCACHED_BEHAVIOR_SERVER_FAILURE_LIMIT
    const HAVE_IGBINARY = 1;
    const HAVE_JSON = 1;
    const GET_PRESERVE_ORDER = 1;
    const RES_SUCCESS = 0;                  // MEMCACHED_SUCCESS
    const RES_FAILURE = 1;                  // MEMCACHED_FAILURE
    const RES_HOST_LOOKUP_FAILURE = 2;      // MEMCACHED_HOST_LOOKUP_FAILURE
    const RES_UNKNOWN_READ_FAILURE = 7;     // MEMCACHED_UNKNOWN_READ_FAILURE
    const RES_PROTOCOL_ERROR = 8;           // MEMCACHED_PROTOCOL_ERROR
    const RES_CLIENT_ERROR = 9;             // MEMCACHED_CLIENT_ERROR
    const RES_SERVER_ERROR = 10;            // MEMCACHED_SERVER_ERROR
    const RES_WRITE_FAILURE = 5;            // MEMCACHED_WRITE_FAILURE
    const RES_DATA_EXISTS = 12;             // MEMCACHED_DATA_EXISTS
    const RES_NOTSTORED = 14;               // MEMCACHED_NOTSTORED
    const RES_NOTFOUND = 16;                // MEMCACHED_NOTFOUND
    const RES_PARTIAL_READ = 18;            // MEMCACHED_PARTIAL_READ
    const RES_SOME_ERRORS = 19;             // MEMCACHED_SOME_ERRORS
    const RES_NO_SERVERS = 20;              // MEMCACHED_NO_SERVERS
    const RES_END = 21;                     // MEMCACHED_END
    const RES_ERRNO = 26;                   // MEMCACHED_ERRNO
    const RES_BUFFERED = 32;                // MEMCACHED_BUFFERED
    const RES_TIMEOUT = 31;                 // MEMCACHED_TIMEOUT
    const RES_BAD_KEY_PROVIDED = 33;        // MEMCACHED_BAD_KEY_PROVIDED
    const RES_CONNECTION_SOCKET_CREATE_FAILURE = 11;    // MEMCACHED_CONNECTION_SOCKET_CREATE_FAILURE
    const RES_PAYLOAD_FAILURE = -1001;

    /**
     * 一些选项
     * @var array
     */
    protected $option = array(
        Memcached::OPT_COMPRESSION => true,
        Memcached::OPT_SERIALIZER => Memcached::SERIALIZER_PHP,
        Memcached::OPT_PREFIX_KEY => '',
        Memcached::OPT_HASH => Memcached::HASH_DEFAULT,
        Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_MODULA,
        Memcached::OPT_LIBKETAMA_COMPATIBLE => false,
        Memcached::OPT_BUFFER_WRITES => false,
        Memcached::OPT_BINARY_PROTOCOL => false,
        Memcached::OPT_NO_BLOCK => false,
        Memcached::OPT_TCP_NODELAY => false,
        // This two is a value by guess
        Memcached::OPT_SOCKET_SEND_SIZE => 32767,
        Memcached::OPT_SOCKET_RECV_SIZE => 65535,
        Memcached::OPT_CONNECT_TIMEOUT => 1000,
        Memcached::OPT_RETRY_TIMEOUT => 0,
        Memcached::OPT_SEND_TIMEOUT => 0,
        Memcached::OPT_RECV_TIMEOUT => 0,
        Memcached::OPT_POLL_TIMEOUT => 1000,
        Memcached::OPT_CACHE_LOOKUPS => false,
        Memcached::OPT_SERVER_FAILURE_LIMIT => 0,
    );
    protected $resultCode = 0;
    protected $resultMessage = '';
    protected $server = array();
    protected $socket = null;

    /**
     * 链接一台服务器
     * @param   string  $host
     * @param   int     $port
     * @param   int     $weight
     * @return  boolean
     */
    public function connect($host, $port = 11211, $weight = 0) {
        $key = $this->getServerKey($host, $port, $weight);
        if (isset($this->server[$key])) {
            // Dup
            $this->resultCode = Memcached::RES_FAILURE;
            $this->resultMessage = 'Server duplicate.';
            return false;
        } else {
            $this->server[$key] = array(
                'host' => $host,
                'port' => $port,
                'weight' => $weight,
            );
            $this->connects();
            return true;
        }
    }

    /**
     * 开始连接
     * @return  boolean
     */
    protected function connects() {
        $rs = false;
        foreach ((array) $this->server as $svr) {
            $error = 0;
            $errstr = '';
            $rs = @fsockopen($svr['host'], $svr['port'], $error, $errstr);
            if ($rs) {
                $this->socket = $rs;
            } else {
                $key = $this->getServerKey(
                        $svr['host'], $svr['port'], $svr['weight']
                );
                $s = "Connect to $key error:" . PHP_EOL .
                        "    [$error] $errstr";
                error_log($s);
            }
        }
        if (is_null($this->socket)) {
            $this->resultCode = Memcached::RES_FAILURE;
            $this->resultMessage = 'No server avaliable.';
            return false;
        } else {
            $this->resultCode = Memcached::RES_SUCCESS;
            $this->resultMessage = '';
            return true;
        }
    }

    /**
     * 删除一个key
     * @param   string  $key
     * @param   int     $time       Ignored
     * @return  boolean
     */
    public function del($key, $time = 0) {
        $keyString = $this->getKey($key);
        $this->writeSocket("delete $keyString");
        $s = $this->readSocket();
        if ('DELETED' == $s) {
            $this->resultCode = Memcached::RES_SUCCESS;
            $this->resultMessage = '';
            return true;
        } else {
            $this->resultCode = Memcached::RES_NOTFOUND;
            $this->resultMessage = 'Delete fail, key not exists.';
            return false;
        }
    }

    /**
     * 获取一个key
     * @param   string  $key
     * @param   callable    $cache_cb       Ignored
     * @param   float   $cas_token          Ignored
     * @return  mixed
     */
    public function get($key, $cache_cb = null, $cas_token = null) {
        $keyString = $this->getKey($key);
        $this->writeSocket("get $keyString");
        $s = $this->readSocket();
        if (is_null($s) || 'VALUE' != substr($s, 0, 5)) {
            $this->resultCode = Memcached::RES_FAILURE;
            $this->resultMessage = 'Get fail.';
            return false;
        } else {
            $s_result = '';
            $s = $this->readSocket();
            while ('END' != $s) {
                $s_result .= $s;
                $s = $this->readSocket();
            }
            $this->resultCode = Memcached::RES_SUCCESS;
            $this->resultMessage = '';
            return unserialize($s_result);
        }
    }

    /**
     * Get item key
     * @param   string  $key
     * @return  string
     */
    public function getKey($key) {
        return addslashes($this->option[Memcached::OPT_PREFIX_KEY]) . $key;
    }

    /**
     * Get a memcached option value
     * @param   int     $option
     * @return  mixed
     */
    public function getOption($option) {
        if (isset($this->option[$option])) {
            $this->resultCode = Memcached::RES_SUCCESS;
            $this->resultMessage = '';
            return $this->option[$option];
        } else {
            $this->resultCode = Memcached::RES_FAILURE;
            $this->resultMessage = 'Option not seted.';
            return false;
        }
    }

    /**
     * 返回上次操作的结果代码
     * @return  int
     */
    public function getResultCode() {
        return $this->resultCode;
    }

    /**
     * 返回最后的操作结果信息
     * @return  string
     */
    public function getResultMessage() {
        return $this->resultMessage;
    }

    /**
     * 拼接信息
     * @param   string  $host
     * @param   int     $port
     * @param   int     $weight
     * @return  string
     */
    protected function getServerKey($host, $port = 11211, $weight = 0) {
        return "$host:$port:$weight";
    }

    /**
     * 所有链接的列表
     * @see     $server
     * @return  array
     */
    public function getServerList() {
        return $this->server;
    }

    /**
     * 读取socket句柄
     * @return  string|null
     */
    protected function readSocket() {
        if (is_null($this->socket)) {
            return null;
        }
        return trim(fgets($this->socket));
    }

    /**
     * 设置一个key
     * 其实应该有四个参数的,扩展里就有四个参数 ----by lovefc
     * @param   string  $key
     * @param   mixed   $val
     * @param   int     $compress
     * @param   int     $expt
     * @return  boolean
     */
    public function set($key, $val, $compress = 0, $expt = 0) {
        $valueString = serialize($val);
        $keyString = $this->getKey($key);
        $this->writeSocket(
                "set $keyString $compress $expt " . strlen($valueString)
        );
        $s = $this->writeSocket($valueString, true);
        if ('STORED' == $s) {
            $this->resultCode = Memcached::RES_SUCCESS;
            $this->resultMessage = '';
            return true;
        } else {
            $this->resultCode = Memcached::RES_FAILURE;
            $this->resultMessage = 'Set fail.';
            return false;
        }
    }

    /**
     * 执行操作
     * @param   string  $cmd
     * @param   boolean $result     Need result/response
     * @return  mixed
     */
    protected function writeSocket($cmd, $result = false) {
        if (is_null($this->socket)) {
            return false;
        }
        fwrite($this->socket, $cmd . "\r\n");
        if (true == $result) {
            return $this->readSocket();
        }
        return true;
    }

}
