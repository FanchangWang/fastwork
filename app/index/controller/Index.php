<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/2
 * Time: 9:19
 */

namespace app\index\controller;


use fastwork\Controller;
use fastwork\facades\Config;

class Index extends Controller
{

    public function index()
    {
        Config::set('hass',11);
        return $this->success(1111);
    }
}