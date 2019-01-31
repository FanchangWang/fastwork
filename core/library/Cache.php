<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 13:09
 */

namespace Core;

use Traits\Pools;

/**
 * Class  Cache
 * @method  Cache init($server) 初始化，加入server
 * @method  Cache instance();
 * @method  Cache put(object $redis)
 * @method  Cache setDefer(bool $bool = true)
 *
 * //base
 * @method  Cache expire(string $key, int $ttl)
 * @method  Cache keys(string $key);
 *
 *
 * //string
 * @method  Cache get(string $key)
 * @method  Cache set(string $key, string $value, int $timeout = 0)
 * @method  Cache setex(string $key, int $ttl, string $value)
 * @method  Cache psetex(string $key, int $expire, string $value)
 * @method  Cache setnx(string $key, string $value)
 * @method  Cache del(string ... $key)
 * @method  Cache delete(string ... $key)
 * @method  Cache getSet(string $key, string $value)
 * @method  Cache exists(string $key)
 * @method  Cache incr(string $key)
 * @method  Cache incrBy(string $key, int $increment)
 * @method  Cache incrByFloat(string $key, float $increment)
 * @method  Cache decr(string $key)
 * @method  Cache decrBy(string $key, int $increment)
 * @method  Cache mget(array ... $keys)
 * @method  Cache append(string $key, string $value)
 * @method  Cache getRange(string $key, int $start, int $end)
 * @method  Cache setRange(string $key, int $offset, string $value)
 * @method  Cache strlen(string $key)
 * @method  Cache getBit(string $key, int $offset)
 * @method  Cache setBit(string $key, int $offset, bool $bool)
 * @method  Cache mset(array $keyValue)
 *
 * //list
 * @method  Cache lPush(string $key, string $value)
 * @method  Cache rPush(string $key, string $value)
 * @method  Cache lPushx(string $key, string $value)
 * @method  Cache rPushx(string $key, string $value)
 * @method  Cache lPop(string $key)
 * @method  Cache rPop(string $key)
 * @method  Cache blpop(array $keys, int $timeout)
 * @method  Cache brpop(array $keys, int $timeout)
 * @method  Cache lSize(string $key)
 * @method  Cache lGet(string $key, int $index)
 * @method  Cache lSet(string $key, int $index, string $value)
 * @method  Cache IRange(string $key, int $start, int $end)
 * @method  Cache lTrim(string $key, int $start, int $end)
 * @method  Cache lRem(string $key, string $value, int $count)
 * @method  Cache rpoplpush(string $srcKey, string $dstKey)
 * @method  Cache brpoplpush(string $srcKey, string $detKey, int $timeout)
 *
 * //set
 * @method  Cache sAdd(string $key, string $value)
 * @method  Cache sRem(string $key, string $value)
 * @method  Cache sMove(string $srcKey, string $dstKey, string $value)
 * @method  Cache sIsMember(string $key, string $value)
 * @method  Cache sCard(string $key)
 * @method  Cache sPop(string $key)
 * @method  Cache sRandMember(string $key)
 * @method  Cache sInter(string ... $keys)
 * @method  Cache sInterStore(string $dstKey, string ... $srcKey)
 * @method  Cache sUnion(string ... $keys)
 * @method  Cache sUnionStore(string $dstKey, string ... $srcKey)
 * @method  Cache sDiff(string ... $keys)
 * @method  Cache sDiffStore(string $dstKey, string ... $srcKey)
 * @method  Cache sMembers(string $key)
 *
 * //zset
 * @method  Cache zAdd(string $key, double $score, string $value)
 * @method  Cache zRange(string $key, int $start, int $end)
 * @method  Cache zDelete(string $key, string $value)
 * @method  Cache zRevRange(string $key, int $start, int $end)
 * @method  Cache zRangeByScore(string $key, int $start, int $end, array $options = [])
 * @method  Cache zCount(string $key, int $start, int $end)
 * @method  Cache zRemRangeByScore(string $key, int $start, int $end)
 * @method  Cache zRemRangeByRank(string $key, int $start, int $end)
 * @method  Cache zSize(string $key)
 * @method  Cache zScore(string $key, string $value)
 * @method  Cache zRank(string $key, string $value)
 * @method  Cache zRevRank(string $key, string $value)
 * @method  Cache zIncrBy(string $key, double $score, string $value)
 *
 * //hash
 * @method  Cache hSet(string $key, string $hashKey, string $value)
 * @method  Cache hSetNx(string $key, string $hashKey, string $value)
 * @method  Cache hGet(string $key, string $hashKey)
 * @method  Cache hLen(string $key)
 * @method  Cache hDel(string $key, string $hashKey)
 * @method  Cache hKeys(string $key)
 * @method  Cache hVals(string $key)
 * @method  Cache hGetAll(string $key)
 * @method  Cache hExists(string $key, string $hashKey)
 * @method  Cache hIncrBy(string $key, string $hashKey, int $value)
 * @method  Cache hIncrByFloat(string $key, string $hashKey, float $value)
 * @method  Cache hMset(string $key, array $keyValue)
 * @method  Cache hMGet(string $key, array $hashKeys)
 */
class Cache
{
    use Pools;
    protected $options = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'auth' => '',
    ];

    public function __construct($options = null)
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $this->make($this->options);
    }

    public static function __make(Config $config)
    {
        $request = new static($config->pull('cache'));
        return $request;
    }

    public function __call($method, $args)
    {
        $class = '\\Core\\cache\\CoRedisMap';
        return call_user_func_array([new $class, $method], $args);
    }


    protected function createPool()
    {
        //无空闲连接，创建新连接
        $server = new \Swoole\Coroutine\Redis([
            'connect_timeout' => $this->config['connect_timeout'],
            'timeout' => $this->config['timeout'],
            'serialize' => $this->config['serialize'],
            'reconnect' => $this->config['reconnect']
        ]);

        $server->connect($this->config['host'], $this->config['port']);

        if (!empty($this->config['auth'])) {
            $server->auth($this->config['auth']);
        }
    }

}