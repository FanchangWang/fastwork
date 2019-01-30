<?php

namespace Core;


class App extends Container
{
    /**
     * 应用类库命名空间
     * @var string
     */
    protected $namespace = 'App';
    /**
     * 应用类库目录
     * @var string
     */
    protected $appPath;
    /**
     * core内核路径
     * @var string
     */
    protected $corePath;
    /**
     * 应用根目录
     * @var string
     */
    protected $rootPath;

    /**
     * 运行时目录
     * @var string
     */
    protected $runtimePath;

    /**
     * 配置目录
     * @var string
     */
    protected $configPath;
    /**
     * 配置后缀
     * @var string
     */
    protected $configExt;


    public function __construct($appPath = '')
    {
        $this->corePath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
        $this->path($appPath);
    }

    public function initialize()
    {

    }

    /**
     * 获取应用配置目录
     * @access public
     * @return string
     */
    public function getConfigPath()
    {
        return $this->configPath;
    }

    /**
     * 获取配置后缀
     * @access public
     * @return string
     */
    public function getConfigExt()
    {
        return $this->configExt;
    }

    /**
     * 设置应用类库目录
     * @access public
     * @param  string $path 路径
     * @return $this
     */
    public function path($path)
    {
        $this->appPath = realpath($path) . DIRECTORY_SEPARATOR;

        return $this;
    }

    /**
     * 获取应用类库目录
     * @access public
     * @return string
     */
    public function getAppPath()
    {
        return $this->appPath;
    }


}