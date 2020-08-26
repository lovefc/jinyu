<?php

namespace FC\Cache;

/**
 * redis协议类
 * author:lovefc
 * time:2017/07/26
 * 只是封装了基本的操作，更多的操作可以去扩展
 * 更多命令可参考 http://www.redis.net.cn/order/.
 *
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-12 10:43:07
 */
class Redis
{
    private $connection;

    /**
     * 链接redis.
     *
     * @param $host 主机
     * @param $port 端口
     * @param $time 时间(单位为s)
     *
     * @return bool
     */
    public function connect($host, $port = 6379, $time = 0.1)
    {
        $connection = @fsockopen($host, $port, $errorN, $errorStr, $time);
        if (empty($connection)) {
            return false;
        }
        $this->connection = $connection;
        return true;
    }

    /*
     * 执行redis命令
     */
    public function command($command, $ar = array())
    {
        return $this->runCommand($this->mkCommand($command, $ar));
    }

    /**
     * 拼接处理命令.
     *
     * @param $command
     * @param $ar
     *
     * @return string
     */
    private function mkCommand($command, $ar = array())
    {
        $count = count($ar);
        for ($i = 0; $i < $count; ++$i) {
            $str = is_array($ar[$i]) ? implode(" ", $ar[$i]) : $ar[$i];
            $command .= ' "' . str_replace(array("\n", "\r"), array('\n', '\r'), $str) . '"';
        }
        return $command;
    }

    /**
     * 执行命令.
     *
     * @param $command
     *
     * @return array|bool|int|string
     */
    private function runCommand($command)
    {
        $handle = $this->connection;
        fwrite($handle, $command . "\r\n");
        $fl = fgets($handle); //fl:First Line
        if (!$fl) return 0;
        $re = false;
        switch ($fl[0]) {
            case '+':
                $re = trim(substr($fl, 1));
                $array = array('none', 'string', 'set', 'zset', 'hash', 'list');
                if (!in_array($re, $array)) {
                    $re = true;
                }
                break;
            case '-':
                throw new \Exception($fl);
                break;
            case ':':
                $re = (int) (substr($fl, 1, -2));
                break;
            case '$':
                $len = (int) (substr($fl, 1, -2)) + 2;
                $size = 0;
                while ($size < $len) {
                    $re .= fgets($handle);
                    $size = strlen($re);
                }
                $re = substr($re, 0, $len - 2);
                break;
            case '*':
                $re = array();
                $count = (int) (substr($fl, 1, -2));
                for ($i = 0; $i < $count; ++$i) {
                    $l = fgets($handle);
                    $len = (int) (substr($l, 1, -2));
                    $size = 0;
                    $str = '';
                    while ($size < $len) {
                        $size = strlen($str .= fgets($handle));
                    }
                    $str = substr($str, 0, $len);
                    $re[] = $str;
                }
                break;
        }

        return $re;
    }

    /**
     * 使用客户端向 Redis 服务器发送一个 PING.
     *
     * @return integer |' PONG'
     */
    public function ping()
    {
        return $this->command('PING');
    }

    /**
     * 关闭客户端
     *
     * @return integer |'OK'
     */
    public function quit()
    {
        return $this->command('QUIT');
    }

    /**
     * 切换到指定的数据库，数据库索引号 index 用数字值指定，以 0 作为起始索引值.
     *
     * @param $index  
     *
     * @return integer |'OK'
     */
    public function select($index)
    {
        return $this->command('SELECT', array(
            $index,
        ));
    }

    /**
     * 打印字符串.
     *
     * @param $message
     *
     * @return string
     */
    public function echo($message)
    {
        return $this->command('ECHO', array(
            $message,
        ));
    }

    /**
     * 检测给定的密码和配置文件中的密码是否相符.
     *
     * @param $password   
     *
     * @return integer
     */
    public function auth($password)
    {
        return $this->command('AUTH', array(
            $password,
        ));
    }

    /**
     * 设置一个key.
     *
     * @param $key    键名
     * @param $value  键值
     * @param $array  注意这个值，可能是值也可能是过期时间
     *
     * @return integer
     */
    public function set($key, $value, $array = false)
    {
        if (!empty($array) && is_array($array)) {
            $array = array_change_key_case($array);
            $func   = isset($array[0]) ? $array[0] : '';
            $ex = isset($array['px']) ? $array['px'] / 1000 : 0;
            if ($ex === 0) {
                $ex = isset($array['ex']) ? $array['ex'] : 1;
            }
            if ($func == 'NX' && $func == 'nx') {
                if ($this->exists($key)) {
                    return 1;
                }
                $ok = $this->set($key, $value);
                if ($ok) {
                    $this->expire($key, $ex);
                }
                return 0;
            }
            return;
        }
        $status = $this->command('SET', array(
            $key,
            $value,
        ));
        if (is_numeric($array)) {
            $this->expire($key, $array);
        }
        return  $status;
    }

    /**
     * 命令为指定的 key 设置值及其过期时间.
     *
     * @param $key    键名
     * @param $expire 过期时间
     * @param $value  键值
     *
     * @return integer
     */
    public function setex($key, $expire, $value)
    {
        return $this->command('SETEX', array(
            $key,
            $expire,
            $value,
        ));
    }

    /**
     * 设置key的过期时间.
     *
     * @param $key
     * @param $expire 过期时间（秒）
     *
     * @return integer
     */
    public function expire($key, $expire = 60)
    {
        if (0 == $this->exists($key)) {
            return 0;
        }
        return  $this->command('Expire', array(
            $key,
            (int) $expire,
        ));
    }

    /**
     * 设置key的过期时间 (时间戳).
     *
     * @param $key
     * @param $timestamp UNIX 时间戳
     *
     * @return integer
     */
    public function expireat($key, $timestamp)
    {
        if (1 == $this->has($key)) {
            return 0;
        }
        return  $this->command('Expireat', array(
            $key,
            $timestamp,
        ));
    }

    /**
     * 随机返回一个key.
     *
     * @return string
     */
    public function randomkey()
    {
        return $this->runCommand('RANDOMKEY');
    }

    /**
     * 移除key的过期时间
     *
     * @param $key
     *
     * @return integer
     */
    public function persist($key)
    {
        return  $this->command('PERSIST', array(
            $key
        ));
    }

    /**
     * 搜索key的值
     *
     * @param $pattern
     *
     * @return array
     */
    public function keys($pattern = false)
    {
        if (empty($pattern)) {
            $pattern = '*';
        }
        return  $this->command('KEYS', array(
            $pattern
        ));
    }

    /**
     * 重命名 key (会覆盖原来的值)
     *
     * @param $old_key
     * @param $new_key
     *
     * @return string
     */
    public function rename($old_key, $new_key)
    {
        if (false == $this->has($old_key)) {
            return 'Not old key';
        }
        if (empty($new_key)) {
            return 'Not new key name';
        }
        return  $this->command('RENAME', array(
            $old_key,
            $new_key
        ));
    }

    /**
     * 重命名 key (不会覆盖原来的值)
     * 修改成功时，返回 1 。 如果 NEW_KEY_NAME 已经存在，返回 0
     *
     * @param $old_key
     * @param $new_key
     *
     * @return string
     */
    public function renamenx($old_key, $new_key)
    {
        if (0 == $this->exists($old_key)) {
            return 'Not old key';
        }
        if (empty($new_key)) {
            return 'Not new key name';
        }
        return  $this->command('RENAMENX', array(
            $old_key,
            $new_key
        ));
    }

    /**
     * 获取一个key.
     *
     * @param $key
     *
     * @return array|bool|int|string
     */
    public function get($key)
    {
        if (0 == $this->exists($key)) {
            return false;
        }
        return $this->command('GET', array(
            $key,
        ));
    }

    /**
     * 当前数据库的 key 的数量
     * 
     * @return integer
     */
    public function dbsize()
    {
        return $this->command('DBSIZE');
    }

    /**
     * 获取服务器时间
     * 
     * @return array 第一个键值是当前时间(UNIX时间戳)，第二个是当前这一秒钟已经逝去的微秒数
     */
    public function time()
    {
        return $this->command('TIME');
    }

    /**
     * 判断key的类型.
     *
     * @param $key
     *
     * @return none|string|set|zset|hash|list
     */
    public function type($key)
    {
        $r = $this->command('TYPE', array(
            $key,
        ));
        return $r;
    }

    /**
     * 返回 key 的剩余过期时间(毫秒).
     * 当 key 不存在时，返回 -2 。
     * 当 key 存在但没有设置剩余生存时间时，返回 -1 。 否则，以毫秒为单位，返回 key 的剩余生存时间
     * 
     * @param $key
     *
     * @return array|bool|int|string
     */
    public function pttl($key)
    {
        return $this->command('PTTL', array(
            $key,
        ));
    }

    /**
     * 返回 key 的剩余过期时间.
     * 当 key 不存在时，返回 -2 。
     * 当 key 存在但没有设置剩余生存时间时，返回 -1 。 否则，以秒为单位，返回 key 的剩余生存时间
     * 
     * @param $key
     *
     * @return array|bool|int|string
     */
    public function ttl($key)
    {
        return $this->command('TTL', array(
            $key,
        ));
    }

    /**
     * 为指定的 key 追加值.
     * 如果 key 已经存在并且是一个字符串， APPEND 命令将 value 追加到 key 原来的值的末尾。
     * 如果 key 不存在， APPEND 就简单地将给定 key 设为 value ，就像执行 SET key value 一样。
     * 
     * @param $key
     *
     * @return integer key 中字符串的长度
     */
    public function append($key)
    {
        return $this->command('APPEND', array(
            $key,
        ));
    }

    /**
     * 开始事务
     *
     * @return integer
     */
    public function multi()
    {
        return $this->command('MULTI');
    }

    /**
     * 监视一个(或多个) key ，如果在事务执行之前这个(或这些) key 被其他命令所改动，那么事务将被打断
     * 
     * @param $keys
     *
     * @return integer | 'OK'
     */
    public function watch($keys)
    {
        return $this->command('WATCH', array(
            $keys
        ));
    }

    /**
     * 放弃所有的监听
     *
     * @return integer | 'OK'
     */
    public function unwatch()
    {
        return $this->command('UNWATCH');
    }

    /**
     * 放弃事务
     *
     * @return integer
     */
    public function discard()
    {
        return $this->command('DISCARD');
    }

    /**
     * 执行事务内的命令
     * 事务块内所有命令的返回值，按命令执行的先后顺序排列。 当操作被打断时，返回空值 nil
     * 
     * @return string  
     */
    public function exec()
    {
        return $this->command('EXEC');
    }

    /**
     * 判断一个key是否存在或者过期(如果有第二个参数就是判断哈希表中的字段了).
     *
     * @param $key
     *
     * @return integer
     */
    public function has($key, $field = null)
    {
        if (empty($field)) {
            return $this->command('EXISTS', array(
                $key,
            ));
        } else {
            return $this->command('HEXISTS', array(
                $key,
                $field,
            ));
        }
    }


    /**
     * 判断一个key是否存在或者过期
     *
     * @param $key
     *
     * @return integer
     */
    public function exists($key)
    {
        return $this->command('EXISTS', array(
            $key,
        ));
    }

    /**
     * 删除一个key(如果有第二个参数就是删除哈希表中的字段了).
     *
     * @param $key
     *
     * @return integer 返回被删除的数量
     */
    public function del($key, $field)
    {
        if (empty($field)) {
            return $this->command('DEL', array(
                $key,
            ));
        } else {
            return $this->command('HDEL', array(
                $key,
                $field,
            ));
        }
    }

    /**
     * 删除所有的key.
     *
     * @return void
     */
    public function flushall()
    {
        return $this->runCommand('FLUSHALL');
    }

    /**
     * 返回最近一次 Redis 成功将数据保存到磁盘上的时间，以 UNIX 时间戳格式表示
     *
     * @return integer
     */
    public function lastsave()
    {
        return $this->runCommand('LASTSAVE');
    }

    /**
     * 保存数据到磁盘
     *
     * @return string
     */
    public function save()
    {
        return $this->runCommand('SAVE');
    }

    /**
     * 异步保存数据到磁盘
     *
     * @return string
     */
    public function bgsave()
    {
        return $this->runCommand('BGSAVE');
    }

    /**
     * 将哈希表 key 中的字段 field 的值设为 value.
     *
     * @param $key
     * @param $field
     * @param $value
     *
     * @return array|bool|int|string
     */
    public function hset($key, $field, $value)
    {
        return $this->command(
            'HSET',
            array(
                $key,
                $field,
                $value,
            )
        );
    }

    /**
     * 获取在哈希表中指定 key 的所有字段和值
     *
     * @param $key
     *
     * @return array
     */
    public function hgetall($key)
    {
        $re = $this->command(
            'HGETALL',
            array(
                $key,
            )
        );
        $return = array();
        $count = count($re) / 2;
        for ($i = 0; $i < $count; ++$i) {
            $return[$re[$i * 2]] = $re[$i * 2 + 1];
        }
        return $return;
    }

    /**
     * 获取存储在哈希表中指定字段的值
     *
     * @param $key
     * @param $field
     *
     * @return array|bool|int|string
     */
    public function hget($key, $field)
    {
        return $this->command(
            'HGET',
            array(
                $key,
                $field,
            )
        );
    }

    /**
     * 获取哈希表中所有键名
     *
     * @param $key
     * @param $field
     * 
     * @return integer
     */
    public function hkeys($key, $field, $value)
    {
        return $this->command(
            'HKEYS',
            array(
                $key,
                $field,
                $value
            )
        );
    }

    /**
     * 获取哈希表中所有值
     *
     * @param $key
     * 
     * @return array|bool|int|string
     */
    public function hvals($key)
    {
        return $this->command(
            'HVALS',
            array(
                $key,
            )
        );
    }

    /**
     * 查看哈希表的指定字段是否存在
     *
     * @param $key
     * @param $field
     * 
     * @param integer
     */
    public function hexists($key, $field)
    {
        return $this->command(
            'HEXISTS',
            array(
                $key,
                $field,
            )
        );
    }

    /**
     * 删除哈希表 key 中的一个或多个指定字段
     *
     * @param $key
     * @param $field
     * 
     * @param integer
     */
    public function hdel($key, $field)
    {
        return $this->command(
            'HDEL',
            array(
                $key,
                $field,
            )
        );
    }

    /**
     * 获取哈希表中字段的数量
     *
     * @param $key
     * 
     * @param integer
     */
    public function hlen($key)
    {
        return $this->command(
            'HLEN',
            array(
                $key,
            )
        );
    }

    /**
     * redis信息.
     *
     * @return string
     */
    public function info()
    {
        return $this->runCommand('INFO');
    }

    /**
     * 获取版本.
     *
     * @return integer
     */
    public function ver()
    {
        $info = $this->info();
        preg_match_all('/redis_version:([0-9\.]+)/', $info, $matches);
        return $matches[1][0];
    }

    /**
     * redis的运行天数
     *
     * @return integer
     */
    public function days()
    {
        $info = $this->info();
        preg_match_all('/uptime_in_days:([0-9\.]+)/', $info, $matches);
        return $matches[1][0];
    }

    /**
     * 获取存储在哈希表中指定字段的值的长度.
     *
     * @param $key
     * @param $field
     *
     * @return integer
     */
    public function hstrlen($key, $field)
    {
        return $this->command(
            'HSTRLEN',
            array(
                $key,
                $field,
            )
        );
    }

    /**
     * 为哈希表中的字段值加上指定增量值.
     *
     * @param $key
     * @param $field
     * @param $value
     *
     * @return integer
     */
    public function hincrby($key, $field, $value)
    {
        return $this->command(
            'HINCRBY',
            array(
                $key,
                $field,
                $value,
            )
        );
    }


    /**
     * 将 key 中储存的数字值增一
     *
     * @param $key
     *
     * @return integer
     */
    public function incr($key)
    {
        return $this->command(
            'INCR',
            array(
                $key,
            )
        );
    }

    /**
     * 将 key 中储存的数字值减一
     *
     * @param $key
     *
     * @return integer
     */
    public function decr($key)
    {
        return $this->command(
            'DECR',
            array(
                $key,
            )
        );
    }

    /**
     * 返回key存储的value的长度.
     *
     * @param $key
     *
     * @return integer
     */
    public function strlen($key)
    {
        return $this->command(
            'STRLEN',
            array(
                $key,
            )
        );
    }

    /**
     * 指定的 key 不存在时，为 key 设置指定的值
     *
     * @param $key
     * @param $value
     *
     * @return 0|1
     */
    public function setnx($key, $value)
    {
        return $this->command('SETNX', array(
            $key,
            $value,
        ));
    }

    /**
     * 给key增加相应的数量.
     *
     * @param $key
     * @param $value
     *
     * @return integer
     */
    public function incrby($key, $value)
    {
        return $this->command(
            'INCRBY',
            array(
                $key,
                $value,
            )
        );
    }

    /**
     * 给key减去相应的数量.
     *
     * @param $key
     * @param $value
     *
     * @return integer
     */
    public function decrby($key, $value)
    {
        return $this->command(
            'DECRBY',
            array(
                $key,
                $value,
            )
        );
    }

    /**
     * 使用 Lua 解释器执行脚本.
     *
     * @param [type] $script
     * @param [type] $keys
     * @param [type] $numkeys
     *
     * @return integer
     */
    public function eval($script, $keys, $numkeys, $args = false)
    {
        return $this->command(
            'EVAL',
            array(
                $script,
                $numkeys,
                $keys,
                $args
            )
        );
    }

    /**
     * 返回列表的长度
     *
     * @param $key
     * 
     * @return integer
     */
    public function llen($key)
    {
        if($this->type($key)!='list'){
             return 0;
        }
        return $this->command(
            'LLEN',
            array(
                $key,
            )
        );
    }

    /**
     * 移除并返回列表的第一个元素
     *
     * @param $key
     * 
     * @return string
     */
    public function lpop($key)
    {
        if ($this->type($key) != 'list') {
            return 0;
        }
        $r = $this->command(
            'LPOP',
            array(
                $key,
            )
        );
        return $r;
    }

    /**
     * 移除并返回列表的最后一个元素
     *
     * @param $key
     * 
     * @return string
     */
    public function rpop($key)
    {
        if ($this->type($key) != 'list') {
            return 0;
        }
        return $this->command(
            'RPOP',
            array(
                $key,
            )
        );
    }

    /**
     * 将一个或多个值插入到列表头部。 如果 key 不存在，一个空列表会被创建并执行 LPUSH 操作
     *
     * @param $key
     * @param $array
     * 
     * @return integer
     */
    public function lpush($key, $array)
    {
        return $this->command(
            'LPUSH',
            array(
                $key,
                $array
            )
        );
    }

    /**
     * 将一个或多个值插入到列表尾部。 如果 key 不存在，一个空列表会被创建并执行 LPUSH 操作
     *
     * @param $key
     * @param $array
     * 
     * @return integer
     */
    public function rpush($key, $array)
    {
        return $this->command(
            'RPUSH',
            array(
                $key,
                $array
            )
        );
    }

    /**
     * 将一个值插入到已存在的列表头部，列表不存在时操作无效
     *
     * @param $key
     * @param $array
     * 
     * @return integer
     */
    public function lpushx($key, $array)
    {
        if ($this->type($key) != 'list') {
            return 0;
        }
        return $this->command(
            'LPUSHX',
            array(
                $key,
                $array
            )
        );
    }

    /**
     * 将一个值插入到已存在的列表尾部，列表不存在时操作无效
     *
     * @param $key
     * @param $array
     * 
     * @return integer
     */
    public function rpushx($key, $array)
    {
        if ($this->type($key) != 'list') {
            return 0;
        }
        return $this->command(
            'RPUSHX',
            array(
                $key,
                $array
            )
        );
    }

    /**
     * 返回列表中指定区间内的元素，区间以偏移量 START 和 END 指定
     *
     * @param $key
     * @param $start 0代表第一个元素
     * @param $end 
     * 
     * @return array
     */
    public function lrange($key, $start, $end)
    {
        if ($this->type($key) != 'list') {
            return 0;
        }
        return $this->command(
            'LRANGE',
            array(
                $key,
                $start,
                $end
            )
        );
    }

    /**
     * 根据参数 COUNT 的值，移除列表中与参数 VALUE 相等的元素
     *
     * @param $key
     * @param $count 0> 从表头开始向表尾搜索，<0 从表尾开始向表头搜索 =0 移除表中所有与 VALUE 相等的值
     * @param $value 要移除的值
     * 
     * @return array
     */
    public function lrem($key, $count, $value)
    {
        if ($this->type($key) != 'list') {
            return 0;
        }
        return $this->command(
            'LREM',
            array(
                $key,
                $count,
                $value
            )
        );
    }

    /**
     * 通过索引来设置元素的值
     *
     * @param $key
     * @param $index 索引值
     * @param $value 值
     * 
     * @return array
     */
    public function lset($key, $index, $value)
    {
        return $this->command(
            'LSET',
            array(
                $key,
                $index,
                $value
            )
        );
    }

    /**
     * 通过索引获取列表中的元素
     *
     * @param $key
     * @param $index 索引值
     * 
     * @return array
     */
    public function lindex($key, $index)
    {
        if ($this->type($key) != 'list') {
            return 0;
        }
        return $this->command(
            'LINDEX',
            array(
                $key,
                $index
            )
        );
    }
}
