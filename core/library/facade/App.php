<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 14:21
 */

namespace Core\facade;


use Core\Facade;

class App extends Facade
{

    protected static function getFacadeClass(): string
    {
        return 'app';
    }
}