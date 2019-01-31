<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/31
 * Time: 15:14
 */

namespace Core\cache;


use Core\Cache;

class CoRedisMap
{
    /**
     * @var Cache
     */
    protected $RedisPool;
    protected $options = [
        'setDefer' => true
    ];

    public function init(Cache $RedisPool)
    {
        $this->RedisPool = $RedisPool;
        $this->options['setDefer'] = $RedisPool->config['setDefer'];
        return $this;
    }

    public function instance()
    {
        return $this->RedisPool->pop();
    }

    public function put($redis)
    {
        if ($redis instanceof \Swoole\Coroutine\Redis) {
            $this->RedisPool->put($redis);
        } else {
            throw new \RuntimeException('传入的$redis不属于该连接池');
        }
    }

    public function setDefer($bool = true)
    {
        $this->options['setDefer'] = $bool;
        return $this;
    }

    protected function query($method, $args)
    {
        $chan = new \chan(1);

        go(function () use ($chan, $method, $args) {
            $redis = $this->RedisPool->pop();

            $rs = call_user_func_array([$redis, $method], $args);
            $this->put($redis);

            if ($this->options['setDefer']) {
                $chan->push($rs);
            }
        });

        if ($this->options['setDefer']) {
            return $chan->pop();
        }
    }

    public function __call($method, $args)
    {
        if ($this->RedisPool) {
            return $this->query($method, $args);
        } else {
            throw new \RuntimeException('请先执行init()函数');
        }
    }
}