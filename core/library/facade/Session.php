<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 15:57
 */

namespace Core\facade;


use Core\Facade;

class Session extends Facade
{
    protected static function getFacadeClass(): string
    {
        return 'session';
    }
}