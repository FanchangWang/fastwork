<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 15:26
 */

namespace Core\facade;


use Core\Facade;

/**
 * @see \Core\Config
 * @mixin \Core\Config
 * @method bool has(string $name) static 检测配置是否存在
 * @method mixed get(string $name = null, mixed $default = null) static 获取环境变量值
 * @method mixed pull(string $name = null) static 拉取当个文件里所有配置
 */
class Config extends Facade
{

    protected static function getFacadeClass(): string
    {
        return 'config';
    }
}