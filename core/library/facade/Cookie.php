<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 22:35
 */

namespace Core\facade;


use Core\Facade;

class Cookie extends Facade
{
    protected static function getFacadeClass(): string
    {
        return 'cookie';
    }
}