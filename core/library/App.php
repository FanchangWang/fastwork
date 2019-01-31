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

    /**
     * @var swoole的配置
     */
    protected $swoole_config;
    /**
     * 支持的响应事件
     * @var array
     */
    protected $event = ['Start', 'Shutdown', 'WorkerStart', 'WorkerStop', 'WorkerExit', 'Connect', 'Receive', 'WorkerError', 'ManagerStart', 'ManagerStop', 'Request'];
    /**
     * 初始化的swoole
     * @var swoole_websocket_server
     */
    public $swoole;

    private $lastMtime;


    public function __construct($appPath = '')
    {
        $this->corePath = dirname(__DIR__) . DIRECTORY_SEPARATOR;
    }

    public function initialize()
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;

        $this->rootPath = dirname($this->appPath) . DIRECTORY_SEPARATOR;
        $this->configPath = $this->rootPath . 'config' . DIRECTORY_SEPARATOR;
        $this->runtimePath = $this->rootPath . 'runtime' . DIRECTORY_SEPARATOR;
        $this->instance('app', $this);

        $this->configExt = $this->env->get('config_ext', '.php');

        $env = [
            'core_path' => $this->corePath,
            'root_path' => $this->rootPath,
            'app_path' => $this->appPath,
            'runtime_path' => $this->runtimePath,
            'config_path' => $this->configPath
        ];
        // 设置路径环境变量
        $this->env->set($env);

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
        $path = $this->appPath . $module;
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
        $this->setModulePath($path);

        $this->request->init($this->config->get('app'));
        $this->cookie->init($this->config->get('cookie'));

        if ($module) {
            // 对容器中的对象实例进行配置更新
            $this->containerConfigUpdate($module);
        }
    }

    protected function containerConfigUpdate($module)
    {
    }

    /**
     * 获取模块路径
     * @access public
     * @return string
     */
    public function getModulePath()
    {
        return $this->modulePath;
    }

    /**
     * 设置模块路径
     * @access public
     * @param  string $path 路径
     * @return void
     */
    public function setModulePath($path)
    {
        $this->modulePath = $path;
        $this->env->set('module_path', $path);
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


    /**
     * 启动
     */
    public function run()
    {
        $this->initialize();

        $this->swoole_config = $config = $this->config->pull('swoole');
        $swoole_server = isset($config['server']) && $config['server'] == 'websocket' ? 'swoole_websocket_server' : 'swoole_http_server';
        $config['ip'] = $ip = isset($config['ip']) && ip2long($config['ip']) ? $config['ip'] : '0.0.0.0';
        $config['port'] = $port = isset($config['port']) && intval($config['port']) ? $config['port'] : 9527;
        $this->swoole = new $swoole_server($ip, $port);
        $this->swoole->set($config['set']);

        // 设置回调
        foreach ($this->event as $event) {
            if (method_exists($this, 'on' . $event)) {
                $this->swoole->on($event, [$this, 'on' . $event]);
            }
        }
        if ($config['server'] == 'websocket') {
            $this->swoole->on('open', [$this, 'onWsOpen']);
            $this->swoole->on('message', [$this, 'onWsMessage']);
            $this->swoole->on('close', [$this, 'onWsClose']);
        }
        if (isset($config['set']['task_worker_num']) && $config['set']['task_worker_num'] > 0) {
            if (method_exists($this, 'onTask')) {
                $this->swoole->on('task', [$this, 'onTask']);
            }
            if (method_exists($this, 'onFinish')) {
                $this->swoole->on('finish', [$this, 'onFinish']);
            }
        }
        $this->swoole->start();
    }

    public function onStart()
    {
        date_default_timezone_set('Asia/Shanghai');
        echo "swoole is start {$this->swoole_config['ip']}:{$this->swoole_config['port']}" . PHP_EOL;
    }

    /**
     * Worker进程/Task进程启动时
     * @param \swoole_server $server
     * @param $worker_id
     */
    public function onWorkerStart(\swoole_server $server, $worker_id)
    {
        $this->lastMtime = time();
        $monitor = isset($this->swoole_config['monitor']['debug']) ? $this->swoole_config['monitor']['debug'] : false;
        if (0 == $worker_id && $monitor) {
            $this->monitor($server);
        }
    }

    /**
     * 当worker/task_worker进程发生异常
     */
    public function onWorkerError($server, $worker_id, $worker_pid, $exit_code)
    {
        $this->log->record('SWOOLE', "进程异常", "WorkerID:{$worker_id}", "WorkerPID:{$worker_pid}", "ExitCode:{$exit_code}");
    }

    /**
     * 当管理进程启动时
     * @param $serv
     */
    public function onManagerStart($serv)
    {
        echo 'swoole Manager on start' . PHP_EOL;
    }

    /**
     * request回调
     * @param $request
     * @param $response
     * @return mixed
     */
    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        $env = \Core\facade\Env::get();
        // 执行应用并响应
        $this->request->setRequest($request);
        $this->response->setRespone($response);

        $this->route->http($this->request);

        return $response->end($this->response->json(111));
    }

    public function onTask($server, $task_id, $workder_id, $data)
    {

    }

    public function onFinish($server, $task_id, $data)
    {

    }


    public function onWsOpen($server, $request)
    {

    }

    public function onWsMessage($server, $frame)
    {

    }

    public function onWsClose($server, $fd)
    {

    }

    /**
     * 文件监控
     *
     * @param $server
     */
    protected function monitor(\swoole_server $server)
    {
        $monitor = isset($this->swoole_config['monitor']) ? $this->swoole_config['monitor'] : false;
        $paths = $monitor['path'] ?: [$this->getAppPath(), $this->getConfigPath()];
        $timer = $monitor['timer'] ?: 2;

        $server->tick($timer, function () use ($paths, $server) {
            foreach ($paths as $path) {
                $dir = new \RecursiveDirectoryIterator($path);
                $iterator = new \RecursiveIteratorIterator($dir);

                foreach ($iterator as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) != 'php') {
                        continue;
                    }

                    if ($this->lastMtime < $file->getMTime()) {
                        $this->lastMtime = $file->getMTime();
                        echo '[update]' . $file . " reload...\n";
                        $server->reload();
                        return;
                    }
                }
            }
        });
    }

}