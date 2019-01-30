<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 13:43
 */

namespace Core\facade;

use Core\Facade;

class Cache extends Facade
{

    protected static function getFacadeClass(): string
    {
        return 'cache';
    }
}