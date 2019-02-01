<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/1
 * Time: 19:04
 */

namespace fastwork;


class Fastwork extends Container
{

    /**
     * 配置的文件目录
     * @var
     */
    protected $configPath;

    /**应用目录
     * @var
     */
    protected $app_path;

    /**
     * 初始化
     * @var bool
     */
    protected $initialized = false;
    /**
     * 命名空间
     * @var string
     */
    private $namespace = 'fastwork';

    private $configExt = '.php';

    public function initialize()
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;
        $corePath = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        $rootPath = dirname($this->app_path) . DIRECTORY_SEPARATOR;
        $configPath = $rootPath . 'config' . DIRECTORY_SEPARATOR;
        $runtimePath = $rootPath . 'runtime' . DIRECTORY_SEPARATOR;
        static::setInstance($this);
        $this->instance('fastwork', $this);
        $env = [
            'core_path' => $corePath,
            'root_path' => $rootPath,
            'app_path' => $this->getAppPath(),
            'runtime_path' => $runtimePath,
            'config_path' => $configPath
        ];
        // 设置路径环境变量
        $this->env->set($env);
        // 加载环境变量配置文件
        if (is_file($rootPath . '.env')) {
            $this->env->load($rootPath . '.env');
        }
        $namespace = $this->env->get('app_namespace', $this->namespace);
        $this->env->set('app_namespace', $namespace);
        $this->setConfigPath($configPath);
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
        $path = $this->app_path . $module;
        // 加载公共文件
        if (is_file($path . 'common.php')) {
            include_once $path . 'common.php';
        }
        // 自动读取配置文件
        if (is_dir($path . 'config')) {
            $dir = $path . 'config' . DIRECTORY_SEPARATOR;
        } elseif (is_dir($this->configPath . $module)) {
            $dir = $this->configPath . $module;
        }
        $files = isset($dir) ? scandir($dir) : [];
        foreach ($files as $file) {
            if ('.' . pathinfo($file, PATHINFO_EXTENSION) === $this->configExt) {
                $this->config->load($dir . $file, pathinfo($file, PATHINFO_FILENAME));
            }
        }
    }


    /**
     * 启动swoole
     */
    public function run()
    {
        $this->initialize();
        $config = $this->config->pull('swoole');
        $swoole_server = isset($config['server']) && $config['server'] == 'websocket' ? 'swoole_websocket_server' : 'swoole_http_server';
        $config['ip'] = $ip = isset($config['ip']) && ip2long($config['ip']) ? $config['ip'] : '0.0.0.0';
        $config['port'] = $port = isset($config['port']) && intval($config['port']) ? $config['port'] : 9527;

        $protecl = isset($config['server']) && $config['server'] == 'websocket' ? 'WsHttpServer' : 'HttpServer';
        $class = "\\{$this->namespace}\\swoole\\{$protecl}";

        if (!class_exists($class)) {
            var_dump("class not exits:" . $class);
            return;
        }

        $swoole = new $swoole_server($ip, $port);
        $swoole->set($config['set']);

        $rf = new \ReflectionClass($class);
        $methods = $rf->getMethods(\ReflectionMethod::IS_PUBLIC);

        $call = [];
        foreach ($methods as $method) {
            if (strpos($method->class, "{$this->namespace}\\swoole\\") === 0) {
                if (substr($method->name, 0, 2) == 'on') {
                    $call[strtolower(substr($method->name, 2))] = $method->name;
                }
            }
        }
        $obj = new $class($swoole, $config);
        foreach ($call as $e => $f) {
            $swoole->on($e, [$obj, $f]);
        }
        $swoole->start();
    }

    /**
     * @param mixed $app_path
     * @return Fastwork
     */
    public function setAppPath($app_path): Fastwork
    {
        $this->app_path = $app_path;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAppPath()
    {
        return $this->app_path;
    }

    /**
     * @param mixed $configPath
     */
    public function setConfigPath($configPath): void
    {
        $this->configPath = $configPath;
    }

    /**
     * @return mixed
     */
    public function getConfigPath()
    {
        return $this->configPath;
    }

}