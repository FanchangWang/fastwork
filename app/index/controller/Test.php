<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/4
 * Time: 15:55
 */

namespace app\index\controller;


use fastwork\facades\Config;
use fastwork\Request;

class Test
{

    public function index(Request $request)
    {
        $conf = Config::get('hass');
        return $conf;
    }

}