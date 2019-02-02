<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/1
 * Time: 19:06
 */

namespace fastwork;

use fastwork\facades\Cookie as FastCookie;

class Session
{
    /**
     * 配置参数
     * @var array
     */
    protected $config = [];

    /**
     * 前缀
     * @var string
     */
    protected $prefix = '';

    public $sessionName = 'sessid';

    /**
     * Session有效期
     * @var int
     */
    protected $expire = 0;
    /**
     * 是否初始化
     * @var bool
     */
    protected $init = null;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * 反射自动注入
     * @param Config $config
     * @return Session
     */
    public static function __make(Config $config)
    {
        return new static($config->pull('session'));
    }

    /**
     * session初始化
     * @access public
     * @param  array $config
     * @return void
     * @throws \think\Exception
     */
    public function init(array $config = [])
    {
        $config = $config ?: $this->config;

        if (!empty($config['name'])) {
            $this->sessionName = $config['name'];
        }

        if (!empty($config['expire'])) {
            $this->expire = $config['expire'];
        }

        if (!empty($config['auto_start'])) {
            $this->start();
        } else {
            $this->init = false;
        }

        return $this;
    }

    /**
     * 启动session
     * @access public
     * @return void
     */
    public function start()
    {
        $sessionId = $this->getId();
        if (!$sessionId) {
            $this->regenerate();
        }
        $this->init = true;
    }

    /**
     * 获取session_id
     * @access public
     * @return string
     */
    public function getId()
    {
        return FastCookie::get($this->sessionName) ?: '';
    }

    /**
     * session_id设置
     * @access public
     * @param  string $id session_id
     * @param  int $expire Session有效期
     * @return void
     */
    public function setId($id, $expire = null)
    {
        FastCookie::set($this->sessionName, $id, $expire);
    }

    /**
     * 设置或者获取session作用域（前缀）
     * @access public
     * @param  string $prefix
     * @return string|void
     */
    public function prefix($prefix = '')
    {
        empty($this->init) && $this->boot();

        if (empty($prefix) && null !== $prefix) {
            return $this->prefix;
        } else {
            $this->prefix = $prefix;
        }
    }
    /**
     * session自动启动或者初始化
     * @access public
     * @return void
     */
    public function boot()
    {
        if (is_null($this->init)) {
            $this->init();
        }

        if (false === $this->init) {
            $this->start();
        }
    }
    /**
     * 生成session_id
     * @access public
     * @param  bool $delete 是否删除关联会话文件
     * @return string
     */
    public function regenerate($delete = false)
    {
        if ($delete) {
            $this->destroy();
        }

        $sessionId = sha1(microtime(true) . uniqid());

        $this->setId($sessionId);

        return $sessionId;
    }
    /**
     * 销毁session
     * @access public
     * @return void
     */
    public function destroy()
    {
        $sessionId = $this->getId();

        if ($sessionId) {
            $this->destroySession($sessionId);
        }

        $this->init = null;
    }
    /**
     * 销毁session
     * @access protected
     * @param  string $sessionId session_id
     * @return void
     */
    protected function destroySession($sessionId)
    {
        Cache::rm('sess_' . $sessionId);
    }
    public function name($name)
    {
        $this->sessionName = $name;
    }

}