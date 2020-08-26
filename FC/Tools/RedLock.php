<?php

namespace FC\Tools;

/*
 * Redis 并发锁
 * @Author: lovefc
 * @Date: 2019-10-11 10:40:43 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-12 10:43:04
 */

class RedLock
{
    private $redis;
    private $lockKey;
    private $lockValue;

    /**
     * 构造
     *
     * @param array $config_name 配置名称
     */
    function __construct($config_name = 'redis')
    {
        $this->redis = \FC\obj('FC\Glue\Cache')->G($config_name);
    }

    /**
     * 加锁
     *
     * @param [type] $resource
     * @param [type] $ttl 锁住时长(秒)
     * @return integer
     */
    public function lock($lockKey, $ttl)
    {
        $this->lockKey = $lockKey;
        $this->lockValue  = uniqid();
        return $this->redis->set($lockKey, $this->lockValue, ['NX', 'EX' => $ttl]);
    }

    /**
     * 解锁
     *
     * @param array $lock
     * 
     * @return integer
     */
    public function unlock()
    {
        $script = "
            if redis.call('get',KEYS[1]) == ARGV[1] then
                return redis.call('del',KEYS[1])
            else
                return 0
            end
        ";
        $lockKey   = $this->lockKey;
        $lockValue = $this->lockValue;
        return $this->redis->eval($script, [$lockKey, $lockValue], 1);
    }

    /**
     * 限制访问次数
     *
     * @param [type] $key
     * @param integer $number
     * @param integer $time
     * @return integer
     */
    public function access($key, $number = 10, $time = 60)
    {
        $redis = $this->redis;
        $check = $redis->exists($key);
        if ($check) {
            $redis->incr($key);  //键值递增
            $count = $redis->get($key);
            if ($count > $number) {
                return 0;
            }
        } else {
            $redis->incr($key);
            $redis->expire($key, $time);
        }
        $count = $redis->get($key);
        return $count;
    }
}
