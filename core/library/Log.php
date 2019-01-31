<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/31
 * Time: 12:53
 */

namespace Core;


class Log
{
    /**
     * 配置参数
     * @var array
     */
    private static $config = [];

    private static $logs = [];

    protected $app;

    public function __construct(App $app, Config $config)
    {

        $this->app = $app;
        self::$config = $config;
    }

    public static function __make(App $app, Config $config)
    {
        $request = new static($app, $config->pull('log'));
        return $request;
    }

    /**
     * 写入日志
     * @param       $type
     * @param array $msg
     * @return bool
     */
    public function record($type, ...$msg)
    {
        $type = strtoupper($type);
        $msg = "{$type} \t " . date("Y-m-d h:i:s") . " \t " . join(" \t ", $msg);
        if (!in_array($type, self::$config['level'])) return false;
        self::$logs[$type][] = $msg;
    }

    /**
     * swoole异步写入日志信息
     * @return bool
     */
    public function save()
    {
        if (empty(self::$logs)) return false;
        $path = $this->app['env']->get('runtime_path');
        foreach (self::$logs as $type => $logs) {
            $dir_path = $path . '/log/' . date('Ymd') . DIRECTORY_SEPARATOR;
            !is_dir($dir_path) && mkdir($dir_path, 0777, TRUE);
            $filename = date("H") . '.' . $type . '.log';
            $content = NULL;
            foreach ($logs as $log) {
                $content .= $log . PHP_EOL;
            }
            swoole_async_writefile($dir_path . $filename, $content, NULL, FILE_APPEND);
        }
        self::$logs = [];
        return true;
    }

    /**
     * 记录日志信息
     * @access public
     * @param  string $level 日志级别
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function log($level, $message)
    {
        $this->record($level, $message);
    }

    /**
     * 记录emergency信息
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function emergency($message)
    {
        $this->log(__FUNCTION__, $message);
    }

    /**
     * 记录警报信息
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function alert($message)
    {
        $this->log(__FUNCTION__, $message);
    }

    /**
     * 记录紧急情况
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function critical($message)
    {
        $this->log(__FUNCTION__, $message);
    }

    /**
     * 记录错误信息
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function error($message)
    {
        $this->log(__FUNCTION__, $message);
    }

    /**
     * 记录warning信息
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function warning($message)
    {
        $this->log(__FUNCTION__, $message);
    }

    /**
     * 记录notice信息
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function notice($message)
    {
        $this->log(__FUNCTION__, $message);
    }

    /**
     * 记录一般信息
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function info($message)
    {
        $this->log(__FUNCTION__, $message);
    }

    /**
     * 记录调试信息
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function debug($message)
    {
        $this->log(__FUNCTION__, $message);
    }

    /**
     * 记录sql信息
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function sql($message)
    {
        $this->log(__FUNCTION__, $message);
    }

}