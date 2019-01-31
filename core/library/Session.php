<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 12:41
 */

namespace Core;

use Core\facade\Cookie as cookieFacade;

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
    /**
     * 记录Session name
     * @var string
     */
    protected $sessionName = 'sessid';

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

    /**
     * 锁驱动
     * @var object
     */
    protected $lockDriver = null;

    /**
     * 锁key
     * @var string
     */
    protected $sessKey = 'session';

    public function __construct(array $config = [])
    {
        $this->config = $config;
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
        return cookieFacade::get($this->sessionName) ?: '';
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
        cookieFacade::set($this->sessionName, $id, $expire);
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

        $sessionId = md5(microtime(true) . uniqid());

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

}