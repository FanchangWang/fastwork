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
    /**
     * 初始化
     * @var bool
     */
    protected $initialized = false;

    public function __construct($appPath = '')
    {
        $this->corePath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
        $this->path($appPath);
    }

    public function initialize()
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;

        $this->rootPath = dirname($this->appPath) . DIRECTORY_SEPARATOR;
        $this->configPath = $this->rootPath . 'config' . DIRECTORY_SEPARATOR;

        $this->instance('app', $this);

        $this->configExt = $this->env->get('config_ext', '.php');

        // 设置路径环境变量
        $this->env->set([
            'core_path' => $this->corePath,
            'root_path' => $this->rootPath,
            'app_path' => $this->appPath,
            'config_path' => $this->configPath
        ]);

        // 加载环境变量配置文件
        if (is_file($this->rootPath . '.env')) {
            $this->env->load($this->rootPath . '.env');
        }

        $this->namespace = $this->env->get('app_namespace', $this->namespace);
        $this->env->set('app_namespace', $this->namespace);
        // 初始化应用
        $this->init();

    }

    /**
     * 初始化应用或模块
     * @access public
     * @param  string $module 模块名
     * @return void
     */
    public function init($module = '')
    {
        // 定位模块目录
        $module = $module ? $module . DIRECTORY_SEPARATOR : '';
        $path   = $this->appPath . $module;
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