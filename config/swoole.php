<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 12:53
 */
return [
    // 扩展自身配置
    'host' => '0.0.0.0', // 监听地址
    'port' => 8089, // 监听端口
    'mode' => '', // 运行模式 默认为SWOOLE_PROCESS
    'sock_type' => '', // sock type 默认为SWOOLE_SOCK_TCP
    'server_type' => 'websocket',
    'app_path' => Env::get('app_path'), // 应用地址 如果开启了 'daemonize'=>true 必须设置（使用绝对路径）
    'file_monitor' => false, // 是否开启PHP文件更改监控（调试模式下自动开启）
    'file_monitor_interval' => 2, // 文件变化监控检测时间间隔（秒）
    'file_monitor_path' => [
        Env::get('app_path'),
        Env::get('config_path'),
        Env::get('extend_path'),
    ], // 文件监控目录 默认监控application和config目录
    'daemonize' => 0,
    // 可以支持swoole的所有配置参数
    'pid_file' => Env::get('runtime_path') . 'swoole.pid',
    'log_file' => Env::get('runtime_path') . 'swoole.log',
    'document_root' => Env::get('root_path') . 'public',
    'enable_static_handler' => true,
    'timer' => true,//是否开启系统定时器
    'interval' => 1000,//系统定时器 时间间隔
    'worker_num' => 2,    //worker process num
    'task_worker_num' => 4,//swoole 任务工作进程数量
    'max_request' => 10000,
    //websocket心跳配置
    'heartbeat_check_interval' => 10,
    'heartbeat_idle_time' => 60,
];
