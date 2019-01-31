<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 12:53
 */

use Core\facade\Env;

return [
    //server设置
    'ip' => '0.0.0.0',   //监听IP
    'port' => 9501,        //监听端口
    'server' => 'http',     //服务，可选 websocket 默认http
    'app_path' => Env::get('app_path'), // 应用地址 如果开启了 'daemonize'=>true 必须设置（使用绝对路径）

    'set' => [            //配置参数  请查看  https://wiki.swoole.com/wiki/page/274.html
        'daemonize' => 0,
//        'enable_static_handler' => true,
//        'document_root' => Env::get('root_path') . 'public',
        'worker_num' => 2,
        'max_request' => 10000,
        'task_worker_num' => 4,
        'reload_async' => true,
    ],
    'monitor' => [
        'timer' => 3000,  //定时器间隔时间，单位毫秒
        'debug' => true,       //重启
        'path' => [
            Env::get('app_path'),
            Env::get('config_path'),
        ]
    ],
    // 可以支持swoole的所有配置参数
    'pid_file' => Env::get('runtime_path') . 'swoole.pid'

];
