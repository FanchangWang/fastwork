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
    protected $pushTime;
    //新建时间
    protected $addPoolTime = '';
    //池状态
    protected $available = true;

    public function __construct($config)
    {
        if (isset($config['clearAll'])) {
            if ($config['clearAll'] < $config['clearTime']) {
                $config['clearAll'] = (int)($config['clearTime'] / 1000);
            } else {
                $config['clearAll'] = (int)($config['clearAll'] / 1000);
            }
        }

        $this->config = array_merge($this->config, $config);
        $this->pool = new Channel($this->config['poolMax']);
    }

    public static function __make(Config $config)
    {
        return new static($config->pull('redis'));
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
     */
    public function pop()
    {
        $re_i = -1;
        back:
        $re_i++;
        if (!$this->available) {
            throw new RedisNotAvailableException('Redis连接池正在销毁');
            return false;
        }
        //有空闲连接且连接池处于可用状态
        if ($this->pool->length() > 0) {
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
            if ($re_i <= $this->config['poolMin']) {
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
        $server->tick($this->config['clearTime'], function () use ($server) {
            if ($this->pool->length() > $this->config['poolMin'] && time() - 5 > $this->addPoolTime) {
                $this->pool->pop();
            }
            if ($this->pool->length() > 0 && time() - $this->config['clearAll'] > $this->pushTime) {
                while (!$this->pool->isEmpty()) {
                    $this->pool->pop();
                }
            }
        });
    }

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

    public function setDefer($bool = true)
    {
        $this->config['setDefer'] = $bool;
        return $this;
    }

    public function __call($method, $args)
    {
        return $this->query($method, $args);
    }

    public function destruct()
    {
        // 连接池销毁, 置不可用状态, 防止新的客户端进入常驻连接池, 导致服务器无法平滑退出
        $this->available = false;
        while (!$this->pool->isEmpty()) {
            $this->pool->pop();
        }
    }
}