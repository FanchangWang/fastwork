<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/31
 * Time: 14:59
 */

namespace Traits;


use Swoole\Coroutine\Channel;

trait Pools
{
    /**
     * 连接池
     * @var \Swoole\Coroutine\Channel
     */
    protected $pool = [];

    //池状态
    protected $available = true;

    //新建时间
    protected $addPoolTime = '';

    //入池时间
    protected $pushTime = 0;
    /**
     * 配置文件
     * @var array
     */
    public $config = [];

    public function make($config)
    {
        $this->config = array_merge($this->config, $config);
        $this->pool = new Channel(($this->config['poolMax']));
        return $this;
    }

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
        //有空闲连接且连接池处于可用状态
        if ($this->pool->length() > 0) {
            $server = $this->pool->pop();
        } else {
            if (!method_exists('createPool')) {
                throw  new \RuntimeException('createPool Method Not Found');
            }
            $server = $this->createPool();
            $this->addPoolTime = time();
        }

        if ($server->connected === true) {
            return $server;
        } else {
            if ($re_i <= $this->config['reconnect']) {
                $server->close();
                unset($server);
                goto back;
            }
        }
    }

    /**
     * @定时器
     *
     * @param $server
     */
    public function clearTimer($server)
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


}