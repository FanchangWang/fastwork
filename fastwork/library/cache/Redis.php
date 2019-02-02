<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/2
 * Time: 10:28
 */

namespace fastwork\cache;


use fastwork\Config;
use fastwork\exception\RedisNotAvailableException;
use fastwork\facades\Log;
use Swoole\Coroutine\Channel;

/**
 * Class Redis
 * @see \Redis
 * //base
 * @method \Redis expire(string $key, int $ttl)
 * @method \Redis keys(string $key);
 * //string
 * @method \Redis get(string $key)
 * @method \Redis set(string $key, string $value, int $timeout = 0)
 * @method \Redis setex(string $key, int $ttl, string $value)
 * @method \Redis psetex(string $key, int $expire, string $value)
 * @method \Redis setnx(string $key, string $value)
 * @method \Redis del(string ... $key)
 * @method \Redis delete(string ... $key)
 * @method \Redis getSet(string $key, string $value)
 * @method \Redis exists(string $key)
 * @method \Redis incr(string $key)
 * @method \Redis incrBy(string $key, int $increment)
 * @method \Redis incrByFloat(string $key, float $increment)
 * @method \Redis decr(string $key)
 * @method \Redis decrBy(string $key, int $increment)
 * @method \Redis mget(array ... $keys)
 * @method \Redis append(string $key, string $value)
 * @method \Redis getRange(string $key, int $start, int $end)
 * @method \Redis setRange(string $key, int $offset, string $value)
 * @method \Redis strlen(string $key)
 * @method \Redis getBit(string $key, int $offset)
 * @method \Redis setBit(string $key, int $offset, bool $bool)
 * @method \Redis mset(array $keyValue)
 * //list
 * @method \Redis lPush(string $key, string $value)
 * @method \Redis rPush(string $key, string $value)
 * @method \Redis lPushx(string $key, string $value)
 * @method \Redis rPushx(string $key, string $value)
 * @method \Redis lPop(string $key)
 * @method \Redis rPop(string $key)
 * @method \Redis blpop(array $keys, int $timeout)
 * @method \Redis brpop(array $keys, int $timeout)
 * @method \Redis lSize(string $key)
 * @method \Redis lGet(string $key, int $index)
 * @method \Redis lSet(string $key, int $index, string $value)
 * @method \Redis IRange(string $key, int $start, int $end)
 * @method \Redis lTrim(string $key, int $start, int $end)
 * @method \Redis lRem(string $key, string $value, int $count)
 * @method \Redis rpoplpush(string $srcKey, string $dstKey)
 * @method \Redis brpoplpush(string $srcKey, string $detKey, int $timeout)
 * //set
 * @method \Redis sAdd(string $key, string $value)
 * @method \Redis sRem(string $key, string $value)
 * @method \Redis sMove(string $srcKey, string $dstKey, string $value)
 * @method \Redis sIsMember(string $key, string $value)
 * @method \Redis sCard(string $key)
 * @method \Redis sPop(string $key)
 * @method \Redis sRandMember(string $key)
 * @method \Redis sInter(string ... $keys)
 * @method \Redis sInterStore(string $dstKey, string ... $srcKey)
 * @method \Redis sUnion(string ... $keys)
 * @method \Redis sUnionStore(string $dstKey, string ... $srcKey)
 * @method \Redis sDiff(string ... $keys)
 * @method \Redis sDiffStore(string $dstKey, string ... $srcKey)
 * @method \Redis sMembers(string $key)
 *
 * //zset SortedSet（有序集合）
 * @method \Redis zAdd(string $key, double $score, string $value)
 * @method \Redis zRange(string $key, int $start, int $end)
 * @method \Redis zDelete(string $key, string $value)
 * @method \Redis zRevRange(string $key, int $start, int $end)
 * @method \Redis zRangeByScore(string $key, int $start, int $end, array $options = [])
 * @method \Redis zCount(string $key, int $start, int $end)
 * @method \Redis zRemRangeByScore(string $key, int $start, int $end)
 * @method \Redis zRemRangeByRank(string $key, int $start, int $end)
 * @method \Redis zSize(string $key)
 * @method \Redis zScore(string $key, string $value)
 * @method \Redis zRank(string $key, string $value)
 * @method \Redis zRevRank(string $key, string $value)
 * @method \Redis zIncrBy(string $key, double $score, string $value)
 * //hash
 * @method \Redis hSet(string $key, string $hashKey, string $value)
 * @method \Redis hSetNx(string $key, string $hashKey, string $value)
 * @method \Redis hGet(string $key, string $hashKey)
 * @method \Redis hLen(string $key)
 * @method \Redis hDel(string $key, string $hashKey)
 * @method \Redis hKeys(string $key)
 * @method \Redis hVals(string $key)
 * @method \Redis hGetAll(string $key)
 * @method \Redis hExists(string $key, string $hashKey)
 * @method \Redis hIncrBy(string $key, string $hashKey, int $value)
 * @method \Redis hIncrByFloat(string $key, string $hashKey, float $value)
 * @method \Redis hMset(string $key, array $keyValue)
 * @method \Redis hMGet(string $key, array $hashKeys)
 *
 */
class Redis
{
    /**
     * @var Channel
     */
    protected $pool;
    //配置
    public $config = [
        //服务器地址
        'host' => '127.0.0.1',
        //端口
        'port' => 6379,
        //密码
        'auth' => '',
        //空闲时，保存的最大链接，默认为5
        'poolMin' => 5,
        //地址池最大连接数，默认1000
        'poolMax' => 1000,
        //清除空闲链接的定时器，默认60s
        'clearTime' => 60000,
        //空闲多久清空所有连接,默认300s
        'clearAll' => 300,
        //设置是否返回结果
        'setDefer' => true,
        //options配置
        'connect_timeout' => 1, //连接超时时间，默认为1s
        'timeout' => 1, //超时时间，默认为1s
        'serialize' => false, //自动序列化，默认false
        'reconnect' => 1  //自动连接尝试次数，默认为1次
    ];
    /**
     * 入池时间
     * @var
     */
    protected $pushTime = '';
    //新建时间
    protected $addPoolTime = '';
    //池状态
    protected $available = true;

    public function __construct($config)
    {
        if ($config['clearAll'] < $config['clearTime']) {
            $config['clearAll'] = $config['clearTime'];
        }
        $this->config = array_merge($this->config, $config);
        $this->pool = new Channel($this->config['poolMax']);
        /**
         * 创建最小的连接池
         */
        if ($this->addPoolTime == '') {
            $this->initMinPool();
        }
    }

    public static function __make(Config $config)
    {
        $redisConfig = $config->get('cache.redis');
        return new static($redisConfig);
    }

    /**
     * @入池
     *
     * @param $redis
     */
    public function push($redis)
    {
        //未超出池最大值时
        if ($this->pool->length() < $this->config['poolMax']) {
            $this->pool->push($redis);
        }
        $this->pushTime = time();
    }

    /**
     * @出池
     * @param null $create
     * @return bool|mixed|\Swoole\Coroutine\Redis
     */
    public function pop($create = null)
    {
        $re_i = -1;
        back:
        $re_i++;
        if (!$this->available) {
            throw new RedisNotAvailableException('Redis连接池正在销毁');
            return false;
        }
        //有空闲连接且连接池处于可用状态
        if ($this->pool->length() > 0 && $create == null) {
            $redis = $this->pool->pop();
        } else {
            //无空闲连接，创建新连接
            $redis = new \Swoole\Coroutine\Redis([
                'connect_timeout' => $this->config['connect_timeout'],
                'timeout' => $this->config['timeout'],
                'serialize' => $this->config['serialize'],
                'reconnect' => $this->config['reconnect']
            ]);
            $redis->connect($this->config['host'], $this->config['port']);

            if (!empty($this->config['auth'])) {
                $redis->auth($this->config['auth']);
            }
            $this->addPoolTime = time();
        }

        if ($redis->connected === true && $redis->errCode === 0) {
            return $redis;
        } else {
            if ($re_i <= $this->config['reconnect']) {
                Log::alert("重连次数{$re_i}，[errCode：{$redis->errCode}，errMsg：{$redis->errMsg}]");
                $redis->close();
                unset($redis);
                goto back;
            }
        }
    }

    /**
     * @定时器
     *
     * @param $server
     */
    public function clearTimer(\swoole_server $server)
    {

        $server->tick($this->config['clearTime'] * 1000, function () use ($server) {
            if ($this->pool->length() > $this->config['poolMin'] && time() - 10 > $this->addPoolTime) {
                $this->pool->pop();
            }
            if ($this->pool->length() > $this->config['poolMin'] && time() - $this->config['clearAll'] > $this->pushTime) {
                while (!$this->pool->isEmpty()) {
                    if ($this->pool->length() <= $this->config['poolMin']) {
                        break;
                    }
                    $this->pool->pop();
                }
            }
        });
    }

    /**
     * 获取一个连接池
     * @return mixed
     */
    public function getPool()
    {
        return $this->pool->pop();
    }


    public function setDefer($bool = true)
    {
        $this->config['setDefer'] = $bool;
        return $this;
    }

    /**
     * 销毁所有的redis连接池
     */
    public function destruct()
    {
        // 连接池销毁, 置不可用状态, 防止新的客户端进入常驻连接池, 导致服务器无法平滑退出
        $this->available = false;
        while (!$this->pool->isEmpty()) {
            $this->pool->pop();
        }
    }

    protected function initMinPool()
    {
        for ($i = 1; $i <= $this->config['poolMin']; $i++) {
            $this->pool->push($this->pop(true));
        }
    }

    /**
     * 协程版本redis总调用
     * @param $method
     * @param $args
     * @return mixed
     */
    protected function query($method, $args)
    {
        $chan = new \chan(1);

        go(function () use ($chan, $method, $args) {
            $redis = $this->pop();
            $rs = call_user_func_array([$redis, $method], $args);
            $this->push($redis);
            if ($this->config['setDefer']) {
                $chan->push($rs);
            }
        });

        if ($this->config['setDefer']) {
            return $chan->pop();
        }
    }

    public function __call($method, $args)
    {
        return $this->query($method, $args);
    }
}