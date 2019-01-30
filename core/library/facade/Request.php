<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 15:49
 */

namespace Core\facade;


use Core\Facade;

class Request extends Facade
{
    protected static function getFacadeClass(): string
    {
        return 'request';
    }
}