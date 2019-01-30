<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 13:08
 */

namespace Core;


class Config
{
    /**
     * 配置参数
     * @var array
     */
    protected static $config = [];

    /**
     * 配置文件目录
     * @var string
     */
    protected $path;

    /**
     * 配置文件后缀
     * @var string
     */
    protected $ext = '.php';

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function setExt($ext = '.php')
    {
        $this->ext = $ext;
    }

    /**
     * 获取配置参数
     * @param string $keys 参数名 格式：文件名.参数名1.参数名2....
     * @param null $default 错误默认返回值
     *
     * @return mixed|null
     */
    public function get($keys, $default = NULL)
    {

        $keys = array_filter(explode('.', strtolower($keys)));
        if (empty($keys)) return NULL;
        $file = array_shift($keys);
        $config = $this->pull($file);
        while ($keys) {
            $key = array_shift($keys);
            if (!isset($config[$key])) {
                $config = $default;
                break;
            }
            $config = $config[$key];
        }

        return $config;
    }

    /**
     * 获取一级配置
     * @access public
     * @param  string $name 一级配置名
     * @return array
     */
    public function pull($name)
    {
        $file = strtolower($name);
        $path = $this->path . $file . $this->ext;
        if (!is_file($path)) {
            return NULL;
        }
        self::$config[$file] = require $path;

        return self::$config[$file];
    }

}